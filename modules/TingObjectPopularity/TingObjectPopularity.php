<?php

// PROOF OF CONCEPT MODULE.
class TingObjectPopularity extends Module
{
  public $version = 1;
  
  /**
   * The table.
   */
  private $table = 'm_tingobject_popularity';

  /**
   * Abstract getEvents().
   */
  public function getEvents() {
    return array(
      'createElement' => 'onElementCreated',
      'cron' => 'cron',
    );
  }
  
  /**
   * A function which can be called through the SOAP interface (notice it's
   * public). It simply gets the popularity of an object.
   */
  public function getPopularity($object_id) {
    $result = DB::q('
SELECT popularity
FROM !table
WHERE
  object_id = "@object_id"
    ', array(
      '!table' => $this->table,
      '@object_id' => $object_id
    ));
    
    return $result->fetch_assoc();
  }

  /**
   * When an element is created, increment it's popularity.
   */
  protected function onElementCreated($element_id, $list_id, $data) {
    // We only want to update the popularity if the element is a ting_object.
    if ($data['type'] == 'ting_object') {
      DB::q('
INSERT INTO !table
(object_id, popularity, modified)
VALUES ("@object_id", 1, UNIX_TIMESTAMP())
  ON DUPLICATE KEY UPDATE
    popularity = popularity + 1, modified = UNIX_TIMESTAMP()
      ', array(
        '!table' => $this->table,
        '@object_id' => $data['id']
      ));
      
      return TRUE;
    }
    
    return FALSE;
  }
  
  /**
   * Decrease the popularity.
   */
  protected function cron($arg) {
    // This cron only triggers on an "hour" cron.
    if (in_array('hour', $arg)) {
      DB::q('
UPDATE !table
SET popularity = popularity * 0.99
      ', array('!table' => $this->table));
      
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Create the module table on install.
   */
  protected function _install() {
    DB::q('
CREATE TABLE IF NOT EXISTS !table (
  object_id char(20) NOT NULL,
  popularity decimal(10,2) NOT NULL,
  modified int(11) NOT NULL,
  PRIMARY KEY (object_id)
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

new TingObjectPopularity();