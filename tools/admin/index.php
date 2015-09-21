<?php

/**
 * @file
 * Admin tool.
 */

require_once dirname(__FILE__) . '/../../settings.php';
require_once OPENLIST_CLASSES_PATH . '/DB.php';
require_once OPENLIST_CLASSES_PATH . '/Admin.php';

define('DEFAULT_WSDL', 'http://' . $_SERVER['SERVER_NAME'] . '?wsdl');

/**
 * Clean text.
 */
function clean($text) {
  return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
/**
 * Parse arguments.
 */
function parseArgument($argument) {
  if ($argument === '') {
    return;
  }

  switch (strtolower(substr($argument, 0, 2))) {
    case 'a]':
      $arguments = explode('|', substr($argument, 2));
      $argument = array();
      for ($i = 0, $count = count($arguments); $i < $count; $i++) {
        $argument[$i] = parseArgument($arguments[$i]);
      }
      break;

    case 'i]':
      $argument = (int) substr($argument, 2);
      break;

    case 's]':
      $argument = (string) substr($argument, 2);
      break;

    case 'b]':
      $argument = (boolean) substr($argument, 2);
      break;

    case 'j]':
      $argument = json_decode(substr($argument, 2), TRUE);
      break;

    case 'p]':
      $argument = unserialize(substr($argument, 2));
      break;
  }

  return $argument;
}
// ]

// Using the output buffer, we can easily print our debugging information in
// the same location.
ob_start();

if (isset($_POST['send'])) {
  $arguments = explode("\r\n", $_POST['arguments']);
  for ($i = 0, $count = count($arguments); $i < $count; $i++) {
    $arguments[$i] = parseArgument($arguments[$i]);
  }

  $dev = (isset($_POST['developer']) ? '&developer=on' : '');

  switch ($_POST['send']) {
    case 'Install':
      $url = OPENLIST_URL . '?admin=' . OPENLIST_ADMIN_GET_PASSWORD . $dev . '&install=' . $arguments[0];
      var_dump($url);
      var_dump(file_get_contents($url));
      break;

    case 'Uninstall':
      $url = OPENLIST_URL . '?admin=' . OPENLIST_ADMIN_GET_PASSWORD . $dev . '&uninstall=' . $arguments[0];
      var_dump($url);
      var_dump(file_get_contents($url));
      break;

    case 'Cron':
      echo file_get_contents(OPENLIST_URL . '?admin=' . OPENLIST_ADMIN_GET_PASSWORD . $dev . '&cron=' . $arguments[0]);
      break;

    case 'Normalize':
      Admin::normalizeElements($arguments[0]);
      break;
  }
}

// Get any debugging output.
$debug_messages = ob_get_contents();
ob_end_clean();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>Admin</title>

    <meta http-equiv="content-type" content="text/html; charset=utf-8" />

    <link href="../main.css" rel="stylesheet" type="text/css" />

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js" type="text/javascript"></script>
    <script src="../main.js" type="text/javascript"></script>
  </head>
  <body>
    <div class="section">
      <form method="post" action="">
        <div class="form-input">
          <label for="input-arguments">Arguments:</label>
          <div class="help">
            <div class="toggler">?</div>
            <div class="text">
              Separate each argument by a newline.<br />
              You can also use the following prefixes as type hinting:
              <ul>
                <li><strong>a]</strong> Typed as an array, where we split on the | character. Example: <pre>a]first element|second|a]nested|stuff</pre></li>
                <li><strong>i]</strong> integer</li>
                <li><strong>s]</strong> string</li>
                <li><strong>b]</strong> boolean</li>
                <li><strong>j]</strong> JSON. Simple JSON without the outer " (quotes). Also notice that objects are converted to associated arrays and not structures in the SOAP call.<br/>
                Also remember that JSON is kind of strict so use only " double quotes.</li>
                <li><strong>p]</strong> Takes a PHP serialized string, and unserialize it.</li>
              </ul>
              Full example of 3 arguments, 1st isn't typed specifically (will default to string), 2nd is an array, and 3rd a boolean:
              <pre>this is a simple string
a]something|more|j]{"key":"value","number":123,"boo":true}|j]"you can also use j to string stuff"
b]1</pre>
            </div>
          </div>
          <textarea id="input-arguments" name="arguments"><?php print isset($_POST['arguments']) ? $_POST['arguments'] : ''; ?></textarea>
        </div>

        <div class="form-input">
          <label for="input-as-user">Developer: </label>
          <div class="help">
            <div class="toggler">?</div>
            <div class="text">
              Is developer.
            </div>
          </div>
          <input id="input-developer" name="developer" type="checkbox" <?php print isset($_POST['developer']) ? ' checked="checked"' : ''; ?> />
        </div>

        <input type="submit" name="send" value="Install" />
        <input type="submit" name="send" value="Uninstall" /><br />
        <input type="submit" name="send" value="Cron" /><br />
        <input type="submit" name="send" value="Normalize" />
        <input type="submit" name="send" value="Check" />
        <input type="submit" name="send" value="Clean" />
      </form>
    </div>

    <?php if (!empty($debug_messages)): ?>
    <div class="section">
      <div class="data">
        <h3>Debug</h3>
        <pre><?php print clean($debug_messages); ?></pre>
      </div>
    </div>
    <?php
endif; ?>

    <?php if (!empty($result)): ?>
    <div class="section">
      <div class="data">
        <h3>Result</h3>
        <pre><?php print clean($result); ?></pre>
      </div>
    </div>
    <?php
endif; ?>
  </body>
</html>
