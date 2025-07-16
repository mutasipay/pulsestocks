<?php
defined('BASEPATH') OR exit('No direct script access allowed: Insert');

class About extends MY_Dashboard {
	private $error = FALSE, $error_msg = [];
	protected $DateObject;
	protected $base_dash;
	function __construct() {
		parent::__construct();
  $this->load->model('dash/dashboard/Model_dashboard', 'mod_dashboard');
		
		$this->DateObject = $this->mod_dashboard->get_dateobject();
	}
	
	
	public function index() {
  $collectData = [
   'page' => 'dashboard-about',
   'collect' => [],
   'error' => false,
   'title' => 'Dashboard: About | Dash',
  ];
  $collectData['collect']['dates'] = [
   'current' => $this->DateObject->format('Y-m-d H:i:s'),
  ];
  $collectData['collect']['users'] = [
   'localdata' => $this->localdata,
   'userdata' => $this->userdata,
  ];
  
  
  $this->load->view('dash/dashboard/dashboard.php', $collectData);
 }









}