<?php
class Cleaner extends Module
{
  public $version = 1;
  
  public function getEvents() {
    return array();
  }

  public function lists($owner) {
    DB::q('
DELETE
FROM lists
WHERE
  owner = "@owner"
  AND status = 0', array(
      '@owner' => $owner,
    ));
    
    return TRUE;
  }
}

new Cleaner();