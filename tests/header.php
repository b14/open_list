<?php

$GLOBALS['url'] = 'http://v2rest.openlist.server003.b14cms.dk/json/';

ob_start();

function line($message = '', $underline = FALSE) {
  output($message . PHP_EOL);
  if ($underline) {
    output(str_repeat('-', strlen($message)) . PHP_EOL);
  }
}
function output($message) {
  echo $message;
  ob_flush();
  flush();
}
function out_result($result) {
  line('<strong>' . $result[0] . '</strong>');
  if (isset($result[2])) {
    line('<span style="color: #999;">' . htmlentities(var_export($result[2], TRUE)) . '</span>');
  }
  line();
  line(htmlentities(var_export($result[1], TRUE)));
  line(str_repeat('_', 80));
  line();
}

function curlit($method, $get = array(), $post = array(), $print_result = FALSE, $type = 'GET') {
  if (!empty($post)) {
    $type = 'POST';
  }
  
  $ch = curl_init();
  
  $url = $GLOBALS['url'] . $method . '?admin';
  if (!empty($get)) {
    $url .= '&' . http_build_query($get);
  }
  
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  
  if ($type === 'POST') {
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
  }
  
  $result = curl_exec($ch);
  
  curl_close($ch);
  
  if ($print_result || ALWAYS_PRINT_RESULT) {
    var_dump($result);
  }
  
  
  if ($type === 'POST') {
    $result = array($url, json_decode($result), $post);
  } else {
    $result = array($url, json_decode($result));
  }
  
  out_result($result);
  
  return $result;
}


echo '<pre>';