<?php
/**
 * 
 * @author Julio_MOLINERO
 * @version 1.0
 *
 */
class OneLogin_Acl_Login
{
    public function __construct() {    
        $this->db = Zend_Registry::get('db');
        $this->auth = Zend_Auth::getInstance();
    }
    
    public function login($username, $password) {
        // Remove backslashes
        $username = str_replace("\\", "", $username);
        // filter data from the user
        $f = new Zend_Filter_StripTags();
        $this->user = $f->filter($username);
        $this->pwd = $f->filter($password);        
        // Validate credentials
        if ( empty($username) ) {
            throw new Exception('Invalid username');
        }
        if ( empty($password) ) {
            throw new Exception('Invalid password');
        }        
        // Username can be alphanum with dash, underscore, @, periods and apostrophe
        $usernameValidator = new Zend_Validate_Regex('/^([A-Za-z0-9-_@\.\']+)$/');
        if (!$usernameValidator->isValid($username)) {
            Throw new Exception('Please enter a valid username');
        }        
        
        // setup Zend_Auth adapter for a database table
        $this->db->setFetchMode(Zend_Db::FETCH_ASSOC);
        $authAdapter = new Zend_Auth_Adapter_DbTable($this->db);
        $authAdapter->setTableName('ol_admins');
        $authAdapter->setIdentityColumn('user');
        $authAdapter->setCredentialColumn('password');
        
        // Set the input credential values to authenticate against
        $authAdapter->setIdentity($username);
        $authAdapter->setCredential( md5($password) );
        $authAdapter->getDbSelect()->where('active = ?', 1); // MUST be an active account
        
        // do the authentication
        $result = $this->auth->authenticate($authAdapter);
        $this->db->setFetchMode(Zend_Db::FETCH_OBJ);
        
        if (!$result->isValid()) throw new Exception('Login failed.');
        
        //var_dump($authAdapter->getResultRowObject()); exit();
        
        // Update last login date
        $users = new OneLogin_Acl_Users();
        $users->updateLastLoginDate($username);
     
        // Define object and set auth information
        $objUser = new stdClass();
        $objUser->user_id = $authAdapter->getResultRowObject()->id;
        $objUser->api_user_username = $username;
        $objUser->api_user_password = $password;        
        $objUser->active = $authAdapter->getResultRowObject()->active;
        $this->auth->getStorage()->write($objUser);        
    }    
}
?>