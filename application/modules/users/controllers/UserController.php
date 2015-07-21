<?php
/**
 * UsersController
*
* @author Julio Molinero
*
* @version 1.0
*
*/
class Users_UserController extends Zend_Controller_Action{
    // Location to store XML and CSV files
    private $_pathToUploadFiles = "users/files/";    
    public function init()  {
        $this->view->baseUrl = $this->_request->getBaseUrl();
        // Get current user 
        $this->_userName = Zend_Auth::getInstance()->getIdentity()->api_user_username;
    }
    public function index(){
        
    }
    /**
     * Let users upload either XML or CSV files only
     */
    public function filesAction() {        
                
    }
    /**
     * Send files to server
     */
    public function uploadAction(){
        $this->view->errorCode = 0;
        try {
            if( empty($_FILES) ){
                throw new Exception('File is empty', 10006);
            }
			// Get file name and extension
			$targetFile = $this->_pathToUploadFiles . basename($_FILES["usersFile"]["name"]);			
			$fileType = pathinfo($targetFile, PATHINFO_EXTENSION);
			// Replace spaces
			$targetFile = str_replace(' ', '', $targetFile);
			// Validate file type
			if( !in_array( strtolower($fileType), array('xml','csv') ) ){
			    throw new Exception('Invalid file type', 10007);
			}
			// Check if file already exists, then remove it
			if ( file_exists($targetFile) ) {
			    unlink($targetFile);			    
			}
			// Proceed to upload and move file to a physical location
			if (move_uploaded_file($_FILES["usersFile"]["tmp_name"], $targetFile)) {
			    $this->view->message = 'File uploaded';
			    $this->view->fileName = $targetFile;			    
			} else {
			    throw new Exception('File could not be uploaded', 10007);
			}
			// Add file to database
			try {
			    if ( strtolower($fileType) == 'xml') {
			        $this->_importXmlFile($targetFile);
			    } else {
			        $this->_importCsvFile($targetFile);
			    }
			} catch (Exception $e) {
			    $message = $e->getMessage();
			    throw new Exception('File could not be imported.'.$message, 10008);
			}
		} catch(Exception $e) {
			$this->view->message = str_replace("'", '', trim($e->getMessage()) );
			$this->view->errorCode = 1;
		}
    }
    /**
     * List recently imported users
     */
    public function listAsyncAction() {
        // Disable layout
        $this->_helper->layout->disableLayout();                
        // Create instance
        $usersToImport = new OneLogin_Support_UsersToImport();
        // Send results to view
        $this->view->applicationUsersToImport = $usersToImport->searchUsers("", 100, 3);        
    }
    /**
     * Add user to Application defined
     * 
     */
    public function addUsersAsyncAction() {
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        // No need to render
        $this->_helper->viewRenderer->setNoRender();
        // Create json object
        $json = new stdClass();
        $json->error = 0;
        $json->message = "";
        // The entire process
        try {
            // Get users to add from database
            $usersToImport = new OneLogin_Support_UsersToImport();
            $users = $usersToImport->listForExport();            
            // Create API instance and create the user
            $usersApi = new OneLogin_Api_Users();            
            // Proceed to create the user
            foreach ($users as $user){
                $data = $this->_prepareUser($user);
                // Prepare Log params
                $logParams = $kv = null;
                foreach ($data as $key => $value) { $kv[] = "$key=$value"; }
                $logParams = join("&", $kv);
                $response = $usersApi->createUser( $data ); //var_dump($response); exit();
                if ($response->isSuccessful()) {
                    $message = 'User&nbsp;'.str_replace( "'", '', trim($response->getMessage()) );
                    $json->message = $message;
                    $this->_addToLog( 'AddUsersAsync', $message, $logParams);
                    // User is created, can we set the password ?
                    $responseCreate = $usersApi->setUserPasswordByUserName( $data );
                    if( $responseCreate->isSuccessful() ){
                        $json->message .= '&nbsp;password has been set.&nbsp;'.str_replace( "'", '', trim($responseCreate->getMessage()));
                    } else {
                        $json->message .= '&nbsp;password could not be set.&nbsp;'.str_replace( "'", '', trim($responseCreate->getMessage()));
                        $logMessage = 'Password for '.$user->email.' could not be set.';
                        $this->_addToLog( 'AddUsersAsync', $logMessage, $logParams.'<br/>-HTTPResponse-'.$responseCreate->getRawBody());
                    }
                    // Remove user added
                    $usersToImport->delete( array( "email=?" => $user->email ) );
                } else {                    
                    $message = 'User could not be created.'.$user->email.'<br/>'.$response->getRawBody();
                    $this->_addToLog( 'AddUsersAsync', $message.$response->getRawBody(), $logParams);
                    throw new Exception( $message );
                }
            }
            $json->message = '<div class="successMessage">Process complete</div>';
        } catch( Exception $e){
            $message = str_replace("'", '', trim($e->getMessage()) );
            $json->error = 1;
            $json->message = '<div class="errorMessage">Error while processing your request<br/>'.$message.'<br/>Please contact your system administrator.</div>';            
        }
        echo $this->_helper->json($json); // Send response
    }
    /**
     * Process a CSV file and add users to applications_users_to_import
     * @param string $fileName File Location
     */
    private function _importCsvFile( $fileName ){                
        // Create instance
        $usersToImport = new OneLogin_Support_UsersToImport();
        // Delete previous users
        $usersToImport->delete( array("1=1") );
        // Open the CSV file for reading
        $fileProcess = fopen($fileName, 'r');
        // Get rows and store them into an array        
        while( ($rows = fgetcsv( $fileProcess , 1000, ",", "\n") ) !== FALSE ){
            //echo $rows[0] . $rows[1] . $rows[2] . $rows[3] . "<br />\n";
            // Create one object per row
            $user = new stdClass();
            $user->userName = $rows[0];
            $user->firstName = $rows[1];
            $user->lastName = $rows[2];
            $user->email = $rows[3];
            //var_dump($user); exit();
            if ( !$usersToImport ) {
                // Create instance
                $usersToImport = new OneLogin_Support_UsersToImport();
            }
            // Add to database
            $usersToImport->importUser($user);
        } // End while
        // Release resources
        fclose( $fileProcess );
        unset($applicationsUsersToImport);        
    }
    /**
     * Process a XML file and add users to applications_users_to_import
     * @param string $fileName File Location
     */
    private function _importXmlFile( $fileName ){                
        // Create instance
        $usersToImport = new OneLogin_Support_UsersToImport();
        // Delete previous users
        $usersToImport->delete( array("1=1") );
        // Open the XML file for reading
        $users = new SimpleXMLElement($fileName, NULL, true); //echo count($users); var_dump($users->user);
        foreach ($users as $xmlUser) { //echo $xmlUser->username;             
            // Create one object per row
            $user = new stdClass();
            $user->userName = $xmlUser->username;
            $user->firstName = $xmlUser->firstname;
            $user->lastName = $xmlUser->lastname;
            $user->email = $xmlUser->email;
            //var_dump($user); exit();
            if ( !$usersToImport ) {
                // Create instance
                $usersToImport = new OneLogin_Support_UsersToImport();
            }
            // Add to database
            $usersToImport->importUser($user);
        }
        // Release resources
        unset($users);
        unset($applicationsUsersToImport);
    }
    /**
     * Get User as object as parse into array
     * @param object $user
     * @return array
     */
    private function _prepareUser($user) {        
        // User must be an array
        $data = array();
        // Get values from database
        $data['username'] = $user->user_name;
        $data['firstname'] = $user->first_name;
        $data['lastname'] = $user->last_name;
        $data['password'] = $this->_getPassword(7);
        $data['email'] = $user->email;
        // Return array
        return $data;
    }
    /**
     * Build a random string to use it as password
     * @param integer $l String length
     * @param string $c
     * @return string
     */
    private function _getPassword($l, $c = 'abcdefghijklmnopqrstuvwxyz1234567890'){
        for ($s = '', $cl = strlen($c)-1, $i = 0; $i < $l; $s .= $c[mt_rand(0, $cl)], ++$i);
        return '0n_'.strtoupper($s).'!';        
    }
    /**
     * Add to log
     * @param string $action
     * @param string $message
     * @param array $post All information sent via POST object
     */
    private function _addToLog($page, $message, $post){                
        // Define data to add
		$data = array();
		$data['user'] = strip_tags( $this->_userName );
		$data['page'] = $page;
		$data['params'] = $message.' '.$post;
		$data['ip'] = $_SERVER['REMOTE_ADDR'];
		$data['date'] = time();
		$data['remote_host'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);						
		// add row to ssp_sites_metrics table
		$usersLogs = new OneLogin_Acl_UsersLogs();
		$usersLogs->insert($data);
    }
}
?>