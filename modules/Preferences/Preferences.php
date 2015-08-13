<?php

/**
 * Handle preferences.
 */
class Preferences extends Module
{
  public $version = 1;
  
  public function getEvents() {
    return array();
  }

  /**
   * Set a preference.
   *
   * @param {string} $owner
   *   The user.
   * @param {string} $key
   *   Name of the preference.
   * @param {mixed} $value
   *   Value of the preference.
   */
  public function set($owner, $key, $value) {
    $data = array(
      'value' => $key . '=' . $value,
      'type' => 'preference',
    );
    
    if ($element = $this->getElement($owner, $key)) {
      OpenList::$instance->editElement($element['element_id'], $data);
      return $value;
    }
    
    $result = DB::q(
      'SELECT list_id FROM lists l WHERE l.owner = "@owner" AND l.type = "preferences"',
      array('@owner' => $owner)
    );
    
    if ($result->num_rows > 0) {
      $list_id = $result->fetch_assoc()['list_id'];
    } else {
      $list_id = OpenList::$instance->createList($owner, 'Preferences', 'preferences');
    }
    
    OpenList::$instance->createElement($list_id, $data);
    
    return $value;
  }
  
  /**
   * Get a preference.
   *
   * @param {string} $owner
   *   The user.
   * @param {string} $key
   *   Name of the preference.
   * @return {mixed}
   *   The value of the preference.
   */
  public function get($owner, $key) {
    if ($element = $this->getElement($owner, $key)) {
      return $element['value'];
    }
    return NULL;
  }
  
  /**
   * Get all user preferences.
   *
   * @param {string} $owner
   *   The user.
   * @return {array}
   *   A mapped array of the preferences.
   */
  public function getAll($owner) {
    $result = DB::q('
SELECT e.element_id, e.data
FROM lists l JOIN elements e ON (e.list_id = l.list_id)
WHERE
  l.owner = "@owner"
  AND l.type = "preferences"
    ', array(
      '@owner' => $owner,
    ));
    
    $elements = array();
    
    if ($result) {
      while ($row = $result->fetch_assoc()) {
        $data = unserialize($row['data']);
        $keyvalue = explode('=', $data['value'], 2);
        
        $elements[$keyvalue[0]] = $keyvalue[1];
      }
    }
    
    return $elements;
  }
  
  /**
   * Get a preference list element.
   *
   * @param {string} $owner
   *   The owner.
   * @param {string} $key
   *   The key.
   */
  private function getElement($owner, $key) {
    $result = DB::q('
SELECT e.element_id, e.data
FROM lists l JOIN elements e ON (e.list_id = l.list_id)
WHERE
  l.owner = "@owner"
  AND l.type = "preferences"
    ', array(
      '@owner' => $owner,
    ));
    
    $element = FALSE;
    
    if ($result) {
      while ($row = $result->fetch_assoc()) {
        $data = unserialize($row['data']);
        $keyvalue = explode('=', $data['value'], 2);
        
        if ($keyvalue[0] == $key) {
          $element = array('element_id' => $row['element_id'], 'value' => $keyvalue[1]);
          break;
        }
      }
    }
    
    return $element;
  }
}

new Preferences();