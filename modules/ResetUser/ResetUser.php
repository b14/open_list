<?php

/**
 * Reset a user.
 *
 * When resetting a user, all the users lists will have their status = 0.
 *
 * Notice that this can also hardReset() a user, this will delete the users
 * lists and elements completely. Not just setting it's status but removing
 * them from the database, so be VERY careful with this!
 */
class ResetUser extends Module
{ 
  public $version = 1;
  

  /**
   * Abstract getEvents().
   */
  public function getEvents() {
    return array();
  }
  
  /**
   * Reset a user.
   *
   * @param string $owner
   *   The user to reset.
   *
   * @return boolean
   *   True or false depending on success.
   */
  public function reset($owner) {
    $result = DB::q('
UPDATE lists
SET status = 0, modified = UNIX_TIMESTAMP()
WHERE owner = "@owner"
      ', array('@owner' => $owner));
    
    if ($result) {
      return $result;
    }
    
    return FALSE;
  }
  
  /**
   * Reset a user.
   *
   * @param string $owner
   *   The user to reset.
   *
   * @return boolean
   *   True or false depending on success.
   */
  public function hardReset($owner) {
    $result = DB::q('
DELETE e.*
FROM
  lists l
  LEFT JOIN elements e ON (e.list_id = l.list_id)
WHERE l.owner = "@owner";
DELETE FROM lists
WHERE owner = "@owner";
      ', array('@owner' => $owner), TRUE); // (last tru enables multi query)
      
    // Clear the result list after our multi_query
    DB::clearResults();
    
    if ($result) {
      return $result;
    }
    
    return FALSE;
  }
}

new ResetUser();