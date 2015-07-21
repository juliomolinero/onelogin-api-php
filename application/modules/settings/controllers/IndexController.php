<?php
/**
 * IndexController
 *
 * @author Julio Molinero
 * @version 1.0
 *
 */
class Settings_IndexController extends Zend_Controller_Action
{
    /**
     * The default action - show the home page
     */
    public function indexAction(){
        // Display current PHP and Zend version        
        $this->view->zfVersion = Zend_Version::VERSION;        
        $this->view->phpInfo = phpinfo();        
    }
}
?>