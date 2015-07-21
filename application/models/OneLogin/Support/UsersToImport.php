<?php
class OneLogin_Support_UsersToImport extends Zend_Db_Table_Abstract{

    protected $_name = "applications_users_to_import" ;    

    /**
     * Constructor, user must provide Application's ID
     */
    public function __construct( ){
        $register = Zend_Registry::getInstance();
        $dbConnector = $register->get("db");
        $dbConfig = $dbConnector->getConfig() ;
        $dbSchema = array() ;
        $dbSchema["db"] = $dbConnector ;
        $dbSchema["schema"] = $dbConfig["dbname"] ;
        parent::__construct($dbSchema);
        
    }
    /**
     * Verify user already exists to prevent duplicity
     * @param string $shortName
     * @return boolean
     */
    public function userExists($email) {
        $exists = false;
        $select = $this->_db->select();
        $select->from($this->_name, array("exists" => new Zend_Db_Expr('COUNT(1)')) )
            ->where("email=?", $email)        
            ->limit(1);
    
        $result = $select->query()->fetchColumn();
        $exists = ($result == 1) ? true : false;
    
        return $exists;
    }
    /**
     * Add a record to applications_users table
     * @param object $user
     * @throws Exception on an empty user
     */
    public function importUser($user) {
        if (empty($user)) {
            throw new Exception("User cannot be empty", "10004");
        }        
        $data = array();                
        $data['user_name'] = $user->userName;
        $data['first_name'] = $user->firstName;
        $data['last_name'] = $user->lastName;
        $data['email'] = $user->email;        
        // It is an insert, always
        if ( !self::userExists($user->email) ) {            
            $this->_db->insert($this->_name, $data);            
        }        
    }    
    /**
     * Provide list of users to be imported to application per se and main application table, it's recommended to use it for asynchronus tasks only
     * @return Record List
     */
    public function listForExport(){
        // Main query
        $select = $this->_db->select();
        $select->from($this->_name);        
        // Execute query and return results
        $results = $select->query()->fetchAll();
        return $results;        
    }
    /**
     * Search for users
     * @param string $userText
     * @param number $limit
     * @param number $order
     * @return Record list
     */
    public function searchUsers($userText = "", $limit = 50, $order = 0) {
        // Set column to order
        switch ( intval($order) ) {
            default:
            case 0:
                $orderBy = 'user_name';
                break;
            case 1:
                $orderBy = 'first_name';
                break;
            case 2:
                $orderBy = 'last_name';
                break;
            case 3:
                $orderBy = 'email';
                break;            
        }
        // Main query
        $select = $this->_db->select();
        $select->from($this->_name)
            ->order($orderBy)
            ->limit($limit);
        // Build filters if any.
        if (!empty($userText)) {
            $select->where("user_name LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR email LIKE ? ", "%".$userText."%");
        }
        // Execute query and return results
        $results = $select->query()->fetchAll();
        return $results;
    }    
}
?>