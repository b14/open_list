<?php
// PROOF OF CONCEPT MODULE.
class Counter extends Module
{
  public $version = 1;

  public function getEvents() {
    return array(
      'createElement' => 'increment',
      'deleteElement' => 'decrement',
      'cron' => 'cron'
    );
  }

  protected function increment($element_id, $list_id, $data) {
    DB::q('
INSERT INTO m_counter
(list_id, elements)
VALUES (%list_id, 1)
ON DUPLICATE KEY UPDATE elements = elements + 1
    ', array('%list_id' => $list_id));
    
    return TRUE;
  }

  protected function decrement($element_id) {
    DB::q('
UPDATE m_counter mc, elements e
SET mc.elements = mc.elements - 1
WHERE
  element_id IN (?%element_id)
  AND mc.list_id = e.list_id
    ', array('?%element_id' => $element_id));
    
    return TRUE;
  }

  protected function cron($cron) {
    if (in_array('hour', $cron)) {
      DB::q('
TRUNCATE m_counter;
INSERT INTO m_counter
  SELECT list_id, COUNT(element_id) as elements
  FROM elements
  WHERE status > 0
  GROUP BY list_id;
      ',
      null,
      true);
      
      DB::clearResults();
      
      return TRUE;
    }
    return FALSE;
  }

  protected function _install() {
    DB::q('
CREATE TABLE IF NOT EXISTS m_counter (
  list_id int(11) NOT NULL,
  elements int(11) NOT NULL,
  PRIMARY KEY (list_id)
) ENGINE = InnoDB
    ');

    $this->cron(array('hour'));
    
    return TRUE;
  }
  protected function _uninstall() {
    DB::q('DROP TABLE IF EXISTS m_counter');
    
    return TRUE;
  }
}

new Counter();