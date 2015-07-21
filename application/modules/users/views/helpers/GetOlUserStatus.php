<?php
class Zend_View_Helper_GetOlUserStatus extends Zend_View_Helper_Abstract  {

    /**
     * Return status description based on id
     *
     * @param integer $statusId
     * @return string Status
     */
    public function getOlUserStatus( $userName ){
        $status = "";
        $userId = 0;
        $userName = trim( strtolower($userName) );
        try {
            $users = new OneLogin_Api_Users( );
            $response = $users->getUser( $userName );
            if ($response->isSuccessful()) {
                //echo $xmlResult;
                $xmlResult = simplexml_load_string( $response->getBody() );                
                $elementsRequired = array('status', 'id');
                foreach($xmlResult as $child){                
                    if( in_array( strtolower($child->getName()), $elementsRequired  ) ){
                        $tagName = trim( str_replace('-', '&nbsp;', $child->getName()) );
                        $className = trim( str_replace('-', '', $child->getName()) );
                        // Get status description if needed
                        if ( strtolower($className)=='status' ) {                            
                            $status = self::_getUserStatus( trim($child) );                        
                        }
                        if ( strtolower($className)=='id' ) {
                            $userId = trim($child);
                        }
                    } // End if not in array                
                } // end foreach
            } else {
                //var_dump($response);exit();
                throw new Exception( $response->getMessage().'&nbsp;'.$response->getBody() );
            }                    
        } catch (Exception $e) {
            $msg = $e->getMessage();//var_dump($e->getMessage()); exit(); // DEBUG ONLY
            $status = "";
        }
        return array($status, $userId);
    }
    private function _getUserStatus( $statusId ){
        $status = "Unknown";
        switch ( intval($statusId) ) {
            case 0:
            default:
                $status = 'Unactivated';
                break;
            case 1:
                $status = 'Active';
                break;
            case 2:
                $status = 'Suspended';
                break;
            case 3:
                $status = 'Locked';
                break;
            case 4:
                $status = 'Password expired';
                break;
            case 5:
                $status = 'Awaiting password reset';
                break;
        }
        return $status;
    }
}