<?php
/**
 * @file
 * Databse handling
 */

class DB {
  private static $qCounter = 0;

  private static $history = array();

  private static $db;

  /**
   * Initialize.
   */
  public static function initialize($host, $user, $pass, $db) {
    // Connect to the database.
    self::$db = new mysqli($host, $user, $pass, $db);

    // Make sure the server uses utf8.
    // This is equivalent with the "SET NAMES utf8" sql commando.
    self::$db->set_charset('utf8');
  }

  /**
   * Wraps the insert_id variable.
   */
  public static function insert_id() {
    return self::$db->insert_id;
  }

  /**
   * Wraps the affected_rows variable.
   */
  public static function affected_rows() {
    return self::$db->affected_rows;
  }

  /**
   * Wraps the errno variable.
   */
  public static function errno() {
    return self::$db->errno;
  }

  /**
   * Get the complete history list, or a single entry.
   *
   * If you call this function without any arguments, it will return the latest
   * entry in the history list.
   *
   * @param int $pos
   *   The position of the history entry you want. In this case the last entry
   *   of the list is 1 (which is default). If you set this to 0 you'll get the
   *   complete list.
   */
  public static function getHistory($pos = 1) {
    if ($pos == 0) {
      return self::$history;
    }

    return self::$history[self::$qCounter - $pos];
  }


  /**
   * Insert the arguments into the SQL.
   *
   * This will automatically escape them, depending on their prefix. Read more
   * about the prefixes in the parseArgument() function.
   *
   * @param string $sql
   *   The SQL query.
   * @param array $args
   *   The associated array with the key being the needle, and the value being
   *   the replacement.
   *
   * @return string
   *   The complete SQL query with all it's needles parsed and replaced.
   *
   * @see parseArgument()
   */
  private static function parseSql($sql, $args) {
    // Run through the arguments, and parse them through our cleaner.
    foreach ($args as $key => $value) {
      $args[$key] = self::parseArgument($key, $value);
    }

    return strtr($sql, $args);
  }

  /**
   * Depending on the key prefix given, this will clean up the argument.
   *
   * @param string $key
   *   The key of, which have a specific prefix.
   * @param string $argument
   *   The value.
   */
  private static function parseArgument($key, $argument) {
    switch ($key[0]) {
      case '@':
        return self::$db->real_escape_string($argument);

      case '%':
        return (int) $argument;

      case '!':
        return $argument;

      case '?':
        foreach ($argument as $sub_key => $value) {
          $argument[$sub_key] = self::parseArgument(substr($key, 1), $value);
        }
        return implode(', ', $argument);
    }

    return NULL;
  }

  /**
   * Execute an SQL query.
   *
   * @param string $sql
   *   The SQL query.
   * @param array $args
   *   The associated needle => replacement array. See the the parseSQL() and
   *   parseArgument() functions.
   * @param bool $multi_query
   *   If set to true it'll use the multi_query() function instead of the
   *   normal query() function.
   *
   * @return mixed
   *   Depending on what kind of SQL query you're executing, it will reutn
   *   a mysqli_result class (for SELECTS) and boolean (for UPDATE, DELETE and
   *   any multi query).
   */
  public static function q($sql, $args = NULL, $multi_query = FALSE) {
    $sqlString = $sql;
    if ($args !== NULL) {
      $sqlString = self::parseSql($sql, $args);
    }

    $startTime = microtime(TRUE);
    if (!$multi_query) {
      $result = self::$db->query($sqlString);
    }
    else {
      $result = self::$db->multi_query($sqlString);
    }

    self::$history[self::$qCounter] = array(
      'sql' => $sql,
      'args' => $args,
      'sqlString' => $sqlString,
      'time' => microtime(TRUE) - $startTime,
    );

    if (!$result) {
      $backtrace = debug_backtrace();
      self::$history[self::$qCounter]['error'] = array(
        'number' => self::$db->errno,
        'message' => self::$db->error,
        'backtrace' => array(
          'function' => $backtrace[1]['function'],
          'line' => $backtrace[0]['line'],
          'arguments' => $backtrace[1]['args'],
        ),
      );
    }
    self::$qCounter++;

    return $result;
  }

  /**
   * If you've used multiple queries, this will get the next result.
   *
   * You need to call this to use the first result as well after you've called
   * the q() function with the multi_query argument true.
   */
  public static function getNextResult() {
    $result = self::$db->store_result();
    self::$db->next_result();
    return $result;
  }

  /**
   * Clean the result list..
   */
  public static function clearResults() {
    if (!self::$db->more_results()) {
      return;
    }

    do {
      // By using use_result() instead of store_result() we save alot of
      // memory.
      $result = self::$db->use_result();
      if ($result instanceof mysqli_result) {
        $result->free();
      }
    } while (self::$db->next_result());
  }
}

// Always initialize the database, when including this file.
// If the database connection is not used, it's a tiny overhead.
DB::initialize(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
