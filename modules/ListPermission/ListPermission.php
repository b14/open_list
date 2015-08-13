<?php

/**
 * Handle list permissions.
 */
class ListPermission extends Module
{
  public $version = 1;

  /**
   * The table.
   */
  private $table = 'm_list_permission';

  /**
   * Abstract getEvents().
   */
  public function getEvents() {
    return array(
      'createList' => 'onListCreate',
      'editList' => 'onListEdit',
    );
  }
  
  /**
   *
   */
  public function getPublicLists($title = '') {
    $result = DB::q('
SELECT l.list_id, l.type, l.title, l.modified, l.status, l.data
FROM lists l JOIN !table lp ON (lp.list_id = l.list_id)
WHERE
  l.title LIKE "%@title%"
  AND lp.permission = "public"
ORDER BY
  l.title
    ', array(
      '!table' => $this->table,
      '@title' => $title,
    ));

    if ($result) {
      $lists = array();
      while ($row = $result->fetch_assoc()) {
        $row['data'] = unserialize($row['data']);

        $lists[] = $row;
      }
      return $lists;
    }
  }

  /**
   *
   */
  protected function onListEdit($list_id, $title, $data) {
    if (!empty($data['fields'])) {
      foreach ($data['fields'] as $field) {
        if ($field['name'] == 'field_ding_list_status') {
          DB::q('
INSERT INTO !table
(list_id, permission)
VALUES (%list_id, "@permission")
  ON DUPLICATE KEY UPDATE
    permission = "@permission"
          ', array(
            '!table' => $this->table,
            '@permission' => $field['value'],
            '%list_id' => $list_id,
          ));
        }
      }
    }
    
    return TRUE;
  }

  /**
   *
   */
  protected function onListCreate($insert_id, $owner, $title, $data) {
    if (!empty($data['fields'])) {
      foreach ($data['fields'] as $field) {
        if ($field['name'] == 'field_ding_list_status') {
          DB::q('
INSERT INTO !table
(list_id, permission)
VALUES (%list_id, "@permission")
  ON DUPLICATE KEY UPDATE
    permission = "@permission"
          ', array(
            '!table' => $this->table,
            '@permission' => $field['value'],
            '%list_id' => $insert_id,
          ));
        }
      }
    }
    
    return TRUE;
  }

  /**
   * Create the module table on install.
   */
  protected function _install() {
    DB::q('
CREATE TABLE IF NOT EXISTS m_list_permission (
  permission_id int(11) NOT NULL AUTO_INCREMENT,
  list_id int(11) NOT NULL,
  permission enum("private","shared","public") NOT NULL,
  PRIMARY KEY (permission_id),
  KEY list_id (list_id)
) ENGINE=InnoDB;
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

new ListPermission();