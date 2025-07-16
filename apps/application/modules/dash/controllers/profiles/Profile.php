<?php
if(!defined('BASEPATH')) {
exit('No direct script access allowed.');
}

class Profile extends MY_Dashboard {
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
   'page' => 'profile-profile',
   'collect' => [
    'users' => [
     'localdata' => $this->localdata,
     'userdata' => $this->userdata,
    ],
   ],
   'error' => false,
   'title' => 'Profile: Profile | Dash',
  ];
  $collectData['collect']['dates'] = [
   'current' => $this->DateObject->format('Y-m-d H:i:s'),
  ];
  //-------------------------------
  $collectData['json_response'] = [
   'status' => false,
  ];
  
  
  $this->load->view('dash/profiles/profiles', $collectData);
 }
 

}