<?php

$GLOBALS['library_code'] = isset($_COOKIE['library_code']) ?
  $_COOKIE['library_code'] : (isset($_GET['library_code']) ? 
    $_GET['library_code'] : (isset($_GET['admin']) ?
      'admin' : FALSE));

$allowed_codes = array(
  'admin' => 'Admin',
  'dev_tool' => 'Dev tool',
  'fkb-ov2' => 'FKB Openlist v2',
  '714700' => 'fkb'
);

if (!isset($allowed_codes[$GLOBALS['library_code']])) {
  header('HTTP/1.1 403 Forbidden');
  exit('Missing library code');
}