<?php

define('ALWAYS_PRINT_RESULT', FALSE);

include_once 'header.php';

$owner = 'TEST_USER';

line('Test ONE', TRUE);
line();

curlit('createList', array(), array('owner' => $owner, 'title' => 'Test liste', 'type' => 'test-type'));

$result = curlit('getLists', array('owner' => $owner));
foreach ($result[1] as $list) {
  if ($list->status == 1) {
    curlit('deleteList', array('list_id' => $list->list_id));
  }
}

curlit('getLists', array('owner' => $owner));
curlit('Cleaner/lists', array('owner' => $owner));
curlit('getLists', array('owner' => $owner));

include_once 'footer.php';