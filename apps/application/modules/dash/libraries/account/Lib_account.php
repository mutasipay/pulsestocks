<?php
if(!defined('BASEPATH')) { 
 exit('Cannot load script directly.');
}


class Lib_account {
 protected $CI;
 protected $base_dash;
 private $DateObject;
 private $userdata = NULL, $localdata = NULL;
 function __construct() {
  $this->CI = &get_instance();
  $this->CI->load->config('dash/base_dash');
  $this->base_dash = $this->CI->config->item('base_dash');
  # Load helpers for dash
  $this->CI->load->helper('dash/base_dashboard');
  # Make DateObject
  $this->DateObject = \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'), new DateTimeZone(Instance_config::$timezone));
  # Load Databases:
  $this->db_account = $this->CI->load->database('account', TRUE);
  
  # Load Session
  if(!is_cli()) {
   $this->CI->load->library('session');
  }
  
  // Start Userdata if Logged
  $this->userdata_start();
 }
 
 public function get_base_dash() {
  return $this->base_dash;
 }
 public function get_dateobject() {
  return $this->DateObject;
 }
 
 public function get_userdata() {
  return $this->userdata;
 }
 public function get_localdata() {
  return $this->localdata;
 }

 public function generate_client(): Array {
  return [
   'cf' => 
   (isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] :
    (isset($_SERVER['CF_CONNECTING_IP']) ? $_SERVER['CF_CONNECTING_IP'] :
     '0.0.0.0')),
   'original' => 
   (isset($_SERVER['HTTP_X_ORIGINAL_IP']) ? $_SERVER['HTTP_X_ORIGINAL_IP'] : 
    (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 
     '0.0.0.0')),
   'ip' => 
   (isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] :
    (isset($_SERVER['HTTP_X_AUGIPT_REALIP']) ? $_SERVER['HTTP_X_AUGIPT_REALIP'] :
     (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] :
      (isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] :
       (getenv('HTTP_X_FORWARDED_FOR') ? getenv('HTTP_X_FORWARDED_FOR') :
        (isset($_ENV['HTTP_X_FORWARDED_FOR']) ? $_ENV['HTTP_X_FORWARDED_FOR'] :
         (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] :
          (getenv('REMOTE_ADDR') ? getenv('REMOTE_ADDR') :
           (isset($_ENV['REMOTE_ADDR']) ? $_ENV['REMOTE_ADDR'] :
            '0.0.0.0'))))))))),
   'proxy' => (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : (getenv('REMOTE_ADDR') ? getenv('REMOTE_ADDR') : (isset($_ENV['REMOTE_ADDR']) ? $_ENV['REMOTE_ADDR'] : '0.0.0.0'))),
   'ua' => ((isset($_SERVER['HTTP_USER_AGENT']) && !empty($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown.Browser.UA'),
   'host' => (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] :
    (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] :
     'localhost')),
  ];
 }
 
 
 
 private function get_login_data_localdata($uid, string $login_source = 'local', string $logged_key = '', bool $use_sql = false) {
		if(empty($login_source)) {
			$login_source = 'local';
		}
  $sql_param = 'integer';
  if(is_numeric($uid)) {
   $social_uid = (int)$uid;
  } else if(is_string($uid)) {
   $social_uid = trim($uid);
   $sql_param = 'string';
  } else {
   $social_uid = (($this->CI->session->session_id != null) ? $this->CI->session->session_id : '');
   $sql_param = 'session';
  }
  $cache_params = [
   'login_source' => trim(strtolower($login_source)),
   'expired' => (int)$this->base_dash['logged_in_redis_cache'],
   'logged_data' => $this->base_dash['logged_cache']['social_seq'],
   'social_data' => $this->base_dash['logged_cache']['social_data'],
   'user_data' => $this->base_dash['logged_cache']['userdata'],
   'strings' => [
    'social_seq' => $social_uid,
    'session_id' => (($this->CI->session->session_id != null) ? $this->CI->session->session_id : '')
   ],
  ];
  if(empty($logged_key)) {
   $cache_params['key'] = sprintf('%s:%s',
    $cache_params['social_data'],
    sha1(json_encode($cache_params['strings']))
   );
  } else {
   $cache_params['key'] = trim($logged_key);
  }
		try {
			if($use_sql === true) {
    $this->db_account->select('soc.seq AS social_seq, soc.local_seq, acc.*');
    $this->db_account->from("{$this->base_dash['tables']['accounts']['dashboard_account_social']} AS soc");
				$this->db_account->join("{$this->base_dash['tables']['accounts']['dashboard_account']} AS acc", 'acc.seq = soc.local_seq', 'LEFT');
    
    if($sql_param == 'integer') {
     $this->db_account->where('soc.seq', $social_uid);
    } else if($sql_param == 'string') {
     switch($cache_params['login_source']) {
      case 'goodgames':
       $this->db_account->where('LOWER(soc.login_username)', $social_uid);
      break;
      case 'localhost':
      case 'local':
      default:
       $this->db_account->where('LOWER(soc.login_email)', $social_uid);
      break;
     }
    } else {
     $this->db_account->where('LOWER(acc.account_logged_session_id)', $social_uid);
    }
    $this->db_account->limit(1);
				$sql_query = $this->db_account->get();
				$sql_results = $sql_query->row();
    if(isset($sql_results->seq) && isset($sql_results->social_seq) && isset($sql_results->local_seq)) {
     $sql_results->cache_key = $cache_params['key'];
     $sql_results->use_cache = false;
     $this->CI->cache->redis->save($cache_params['key'], json_encode($sql_results, JSON_UNESCAPED_SLASHES), $cache_params['expired']);
    }
    return $sql_results;
   } else {
    $sql_results = $this->CI->cache->redis->get($cache_params['key']);
    if(!$sql_results) {
     return $this->get_login_data_localdata($uid, $cache_params['login_source'], $logged_key, true);
    } else {
     $sql_results = json_decode($sql_results);
     $sql_results->use_cache = true;
     return $sql_results;
    }
   }
		} catch(Exception $e) {
			throw $e;
		}
	}
 
 private function get_multiple_login_devices(array $params, bool $is_allowed = true, bool $use_sql = false) {
  if(!isset($params['login_social_log_seq']) || !isset($params['seq'])) {
   return;
  }
  $cache_params = [
   'expired' => (int)$this->base_dash['logged_in_redis_cache'],
   'strings' => [
    'data' => [
     'logged_data' => $this->base_dash['logged_cache']['social_seq'],
     'social_data' => $this->base_dash['logged_cache']['social_data'],
     'user_data' => $this->base_dash['logged_cache']['userdata'],
    ],
    'params' => $params,
    'is_allowed' => $is_allowed,
   ],
  ];
  $cache_params['key'] = sprintf("devices:%s",
   sha1(json_encode($cache_params['strings']))
  );
  try {
   $soclog_data = [
    'login_session' => '',
    'display_width' => '',
    'display_height' => '',
    'gpu_information' => '',
    'social_seq' => 0,
   ];
   if($use_sql === true) {
    if($cache_params['strings']['is_allowed'] === true) {
     $this->db_account->select('soc.*, soclog.login_session, soclog.display_width, soclog.display_height, soclog.social_seq, soclog.gpu_information');
     $this->db_account->from("{$this->base_dash['tables']['accounts']['dashboard_account_social']} AS soc");
     $this->db_account->join("{$this->base_dash['tables']['accounts']['dashboard_account_social_log']} AS soclog", 'soclog.social_seq = soc.seq', 'LEFT');
     $this->db_account->where('soc.local_seq', $params['seq']);
     $this->db_account->order_by('soc.seq', 'DESC');
     $this->db_account->limit(1);
    } else {
     $this->db_account->select('soclog.login_session, soclog.display_width, soclog.display_height, soclog.social_seq, soclog.gpu_information');
     $this->db_account->from("{$this->base_dash['tables']['accounts']['dashboard_account_social_log']} AS soclog");
     $this->db_account->where('soclog.seq', $params['login_social_log_seq']);
    }
    
    $sql_query = $this->db_account->get();
    $sql_results = $sql_query->row();
    if(isset($sql_results->login_session) && isset($sql_results->display_width) && isset($sql_results->display_height) && isset($sql_results->social_seq) && isset($sql_results->gpu_information)) {
     $soclog_data['login_session'] = $sql_results->login_session;
     $soclog_data['display_width'] = $sql_results->display_width;
     $soclog_data['display_height'] = $sql_results->display_height;
     $soclog_data['gpu_information'] = $sql_results->gpu_information;
     $soclog_data['social_seq'] = (int)$sql_results->social_seq;
     $this->CI->cache->redis->save($cache_params['key'], serialize($soclog_data), $cache_params['expired']);
    }
    return $soclog_data;
   } else {
    $sql_results = $this->CI->cache->redis->get($cache_params['key']);
    if(!$sql_results) {
     return $this->get_multiple_login_devices($params, $is_allowed, true);
    } else {
     $soclog_data = unserialize($sql_results);
     return $soclog_data;
    }
   }
  } catch(Exception $e) {
   return $e;
  }
 }
 private function get_set_logged_in_userdata_by_rediskey_account_seq(String $logged_key, Int $local_seq = 0, Bool $use_sql = false) {
  if($local_seq == 0) {
   return [];
  }
  if(empty($logged_key)) {
   return [];
  }
  $cache_params = [
   'expired' => (int)$this->base_dash['logged_in_redis_cache'],
   'logged_data' => $this->base_dash['logged_cache']['social_seq'],
   'social_data' => $this->base_dash['logged_cache']['social_data'],
   'user_data' => $this->base_dash['logged_cache']['userdata'],
   'strings' => [
    'social_seq' => (int)$local_seq,
    'logged_key' => trim($logged_key),
   ],
  ];
  $cache_params['key'] = sprintf("%s:%s",
   $cache_params['user_data'],
   sha1(json_encode($cache_params['strings']))
  );
  
  try {
   if($use_sql === true) {
    $this->db_account->select('a.*, r.role_id, r.role_code, r.role_name');
    $this->db_account->from("{$this->base_dash['tables']['accounts']['dashboard_account']} AS a");
    $this->db_account->join("{$this->base_dash['tables']['accounts']['dashboard_account_roles']} AS r", 'r.seq = a.account_role', 'LEFT');
    $this->db_account->where('a.seq', $local_seq);
    $sql_query = $this->db_account->get();
    $sql_results = $sql_query->row_array();
    if(isset($sql_results['seq']) && isset($sql_results['login_social_log_seq']) && isset($sql_results['account_logged_session_id'])) {
     $sql_results['soclog_data'] = $this->get_multiple_login_devices($sql_results, $this->base_dash['allow_login_multiple_devices']);
     
     $sql_results['login_session'] = $sql_results['soclog_data']['login_session'];
     $sql_results['display_width'] = $sql_results['soclog_data']['display_width'];
     $sql_results['display_height'] = $sql_results['soclog_data']['display_height'];
     $sql_results['gpu_information'] = $sql_results['soclog_data']['gpu_information'];
     $sql_results['social_seq'] = $sql_results['soclog_data']['social_seq'];
     /*---------------
     * Save to Redis *
     ---------------*/
     $this->CI->cache->redis->save($cache_params['key'], serialize($sql_results), $cache_params['expired']);
    }
    return $sql_results;
   } else {
    $sql_results = $this->CI->cache->redis->get($cache_params['key']);
    if(!$sql_results) {
     return $this->get_set_logged_in_userdata_by_rediskey_account_seq($logged_key, $local_seq, true);
    } else {
     return unserialize($sql_results);
    }
   }
  } catch(Exception $e) {
   throw $e;
  }
	}
	public function get_login_data_userdata(int $local_seq = 0, string $logged_key = '', bool $use_sql = false) {
		if($local_seq === 0) {
			return false;
		}
  $cache_params = [
   'expired' => (int)$this->base_dash['logged_in_redis_cache'],
   'logged_data' => $this->base_dash['logged_cache']['social_seq'],
   'social_data' => $this->base_dash['logged_cache']['social_data'],
   'user_data' => $this->base_dash['logged_cache']['userdata'],
   'strings' => [
    'social_seq' => (int)$local_seq,
    'session_id' => (($this->CI->session->session_id != null) ? $this->CI->session->session_id : '')
   ],
  ];
		if(empty($logged_key)) {
   $cache_params['key'] = sprintf("%s:%s",
    $cache_params['user_data'],
    sha1(json_encode($cache_params['strings']))
   );
  } else {
   $cache_params['key'] = $logged_key;
  }
  try {
   if($use_sql === true) {
    $sql_results = $this->get_set_logged_in_userdata_by_rediskey_account_seq($cache_params['key'], $cache_params['strings']['social_seq']);
    if(isset($sql_results['seq']) && isset($sql_results['login_social_log_seq'])) {
     $this->CI->cache->redis->save($cache_params['key'], serialize($sql_results), $cache_params['expired']);
    }
    return $sql_results;
   } else {
    $sql_results = $this->CI->cache->redis->get($cache_params['key']);
    if(!$sql_results) {
     return $this->get_login_data_userdata($local_seq, $logged_key, true);
    } else {
     return unserialize($sql_results);
    }
   }
  } catch(Exception $e) {
   throw $e;
  }
	}
 protected function prepare_userdata(Int $social_seq = 0, String $cache_key = '') {
		if($social_seq === 0) {
			return false;
		}
  $cache_params = [
   'login_source' => 'local',
   'expired' => (int)$this->base_dash['logged_in_redis_cache'],
   'logged_data' => $this->base_dash['logged_cache']['social_seq'],
   'social_data' => $this->base_dash['logged_cache']['social_data'],
   'user_data' => $this->base_dash['logged_cache']['userdata'],
   'strings' => [
    'social_seq' => $social_seq,
    'session_id' => (($this->CI->session->session_id != null) ? $this->CI->session->session_id : '')
   ],
  ];
  if(empty($cache_key)) {
   $cache_params['key'] = sprintf("%s:%s",
    $cache_params['social_data'],
    sha1(json_encode($cache_params['strings']))
   );
  } else {
   $cache_params['key'] = trim($cache_key);
  }
		try {
   $userdata = $this->get_login_data_localdata($cache_params['strings']['social_seq'], $cache_params['login_source'], $cache_params['key']);
   if(isset($userdata->local_seq)) {
    $cache_params['logged_key'] = sprintf("%s:%s",
     $cache_params['user_data'],
     sha1(json_encode([
      'local_seq' => $userdata->local_seq,
      'session_id' => $cache_params['strings']['session_id']
     ]))
    );
    $this->localdata = $this->get_login_data_userdata($userdata->local_seq, $cache_params['logged_key']);
    
    if(isset($this->localdata['seq'])) {
     return $userdata;
    }
   } else {
    return false;
   }
  } catch(Exception $e) {
   throw $e;
  }
	}
 private function make_userdata_starting(Bool $use_sql = false) {
		if($this->CI->session->userdata('social_account_login_seq') != NULL) {
   $cache_params = [
    'expired' => (int)$this->base_dash['logged_in_redis_cache'],
    'logged_data' => $this->base_dash['logged_cache']['social_seq'],
    'social_data' => $this->base_dash['logged_cache']['social_data'],
    'strings' => [
     'social_seq' => $this->CI->session->userdata('social_account_login_seq'),
     'session_id' => $this->CI->session->session_id
    ],
   ];
   $cache_params['key'] = sprintf("%s:%s",
    $cache_params['logged_data'],
    sha1(json_encode($cache_params['strings']))
   );
			try {
    if($use_sql === true) {
     $this->db_account->select('seq, social_seq')->from($this->base_dash['tables']['accounts']['dashboard_account_social_log']);
					$this->db_account->where('social_seq', $cache_params['strings']['social_seq']);
					$this->db_account->where('login_session', $cache_params['strings']['session_id']);
					$sql_query = $this->db_account->get();
					$sql_results = $sql_query->row();
     if(isset($sql_results->seq) && isset($sql_results->social_seq)) {
      $sql_results->cache_key = sprintf("%s:%s",
       $cache_params['social_data'],
       sha1(json_encode([
        'social_seq' => $sql_results->social_seq,
        'session_id' => $cache_params['strings']['session_id']
       ]))
      );
      // Save to Redis
      $this->CI->cache->redis->save($cache_params['key'], json_encode($sql_results, JSON_NUMERIC_CHECK), $cache_params['expired']);
     }
     return $sql_results;
    } else {
     $sql_results = $this->CI->cache->redis->get($cache_params['key']);
     if(!$sql_results) {
      return $this->make_userdata_starting(true);
     } else {
      return json_decode($sql_results);
     }
    }
   } catch(Exception $e) {
    throw $e;
   }
  } else {
   return false;
  }
	}
 
 public function userdata_start() {
  try {
   $userdata = $this->make_userdata_starting();
   if(isset($userdata->social_seq) && isset($userdata->cache_key)) {
    $this->userdata = $this->prepare_userdata((int)$userdata->social_seq, (string)$userdata->cache_key);
   }
  } catch(Exception $e) {
   throw $e;
  }
 }
 
}