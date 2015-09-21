<?php

/**
 * @file
 * Gets the WSDL file.
 */

/**
 * Convert array to xml.
 */
function array_to_xml($array, &$xml) {
  foreach ($array as $key => $value) {
    if (is_array($value)) {
      if (!is_numeric($key)) {
        $subnode = $xml->addChild("$key");
        array_to_xml($value, $subnode);
      }
      else {
        $subnode = $xml->addChild("item$key");
        array_to_xml($value, $subnode);
      }
    }
    else {
      $xml->addChild("$key", htmlspecialchars("$value"));
    }
  }
}
