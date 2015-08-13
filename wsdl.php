<?php

header('Content-Type: text/xml; charset=utf-8');

// Only create a new WSDL file if the OpenList.php file has changed, since
// the last WSDL file was created.
if (filemtime(OPENLIST_CLASSES_PATH . '/OpenList.php') < filemtime(WSDL_LOCAL_PATH)) {
  echo file_get_contents(WSDL_LOCAL_PATH);
} else {
  try {
    require_once 'Zend/Soap/AutoDiscover.php';
    require_once(OPENLIST_CLASSES_PATH . '/OpenList.php');

    $autodiscover = new Zend_Soap_AutoDiscover();

    $autodiscover->setClass('OpenList');
    $autodiscover->dump(WSDL_LOCAL_PATH);
    $autodiscover->handle();
  } catch(Exception $e) {

  }
}