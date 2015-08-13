<?php

/*
 *
 * SOAP:
 * http://php.net/manual/en/class.soapclient.php
 */

header("Content-Type: text/html; charset=utf-8");

define('DEFAULT_WSDL', 'http://' . $_SERVER['SERVER_NAME'] .'?wsdl');
define('DEFAULT_LIBRARY_CODE', 'dev_tool');

ini_set("soap.wsdl_cache_enabled", "0");

// Using the output buffer, so we can print our debugging information in
// the same location.
ob_start();

function clean($text) {
  return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Parses an argument, so it gets typed correct.
 *
 * @param string $argument The argument to parse.
 * @return mixed The argument, typed as the specific type.
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


if (isset($_POST['send'])) {

  // Parse the data.
  $function_name = $_POST['function'];
  $arguments = explode("\r\n", $_POST['arguments']);
  for ($i = 0, $count = count($arguments); $i < $count; $i++) {
    $arguments[$i] = parseArgument($arguments[$i]);
  }
  
  if (isset($_POST['local'])) {
    if ($_POST['send'] == 'send') {
      $_COOKIE['developer'] = !isset($_POST['as_user']) ? 'on' : '';
      $_COOKIE['library_code'] = $_POST['library_code'];
      require_once(dirname(__FILE__) . '/../../settings.php');
      
      $GLOBALS['local'] = array($function_name, $arguments);
      
      require_once(OPENLIST_ROOT . '/boot.php');
    }
  }
  else {
    // Everything is in the same try, the error message will tell us where the
    // problem is.
    try {
      $client = new SoapClient($_POST['wsdl'], array('trace' => TRUE));
      switch ($_POST['send']) {
        case 'Send':
          $client->__setCookie('developer', !isset($_POST['as_user']) ? 'on' : '');
          $client->__setCookie('library_code', $_POST['library_code']);

          // Get the response first, and time it.
          $response['time'] = microtime(TRUE);
          try {
            $response['clean'] = call_user_func_array(array($client, $function_name), $arguments);
          } catch (Exception $e) {
            $trace = $e->getTrace();
            $response['clean'] = array(
              'error' => $e->getMessage(),
              'call' => $trace[1]['function'] . '(' . implode(', ', $trace[1]['args']) .')'
            );
            $response['clean'] = (string) $e;
          }
          $response['time'] = microtime(TRUE) - $response['time'];

          // Get the raw header and data
          $response['header'] = $client->__getLastResponseHeaders();
          $response['data'] = $client->__getLastResponse();
          // Parse the raw XML data with DOMDocuments, to get a formatted output.
          $response['xml'] = new DOMDocument('1.0', 'utf-8');
          $response['xml']->formatOutput = TRUE;
          $response['xml']->loadXML($response['data']);

          // Now get the request data.
          $request['header'] = $client->__getLastRequestHeaders();
          $request['data'] = $client->__getLastRequest();
          $request['xml'] = new DOMDocument('1.0', 'utf-8');
          $request['xml']->formatOutput = TRUE;
          $request['xml']->loadXML($request['data']);
        break;

        case 'Get functions':
          // Get the list of functions available.
          // The SoapClient reads these from the WSDL file.
          var_dump($client->__getFunctions());
        break;
      }
    } catch (Exception $e) {
      echo $e;
    }
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
    <title>Client</title>

    <meta http-equiv="content-type" content="text/html; charset=utf-8" />

    <link href="../main.css" rel="stylesheet" type="text/css" />

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js" type="text/javascript"></script>
    <script src="../main.js" type="text/javascript"></script>
  </head>
  <body>
    <div class="section">
      <form method="post" action="">
        <div class="form-input">
          <label for="input-function">Function name:</label>
          <div class="help">
            <div class="toggler">?</div>
            <div class="text">
              Just write the function name.<br />
              Arguments are passed by the argument field below.
            </div>
          </div>
          <input id="input-function" type="text" name="function" value="<?php print isset($_POST['function']) ? $_POST['function'] : ''; ?>" />
        </div>
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
          <label for="input-wsdl">WSDL: </label>
          <div class="help">
            <div class="toggler">?</div>
            <div class="text">
              Link to the WSDL file.<br />
              This is the file that tells us about the service.
            </div>
          </div>
          <input id="input-wsdl" name="wsdl" type="text" value="<?php print isset($_POST['wsdl']) ? $_POST['wsdl'] : DEFAULT_WSDL; ?>" />
        </div>
        <div class="form-input">
          <label for="input-wsdl">Library code: </label>
          <div class="help">
            <div class="toggler">?</div>
            <div class="text">
              Library code
            </div>
          </div>
          <input id="input-library-code" name="library_code" type="text" value="<?php print isset($_POST['library_code']) ? $_POST['library_code'] : DEFAULT_LIBRARY_CODE; ?>" />
        </div>

        <div class="form-input">
          <label for="input-as-user">As user: </label>
          <div class="help">
            <div class="toggler">?</div>
            <div class="text">
              If checked the developer cookie is not sent.
            </div>
          </div>
          <input id="input-as-user" name="as_user" type="checkbox" <?php print isset($_POST['as_user']) ? ' checked="checked"' : ''; ?> />
        </div>
        
        <div class="form-input">
          <label for="input-as-user">Local: </label>
          <div class="help">
            <div class="toggler">?</div>
            <div class="text">
              Local
            </div>
          </div>
          <input id="input-local" name="local" type="checkbox" <?php print isset($_POST['local']) ? ' checked="checked"' : ''; ?> />
        </div>

        <input type="submit" name="send" value="Send" />
        <input type="submit" name="send" value="Get functions" />
      </form>
    </div>

    <?php if (!empty($debug_messages)) { ?>
    <div class="section">
      <div class="data">
        <h3>Debug</h3>
        <pre><?php print clean($debug_messages); ?></pre>
      </div>
    </div>
    <?php } ?>

    <?php if (!empty($request)) { ?>
    <div class="section">
      <div class="data closed">
        <h3>Raw Request</h3>
        <pre><?php print clean($request['header']); ?>

<?php print clean($request['xml']->saveXML()); ?></pre>
      </div>
    </div>
    <?php } ?>

    <?php if (!empty($response)) { ?>
    <div class="section">
      <div class="data closed">
        <h3>Raw response</h3>
        <pre><?php print clean($response['header']); ?>

<?php print clean($response['xml']->saveXML()); ?></pre>
      </div>
    </div>

    <div class="section">
      <div class="data">
        <h3>Response (<?php print $response['time']; ?>)</h3>
        <pre><?php var_dump($response['clean']); ?></pre>
      </div>
    </div>
    <?php } ?>

  </body>
</html>