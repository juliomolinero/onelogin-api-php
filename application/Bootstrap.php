<?php
/**
 * 
 * @author Julio_Molinero 
 * @version 1.0
 *
 */

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
	
	protected function _initApp() {
		$appname = "One Login API System";
		$appAcronym = "OneLoginAPI";
		Zend_Registry::set("appname",$appname);		
		Zend_Registry::set("appAcronym",$appAcronym);
	}
	
	protected function _initDatabase() {		
		// Load server specific configuration
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/local.ini');
    	$dbconfig=$config->database;
		$db = Zend_Db::factory($dbconfig->adapter, $dbconfig->toArray());
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		Zend_Db_Table_Abstract::setDefaultAdapter($db);		
		/*
		 * Make sure any results we retrieve or commands we send use 
		 * the same charset and collation as the database
		 */ 
		$db->query("SET NAMES 'utf8'");				
		// Store db object in registry
		Zend_Registry::set("db",$db);		
		
		return $db;		
	}		
		
	protected function _initAutoload() {
		Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);		
	}
	
	protected function _initRouter() {		
		$this->bootstrap('FrontController');
		$front = $this->getResource('FrontController');		
	}	
	
	// Force to use SSL ----------------->
	protected function _initForceSSL() {
		// Are we using https ?
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/local.ini');
		$use_ssl = $config->main->use_ssl;
		//if($_SERVER['SERVER_PORT'] != '443') { // Initial approach
		if( ((bool)$use_ssl) && ($_SERVER['SERVER_PORT'] != '443') ) {
			  header('Location: https://' . $_SERVER['HTTP_HOST'] .
			            $_SERVER['REQUEST_URI']);
			  exit();
		}
	}	
}