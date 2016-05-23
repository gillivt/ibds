<?php

// Define the core paths
// Define them as absolute paths to make sure that require_once works as expected

// DIRECTORY_SEPARATOR is a PHP pre-defined constant
// (\ for Windows, / for Unix)
defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);

defined('SITE_ROOT') ? null : 
    define('SITE_ROOT', DS.'wamp'.DS.'www'.DS.'invoicebuddydatabaseservice');
defined('WEB_ROOT') ? null : define('WEB_ROOT', '/invoicebuddydatabaseservice/public');
defined('LIB_PATH') ? null : define('LIB_PATH', SITE_ROOT.DS.'database');

// load config file first
require_once(LIB_PATH.DS.'config.php');

// load basic functions next so that everything after can use them
require_once(LIB_PATH.DS.'functions.php');

// load core objects
require_once(LIB_PATH.DS.'class.session.php');
require_once(LIB_PATH.DS.'class.database.php');
require_once(LIB_PATH.DS.'class.database_object.php');
require_once(LIB_PATH.DS."PHPMailer".DS."class.phpmailer.php");
require_once(LIB_PATH.DS."PHPMailer".DS."class.smtp.php");

// load database-related classes
require_once(LIB_PATH.DS.'class.databaseuser.php');
require_once(LIB_PATH.DS.'class.trader.php');
require_once(LIB_PATH.DS.'class.product.php');
require_once(LIB_PATH.DS.'class.client.php');
require_once(LIB_PATH.DS.'class.invoice.php');

?>