<?php
/**
 * IndexController. Visible to ADMINS only
 *
 * @author Julio Molinero
 * @version 1.0
 *
 */
class Admin_IndexController extends Zend_Controller_Action
{
    /**
     * Initialize controller
     * @see Zend_Controller_Action::init()
     * @throws Exception when user in not an admin
     */
    public function init(){        
        $this->view->baseUrl = $this->_request->getBaseUrl();                
    }    
    /**
     * Enable account
     */
    public function setActiveAsyncAction() {
        // No need to render
        $this->_helper->viewRenderer->setNoRender();
        // Create json object
        $json = new stdClass();
        $json->error = 0;
        $json->message = "";
        // The entire process
        try {
            // Get parameters
            $id = $this->_getParam("id");
            // Create instance and insert user
            $users = new OneLogin_Acl_Users();
            $users->setActive($id);
            $json->message = "User updated";
        } catch (Exception $e){
            $json->error = 1;
            $json->message = str_replace("'", '', trim($e->getMessage()) );
        }
        echo $this->_helper->json($json); // Send response
    }
    /**
     * Disable account
     */
    public function unsetActiveAsyncAction() {        
        // No need to render
        $this->_helper->viewRenderer->setNoRender();
        // Create json object
        $json = new stdClass();
        $json->error = 0;
        $json->message = "";
        // The entire process
        try {
            // Get parameters
            $id = $this->_getParam("id");
            // Create instance and insert user
            $users = new OneLogin_Acl_Users();
            $users->unsetActive($id);
            $json->message = "User updated";
        } catch (Exception $e){
            $json->error = 1;
            $json->message = str_replace("'", '', trim($e->getMessage()) );
        }
        echo $this->_helper->json($json); // Send response
    }    
}