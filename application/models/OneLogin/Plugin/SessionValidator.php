<?php
class OneLogin_Plugin_SessionValidator extends Zend_Controller_Plugin_Abstract
{

    /**
     * This plugin validates sessions on pages that require
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {

        if (!$this->_isPublicPage($request->getRequestUri()))
        {
            // if user has not identity, must login first
            if (!Zend_Auth::getInstance()->hasIdentity())
            {
                $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
                $redirector->gotoUrl('/portal/index/login/');;
            }
        }
    }

    /**
     * This method validates whether is a public section or not
     *
     * @param varchar $uri
     * @return boolean True when it's a public section
     */
    private function _isPublicPage($uri)
    {
        $publics = array('/portal/index/login', '/portal/index/index', '/settings' );

        foreach ($publics as $public)
        {
            if (strpos($uri,$public)){
                return true;
            }
        }
        return false;
    }

}
?>