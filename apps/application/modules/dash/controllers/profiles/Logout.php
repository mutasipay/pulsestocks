<?php
if(!defined('BASEPATH')) {
exit('No direct script access allowed: Alerts');
}

class Logout extends MY_Dashboard {
	private $error = FALSE, $error_msg = [];
	protected $DateObject;
	protected $base_dash;
	function __construct() {
		parent::__construct();
  $this->load->model('dash/dashboard/Model_dashboard', 'mod_dashboard');
		$this->DateObject = $this->mod_dashboard->get_dateobject();
  # Load Model Alerts
  $this->load->model('dash/profile/Model_profiles', 'mod_profiles');
  
  # Load Input Security Check
		$this->load->helper('security');
		$this->load->library('form_validation');
	}
 
 
 
 public function index() {
  $collectData = [
   'page' => 'profile-logout',
   'collect' => [
    'users' => [
     'localdata' => $this->localdata,
     'userdata' => $this->userdata,
    ],
   ],
   'error' => false,
   'title' => 'Dashboard: Alerts | Dash',
  ];
  $collectData['collect']['dates'] = [
   'current' => $this->DateObject->format('Y-m-d H:i:s'),
  ];
  //-------------------------------
  $collectData['json_response'] = [
   'status' => false,
  ];
  try {
   $collectData['collect']['logout'] = $this->mod_profiles->set_logout();
   if(!isset($collectData['collect']['logout']['status'])) {
    $this->error = true;
    $this->error_msg[] = "Not have expected status response from model.";
   }
  } catch(Exception $ex) {
   $this->error = true;
   $this->error_msg[] = "Cannot logged-out profile with exception: {$ex->getMessage()}.";
  }
  if(!$this->error) {
   if($collectData['collect']['logout']['status'] !== true) {
    $this->error = true;
    $this->error_msg[] = "Status response not true while logged-out.";
    if(isset($collectData['collect']['logout']['errors'])) {
     $this->error_msg[] = $collectData['collect']['logout']['errors'];
    }
   }
  }
  if(!$this->error) {
   redirect(base_url('dash/account/login/index'));
   exit;
  } else {
   $this->output->set_content_type('application/json');
   $this->output->set_output(json_encode([
    'status' => false,
    'errors' => $this->error_msg,
   ]));
  }
 }
 

}