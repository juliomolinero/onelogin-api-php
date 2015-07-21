<?php
/**
 * 
 * @author Julio_MOLINERO
 * @version 1.0
 * 
 * @link https://onelogin.zendesk.com/hc/en-us/articles/201175534-Introduction
 * @see REST Operations section
 *
 */
class OneLogin_Api_Abstract {
    // Operation return codes
    const OK = 200; // 200 OK
    const CREATED = 201; // 201 Created
    const NOT_FOUND = 404; // 404 Not found
    const UNPROCESSABLE = 422; // 422 Unprocessable entity - see <errors></errors> in the response document for details.
    // Define properties
    protected $_user_name;
    protected $_user_password;
    protected $_api_login;
    protected $_proxy_config;
    
    /**
     * Create a new object instance.
     * Pull OneLogin API key from local.ini
     * Set API Username and password
     * Optional. Set proxy settings
     * 
     * @throws Exception when either OneLogin API key or password are empty
     */
    public function __construct( ){
        // Load server specific configuration
        $apiConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/local.ini');
        // Perform validation
        if( empty($apiConfig) ){
            throw new Exception('Please set your Application Settings');
        }        
        $api = $apiConfig->olapi->api_key;        
        // Perform validation
        if( empty($api) ){
            throw new Exception('Please set your Application Key');
        }
        if ( strlen($api) == 0 ) {
            throw new Exception('Please set your Application Key');
        }
        // Get current user's password        
        $userPassword = Zend_Auth::getInstance()->getIdentity()->api_user_password;
        if ( empty($userPassword) ) {
            throw new Exception('Invalid password');
        }
        $this->_api_login = $api.':'.$userPassword;
        $this->_user_name = $api;
        $this->_user_password = $userPassword;        
        // Set Proxy if needed
        if ( $apiConfig->olapi->use_proxy == 1 ) {
            $proxy_host = $apiConfig->olapi->proxy_host;
            $proxy_port = $apiConfig->olapi->proxy_port;
            $proxy_user = $apiConfig->olapi->proxy_user;
            $proxy_pass = $apiConfig->olapi->proxy_pass;
            $this->_proxy_config = array('adapter'    => 'Zend_Http_Client_Adapter_Proxy',
                'proxy_host' => $proxy_host,
                'proxy_port' => $proxy_port,
                'proxy_user' => $proxy_user,
                'proxy_pass' => $proxy_pass                
            );
        }        
    }
}
?>