<?php
$starttime = microtime(TRUE);
// error_reporting(E_ALL);
// ini_set('display_errors', TRUE);
// ini_set('display_startup_errors', TRUE);

require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/importer_classes.php';

function line($msg, $incr = 0, $level = 0) {
  if ($incr < $GLOBALS['debug_level']) {
    echo str_repeat(' ', $incr * 2) . $msg . PHP_EOL;
  }
}

$GLOBALS['library_code'] = 'importer';
echo '<pre>';

//
//
$start = !empty($_GET['start']) ? $_GET['start'] : 0;
$count = !empty($_GET['count']) ? $_GET['count'] : 1;
$GLOBALS['debug_level'] = !empty($_GET['debuglevel']) ? $_GET['debuglevel'] : 100;

$_GET['truncate'] = TRUE;
$_GET['truncate'] = FALSE;

$args = getopt('c:s:d:');
if (!empty($args['c']) && is_numeric($args['c'])) {
  $count = $args['c'];
}
if (!empty($args['s']) && is_numeric($args['s'])) {
  $start = $args['s'];
}
if (!empty($args['d']) && is_numeric($args['d'])) {
  $GLOBALS['debug_level'] = $args['d'];
}


// DO NOT USE THIS VARIABLE!!!!
// The import isn't prepared to import from a specific point in time properly.
// We need to make sure new elements to existing lists get inserted correctly.
$from = !empty($_GET['from']) ? $_GET['from'] : 0;
$from = 0;

// $testers = array('8dcd131d4dbc0ea143e2165765cc957b180ff7c2d56435438eb4e81142821a2fa91fd64d3b7b4e371804c35ffc817f572835fff146301f339831ea36b2285d66');

if (!empty($testers)) {
  $count = count($testers);
  foreach ($testers as &$tester) {
    $tester = '"' . $tester . '"';
  }
}

//
//
line('From: ' . DBImport::q('SELECT DATABASE() AS db')->fetch_assoc()['db']);
line('To: ' . DB::q('SELECT DATABASE() AS db')->fetch_assoc()['db']);

if (isset($_SERVER['SCRIPT_URI'])) {
  $next_link = $_SERVER['SCRIPT_URI'] . '?count=' . $count . '&start=' . ($start + $count);
  line('Next link: <a href="' . $next_link . '">' . $next_link . '</a>');
}


//
// Clean the to database
if (isset($_GET['truncate']) && $_GET['truncate'] === TRUE) {
  DB::q('TRUNCATE elements;');
  DB::q('TRUNCATE lists;');
}

//
// Get list of owners.
// Only get owners that have lists with elements in.
$owners_result = DBImport::q('
SELECT l.owner
FROM lists l JOIN elements e ON (e.list_id = l.list_id)
WHERE
  l.status = 1
  AND l.type NOT IN ("follow")
  AND l.modified > %from
  !tester
GROUP BY l.owner
ORDER BY l.owner
LIMIT %start, %count', array(
  '%start' => $start,
  '%count' => $count,
  '!tester' => !empty($testers) ? 'AND l.owner IN (' . implode(',', $testers) . ')' : '',
  '%from' => $from
));

$owners = array();
while ($row = $owners_result->fetch_assoc()) {
  $owners[] = $row['owner'];
}
$owners_result->free();

$ownercount = count($owners);
line('Owners: ' . $ownercount . ' (' . round(DBImport::getHistory()['time'], 4) . 's)');

// Iterate through users.
foreach ($owners as $delta => $owner) {
  $start = microtime(TRUE);
  line('');
  line(($delta + 1) . '/' . $ownercount . ' : ' .$owner, 1);
  
  $list_result = DBImport::q('
SELECT
  list_id, title, created, modified, type, data, guid
FROM lists
WHERE
  status = 1
  AND type NOT IN ("follow")
  AND owner = "@owner"
  AND modified > %from
  ', array(
    '@owner' => $owner,
    '%from' => $from
  ));
  
  $owner_lists = array();
  $previous_elements = array();
  
  line('Lists: ' .$list_result->num_rows . ' (' . round(DBImport::getHistory()['time'], 4) . 's)', 2);
  
  // Iterate through lists.
  while ($row = $list_result->fetch_assoc()) {
    // If it's not a user list make sure we can combine it if there's more
    // lists.
    if (!in_array($row['type'], array('user_list'))) {
      if (!isset($owner_lists[$row['type']])) {
        $owner_lists[$row['type']] = create_list($owner, $row);
      }
      $list_id = $owner_lists[$row['type']];
    } else {
      $list_id = create_list($owner, $row);
    }
    
    line('L: ' . $row['list_id'] . ' -> ' . $list_id, 3);
    
    // Get all the elements.
    $element_result = DBImport::q('
SELECT
  element_id, data, modified, created, guid
FROM elements e
WHERE
  status = 1
  AND modified > %from
  AND list_id = %list_id
ORDER BY weight
    ', array(
      '%list_id' => $row['list_id'],
      '%from' => $from
    ));
    
    if (!isset($previous_elements[$list_id])) {
      $previous_elements[$list_id] = array(0);
    }
    
    line('Elements: ' .$element_result->num_rows . ' (' . round(DBImport::getHistory()['time'], 4) . 's)', 4);
    while ($element_row = $element_result->fetch_assoc()) {
    
      $element_row['weight'] = count($previous_elements[$list_id]) * 8;
      $element_row['previous'] = end($previous_elements[$list_id]);
      
      $element_id = create_element($list_id, convert_from_1_to_2($element_row));
      
      line('E: ' . $element_row['element_id'] . ' -> ' . $element_id, 5);
    
      $previous_elements[$list_id][] = $element_id; // $element_row['element_id'];
    }
  }
  
  line('Owner import time: ' . round(microtime(TRUE) - $start, 4), 1);
}

function convert_from_1_to_2($row) {
  $data = unserialize($row['data']);
  
  // SKULLE VI lave ting_object id'er om til brønd 3?
  
  if (isset($data['id']) && (isset($data['type']) && $data['type'] === 'ting_object')) {
    $data['value'] = $data['id'];
    unset($data['id']);
  }
  return $row;
}

function create_list($owner, $row) {
  DB::q('
  INSERT INTO lists
  (owner, title, created, modified, status, type, data, library_code, guid)
  VALUES ("@owner", "@title", "@created", %modified, %status, "@type", "@data", "@library_code", "@guid")', array(
      '@owner' => $owner,
      '@title' => $row['title'],
      '@created' => $row['created'],
      '%modified' => $row['modified'],
      '%status' => 1,
      '@type' => $row['type'],
      '@data' => $row['data'],
      '@library_code' => $GLOBALS['library_code'],
      '@guid' => $row['guid'],
    ));
  return DB::insert_id();
}

function create_element($list_id, $row) {
  DB::q('
  INSERT INTO elements
  (list_id, data, created, modified, weight, previous, status, library_code, guid)
  VALUES (%list_id, "@data", "@created", %modified, %weight, %previous, %status, "@library_code", "@guid")', array(
      '%list_id' => $list_id,
      '@data' => $row['data'],
      '@created' => $row['created'],
      '%modified' => $row['modified'],
      '%status' => 1,
      '%previous' => $row['previous'],
      '%weight' => $row['weight'],
      '@library_code' => $GLOBALS['library_code'],
      '@guid' => $row['guid'],
    ));
  return DB::insert_id();
}

line('Total time: ' . round(microtime(TRUE) - $starttime, 4));