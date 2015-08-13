<?php

define('OPENLIST_ADMIN_GET_PASSWORD', 'asdfxyz');

/**
 * Database host.
 */
define('DB_HOST', 'your.host.com');
/**
 * Database username.
 */
define('DB_USERNAME', 'openlist');
/**
 * Database password.
 */
define('DB_PASSWORD', 'password');
/**
 * Database to use.
 */
define('DB_DATABASE', 'databasename');


// -------------------------------------------------------------------------------------------------

/**
 * The local root of the openlist.
 */
define('OPENLIST_ROOT', dirname(__FILE__));
define('OPENLIST_CLASSES_PATH', OPENLIST_ROOT . '/classes');


/**
 * Path to the locally saved wsdl file.
 *
 * This file is used, so we don't have to Autodiscover the class on every
 * single request.
 */
define('WSDL_LOCAL_PATH', OPENLIST_ROOT . '/xml/wsdl.xml');


define('MODULES_PATH', OPENLIST_ROOT . '/modules');
define('MODULES_LIST_FILE', MODULES_PATH . '/modules_list.inc');


define('IS_DEVELOPER', isset($_GET['developer']) || (isset($_COOKIE['developer']) && $_COOKIE['developer'] === 'on'));

define('IS_LOCAL', isset($_POST['local']));

// Certain modules may return empty results due to
// sparsity of the result set. In order to fake
// a positive result for testpurposes elsewhere
// this switch does just that.
define('ENABLE_TEST_RESULTS', TRUE);
