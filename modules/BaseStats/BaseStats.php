<?php

/**
 * @file
 * Function stats.
 */

class BaseStats extends Module {
  public $version = 1;

  private $memcache_prefix = 'openlistbasestats_';
  protected $interval = 'm_d_H';

  /**
   * Get the events.
   */
  public function getEvents() {
    return array(
      'links' => 'links',
      'pre_method_call' => 'call',
    );
  }

  /**
   * Get the count.
   */
  public function getFunctionCalls() {
    if (class_exists('Memcached')) {
      $mc = new \Memcached();
      $mc->setOption(\Memcached::OPT_BINARY_PROTOCOL, TRUE);
      $mc->addServer('localhost', 11211);

      $stats = array();

      $ip_list = $mc->get($this->memcache_prefix . '__IP_list');
      if ($ip_list === FALSE) {
        $ip_list = array();
      }
      $function_list = $mc->get($this->memcache_prefix . '__Function_list');
      if ($function_list === FALSE) {
        $function_list = array();
      }

      foreach ($ip_list as $ip) {
        $stats[$ip] = array('global' => array());
        foreach ($function_list as $function) {
          $key = implode('__', array($this->memcache_prefix, $ip, $function));
          if ($count = $mc->get($key)) {
            $stats[$ip]['global'][$function] = $count;
          }
          for ($i = 0; $i < 12; $i++) {
            $hour = date($this->interval, mktime(date("H") - $i));
            if ($count = $mc->get($key . '__' . $hour)) {
              if (!isset($stats[$ip][$hour])) {
                $stats[$ip][$hour] = array();
              }
              $stats[$ip][$hour][$function] = $count;
            }
            else {
              break;
            }
          }
        }
      }

      foreach ($stats as &$ip) {
        foreach ($ip as &$item) {
          arsort($item, SORT_DESC);
        }
      }

      return $stats;
    }
  }

  /**
   * Get the total usage.
   */
  public function getTotalUsage($library = FALSE) {
    $sql = '
SELECT l.type, COUNT(DISTINCT l.list_id) AS lists, COUNT(DISTINCT e.element_id) AS elements
FROM
  lists l
  JOIN elements e ON (e.list_id = l.list_id)
WHERE l.type != "user_loan_history"
GROUP BY l.type
ORDER BY l.type
';

    if ($library !== FALSE) {
      $sql .= ' AND l.library_code = "@library"';
    }

    $result = DB::q($sql, array(
      '@library' => $library,
    ));

    $lists = array();

    if ($result) {
      while ($row = $result->fetch_assoc()) {
        $lists[$row['type']] = array(
          'lists' => $row['lists'],
          'elements' => $row['elements'],
        );
      }
    }

    return $lists;
  }

  /**
   * Get the monthly usage.
   */
  public function getMonthlyUsage($library = FALSE) {
    $years = array();

    $sql = '
SELECT l.type, YEAR(l.created) AS year, MONTH(l.created) AS month, COUNT(DISTINCT l.list_id) AS cnt
FROM lists l
JOIN elements e ON ( e.list_id = l.list_id )
WHERE l.type != "user_loan_history"
GROUP BY YEAR(l.created) , MONTH(l.created), l.type
ORDER BY YEAR(l.created), MONTH(l.created), l.type
';

    if ($library !== FALSE) {
      $sql .= ' AND l.library_code = "@library"';
    }

    $result = DB::q($sql, array(
      '@library' => $library,
    ));

    if ($result) {
      while ($row = $result->fetch_assoc()) {
        if (!isset($years[$row['year']])) {
          $years[$row['year']] = array();
        }
        if (!isset($years[$row['year']][$row['month']])) {
          $years[$row['year']][$row['month']] = array();
        }

        $years[$row['year']][$row['month']][$row['type']]['lists'] = $row['cnt'];
      }
    }

    $sql = '
SELECT l.type, YEAR(e.created) AS year, MONTH(e.created) AS month, COUNT(DISTINCT e.list_id) AS cnt
FROM lists l
JOIN elements e ON ( e.list_id = l.list_id )
WHERE l.type != "user_loan_history"
GROUP BY YEAR(e.created) , MONTH(e.created), l.type
ORDER BY YEAR(e.created), MONTH(e.created), l.type
';

    if ($library !== FALSE) {
      $sql .= ' AND l.library_code = "@library"';
    }

    $result = DB::q($sql, array(
      '@library' => $library,
    ));

    if ($result) {
      while ($row = $result->fetch_assoc()) {
        if (!isset($years[$row['year']])) {
          $years[$row['year']] = array();
        }
        if (!isset($years[$row['year']][$row['month']])) {
          $years[$row['year']][$row['month']] = array();
        }

        $years[$row['year']][$row['month']][$row['type']]['elements'] = $row['cnt'];
      }
    }

    return $years;
  }

  /**
   * Page links.
   */
  protected function links() {
    return array(
      __CLASS__ => __DIR__ . '/pages/index.html',
      __CLASS__ . '/functions' => __DIR__ . '/pages/functions.html',
    );
  }

  /**
   * When a method is called.
   */
  protected function call($method, $args) {
    if (class_exists('Memcached')) {
      $mc = new \Memcached();
      // Increment is only supported when using BINARY_PROTOCOL.
      $mc->setOption(\Memcached::OPT_BINARY_PROTOCOL, TRUE);
      $mc->addServer('localhost', 11211);

      $service = 'openlist';
      $method = $method;

      if ($method === 'callModule') {
        $service = $args[0];
        $method = $args[1];
      }

      $ip_list = $mc->get($this->memcache_prefix . '__IP_list');
      if ($ip_list === FALSE) {
        $ip_list = array();
      }

      // Set IP list if IP missing.
      if (!in_array($_SERVER['REMOTE_ADDR'], $ip_list)) {
        $ip_list[] = $_SERVER['REMOTE_ADDR'];
        $mc->set($this->memcache_prefix . '__IP_list', $ip_list);
      }

      // Get function list.
      $function_list = $mc->get($this->memcache_prefix . '__Function_list');
      if ($function_list === FALSE) {
        $function_list = array();
      }

      // Set IP list if IP missing.
      if (!in_array($service . '__' . $method, $function_list)) {
        $function_list[] = $service . '__' . $method;
        $mc->set($this->memcache_prefix . '__Function_list', $function_list);
      }

      // Create key.
      $key = implode('__', array(
        $this->memcache_prefix,
        $_SERVER['REMOTE_ADDR'],
        $service,
        $method,
      ));

      // Set global.
      if ($mc->get($key) === FALSE) {
        $mc->set($key, 0);
      }
      $mc->increment($key);

      // Set current hour.
      $key .= '__' . date($this->interval);
      if ($mc->get($key) === FALSE) {
        $mc->set($key, 0);
      }
      $mc->increment($key, 1, 0, mktime(date('H'), 0, 0, date('n'), date('j') + 1));
    }
  }
}

new BaseStats();
