<?php

/**
 *
 */
class IPAccess extends Module
{ 
  public $version = 1;
  
  private $ipaccess = array(
    '82.103.132.180' => 'server003',
    '94.18.223.2' => 'b14 local ip'
  );
  
  /**
   * The table.
   */
  private $table = 'm_ip_access';

  /**
   * Abstract getEvents().
   */
  public function getEvents() {
    return array(
      'boot' => 'onBoot',
    );
  }
  
  /**
   *
   */
  public function clearList() {
    $result = DB::q('DELETE FROM !table', array(
      '!table' => $this->table
    ));
    
    if ($result) {
      return DB::affected_rows();
    } else {
      return FALSE;
    }
  }
  
  /**
   * 
   */
  protected function onBoot() {
    if (!isset($this->ipaccess[$_SERVER['REMOTE_ADDR']])) {
      DB::q('
INSERT INTO !table
(ip, calls)
VALUES ("@ip", 1)
  ON DUPLICATE KEY UPDATE
    calls = calls + 1
      ', array(
        '!table' => $this->table,
        '@ip' => $_SERVER['REMOTE_ADDR']
      ));
      
      header('HTTP/1.1 403 Forbidden');
      exit();
    }

    return TRUE;
  }
  
  /**
   * Create the module table on install.
   */
  protected function _install() {
    DB::q('
CREATE TABLE IF NOT EXISTS !table (
  ip char(20) NOT NULL,
  calls int(11) NOT NULL,
  ts TIMESTAMP,
  PRIMARY KEY (ip)
) ENGINE = InnoDB
    ', array('!table' => $this->table));

    return TRUE;
  }
  
  /**
   * Remove the module table on uninstall.
   */
  protected function _uninstall() {
    DB::q('DROP TABLE IF EXISTS !table', array('!table' => $this->table));
    
    return TRUE;
  }
}

new IPAccess();