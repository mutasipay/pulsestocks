<?php
defined('BASEPATH') OR exit('No direct script access allowed: Insert');

class Settings extends MY_Controller {
	private $error = FALSE, $error_msg = [];
	protected $DateObject;
	protected $base_market, $base_payment, $base_dash;
	function __construct() {
		parent::__construct();
		$this->load->model('dash/dashboard/Model_dashboard', 'mod_dashboard');
  $this->load->model('dash/numbers/Model_numbers', 'mod_numbers');
		
		$this->DateObject = $this->mod_dashboard->get_dateobject();
		
		# Load Input Security Check
		$this->load->helper('security');
		//$this->load->helper('form');
		$this->load->library('form_validation');
	}
	
	
	public function index() {
  $collectData = [
   'page' => 'settings-index',
   'collect' => [],
   'error' => false,
   'title' => 'Settings | Dash',
  ];
  $collectData['collect']['dates'] = [
   'current' => $this->DateObject->format('Y-m-d H:i:s'),
  ];
  
  $this->load->view('dash/settings/settings.php', $collectData);
 }









}