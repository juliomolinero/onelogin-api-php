<?php
class Zend_View_Helper_GetUserStatus extends Zend_View_Helper_Abstract  {

    /**
     * Return status description based on id
     * 
     * @param integer $statusId
     * @return string Status
     */
    public function getUserStatus( $statusId ){
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