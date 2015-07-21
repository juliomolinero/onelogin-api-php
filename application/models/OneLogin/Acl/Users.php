<?php
/**
 * 
 * @author Julio_MOLINERO
 * @version 1.0
 *
 */
class OneLogin_Acl_Users extends Zend_Db_Table_Abstract 
{    
    protected $_name = "ol_admins" ;
    
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
    /**
     * Everytime a user logs in, last_login must be updated
     * @param string $user
     * @throws Exception when user does not exist
     */
    public function updateLastLoginDate($user){        
        if ( !$this->userExists($user) ) {
            throw new Exception('User does not exists');
        }
        $data = array( "last_login"=> time() );
        $where = array ( "user = ?" => $user );
        
        $this->_db->update( $this->_name, $data, $where );
    }
    /**
     * Perform a search to validate whether the user exists or not
     * @param string $user
     * @return boolean
     */
    public function userExists($user) {
        $select = $this->_db->select();
        $select->from($this->_name, array("exists" => new Zend_Db_Expr('COUNT(1)')))
            ->where("user=?", $user)
            ->orWhere("id=?", $user)
            ->limit(1);
        
        $result = $select->query()->fetchColumn();
        
        return $result == 1 ? true : false;
        
    }
    /**
     * Search user by email
     * @param string $email
     * @return User ID as array
     */
    public function getId($email){
        $select = $this->_db->select();
        $select->from( $this->_name, array('id') )
            ->where('email=?', $email)
            ->limit(1);
        //echo $select; exit();
        return $this->_db->fetchCol( $select );
    }
    /**
     * Search user by id
     * @param integer $userId
     * @return User ID as array
     */
    public function getUser($userId){
        $select = $this->_db->select();
        $select->from( $this->_name, array('user') )
        ->where('id=?', $userId)
        ->limit(1);
        //echo $select; exit();
        return $this->_db->fetchCol( $select );
    }
    /**
     * Get user by id
     * @param string $email
     * @return User ID as array
     */
    public function getById($userId){
        $select = $this->_db->select();
        $select->from( $this->_name, array('*') )
        ->where('id=?', $userId)
        ->limit(1);
        //echo $select; exit();
        return $this->_db->fetchRow( $select );
    }
    /**
     * Get user by email
     * @param string $email
     * @return One single record
     */
    public function getByEmail($email){
        $select = $this->_db->select();
        $select->from( $this->_name )
            ->where('LOWER(email)=?', strtolower($email) )
            ->limit(1);
        //echo $select; exit();
        return $this->_db->fetchRow( $select );        
    }
    /**
     * List current users
     * @param string $order
     * @param string $sort
     * @param number $limit
     */
    public function listUsers($order = "user", $sort = "ASC", $limit = 10) {
        $select = $this->_db->select();
		$select->from( $this->_name )
			   ->order($order.' '.$sort)
			   ->limit($limit);
//echo $select; exit();		
		return $this->_db->fetchAll( $select );
    }
    /**
     * Insert a record in ol_admins table
     * @param string $userName
     * @param string $email
     * @param string $password
     * @throws Exception when user exists
     */
    public function addUser( $userName, $email, $password ) {
        // Validate user
        if ( self::userExists($userName) ) {
            throw new Exception('User already exists');
        }
        // Check for a valid email address
        $val = new Zend_Validate_EmailAddress();
        if ( !$val->isValid($email) ){
            throw new Exception("Invalid email address", 10002);
        }
        // User can be alphanum with dash, underscore, @ and apostrophe
        $validateUser = new Zend_Validate_Regex('/^([A-Za-z0-9-_\.\@\']+)$/');
        if ( !$validateUser->isValid($userName) ) {
            throw new Exception("Invalid user name", 10003);
        }        
        // Define data to add
        $data = array();
        $data['user'] = $userName;
        $data['password'] = md5($password);
        $data['email'] = $email;
        $data['last_login'] = 0;
        // Perform insert
        $this->_db->insert( $this->_name, $data );        
    }    
    /**
     * Enable user account
     * @param integer $userId
     */
    public function setActive($userId) {
        $data = array( "active"=> 1 );
        $where = array ( "id = ?" => intval($userId) );
        $this->_db->update( $this->_name, $data, $where );
    }
    /**
     * Disable user account
     * @param integer $userId
     */
    public function unsetActive($userId) {
        $data = array( "active"=> 0 );
        $where = array ( "id = ?" => intval($userId) );
        $this->_db->update( $this->_name, $data, $where );
    }
}
?>