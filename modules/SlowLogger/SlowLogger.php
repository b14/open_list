<?php

/**
 * @file
 * This module logs slow requests.
 *
 * This module checks the time it takes for the a to process a request.
 * and if this time exceeds X amount of seconds it will log this request to
 * the SlowLogger table.
 *
 * It's important to notice that this module only meassures the actual
 * processing of a request. So any time spent setting up the SOAP server
 * or sending the response back to the client is ignored. It starts timing
 * right before the OpenList class is called, and stops timing right after
 * the OpenList class is done.
 */

class SlowLogger extends Module {
  public $version = 1;

  /**
   * How many seconds is a slow request.
   */
  public $slow_time = 1.25;


  /**
   * Holds the start time of the request.
   */
  private $start_time = 0;

  /**
   * Holds teh arguments of the request.
   */
  private $args = array();

  /**
   * Name of the SlowLogger table.
   */
  private $table = 'm_slow_logger';

  /**
   * Abstract getEvents().
   */
  public function getEvents() {
    return array(
      'pre_method_call' => 'startLog',
      'post_method_call' => 'endLog',
    );
  }

  /**
   * Before a method is called, this will log the time and arguments.
   */
  protected function startLog($method, &$args) {
    $this->start_time = microtime(TRUE);
    $this->args = $args;
  }

  /**
   * After the method has been processed.
   *
   * This checks the execution time, and if it exceeds the $slow_time, it will
   * log it.
   */
  protected function endLog($method, &$result) {
    $execution_time = microtime(TRUE) - $this->start_time;

    if ($execution_time > $this->slow_time) {
      DB::q('
INSERT INTO !table
(execution, method, arguments)
VALUES (!execution_time, "@method", "@arguments")
      ', array(
        '!table' => $this->table,
        '!execution_time' => $execution_time,
        '@method' => $method,
        '@arguments' => serialize($this->args),
      ));
    }
  }

  /**
   * Create the SlowLogger table.
   */
  protected function _install() {
    DB::q('
CREATE TABLE IF NOT EXISTS !table (
  id int(11) NOT NULL AUTO_INCREMENT,
  stamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  execution decimal(10,7) NOT NULL,
  method char(255) NOT NULL,
  arguments text NOT NULL,
  caller char(255) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
    ', array('!table' => $this->table));

    return TRUE;
  }

  /**
   * Drop the SlowLogger table.
   */
  protected function _uninstall() {
    DB::q('DROP TABLE IF EXISTS !table', array('!table' => $this->table));

    return TRUE;
  }
}

new SlowLogger();
