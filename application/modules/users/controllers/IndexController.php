<?php
/**
 * IndexController. Visible to ADMINS only
 *
 * @author Julio Molinero
 * @version 1.0
 *
 */
class Users_IndexController extends Zend_Controller_Action
{
    /**
     * Initialize controller
     * @see Zend_Controller_Action::init()
     * @throws Exception when user in not an admin
     */
    public function init(){        
        $this->view->baseUrl = $this->_request->getBaseUrl();
        $this->view->currentUser = Zend_Auth::getInstance()->getIdentity()->api_user_username;        
    }
    /**
     * The default action - show the login page
     */
    public function indexAction() {
        $this->view->users = null;
        try {
            $users = new OneLogin_Acl_Users( );            
            $this->view->users = $users->listUsers( 'user', 'ASC', 50 );
        } catch (Exception $e) {
            throw new Exception( $e->getMessage() );
        }
    }
// OneLogin API functions ================================================>    
    /**
     * Get user's information via OneLogin API
     */
    public function olGetUserAction() {
        $this->view->user = null;
        try {
            $userName = $this->_getParam('user');
            $users = new OneLogin_Api_Users( );
            //$response = $users->getUser( Zend_Auth::getInstance()->getIdentity()->api_user_username );
            $response = $users->getUser( $userName );
            //$response = $users->getUser("john_j_o'neill@dell.com"); // Testing single quotes
            if ($response->isSuccessful()) {
                //echo $xmlResult;
                $xmlResult = simplexml_load_string( $response->getBody() );
            } else {
                //var_dump($response);exit();
                throw new Exception( $response->getMessage().'&nbsp;'.$response->getBody() ); 
            }
            $this->view->user = $xmlResult;
    
        } catch (Exception $e) {
            throw new Exception( $e->getMessage() );
        }
    }
    /**
     * List all OneLogin user via API
     * @throws Exception
     */    
    public function olAction() {
        $this->view->users = null;
        try {
            // Create API instance and get the user list
            $users = new OneLogin_Api_Users();
            $response = $users->listUsers();
            if ($response->isSuccessful()) {
                //echo $xmlResult;
                $xmlResult = simplexml_load_string( $response->getBody() );
            } else {
                throw new Exception( $response->getMessage() );
            }
            // Define what XMLElements we are using
            $elementsDisplayed = array('id', 'openid-name','email', 'status', 'last-login');
            // Process the XML into an array
            $users = array();            
            foreach($xmlResult->children() as $child){
                // Create new object
                $user = new stdClass();
                // Get children
                foreach ($child->children() as $subChild){
                    if ( in_array( strtolower($subChild->getName()), $elementsDisplayed) ) {
                        $property = str_replace( '-', '', $subChild->getName());
                        //echo $property .':'.$subChild.'<br/>';
                        $user->$property = strip_tags($subChild);
                    }
                }
                // Add object to array
                array_push( $users, $user );
            } // end foreach
            // Sort array
            asort( $users );
            // Send to view
            $this->view->users = $users;
        } catch (Exception $e) {
            throw new Exception( $e->getMessage() );
        }
    }
    /**
     * List all OneLogin user via API
     * @throws Exception
     */
    public function olListAsyncAction() {
        // Disable layout
        $this->_helper->layout->disableLayout();
        $this->view->users = null;
        try {
            // Create API instance and get the user list
            $users = new OneLogin_Api_Users();
            $response = $users->listUsers();
            if ($response->isSuccessful()) {
                //echo $xmlResult;
                $xmlResult = simplexml_load_string( $response->getBody() );
            } else {
                throw new Exception( $response->getMessage() );
            }
            // Define what XMLElements we are using
            $elementsDisplayed = array('id', 'openid-name','email', 'status', 'last-login');
            // Process the XML into an array
            $users = array();
            foreach($xmlResult->children() as $child){
                // Create new object
                $user = new stdClass();
                // Get children
                foreach ($child->children() as $subChild){
                    if ( in_array( strtolower($subChild->getName()), $elementsDisplayed) ) {
                        $property = str_replace( '-', '', $subChild->getName());
                        //echo $property .':'.$subChild.'<br/>';
                        $user->$property = strip_tags($subChild);
                    }
                }
                // Add object to array
                array_push( $users, $user );
            } // end foreach
            // Sort array
            asort( $users );
            // Send to view
            $this->view->users = $users;
        } catch (Exception $e) {
            throw new Exception( $e->getMessage() );
        }
    }
    /**
     * Remove a user with OneLogin API
     * @throws Exception
     * 
     * @example Add a line when deleting a user
     * @link http://jsfiddle.net/davidThomas/DynyP/1/
     * 
     */
    public function olDeleteAsyncAction(){
        // No need to render
        $this->_helper->viewRenderer->setNoRender();
        // Create json object
        $json = new stdClass();
        $json->error = 0;
        $json->message = "";
        // The entire process
        try {
            // Get parameters
            $userId = $this->_getParam("id");
            // Create API instance and remove the user
            $users = new OneLogin_Api_Users();
            $response = $users->deleteUser( $userId );
            if ($response->isSuccessful()) {
                $message = 'User removed&nbsp;'.str_replace( "'", '', trim($response->getMessage()) );                
                $json->message = $message;
            } else {
                throw new Exception( $response->getMessage() );
            }            
        } catch (Exception $e){
            $json->error = 1;
            $json->message = str_replace("'", '', trim($e->getMessage()) );            
        }
        echo $this->_helper->json($json); // Send response
    }
    /**
     * Create a user with OneLogin API
     * @throws Exception
     */
    public function olCreateAsyncAction(){
        // No need to render
        $this->_helper->viewRenderer->setNoRender();
        // Create json object
        $json = new stdClass();
        $json->error = 0;
        $json->message = "";
        // The entire process
        try {
            // User must be an array
            $user = array();
            // Get parameters and set values in array
            $user['email'] = $this->_getParam("email");
            $user['firstname'] = $this->_getParam("firstname");
            $user['lastname'] = $this->_getParam("lastname");
            $user['password'] = $this->_getParam("password");
            // Create API instance and create the user
            $users = new OneLogin_Api_Users();
            $response = $users->createUser( $user ); //var_dump($response); exit();
            if ($response->isSuccessful()) {
                $message = 'User&nbsp;'.str_replace( "'", '', trim($response->getMessage()) );
                $json->message = $message;                
                // User is created, can we set the password ?
                $responseCreate = $users->setUserPasswordByUserName( $user );
                if( $responseCreate->isSuccessful() ){
                    $json->message .= '&nbsp;password has been set.&nbsp;'.str_replace( "'", '', trim($responseCreate->getMessage()));                    
                } else {
                    $json->message .= '&nbsp;password could not be set.&nbsp;'.str_replace( "'", '', trim($responseCreate->getMessage()));                    
                }                                
            } else {
                throw new Exception( str_replace( "'", '', trim($response) ) );
            }
            
        } catch (Exception $e){
            $json->error = 1;
            $json->message = str_replace("'", '', trim($e->getMessage()) );
        }
        echo $this->_helper->json($json); // Send response        
    }
// OneLogin API functions ================================================>
// Local ol_admins table functions ================================================>    
    /**
     * List current users in ol_admins table
     */
    public function listAdminAsyncAction() {
        // Disable layout
        $this->_helper->layout->disableLayout();
        $this->view->users = null;
        try {
            $users = new OneLogin_Acl_Users( );
            $this->view->users = $users->listUsers( 'user', 'ASC', 50 );
        } catch (Exception $e) {
            throw new Exception( $e->getMessage() );
        }
    }
    /**
     * Insert a user in ol_admins table
     */
    public function addAdminAsyncAction() {
        // No need to render
		$this->_helper->viewRenderer->setNoRender();
		// Create json object
        $json = new stdClass();
        $json->error = 0;
        $json->message = "";
        // The entire process
        try {
            // Get parameters
            $userName = $this->_getParam("username");
            $email = $this->_getParam("email");
            $password = $this->_getParam("password");            
            // Create instance and insert user 
            $users = new OneLogin_Acl_Users();
            $users->addUser( $userName, $email, $password );
            $json->message = "User added";
        } catch (Exception $e){
            $json->error = 1;            
            $json->message = str_replace("'", '', trim($e->getMessage()) );            
        }
        echo $this->_helper->json($json); // Send response
    }
    /**
     * Delete a user from ol_admins table
     */
    public function deleteAdminAsyncAction() {
        // No need to render
        $this->_helper->viewRenderer->setNoRender();
        // Create json object
        $json = new stdClass();
        $json->error = 0;
        $json->message = "";        
        // The entire process
        try {
            // Get parameters
            $userName = $this->_getParam("username");
            $where = array("user = ?" => $userName );
            // Current logged user cannot be removed
            if ( $this->view->currentUser == $userName ) {
                throw new Exception('You cannot remove your own username');
            }
            // Create instance and insert user
            $users = new OneLogin_Acl_Users();
            $users->delete( $where );
            $json->message = "User removed";
        } catch (Exception $e){
            $json->error = 1;
            $json->message = str_replace("'", '', trim($e->getMessage()) );
        }
        echo $this->_helper->json($json); // Send response
    }
// Local ol_admins table functions ================================================>
}