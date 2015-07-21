<?php
/**
 * 
 * @author Julio_MOLINERO
 * @version 1.0
 *
 */
class OneLogin_Acl_UsersLogs extends Zend_Db_Table_Abstract 
{    
    protected $_name = "ol_admins_logs";    
    
    /**
     * Define database connection string
     */
    public function __construct()
    {
        $register = Zend_Registry::getInstance();
        $dbConnector = $register->get("db");
        $dbConfig = $dbConnector->getConfig() ;
        $dbSchema = array() ;
        $dbSchema["db"] = $dbConnector ; 
        $dbSchema["schema"] = $dbConfig["dbname"] ;
        parent::__construct($dbSchema);        
    }    
}
?>