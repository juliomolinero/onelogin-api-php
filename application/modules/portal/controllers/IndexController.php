<?php
/**
 * IndexController
 *
 * @author Julio Molinero
 * @version 1.0
 *
 */
class IndexController extends Zend_Controller_Action
{
    public function init(){
        $this->view->baseUrl = $this->_request->getBaseUrl();
        // Check web browser version
        $browserInfo = $this->_getBrowserInfo();
        if ( $browserInfo['name'] == 'msie' &&  intval($browserInfo['version']) <= 9 ){
            $this->view->versionWarning = 'This version of Internet Explorer is not supported. Some features will not be available.';
        }                
    }
    /**
     * The default action - show the login page
     */
    public function indexAction() {        
        // If we're already logged in, just redirect  
        if(Zend_Auth::getInstance()->hasIdentity()) { 
            return $this->_helper->redirector('index', 'index', 'users');
        }                
    }
    /**
     * Perform login against OneLogin API
     */
    public function loginAction(){
        // Clear identity before logging in
		if ( Zend_Auth::getInstance()->hasIdentity() ){			
			Zend_Auth::getInstance()->clearIdentity();
        	Zend_Session::destroy(true);
		}
		// Proceed to logging in
		if ($this->_request->isPost()) {
		    $username = $this->_request->getPost('username');
		    $password = $this->_request->getPost('password');		    
		    try {
		        // create instance
		        $login = new OneLogin_Acl_Login();
		        $login->login($username,$password);
		        $expirationTime = 60 * 60 * 8; // 8 hours
		        Zend_Session::rememberMe($expirationTime);
		        $this->_helper->layout->enableLayout();
		        return $this->_helper->redirector('ol', 'index', 'users');
		    } catch(Exception $e) {
		        //print_r($e);
		        $msg = $e->getMessage();		        
		        $this->view->message = $msg;
		        $this->render('index');
		    }
		} else {
		    $this->_forward("index");
		}    
    }
    public function logoutAction(){
        Zend_Auth::getInstance()->clearIdentity();
        Zend_Session::destroy(true);
        $this->view->message = "Logged out successfully";
        $this->_forward("index", "index", "portal");
    }    
    /**
     * Check Browser's information
     * @return string
     */
    private function _getBrowserInfo() {
        $browserInfo['agent'] = 'Unknown 0.0.0';
        $browserInfo['name'] = 'Unknown';
        $browserInfo['version'] = '0.0.0';
    
        $browsers = array("firefox", "msie", "opera", "chrome", "safari", "mozilla",
            "seamonkey", "konqueror", "netscape", "gecko", "navigator", "mosaic",
            "lynx", "amaya", "omniweb", "avant", "camino", "flock", "aol");
    
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $match = array();
        foreach($browsers as $browser){
            if (preg_match("#($browser)[/ ]?([0-9.]*)#", $agent, $match)){
                $browserInfo["agent"] = $match[0];
                $browserInfo["name"] = $match[1];
                $browserInfo["version"] = $match[2];
                return $browserInfo;
            }
        }
        return $browserInfo;
    }
}
?>