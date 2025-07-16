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
 
 public function set_logout() {
  $affected_rows = 0;
  $cache_keys = [];
  $cache_params = [
   'expired' => (int)$this->base_dash['logged_in_redis_cache'],
   'logged_data' => $this->base_dash['logged_cache']['social_seq'],
   'social_data' => $this->base_dash['logged_cache']['social_data'],
   'local_data' => $this->base_dash['logged_cache']['userdata'],
   'strings' => [
    'localdata_seq' => '',
    'social_seq' => '',
    'session_id' => $this->session->session_id
   ],
  ];
  if(!isset($this->localdata['seq'])) {
   $this->error = true;
   $this->error_msg[] = "User logged-out not logged-in yet.";
  }
  // Delete from logged databases
  if(!$this->error) {
   try {
    $this->db_account->where('login_session', $this->session->session_id);
    $this->db_account->delete($this->base_dash['tables']['accounts']['dashboard_account_social_log']);
    $affected_rows += $this->db_account->affected_rows();
   } catch(Exception $e) {
    $this->error = true;
    $this->error_msg[] = "Cannot delete logged session from databases with exception: {$e->getMessage()}.";
   }
  }
		// Write Session
  if(!$this->error) {
   try {
    # Open Session
    $this->session->userdata_reopen();
    if($this->session->has_userdata('gg_login_account')) {
     $this->session->unset_userdata('gg_login_account');
    }
    if($this->session->has_userdata('local_login_account')) {
     $this->session->unset_userdata('local_login_account');
    }
    if($this->session->has_userdata('social_account_login_seq')) {
     $cache_params['strings']['social_seq'] = $this->session->userdata('social_account_login_seq');
     $this->session->unset_userdata('social_account_login_seq');
     array_push($cache_keys,  sprintf("%s:%s",
      $cache_params['logged_data'],
      sha1(json_encode([
       'social_seq' => $cache_params['strings']['social_seq'],
       'session_id' => $cache_params['strings']['session_id']
      ]))
     ));
     array_push($cache_keys,  sprintf("%s:%s",
      $cache_params['social_data'],
      sha1(json_encode([
       'social_seq' => $cache_params['strings']['social_seq'],
       'session_id' => $cache_params['strings']['session_id']
      ]))
     ));
    }
    if($this->session->has_userdata('dashboard_account_seq')) {
     $cache_params['strings']['localdata_seq'] = $this->session->userdata('dashboard_account_seq');
     $this->session->unset_userdata('dashboard_account_seq');
     array_push($cache_keys,  sprintf("%s:%s",
      $cache_params['social_data'],
      sha1(json_encode([
       'social_seq' => $cache_params['strings']['localdata_seq'],
       'session_id' => $cache_params['strings']['session_id']
      ]))
     ));
     array_push($cache_keys,  sprintf("%s:%s",
      $cache_params['local_data'],
      sha1(json_encode([
       'social_seq' => $cache_params['strings']['localdata_seq'],
       'session_id' => $cache_params['strings']['session_id']
      ]))
     ));
    }
    # Close Session
    $this->session->userdata_reclose();
    # Destroy Session
    $this->session->sess_destroy();
   } catch(Exception $e) {
    $this->error = true;
    $this->error_msg[] = "Cannot destroy session with exception: {$e->getMessage()}.";
   }
  }
  // Delete all redis-cache datas
  if(!$this->error) {
   try {
    if(!empty($cache_keys)) {
     foreach ($cache_keys as $redkey) {
      $affected_rows += $this->cache->redis->delete($redkey);
     }
    }
   } catch(Exception $e) {
    $this->error = true;
    $this->error_msg[] = "Cannot delete all redis-cache session with exception: {$e->getMessage()}.";
   }
  }
  if(!$this->error) {
   return [
    'status' => true,
    'affected_rows' => $affected_rows,
   ];
  } else {
   return [
    'status' => false,
    'affected_rows' => $affected_rows,
    'errors' => $this->error_msg,
   ];
  }
 }
 public function set_token2fa(object $userdata) {
  $cache_params = [
   'expired' => (int)$this->base_dash['logged_in_redis_cache'],
  ];
  
  
  
  
  
  
 }
}