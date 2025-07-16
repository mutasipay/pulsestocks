<?php
if(!defined('BASEPATH')) {
exit('No direct script access allowed: Alerts');
}

class Alerts extends MY_Dashboard {
	private $error = FALSE, $error_msg = [];
	protected $DateObject;
	protected $base_dash;
	function __construct() {
		parent::__construct();
  $this->load->model('dash/dashboard/Model_dashboard', 'mod_dashboard');
		$this->DateObject = $this->mod_dashboard->get_dateobject();
  # Load Model Alerts
  $this->load->model('dash/alerts/Model_alerts', 'mod_alerts');
  
  # Load Input Security Check
		$this->load->helper('security');
		$this->load->library('form_validation');
	}
 
 
 
 public function users() {
  $collectData = [
   'page' => 'alerts-users',
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
  $collectData['collect']['alert_type'] = 'drafted';
  try {
   $collectData['collect']['alerts'] = [];
   $collectData['collect']['alerts']['counts'] = $this->mod_alerts->get_usersalerts_counts($collectData['collect']['users']['localdata'], $collectData['collect']['alert_type']);
   if(!isset($collectData['collect']['alerts']['counts']->val_counts)) {
    $this->error = true;
    $this->error_msg[] = "Not have val-counts.";
   }
  } catch(Exception $ex) {
   $this->error = true;
   $this->error_msg[] = "Cannot get useralerts with exception: {$ex->getMessage()}.";
  }
  if(!$this->error) {
   $collectData['json_response']['status'] = true;
   $collectData['json_response']['data'] = $collectData['collect']['alerts']['counts'];
  } else {
   $collectData['json_response']['errors'] = $this->error_msg;
  }
  // Show Response
  $this->output->set_content_type('application/json');
  $this->output->set_output(json_encode($collectData['json_response']));
 }
 
 
 
 function get_data() {
  $collectData = [
   'page' => 'alerts-users',
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
  $collectData['collect']['alert_type'] = 'drafted';
  try {
   $this->form_validation->set_rules('alert_type', 'Type of Alerts', 'required|max_length[512]|trim|xss_clean');
   if($this->form_validation->run() == FALSE) {
				$this->error = true;
				$this->error_msg[] = validation_errors();
			}
  } catch(Exception $ex) {
   $this->error = true;
   $this->error_msg[] = "Canot validate input params.";
  }
  if(!$this->error) {
   $colectData['collect']['input_params'] = [
    'alert_type' => $this->input->post('alert_type'),
   ];
  }
  if(!$this->error) {
   try {
    $collectData['collect']['alerts'] = [];
    $collectData['collect']['alerts']['data'] = [
     'count' => $this->mod_alerts->get_usersalerts_counts($collectData['collect']['users']['localdata'], $collectData['collect']['alert_type']),
    ];
    if(!isset($collectData['collect']['alerts']['data']['count']->val_counts)) {
     $this->error = true;
     $this->error_msg[] = "Not have val-counts to get data.";
    }
   } catch(Exception $ex) {
    $this->error = true;
    $this->error_msg[] = "Cannot get useralerts [count] with exception: {$ex->getMessage()}.";
   }
  }
  if(!$this->error) {
   try {
    $collectData['collect']['alerts']['types'] = [
     'alert_type' => $collectData['collect']['alert_type'],
     'alert_processed' => 'N',
    ];
    $collectData['collect']['alerts']['params'] = [
     'limit' => 5,
     'start' => 0,
    ];
    $collectData['collect']['alerts']['data']['data'] = $this->mod_alerts->get_usersalerts_datas($collectData['collect']['users']['localdata'], $collectData['collect']['alerts']['types'], $collectData['collect']['alerts']['params']);
    if(!is_array($collectData['collect']['alerts']['data']['data'])) {
     $this->error = true;
     $this->error_msg[] = "Not have data alerts while get data.";
    }
   } catch(Exception $ex) {
    $this->error = true;
    $this->error_msg[] = "Cannot get useralerts [data] with exception: {$ex->getMessage()}.";
   }
  }
  // Show Response
  if(!$this->error) {
   $collectData['json_response']['status'] = true;
   $collectData['json_response']['data'] = $collectData['collect']['alerts']['data']['data'];
  } else {
   $collectData['json_response']['errors'] = $this->error_msg;
  }
  $this->output->set_content_type('application/json');
  $this->output->set_output(json_encode($collectData['json_response']));
 }
}