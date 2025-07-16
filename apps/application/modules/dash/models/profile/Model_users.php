<?php
if(!defined('BASEPATH')) {
 exit('Cannot load script directly to models.');
}
class Model_profiles extends CI_Model {
 protected $error = FALSE, $error_msg = [];
	protected $DateObject;
	protected $base_dash;
	private $cache_params;
 private $userdata, $localdata;
 private $db_account;
 private $userdata_privileges = [
  'is_systems' => false,
  'is_users' => false,
  'is_administrators' => false,
 ];
	function __construct() {
		parent::__construct();
  $this->load->library('dash/base/Lib_configs', NULL, 'libconf');
		$this->base_dash = $this->libconf->get_base_dash();
		$this->DateObject = $this->libconf->get_dateobject();
  $this->load->library('dash/account/Lib_account', NULL, 'accounts');
  
  $this->cache_params = [
   'expired' => 3600,
  ];
  $this->userdata = $this->accounts->get_userdata();
  $this->localdata = $this->accounts->get_localdata();
  
  $this->db_account = $this->load->database('account', true);
	}
 // Set Is-Users or Is-Administrators
 private function set_userdata_status_as(string $status_code) {
  $cache_params = [
   'expired' => (int)$this->base_dash['logged_in_redis_cache'],
  ];
  $this->userdata_privileges['is_users'] = $this->set_userdata_privileges_as('users');
 }
 
 //----------------------------------
	// 2FA
	//----------------------------------
 
 
 
 
 
 
 
 
 
 
 
}




