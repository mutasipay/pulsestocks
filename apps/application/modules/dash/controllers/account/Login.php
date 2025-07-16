<?php
if(!defined('BASEPATH')) {
 exit('No direct script access allowed: Account/Login');
}

class Login extends MY_Controller {
	private $error = FALSE, $error_msg = [];
	protected $DateObject;
	protected $base_dash;
	function __construct() {
		parent::__construct();
		$this->load->model('dash/account/Model_account', 'mod_account');
		$this->DateObject = $this->mod_account->get_dateobject();
		
		# Load Input Security Check
		$this->load->helper('security');
		//$this->load->helper('form');
		$this->load->library('form_validation');
  
  // Start Userdata
  $this->userdata_start();
	}
	private function userdata_start() {
  try {
   $localdata = $this->mod_account->get_localdata();
   if(isset($localdata['seq'])) {
    redirect(base_url('dash'));
    exit;
   }
  } catch(Exception $e) {
   throw $e;
  }
 }
	
	public function index() {
  $collectData = [
   'page' => 'form-login',
   'collect' => [],
   'error' => false,
   'title' => 'Login to System',
  ];
  $collectData['collect']['dates'] = [
   'current' => $this->DateObject->format('Y-m-d H:i:s'),
  ];
  $collectData['collect']['login_server'] = 'localhost';
  
  
  $this->load->view('dash/account/account.php', $collectData);
 }
 
 
 private function auth(array $input_params = []) {
  $collectData = [
   'page' => 'form-login',
   'collect' => [],
   'error' => false,
   'title' => 'Login to System',
  ];
  $collectData['collect']['dates'] = [
   'current' => $this->DateObject->format('Y-m-d H:i:s'),
  ];
  $collectData['json_response'] = [
   'status' => false,
   'errors' => [],
   'data' => null,
  ];
  if(!$this->error) {
   $collectData['collect']['input_params'] = [
    'login_email' => (isset($input_params['login_email']) ? $input_params['login_email'] : ''),
    'login_password' => (isset($input_params['login_password']) ? $input_params['login_password'] : ''),
   ];
   if(!filter_var($collectData['collect']['input_params']['login_email'], FILTER_VALIDATE_EMAIL)) {
    $this->error = true;
				$this->error_msg[] = "Not valid an email address given.";
   }
   if(empty($collectData['collect']['input_params']['login_password'])) {
    $this->error = true;
				$this->error_msg[] = "Empty password was given.";
   }
  }
  if(!$this->error) {
   try {
    $collectData['collect']['login_params'] = [
     'auth_params' => $this->mod_account->auth_login_params($collectData['collect']['input_params']['login_email'], $collectData['collect']['input_params']['login_password']),
    ];
    if(!isset($collectData['collect']['login_params']['auth_params']['params']['account_email']) || !isset($collectData['collect']['login_params']['auth_params']['params']['account_password']) || !isset($collectData['collect']['login_params']['auth_params']['hashed_strings']['iv']) || !isset($collectData['collect']['login_params']['auth_params']['hashed_strings']['encrypted']) || !isset($collectData['collect']['login_params']['auth_params']['app']['key'])) {
     $this->error = true;
     $this->error_msg[] = "Not have IV and Encrypted-Password, maybe an error is occured.";
     if(isset($collectData['collect']['login_params']['auth_params']['errors'])) {
      $this->error_msg[] = $collectData['collect']['login_params']['auth_params']['errors'];
     }
    }
   } catch(Exception $ex) {
    $this->error = true;
				$this->error_msg[] = "Cannot make password signature as encrypted password with exception: {$ex->getMessage()}.";
   }
  }
  // Make Login to Auth Center
  if(!$this->error) {
   if(isset($collectData['collect']['login_params']['auth_params']['headers'])) {
    $collectData['collect']['authlogin_params'] = [
     'params' => [
      'account_email' => $collectData['collect']['login_params']['auth_params']['params']['account_email'],
      'account_password' => $collectData['collect']['login_params']['auth_params']['hashed_strings']['encrypted'],
     ],
     'headers' => $collectData['collect']['login_params']['auth_params']['headers'],
    ];
    $collectData['collect']['authlogin_params']['headers']['X-Client-Iv'] = trim($collectData['collect']['login_params']['auth_params']['hashed_strings']['iv']);
    try {
     $collectData['collect']['login_params']['hashed_signatures'] = $this->mod_account->auth_login_signatures([
      'email' => $collectData['collect']['login_params']['auth_params']['params']['account_email'],
      'password' => $collectData['collect']['login_params']['auth_params']['params']['account_password'],
     ], $collectData['collect']['login_params']['auth_params']['hashed_strings']['iv'], $collectData['collect']['login_params']['auth_params']['app']['key']);
     if($collectData['collect']['login_params']['hashed_signatures']['status'] !== true) {
      $this->error = true;
      $this->error_msg[] = "Status from model not true while make signature strings.";
      if(isset($collectData['collect']['login_params']['hashed_signatures']['errors'])) {
       $this->error_msg = [...$this->error_msg, ...$collectData['collect']['login_params']['hashed_signatures']['errors']];
      }
     }
     if(!isset($collectData['collect']['login_params']['hashed_signatures']['signatures']['encrypted'])) {
      $this->error = true;
      $this->error_msg[] = "Not have encrypted signature.";
     }
    } catch(Exception $ex) {
     $this->error = true;
     $this->error_msg[] = "Cannot make login-signatures to auth-center with exception: {$ex->getMessage()}.";
    }
   } else {
    $this->error = true;
    $this->error_msg[] = 'Not have headers from model-account as auth-params.';
   }
  }
  if (!$this->error) {
			$collectData['collect']['authlogin_params']['headers']['Signature'] = trim($collectData['collect']['login_params']['hashed_signatures']['signatures']['encrypted']);
   $collectData['collect']['authlogin_params']['login_purpose'] = 'dashboard';
   try {
    $collectData['collect']['authcenter_login'] = $this->mod_account->auth_login_type_password($collectData['collect']['authlogin_params'], $collectData['collect']['login_params']['auth_params']['app']['uuid']);
    if($collectData['collect']['authcenter_login']['status'] !== TRUE) {
     $this->error = true;
     $this->error_msg[] = "Failed login to account center from model-account.";
     $this->error_msg[] = $collectData['collect']['authcenter_login'];
     
     if(isset($collectData['collect']['authcenter_login']['errors'])) {
      $this->error_msg = array(...$this->error_msg, ...$collectData['collect']['authcenter_login']['errors']);
     }
    }
   } catch(Exception $ex) {
    $this->error = true;
    $this->error_msg[] = "Cannot make a login to auth-center with exception: {$ex->getMessage()}.";
   }
		}
  
  // Make Responses
  if(!$this->error) {
   $collectData['json_response']['status'] = true;
   $collectData['json_response']['data'] = $collectData['collect']['authcenter_login'];
  } else {
   $collectData['json_response']['errors'] = $this->error_msg;
  }
  // Response Output
  $this->output->set_content_type('application/json');
  $this->output->set_output(json_encode($collectData['json_response']));
 }
 public function login(string $envcode = '') {
  $collectData = [
   'page' => 'form-login',
   'collect' => [],
   'error' => false,
   'title' => 'Login to System',
  ];
  $collectData['collect']['dates'] = [
   'current' => $this->DateObject->format('Y-m-d H:i:s'),
  ];
  $collectData['json_response'] = [
   'status' => false,
   'errors' => [],
   'data' => null,
  ];
  try {
   $this->form_validation->set_rules('login_email', 'Login Username', 'required|max_length[32]|trim|xss_clean');
			$this->form_validation->set_rules('login_password', 'Login Password', 'required|max_length[128]|trim|xss_clean');
			$this->form_validation->set_rules('login_server', 'Login Server', 'required|max_length[16]|trim|xss_clean');
			if($this->form_validation->run() == FALSE) {
				$this->error = true;
				$this->error_msg[] = validation_errors();
			}
  } catch(Exception $ex) {
   $this->error = true;
   $this->error_msg[] = "Cannot capture login-param inputs with exception: {$ex->getMessage()}."; 
  }
  if(!$this->error) {
   $collectData['collect']['input_params'] = [
    'login_email' => $this->input->post('login_email'),
    'login_password' => $this->input->post('login_password'),
   ];
   return $this->auth($collectData['collect']['input_params']);
  } else {
   $collectData['json_response']['errors'] = $this->error_msg;
   // Response Output
   $this->output->set_content_type('application/json');
   $this->output->set_output(json_encode($collectData['json_response']));
  }
 }
 
 
 public function sha256descrypt() {
  $collectData = [
   'page' => 'form-login',
   'collect' => [],
   'error' => false,
   'title' => 'Login to System',
  ];
  $collectData['collect']['dates'] = [
   'current' => $this->DateObject->format('Y-m-d H:i:s'),
  ];
  $collectData['json_response'] = [
   'status' => false,
   'errors' => [],
   'data' => null,
  ];
  try {
   $this->form_validation->set_rules('login_email', 'Login Username', 'required|max_length[512]|trim|xss_clean');
			$this->form_validation->set_rules('login_password', 'Login Password', 'required|max_length[512]|trim|xss_clean');
			$this->form_validation->set_rules('login_server', 'Login Server', 'required|max_length[16]|trim|xss_clean');
   $this->form_validation->set_rules('login_key', 'Login Key', 'required|max_length[32]|trim|xss_clean');
			if($this->form_validation->run() == FALSE) {
				$this->error = true;
				$this->error_msg[] = validation_errors();
			}
  } catch(Exception $ex) {
   $this->error = true;
   $this->error_msg[] = "Cannot capture login-param inputs with exception: {$ex->getMessage()}."; 
  }
  if(!$this->error) {
   $collectData['collect']['input_params'] = [
    'login_email' => $this->input->post('login_email'),
    'login_password' => $this->input->post('login_password'),
    'login_key' => $this->input->post('login_key'),
   ];
   if(empty($collectData['collect']['input_params']['login_password'])) {
    $this->error = true;
				$this->error_msg[] = "Empty password was given.";
   }
   if(empty($collectData['collect']['input_params']['login_email'])) {
    $this->error = true;
				$this->error_msg[] = "Empty login email.";
   }
  }
  if(!$this->error) {
   if(!is_string($collectData['collect']['input_params']['login_password'])) {
    $this->error = true;
				$this->error_msg[] = "Login key should be in string datatype.";
   }
  }
  if(!$this->error) {
   $collectData['collect']['params'] = [
    'key' => $collectData['collect']['input_params']['login_key'],
   ];
   if(strlen($collectData['collect']['params']['key']) !== 32) {
    $this->error = true;
    $this->error_msg[] = "Length of login key should be 32 chars.";
   }
  }
  // Sccring input as Hexadecimal:
  /*
  if(!$this->error) {
   if($this->mod_account->is_hexadeximal_strings($collectData['collect']['input_params']['login_email']) < 1) {
    $this->error = true;
    $this->error_msg[] = "Login email should be in hexadecimal.";
   }
   if($this->mod_account->is_hexadeximal_strings($collectData['collect']['input_params']['login_password']) < 1) {
    $this->error = true;
    $this->error_msg[] = "Login password should be in hexadecimal.";
   }
  }
  */
  // Make Login Descrypted
  if(!$this->error) {
   try {
    $collectData['collect']['params']['email'] = $this->mod_account->auth_login_descrypted([
     'string_base64' => $collectData['collect']['input_params']['login_email'],
    ], $collectData['collect']['params']['key']);
    $collectData['collect']['params']['password'] = $this->mod_account->auth_login_descrypted([
     'string_base64' => $collectData['collect']['input_params']['login_password'],
    ], $collectData['collect']['params']['key']);
    if($collectData['collect']['params']['email']['status'] !== true) {
     $this->error = true;
     $this->error_msg[] = "Cannot descrypt login email.";
    }
    if($collectData['collect']['params']['password']['status'] !== true) {
     $this->error = true;
     $this->error_msg[] = "Cannot descrypt login password.";
    }
   } catch(Exception $ex) {
    $this->error = true;
				$this->error_msg[] = "Cannot make encrypted params with exception: {$ex->getMessage()}.";
   }
  }
  if(!$this->error) {
   if(!isset($collectData['collect']['params']['email']['string']) || !isset($collectData['collect']['params']['password']['string'])) {
    $this->error = true;
    $this->error_msg[] = "Empty of descrypted email or descrypted password.";
   }
  }
  if(!$this->error) {
   if(!is_string($collectData['collect']['params']['email']['string']) || !is_string($collectData['collect']['params']['password']['string'])) {
    $this->error = true;
				$this->error_msg[] = "Login params for email and password should be in string datatype after descrypted.";
   } else {
    $collectData['collect']['params']['login_email'] = $collectData['collect']['params']['email']['string'];
    $collectData['collect']['params']['login_password'] =  $collectData['collect']['params']['password']['string'];
   }
  }
  // Make Responses
  if(!$this->error) {
   return $this->auth($collectData['collect']['params']);
  } else {
   $collectData['json_response']['errors'] = $this->error_msg;
  }
  // Response Output
  $this->output->set_content_type('application/json');
  $this->output->set_output(json_encode($collectData['json_response']));
 }
}