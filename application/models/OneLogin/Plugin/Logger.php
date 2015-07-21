<?php
class OneLogin_Plugin_Logger extends Zend_Controller_Plugin_Abstract {	

	public function preDispatch(Zend_Controller_Request_Abstract $request) {
		
		// home page must be excluded from log
		if (strtolower($request->getRequestUri()) === strtolower($this->_request->getBaseUrl().'/')) {
			return;
		}		
		if ($this->_isIncludedInLog($request->getRequestUri())) {
			// add metric
			$this->_addMetric($request->getRequestUri());
		}			
	}	

	/**
	 * Add a record to ol_admins_logs table
	 * @param string $uri
	 */
	private function _addMetric($uri) {
		// Get user object in session
		$userName = Zend_Auth::getInstance()->getIdentity()->api_user_username;

		if ( !isset($userName) || empty($userName) ) {
			return ; // user is not logged in
		}	
		
		if ( strlen($userName) > 0 ) {
			// define query_string or post
			$query_string = $_SERVER['QUERY_STRING'];
			if ($_POST && strlen($query_string) == 0) {
			  // TODO: replace this with PHP query building function
			  $kv = array();
			  foreach ($_POST as $key => $value) {
			    $kv[] = "$key=$value";
			  }
			  $query_string = join("&", $kv);
			}
			// Define data to add
			$data = array();
			$data['user'] = strip_tags($userName);
			$data['page'] = strtolower(strtok($_SERVER['SERVER_PROTOCOL'], '/')).'://'.$_SERVER['HTTP_HOST'].$uri;
			$data['params'] = $query_string;
			$data['ip'] = $_SERVER['REMOTE_ADDR'];
			$data['date'] = time();
			$data['remote_host'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);						
			// add row to ssp_sites_metrics table
			$usersLogs = new OneLogin_Acl_UsersLogs();
			$usersLogs->insert($data);			
		}		
	}
	/**
	 * Define what links are going to be logged in our database
	 * @param string $uri
	 * @return boolean
	 */
	private function _isIncludedInLog($uri){
		// define which pages are included in log
		$exclusions = array('users/index/delete-admin-async', 'users/index/add-admin-async', 'users/index/ol-create-async', 'users/index/ol-delete-async',
		    'users/user/upload' );		
		foreach ($exclusions as $exclusion){
			if (strpos($uri,$exclusion)){                                
                return true;
            }
		}
		return false;
	}
		
}