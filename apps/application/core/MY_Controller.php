<?php 
if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}
/* load the MX_Controller class */
require_once(APPPATH . "third_party/MX/Controller.php");


if (!isset(Instance_config::$env_group['env_key'])) {
	header('Content-type: application/json');
	exit( json_encode([
			'status'			=> false,
			'message'			=> "System env config not yet configured."
		])
	);
}

// Not show Error::Deprecated
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

class MY_Controller extends MX_Controller {
 private $apc_cache;
	function __construct() {
		parent::__construct();
  // Load Redis Cache
  $this->load->driver('cache');
  // Get APC Data on Cache
  $this->apc_cache = $this->get_apc_cache();
		// For Debug Purpose
		#$this->output->enable_profiler(TRUE);
	}
 private function get_apc_cache(Bool $is_cached = false) {
  try {
   if($is_cached === true) {
    $cache_data = Instance_config::$env_apc;
    if(isset($cache_data['env'])) {
     $this->cache->redis->save(Instance_config::$cache_params['key'], serialize($cache_data), Instance_config::$cache_params['expired']);
    }
    return $cache_data;
   } else {
    $cache_data = $this->cache->redis->get(Instance_config::$cache_params['key']);
    if(!$cache_data) {
     return $this->get_apc_cache(true);
    } else {
     return unserialize($cache_data);
    }
   }
  } catch(Exception $e) {
   throw $e;
  }
 }
	public function get_service_status(Bool $is_cached = false) {
		return $this->get_apc_cache($is_cached);
	}
}


class MY_Dashboard extends MY_Controller {
 public $userdata, $localdata;
	function __construct() {
		parent::__construct();
  // Load Model Accounts
  try {
   $this->load->model('dash/account/Model_account', 'mod_account');
  
   $this->userdata = $this->mod_account->get_userdata();
   $this->localdata = $this->mod_account->get_localdata();
   
   $this->is_userdata();
  } catch(Exception $e) {
   throw $e;
  }
	}
 public function is_userdata() {
  if(!isset($this->localdata['account_email'])) {
   redirect(base_url('dash/account/login/index'));
   exit;
  }
 }
}
