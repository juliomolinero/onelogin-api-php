<?php
/**
 * 
 * @author Julio_MOLINERO
 * @version 1.0
 * @link https://onelogin.zendesk.com/hc/en-us/articles/201175524-Users-API
 * 
 * @example
 * // It works with basic configuration ===============================================>
 * // Basic settings
        $config = array(
            'adapter'   => 'Zend_Http_Client_Adapter_Curl',
            'curloptions' => array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_HTTPAUTH    =>  CURLAUTH_BASIC,
                CURLOPT_SSL_VERIFYHOST => FALSE,                
                CURLOPT_SSL_VERIFYPEER => FALSE,
                CURLOPT_USERPWD => $this->_api_login
            ),
        );
      //var_dump($config); exit();
        // Create connection to OneLogin API        
        $client = new Zend_Http_Client(OneLogin_Api_Users::URL_LIST, $config);        
        $users = $client->request('GET');
    It works with basic configuration ===============================================>
 *
 */
class OneLogin_Api_Users extends OneLogin_Api_Abstract
{
    const URL_LIST = "https://app.onelogin.com/api/v2/users.xml";
    const URL_GET = "https://app.onelogin.com/api/v2/users/username/{username}";
    const URL_CREATE = "https://app.onelogin.com/api/v2/users.xml";
    const URL_DELETE = "https://app.onelogin.com/api/v1/users/{id}.xml";
    const URL_SET_PASSWORD_ID = "https://app.onelogin.com/api/v2/users/{id}/set_password.xml";
    const URL_SET_PASSWORD_USER = "https://app.onelogin.com/api/v2/users/username/{username}";
    // Status for OneLogin Users
    const USER_UNACTIVATED = "Unactivated";
    const USER_ACTIVE = "Active";
    const USER_SUSPENDED = "Suspended";
    const USER_LOCKED = "Locked";
    const USER_PASSWORD_EXPIRED = "Password expired";
    const USER_AWAITING_PASSWORD_RESET = "Awaiting password reset";
    
    /**
     * List OneLogin users
     * @return data record list
     */
    public function listUsers(){
        $users = array();
        // Trying pure Http Client
        $oneLogin = new Zend_Http_Client( OneLogin_Api_Users::URL_LIST, $this->_proxy_config );
        $oneLogin->setAuth($this->_user_name, $this->_user_password, Zend_Http_Client::AUTH_BASIC);
        $users = $oneLogin->request(Zend_Http_Client::GET); // Return as Response
        
        return $users;        
    }
    /**
     * Search for a specific user
     * @param string $userName
     * @return user in a XML document 
     */
    public function getUser($userName) {
        $user = null;        
        $userName = strtolower( str_replace("'", '', trim($userName) ) ); // Username needs to be lowercase
        $uri = str_replace('{username}', $userName, OneLogin_Api_Users::URL_GET);
//echo $uri; exit();
        // Trying pure Http Client
        $oneLogin = new Zend_Http_Client( $uri, $this->_proxy_config );
        $oneLogin->setAuth($this->_user_name, $this->_user_password, Zend_Http_Client::AUTH_BASIC);
        $user = $oneLogin->request(Zend_Http_Client::GET); // Return as Response
        
        return $user;
    }    
    /**
     * Add a user to OneLogin application
     * 
     * @param array $user
     * 
     * @example http://framework.zend.com/manual/1.12/en/zend.http.client.advanced.html
     * @example XML
     * 
     * $xml = '<book><title>Islands in the Stream</title><author>Ernest Hemingway</author><year>1970</year></book>';
     * $client->setRawData($xml, 'text/xml')->request('POST');
     * 
     * @example base OneLogin API XML
     * <user><email>hanna@onelogin.com</email><firstname>Hanna</firstname><group-id nil="true"></group-id><lastname>Banana</lastname>
     * <username>hanna.banana</username><notes nil="true"></notes><openid-name>hanna</openid-name><pending-apps nil="true"></pending-apps>
     * <phone>310-555-2221</phone></user>
     *      
     */
    public function createUser( $user ) {
        // Validate entry
        if ( !$this->_validateUserEntry( $user) ) {
            throw new Exception('Invalid user type, please provide all fields');
        }
        // Build XML
        // A very basic XML without password
        $baseXml = '<user><email>{email}</email><firstname>{firstname}</firstname><lastname>{lastname}</lastname><username>{username}</username></user>';
        // A very basic XML with password, it does not work, user must be created first and then you need to set the password
        //$baseXml = '<user><email>{email}</email><firstname>{firstname}</firstname><lastname>{lastname}</lastname><username>{email}</username><password>{password}</password><password_confirmation>{password}</password_confirmation><password_salt></password_salt><password_algorithm>salt+sha256</password_algorithm></user>';
        // Clean values
        $firstName = htmlentities(trim($user['firstname']));       
        $lastName = htmlentities(trim($user['lastname']));
        $email = strtolower( trim($user['email']) );
        $password = trim($user['password']);
        $userName = strtolower( str_replace("'", '', trim($email) ) ); // Username needs to be lowercase
        // Replace values in baseXml
        $xml = str_replace('{firstname}', $firstName, $baseXml);
        $xml = str_replace('{lastname}', $lastName, $xml);
        $xml = str_replace('{email}', $email, $xml);
        $xml = str_replace('{password}', $password, $xml);
        $xml = str_replace('{username}', $userName, $xml); //echo $xml; exit();
        // Create HTTP client
        $oneLogin = new Zend_Http_Client( OneLogin_Api_Users::URL_CREATE, $this->_proxy_config );
        $oneLogin->setAuth($this->_user_name, $this->_user_password, Zend_Http_Client::AUTH_BASIC);
        // Create user
        $post = null; 
        $post = $oneLogin->setRawData($xml)->setEncType('text/xml')->request(Zend_Http_Client::POST);        
        // Return result
        return $post;
    }
    /**
     * Set password to a OneLogin user via API application
     *
     * @param array $user
     *     
     *
     * @link https://onelogin.zendesk.com/hc/en-us/articles/201175524-Users-API
     * @example base OneLogin API XML
     * <user><password>{password}</password><password_confirmation>{password}</password_confirmation>
     * <password_salt></password_salt><password_algorithm>salt+sha256</password_algorithm></user>
     *
     */
    public function setUserPasswordByUserName( $user ){
        // Validate entry
        if ( !$this->_validateUserPassword( $user) ) {
            throw new Exception('Invalid user type, please provide username and password');
        }
        // A very basic XML
        $baseXml = '<user><password>{password}</password><password_confirmation>{password}</password_confirmation><password_salt></password_salt><password_algorithm>salt+sha256</password_algorithm></user>';
        $password = trim($user['password']);        
        $userName = strtolower( str_replace("'", '', trim($user['email']) ) ); // Username needs to be lowercase
        // Replace values in baseXml
        $xml = str_replace('{password}', $password, $baseXml); //echo $xml; exit();
        // Replace username
        $uri = str_replace('{username}', $userName, OneLogin_Api_Users::URL_SET_PASSWORD_USER);
        // Create HTTP client
        $oneLogin = new Zend_Http_Client( $uri, $this->_proxy_config );
        $oneLogin->setAuth($this->_user_name, $this->_user_password, Zend_Http_Client::AUTH_BASIC);
        // Create user
        $post = null;
        $post = $oneLogin->setRawData($xml)->setEncType('text/xml')->request(Zend_Http_Client::PUT);
        // Return result
        return $post;
    }
    /**
     * Delete a user from OneLogin application
     * 
     * @param integer $userId
     * @throws Exception when $userId is not numeric nor greater than zero
     * @return Zend_Http_Client::response
     */
    public function deleteUser( $userId ) {
        // Validate parameters
        if ( !is_numeric($userId) || intval($userId) <= 0 ) {
            throw new Exception('Invalid user id');
        }
        // Replace user id
        $uri = str_replace('{id}', $userId, OneLogin_Api_Users::URL_DELETE);
        // Create Http client
        $oneLogin = new Zend_Http_Client( $uri, $this->_proxy_config );
        $oneLogin->setAuth($this->_user_name, $this->_user_password, Zend_Http_Client::AUTH_BASIC);
        // Delete user
        $delete = null;
        $delete = $oneLogin->request(Zend_Http_Client::DELETE); // Return as Response
        // Return result
        return $delete;        
    }
    /**
     * Validate user entry as follows
     * 1.- Must be an array
     * 2.- Must contains email, first name, last name
     * @param array $user
     * @return boolean
     */
    private function _validateUserEntry($user) {
        $valid = true;
        if ( !is_array($user) ) {
            $valid = false;
        }
        if ( !array_key_exists('email', $user) && !array_key_exists('firstname', $user) && !array_key_exists('lastname', $user) && !array_key_exists('password', $user) ) {
            $valid = false;
        }        
        $validator = new Zend_Validate_EmailAddress();        
        if ( !$validator->isValid($user['email']) ) {
            $valid = false;
        }        
        return $valid;
    }
    /**
     * Get user's status in OneLogin
     * 
     * @param integer $status
     * @return string
     * @link http://developers.onelogin.com/v1.0/docs/user-elements
     */
    public function getStatus($statusId){
        switch ( intval($statusId) ) {
            case 0:
            default:
                $status = self::USER_UNACTIVATED;
                break;
            case 1:
                $status = self::USER_ACTIVE;
                break;
            case 2:
                $status = self::USER_SUSPENDED;
                break;
            case 3:
                $status = self::USER_LOCKED;
                break;
            case 4:
                $status = self::USER_PASSWORD_EXPIRED;
                break;
            case 5:
                $status = self::USER_AWAITING_PASSWORD_RESET;
                break;
        }
        return $status;        
    }    
    /**
     * Validate user entry as follows
     * 1.- Must be an array
     * 2.- Must contains email, first name, last name
     * @param array $user
     * @return boolean
     */
    private function _validateUserPassword( $user ) {
        $valid = true;
        if ( !is_array($user) ) {
            $valid = false;
        }
        if ( !array_key_exists('email', $user) && !array_key_exists('password', $user) ) {
            $valid = false;
        }
        
        return $valid;
    }
}
?>