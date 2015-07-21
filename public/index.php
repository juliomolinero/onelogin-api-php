<?php
set_include_path(implode(PATH_SEPARATOR, array(
    dirname(dirname(__FILE__)) . '/library'
)));
date_default_timezone_set("America/Chicago");

require_once 'Zend/Application.php';
require_once 'Zend/Config/Ini.php';

// Remove index.php from the URL
$url = $_SERVER["REQUEST_URI"];
if(strpos($url,"index.php")!==false) {
    $url = str_replace("index.php","",$url);
    header("Location: $url");
    exit;
}

//Define path to application directory
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH',
realpath(dirname(__FILE__) . '/../application'));

// Define application environment
$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/local.ini');
$env = $config->main->env_mode;
defined('APPLICATION_ENV') || define('APPLICATION_ENV', $env ? $env : 'production');

// Define app version and company name
define('APPLICATION_VERSION', '1.0');
define("APPLICATION_CHARSET","utf-8") ;
define("COMPANY_NAME", "") ; // Add your Company name here !!!!

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()->run();