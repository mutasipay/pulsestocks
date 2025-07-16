<?php
if (!defined('BASEPATH')) {
	exit('Cannot load script directly:: mod_account.');
}

class Model_account extends CI_Model {
 private $error = FALSE, $error_msg = [];
	protected $DateObject;
	protected $base_dash;
 private $base_signature, $base_authcenter;
	# Connected Databases
 private $db_stock, $db_account;
 # Make locaData and userData
 protected $localdata, $userdata;
	function __construct() {
		parent::__construct();
  # Load Libraries
  $this->load->library('dash/base/Lib_configs', NULL, 'libconf');
		$this->base_dash = $this->libconf->get_base_dash();
		$this->DateObject = $this->libconf->get_dateobject();
  $this->load->library('dash/account/Lib_authcenter', NULL, 'authcenters');
  $this->base_authcenter = $this->authcenters->get_base_authcenter();
  $this->load->library('dash/account/Lib_signature', $this->base_authcenter['apps'], 'signatures');
  $this->base_signature = $this->signatures->get_base_signature();
  $this->load->library('dash/account/Lib_account', NULL, 'accounts');
  # Load Databases
		$this->db_stock = $this->load->database('stock', true);
  $this->db_account = $this->load->database('account', true);
  
  
  
  $this->localdata = $this->accounts->get_localdata();
  $this->userdata = $this->accounts->get_userdata();
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
 // Get Logged User Roles
 public function get_account_roles() {
  try {
   
  } catch(Exception $e) {
   throw $e;
  }
 }
 ##
 # Make Session Logged
 ##
 private function set_logged_session_session(Array $session_params) {
  try {
			# Open Session
			$this->session->userdata_reopen();
			# Write Session
			$this->session->set_userdata('social_account_login_seq', $session_params['social_account_login_seq']);
			$this->session->set_userdata('dashboard_account_seq', $session_params['dashboard_account_seq']);
   if(isset($session_params['client'])) {
				$this->session->set_userdata('client', $session_params['client']);
			}
			# Close Session
			$this->session->userdata_reclose();
			return TRUE;
		} catch(Exception $e) {
			throw $ex;
		}
 }
 private function set_logged_user_session(array $session_params) {
		if(!isset($session_params['social_account_login_seq']) || !isset($session_params['dashboard_account_seq'])) {
			return false;
		}
		try {
   $logged_session = $this->set_logged_session_session($session_params);
   /*
   $logged_session = $this->set_logged_session_cookie($session_params);
   */
   return $logged_session;
  } catch(Exception $e) {
   throw $e;
  }
	}
 private function set_loginas2fa_session(Array $session_params) {
		if(!isset($session_params['dashboard_account_seq'])) {
			return false;
		}
  try {
   # Open Session
   //$this->session->userdata_reopen();
   # Write Session
   $this->session->set_userdata('login_2fa_account_seq', $session_params['dashboard_account_seq']);
   # Close Session
   //$this->session->userdata_reclose();
  } catch(Exception $e) {
   throw $e;
  }
	}
 
 
 ##
 # App Account
 ##
 private function insert_authenticated_app_login_userdata(Array $auth_login_response) {
		$error = false;
		$error_msg = [];
		$insert_userdata_results = [
			'account_seq' => 0,
			'status' => FALSE,
			'account_email' => ''
		];
		if (!isset($auth_login_response['logged_app_userdata']['account_email']) || !isset($auth_login_response['logged_app_userdata']['account_group'])) {
			$error = true;
			$error_msg[] = "Not have required key as account-email and account-group.";
		}
		if (!$error) {
			$auth_login_params = [
				'app' => [
					'uuid' => (!empty($auth_login_response['logged_app_userdata']['account_group']) ? $auth_login_response['logged_app_userdata']['account_group'] : $this->base_authcenter['apps']['key']),
					'secret' => $this->base_authcenter['apps']['secret'],
					'key' => md5($this->base_authcenter['apps']['secret']),
				],
				'params' => [
					'account_email'		=> $auth_login_response['logged_app_userdata']['account_email'],
				],
				'headers'			=> [
					'Content-Type' => 'application/json',
					'X-Client-Id' => $auth_login_response['logged_app_userdata']['account_group'],
					'X-Client-Iv' => '',
					'Signature' => ''
				],
			];
			try {
				$hashed_string = json_encode([
					'email'		=> $auth_login_params['params']['account_email'],
				]);
				$auth_login_params['hashed_strings'] = $this->signatures->create_hashed_password($hashed_string, $auth_login_params['app']['key']);
				if (!isset($auth_login_params['hashed_strings']['encrypted'])) {
					$error = true;
					$error_msg[] = "Not have hashed encrypted string.";
				}
				if (!isset($auth_login_params['hashed_strings']['iv'])) {
					$error = true;
					$error_msg[] = "Not have hashed iv string.";
				}
			} catch (Exception  $ex) {
				$error = true;
				$error_msg[] = "Cannot make hmac hash of header::Signature and body::account_email with exception: {$ex->getMessage()}.";
			}
		}
		if (!$error) {
			if (isset($auth_login_params['hashed_strings']['iv'])) {
				$auth_login_params['headers']['X-Client-Iv'] = $auth_login_params['hashed_strings']['iv'];
			}
			if (isset($auth_login_params['hashed_strings']['encrypted'])) {
				$auth_login_params['headers']['Signature'] = $auth_login_params['hashed_strings']['encrypted'];
			}
		}
		if (!$error) {
			try {
				$authcenter_response = $this->authcenters->get_profiles_email($auth_login_params['params']['account_email'], $auth_login_params['headers']);
				if (!isset($authcenter_response['curl_response']->success)) {
					$error = true;
					$error_msg[] = "Not have curl-response from authcenter library.";
				}
			} catch (Exception $ex) {
				$error = true;
				$error_msg[] = "Cannot make auth-center login with exception: {$ex->getMessage()}.";
			}
		}
		if (!$error) {
			if ($authcenter_response['curl_response']->success !== TRUE) {
				$error = true;
				$error_msg[] = "Failed login to auth-center [app_login_userdata].";
				if (isset($authcenter_response['curl_response']->message) && is_string($authcenter_response['curl_response']->message)) {
					$error_msg[] = $authcenter_response['curl_response']->message;
				}
			}
		}
		if (!$error) {
			if (!isset($authcenter_response['curl_response']->data->email)) {
				$error = true;
				$error_msg[] = "Not have app account-data::email from auth-center.";
			}
			if (!isset($authcenter_response['curl_response']->data->appName)) {
				$error = true;
				$error_msg[] = "Not have app account-data::appName from auth-center.";
			}
			if (!isset($authcenter_response['curl_response']->data->fullname)) {
				$error = true;
				$error_msg[] = "Not have app account-data::fullname from auth-center.";
			}
		}
		
		
		//
		// Insert App Userdata From Auth Response
		//
		if (!$error) {
			$profile_userdata_params = [
				'account_username'					=> (is_string($authcenter_response['curl_response']->data->email) ? $authcenter_response['curl_response']->data->email : ''),
				'account_email'						=> (is_string($authcenter_response['curl_response']->data->email) ? $authcenter_response['curl_response']->data->email : ''),
				'account_group'						=> (is_string($authcenter_response['curl_response']->data->appName) ? $authcenter_response['curl_response']->data->appName : ''),
				'account_firebase_uid'				=> '',
				'account_firebase_last_seq'			=> 0,
				'account_hash'						=> uniqid(),
				'account_password'					=> '',
				'account_2fa_secretcode'			=> NULL,
				'account_2fa_activated'				=> 'N',
				'account_inserting_datetime'		=> $this->DateObject->format('Y-m-d H:i:s'),
				'account_inserting_remark'			=> '',
				'account_activation_code'			=> '',
				'account_activation_starting'		=> $this->DateObject->format('Y-m-d H:i:s'),
				'account_activation_ending'			=> $this->DateObject->format('Y-m-d H:i:s'),
				'account_activation_datetime'		=> $this->DateObject->format('Y-m-d H:i:s'),
				'account_activation_status'			=> 'N',
				'account_activation_by'				=> 'system',
				'account_active'					=> 'N',
				'account_role'						=> 1,
				'account_nickname'					=> '',
				'account_fullname'					=> (is_string($authcenter_response['curl_response']->data->fullname) ? $authcenter_response['curl_response']->data->fullname : ''),
				'account_picture'					=> NULL,
				'account_address'					=> '',
				'account_phonenumber'				=> '',
				'account_phonemobile'				=> '',
				'account_delete_status'				=> 0,
				'account_delete_datetime'			=> NULL,
				'account_delete_by'					=> NULL,
				'account_edited_datetime'			=> NULL,
				'account_edited_by'					=> NULL,
				'account_need_reset_password'		=> 'N',
				'account_allow_change_password'		=> 'Y',
				'account_logged_session_id'			=> NULL,
				'subscription_starting'				=> NULL,
				'subscription_expiring'				=> NULL,
				'login_datetime'					=> NULL,
				'login_social_log_seq'				=> 0,
				'login_is_online'					=> 0,
				'is_global_ip_address'				=> 'N',
			];
			$account_nicknames = explode('@', $authcenter_response['curl_response']->data->email);
			if (isset($account_nicknames[0])) {
				$profile_userdata_params['account_nickname'] = trim($account_nicknames[0]);
			}
			
			// Validate Group as App Uuid
			if(strtolower($profile_userdata_params['account_group']) !== strtolower($auth_login_params['app']['uuid'])) {
				$error = true;
				$error_msg[] = "No in the same group as App Uuid.";
			}
		}
		if (!$error) {
			try {
				$this->db_account->insert($this->base_dash['tables']['accounts']['dashboard_account'], $profile_userdata_params);
				$insert_userdata_results['account_seq'] = $this->db_account->insert_id();
				if ((int)$insert_userdata_results['account_seq'] > 0) {
					$insert_userdata_results['status'] = TRUE;
				}
				if (is_string($profile_userdata_params['account_email']) && !empty($profile_userdata_params['account_email'])) {
					$insert_userdata_results['account_email'] = trim($profile_userdata_params['account_email']);
				}
			} catch (Exception $e) {
				throw $e;
			}
		}
		return (object)$insert_userdata_results;
	}
 private function get_app_userdata_auth(String $auth_type, String $account_email, String $app_uuid = '') {
		if(empty($app_uuid)) {
			$app_uuid = $this->base_authcenter['apps']['key'];
		}
		if (!in_array($auth_type, [
			'password',
			'social'
		])) {
			$this->error = true;
			$this->error_msg[] = "Auth type not defined.";
		}
		
		if (!$this->error) {
			$account_email = strtolower($account_email);
			try {
				$this->db_account->select('acg.*')->from("{$this->base_dash['tables']['accounts']['dashboard_account']} AS acg");
				$this->db_account->where('acg.account_group', $app_uuid);
				switch ($auth_type) {
					case 'social':
						$this->db_account->where('LOWER(acg.account_email)', $account_email);
					break;
					case 'password':
					default:
						$this->db_account->where(sprintf("(LOWER(acg.account_email) = '%s' OR LOWER(acg.account_username) = '%s')",
       $this->db_account->escape_str($account_email),
       $this->db_account->escape_str($account_email)
      ), null, false);
					break;
				}
    
				$sql_query = $this->db_account->get();
				$sql_results = $sql_query->row();
    return $sql_results;
			} catch(Exception $e) {
				throw $e;
			}
		} else {
			return [
				'errors'		=> $this->error_msg,
			];
		}
	}
 public function get_login_data_userdata(int $local_seq) {
		$local_seq = (is_numeric($local_seq) ? (int)$local_seq : 0);
		if ((int)$local_seq === 0) {
			return false;
		}
  try {
   $this->db_account->select('a.*, r.role_id, r.role_code, r.role_name');
   $this->db_account->from("{$this->base_dash['tables']['accounts']['dashboard_account']} AS a");
   $this->db_account->join("{$this->base_dash['tables']['accounts']['dashboard_account_roles']} AS r", 'r.role_id = a.account_role', 'LEFT');
   $this->db_account->where('a.seq', $local_seq);
   $this->db_account->limit(1);
	
			$sql_query = $this->db_account->get();
   $sql_results = $sql_query->row_array();
   
   return $sql_results;
		} catch(Exception $e) {
			throw $e;
  }
	}
 public function get_logged_userdata($uid, $login_server = 'localhost', string $app_name = 'auth') {
		if (!isset($login_server)) {
			$login_server = 'localhost';
		}
		$uid = ((is_string($uid) || is_numeric($uid)) ? sprintf("%s", $uid) : '');
		try {
			switch(strtolower($login_server)) {
				case 'local':
				case 'localhost':
				case 'password':
     $this->db_account->select('d.*');
					$this->db_account->from("{$this->base_dash['tables']['accounts']['dashboard_account']} AS d");
					if(is_numeric($uid)) {
						$uid = (int)$uid;
						$this->db_account->where('d.seq', $uid);
					} else {
						$this->db_account->where('LOWER(d.account_email)', strtolower($uid));
					}
     // Set App Name
     if($app_name != 'auth') {
      $app_name = strtolower($app_name);
      $this->db_account->where('LOWER(d.account_group)', $app_name);
     }
				break;
				case 'firebase':
     $this->db_account->select('d.*');
					$this->db_account->from("{$this->base_dash['tables']['accounts']['dashboard_account']} AS d");
					$this->db_account->where('LOWER(d.account_firebase_uid)', strtolower($uid));
     // Set App Name
     if($app_name != 'auth') {
      $app_name = strtolower($app_name);
      $this->db_account->where('LOWER(d.account_group)', $app_name);
     }
    break;
				case 'goodgames':
				default:
     $this->db_account->select('s.*, d.seq AS dash_seq');
					$this->db_account->from("{$this->base_dash['tables']['accounts']['dashboard_account_social']} AS s");
     $this->db_account->join("{$this->base_dash['tables']['accounts']['dashboard_account']} AS d", 'd.seq = s.local_seq', 'LEFT');
					$this->db_account->where('LOWER(s.login_username)', strtolower($uid));
     // Set App Name
     if($app_name != 'auth') {
      $app_name = strtolower($app_name);
      $this->db_account->where('LOWER(d.account_group)', $app_name);
     }
				break;
			}
			
   
			$sql_query = $this->db_account->get();
			$sql_results = $sql_query->row();
   return $sql_results;
		} catch(Exception $e) {
			throw $e;
		}
	}
 private function get_social_login_account($social_seq) {
  try {
   $social_seq = (is_numeric($social_seq) ? (int)$social_seq : 0);
		
			$this->db_account->select('*')->from($this->base_dash['tables']['accounts']['dashboard_account_social']);
			$this->db_account->where('seq', $social_seq);
			$sql_query = $this->db_account->get();
			$sql_results = $sql_query->row();
   return $sql_results;
		} catch(Exception $e) {
			throw $e;
		}
	}
 private function insert_social_log_login_account(int $social_seq) {
		$affected_rows = 0;
		$session_seq = 0;
		$social_data = $this->get_social_login_account($social_seq);
		$account_params = array(
			'login_datetime_first'			=> NULL,
		);
		$update_params = array(
			'login_datetime_last'			=> $this->DateObject->format('Y-m-d H:i:s'),
		);
		$social_session_insert_params = [
			'social_seq' => (isset($social_data->seq) ? $social_data->seq : $social_seq),
			'login_session' => $this->session->session_id,
			'login_datetime' => $this->DateObject->format('Y-m-d H:i:s'),
			'gpu_information' => '{}',
			'display_width' => NULL,
			'display_height' => NULL,
			'login_datetime_first' => $this->DateObject->format('Y-m-d H:i:s'),
		];
		if(isset($social_data->seq) && ((int)$social_data->seq > 0)) {
			// Prevent Double Login
			if($this->base_dash['allow_login_multiple_devices'] !== TRUE) {
				$this->db_account->where('social_seq', $social_data->seq);
				$this->db_account->delete($this->base_dash['tables']['accounts']['dashboard_account_social_log']);
				$affected_rows += $this->db_account->affected_rows();
				$this->db_account->reset_query();
			}
			$sql_string = $this->db_account->where('seq', $social_data->seq)->update($this->base_dash['tables']['accounts']['dashboard_account_social'], $update_params);
			$affected_rows += $this->db_account->affected_rows();
			$session_seq = $social_data->seq;
		}
		$sql_string = sprintf("%s ON DUPLICATE KEY UPDATE login_datetime = '%s'",
			$this->db_account->insert_string($this->base_dash['tables']['accounts']['dashboard_account_social_log'], $social_session_insert_params),
			$this->db_account->escape_str($social_session_insert_params['login_datetime'])
		);
		$this->db_account->query($sql_string);
		$session_seq = (($this->db_account->insert_id() > 0) ? $this->db_account->insert_id() : $social_data->seq);
		if(isset($social_data->local_seq)) {
			$this->db_account->where('seq', $social_data->local_seq);
			$this->db_account->update($this->base_dash['tables']['accounts']['dashboard_account'], [
				'account_logged_session_id' => $social_session_insert_params['login_session'],
				'login_is_online' => 1,
				'login_social_log_seq' => $session_seq,
			]);
			$affected_rows += $this->db_account->affected_rows();
		}
		return $session_seq;
	}
 private function update_local_login_account(int $account_seq, int $social_login_seq) {
		try {
   $update_params = [
    'login_datetime' => $this->DateObject->format('Y-m-d H:i:s'),
    'login_social_log_seq' => $social_login_seq,
    'login_is_online' => 1,
   ];
   if($this->base_dash['allow_login_multiple_devices'] !== TRUE) {
    $update_params['account_logged_session_id'] = $this->session->session_id;
   }
   $this->db_account->where('seq', $account_seq);
   $this->db_account->update($this->base_dash['tables']['accounts']['dashboard_account'], $update_params);
   return $this->db_account->affected_rows();
  } catch(Exception $e) {
   throw $e;
  }
	}
 private function get_account_ip_address_by(string $by_type, string|int $by_value, array $condition_params = [], bool $use_sql = false) {
  $by_type = (is_string($by_type) ? strtolower($by_type) : 'seq');
  $value = sprintf("%s", $by_value);
  switch (strtolower($by_type)) {
   case 'ip':
   case 'ip_address':
    $value = sprintf("%s", $value);
   break;
   case 'account_seq':
   case 'ip_seq':
   case 'seq':
   default:
    if (!preg_match('/^[1-9][0-9]*$/', $value)) {
     $value = 0;
    } else {
     $value = (int)$value;
    }
   break;
  }
  $cache_params = [
   'expired'	=> 3600,
   'strings'	=> [
    'condition'	=> $condition_params,
    'params' => [
     'value' => $value,
     'type' => $by_type,
    ]
   ],
  ];
		$cache_params['key'] = sprintf("modelAccount:get_account_ip_address_by:%s",
			sha1(json_encode($cache_params['strings']))
		);
		try {
			if ($use_sql === true) {
				$this->db_account->select('*')->from($this->base_dash['tables']['accounts']['dashboard_account_ip_address']);
				if (isset($condition_params['ip_version']) && isset($condition_params['ip_address'])) {
					switch (strtolower($condition_params['ip_version'])) {
						case 'v6':
							$this->db_account->where('ip_address_v6', $condition_params['ip_address']);
						break;
						case 'v4':
						default:
							$this->db_account->where('ip_address_v4', $condition_params['ip_address']);
						break;
					}
				}
				switch (strtolower($by_type)) {
					case 'account_seq':
						$this->db_account->where('account_seq', $value);
						break;
					case 'ip':
					case 'ip_address':
						switch (strtolower($condition_params['ip_version'])) {
							case 'v6':
								$this->db_account->where('ip_address_v6', $value);
							break;
							case 'v4':
							default:
								$this->db_account->where('ip_address_v4', $value);
							break;
						}
						break;
					case 'ip_seq':
					case 'seq':
					default:
						$this->db_account->where('seq', $value);
						break;
				}
    // If Have Account Data
    if(isset($condition_params['account_seq']) && is_numeric($condition_params['account_seq'])) {
     $condition_params['account_seq'] = (int)$condition_params['account_seq'];
     $this->db_account->where('account_seq', $condition_params['account_seq']);
    }
				if (strtolower($by_type) !== 'account_seq') {
					$this->db_account->limit(1);
				}
				$sql_query = $this->db_account->get();
				if(strtolower($by_type) !== 'account_seq') {
					$sql_results = $sql_query->row();
				} else {
					$sql_results = $sql_query->result();
				}
				if (in_array(gettype($sql_results), [
					'array',
					'object'
				]) && !empty($sql_results)) {
					$this->cache->redis->save($cache_params['key'], serialize($sql_results), $cache_params['expired']);
				}
				return $sql_results;
			} else {
    $sql_results = $this->cache->redis->get($cache_params['key']);
    if(!$sql_results) {
     return $this->get_account_ip_address_by($by_type, $value, $condition_params, true);
    } else {
     return unserialize($sql_results);
    }
			}
		} catch (Exception $e) {
			throw $e;
		}
	}
 
 
 // Get user IP address
 private function set_client_ip(string $ip_address = '', string $ip_original = '', string $ip_cloudflare = '') {
		try {
   $ip = (!empty($ip_address) ? trim($ip_address) : '0.0.0.0');
   $ori = (!empty($ip_original) ? trim($ip_original) : '0.0.0.0');
   $cf = (!empty($ip_cloudflare) ? trim($ip_cloudflare) : '0.0.0.0');
   if(strpos($ip, ',') !== FALSE) {
    $ip2 = explode(',', $ip);
    $count_ips = count($ip2);
    $key_num = ($count_ips - 1);
    $lb_ip = trim($ip2[$key_num]);
    if($lb_ip == $ori) {
     unset($ip2[$key_num]);
     $ip = array_pop($ip2);
    } else if(isset($ip2[1])) {
     $ip = $ip2[1];
    } else if(!preg_match('/(^192\.168\.)/', $ip) && !preg_match('/(^10\.)/', $ip) && !preg_match('/(^172\.16\.)/', $ip)) {
     $ip = reset($ip2);
    }
   }
   $ip = filter_var($ip, FILTER_VALIDATE_IP);
   $ip = ($ip === false) ? '0.0.0.0' : $ip;
   return $ip;
  } catch(Exception $e) {
   throw $e;
  }
	}
 private function local_login_fragment_method(object $local_data, string $method = '') {
  if(!in_array($method, [
   'login_ip_address',
   'force_admin',
   'api',
  ])) {
   $this->error = true;
   $this->error_msg[] = "Method not in allowed-methods.";
  }
  if(!$this->error) {
   switch($method) {
    case 'login_ip_address':
    default:
     if($this->base_dash['is_ip_checked'] === true) {
      if(!isset($local_data->is_global_ip_address)) {
       $this->error = true;
       $this->error_msg[] = "User account do not have colum of global-ip-address.";
      }
      if(strtoupper($local_data->is_global_ip_address) !== 'Y') {
       if(!isset($local_data->client)) {
        $client = $this->accounts->generate_client();
       } else {
        $client = $local_data->client;
       }
       $client['ip'] = $this->set_client_ip($client['ip'], $client['original'], $client['cf']);
       $client['account_ip_addresses'] = $this->get_account_ip_address_by('ip_address', $client['ip'], [
        'ip_version' => 'v4',
        'ip_address' => $client['ip'],
        'datetime' => $this->DateObject->format('Y-m-d H:i:s'),
        // Custom Purposes
        'account_email' => $local_data->account_email,
        'account_seq' => $local_data->seq,
        'purpose' => 'login'
       ]);
       if(!isset($client['account_ip_addresses']->seq)) {
        $this->error = true;
        $this->error_msg[] = sprintf("IP Address: %s not allowed to login for your account.", $client['ip']);
       }
      }
     }
    break;
   }
  }
  
  // Responses
		if (!$this->error) {
			return [
				'status'		=> true,
				'errors'		=> [],
			];
		} else {
			return [
				'status'		=> false,
				'errors'		=> $this->error_msg,
			];
		}
 }
 ##
 # Login Auth Center
 ##
 public function auth_login_params(String $email = '', String $password = '') {
  if(empty($email) || empty($password)) {
   $this->error = true;
   $this->error_msg[] = "Empty email or password.";
  }
  if(!$this->error) {
   try {
    $auth_params = [
     'app' => [
      'uuid' => $this->base_authcenter['apps']['key'],
      'secret' => $this->base_authcenter['apps']['secret'],
      'key' => md5($this->base_authcenter['apps']['secret']),
     ],
     'params' => [
      'account_email' => $email,
      'account_password' => $password,
     ],
     'headers' => [
      'Content-Type' => 'application/json',
      'X-Client-Id' => $this->base_authcenter['apps']['key'],
      'X-Client-Iv' => '',
      'Signature' => ''
     ],
    ];
    $auth_params['hashed_strings'] = $this->signatures->create_hashed_password($auth_params['params']['account_password'], $auth_params['app']['key']);
    if(!isset($auth_params['hashed_strings']['encrypted'])) {
     $this->error = true;
     $this->error_msg[] = "Not have hashed encrypted string.";
    }
    if(!isset($auth_params['hashed_strings']['iv'])) {
     $this->error = true;
     $this->error_msg[] = "Not have hashed iv string.";
    }
   } catch(Exception $e) {
    $this->error = true;
    $this->error_msg[] = $e->getMessage();
   }
  }
  if(!$this->error) {
   return $auth_params;
  } else {
   return [
    'errors' => $this->error_msg,
   ];
  }
 }
 public function auth_login_signatures(Array $params = [], String $iv = '', String $key = '') {
  if(empty($params)) {
   $this->error = true;
   $this->error_msg[] = "Empty params.";
  }
  if(empty($iv) || empty($key)) {
   $this->error = true;
   $this->error_msg[] = "Empty iv and/or key.";
  }
  if(!$this->error) {
   try {
    $auth_signatures = $this->signatures->create_signature_string_with_text_base64iv_key(json_encode($params), trim($iv), trim($key));
    if(!isset($auth_signatures['encrypted'])) {
     $this->error = true;
     $this->error_msg[] = "Not have encrypted signatures from library.";
    }
   } catch(Exception $e) {
    $this->error = true;
    $this->error_msg[] = $e->getMessage();
   }
  }
  if(!$this->error) {
   return [
    'status' => true,
    'signatures' => $auth_signatures,
   ];
  } else {
   return [
    'status' => false,
    'errors' => $this->error_msg,
   ];
  }
 }
 // Encrypt & Descrypt
 public function auth_login_descrypted(Array $params = [], String $key = '') {
  if(empty($params)) {
   $this->error = true;
   $this->error_msg[] = "Empty params.";
  }
  if(empty($key)) {
   $this->error = true;
   $this->error_msg[] = "Empty iv and/or key.";
  }
  if(!isset($params['string_base64'])) {
   $this->error = true;
   $this->error_msg[] = "Not have string_base64 from params.";
  }
  if(!$this->error) {
   try {
    $string_crypt = $this->signatures->descrypt_signature_string_with_text_base64iv_key($params['string_base64'], trim($key));
    if(!is_string($string_crypt)) {
     $this->error = true;
     $this->error_msg[] = "Return of descrypted signatures should be in string datatype.";
    }
   } catch(Exception $e) {
    $this->error = true;
    $this->error_msg[] = $e->getMessage();
   }
  }
  if(!$this->error) {
   return [
    'status' => true,
    'string' => $string_crypt,
   ];
  } else {
   return [
    'status' => false,
    'errors' => $this->error_msg,
   ];
  }
 }
 public function auth_login_encrypted(Array $params = [], String $key = '') {
  if(empty($params)) {
   $this->error = true;
   $this->error_msg[] = "Empty params.";
  }
  if(empty($key)) {
   $this->error = true;
   $this->error_msg[] = "Empty key.";
  }
  if(!isset($params['string_base64'])) {
   $this->error = true;
   $this->error_msg[] = "Not have string_base64 from params.";
  }
  if(!$this->error) {
   try {
    $string_crypt = $this->signatures->encrypt_signature_string_with_text_base64iv_key($params['string_base64'], trim($key));
    if(!is_string($string_crypt)) {
     $this->error = true;
     $this->error_msg[] = "Return of encrypted signatures should be in string datatype.";
    }
   } catch(Exception $e) {
    $this->error = true;
    $this->error_msg[] = $e->getMessage();
   }
  }
  if(!$this->error) {
   return [
    'status' => true,
    'string' => $string_crypt,
   ];
  } else {
   return [
    'status' => false,
    'errors' => $this->error_msg,
   ];
  }
 }
 
 
 
 
 
 # Get Local Userdata After Login From Auth-Center
 public function set_userdata() {
		if (!$this->error) {
			
		}
 }
 //-----------------------------
 # Make Login
 //-----------------------------
 public function auth_login_type_password(Array $auth_params, String $app_uuid = '') {
  $auth_type = 'password';
  if(empty($app_uuid)) {
   $this->error = true;
   $this->error_msg[] = "Empty app-uuid give.";
  }
		if(!$this->error) {
   if(!isset($auth_params['params']) || !isset($auth_params['headers'])) {
    $this->error = true;
    $this->error_msg[] = "Not have auth-params::params or auth-params::headers.";
   }
  }
  if(!$this->error) {
   if(!is_array($auth_params['params']) || !is_array($auth_params['headers'])) {
    $this->error = true;
    $this->error_msg[] = "Headers and Params should be in Array Datatype.";
   }
  }
  if(!$this->error) {
   try {
    $authcenter_response = $this->authcenters->login_with_email_and_password($auth_params['params'], $auth_params['headers']);
    if(!isset($authcenter_response['curl_response']->success)) {
     $this->error = true;
     $this->error_msg[] = "Not have curl-response from authcenter library as expected for success status.";
    }
   } catch(Exception $e) {
    $this->error = true;
    $this->error_msg[] = $e->getMessage();
   }
  }
  if(!$this->error) {
   if($authcenter_response['curl_response']->success !== TRUE) {
    $this->error = true;
    $this->error_msg[] = "Failed login to auth-center [type_password].";
    if(isset($authcenter_response['curl_response']->message)) {
     if(is_string($authcenter_response['curl_response']->message)) {
      $this->error_msg[] = $authcenter_response['curl_response']->message;
     }
    }
    if(isset($authcenter_response['curl_info']['header_request'])) {
     $this->error_msg[] = $authcenter_response['curl_info']['header_request'];
    }
    if(isset($authcenter_response['curl_info']['request_body'])) {
     $this->error_msg[] = $authcenter_response['curl_info']['request_body'];
    }
   }
  }
  if (!$this->error) {
   try {
				$app_userdata = $this->get_app_userdata_auth($auth_type, $auth_params['params']['account_email'], $app_uuid);
   
				if(!isset($app_userdata->seq) || !isset($app_userdata->account_email)) {
					$authcenter_response['logged_app_userdata'] = [
						'account_email' => $auth_params['params']['account_email'],
						'account_group' => $app_uuid,
						'app_name' => $app_uuid,
					];
					
					$insert_authenticated_userdata = $this->insert_authenticated_app_login_userdata($authcenter_response);
					if (!isset($insert_authenticated_userdata->account_seq) || !isset($insert_authenticated_userdata->status) || !isset($insert_authenticated_userdata->account_email)) {
						$this->error = true;
						$this->error_msg[] = "Failed during insert authenticated userdata.";
					} else {
						if ($insert_authenticated_userdata->status !== TRUE) {
							$this->error = true;
							$this->error_msg[] = "Failed insert new app account-userdata.";
							$this->error_msg[] = $authcenter_response;
						} else {
       if(is_string($insert_authenticated_userdata->account_email) && !empty($insert_authenticated_userdata->account_email)) {
        $account_email = $insert_authenticated_userdata->account_email;
       } else {
        $this->error = true;
        $this->error_msg[] = "Userdata email should be in string datatype.";
       }
      }
					}
				} else {
     $account_email = $app_userdata->account_email;
    }
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "Cannot get app-account data on local native-login.";
			}
		}
  //------------------------------------
  # Get exisiting Account Userdata:: [localdata]
		if (!$this->error) {
   try {
    $local_data = $this->get_logged_userdata($account_email, $auth_type, $this->base_authcenter['apps']['key']);
    if(!isset($local_data->account_active) || !isset($local_data->account_delete_status)) {
     $this->error = true;
     $this->error_msg[] = "Account data maybe not exists.";
    }
   } catch(Exception $ex) {
    $this->error = true;
    $this->error_msg[] = "Cannot get local-account data on local native-login with exception: {$ex->getMessage()}.";
   }
		}
  //------------------------------------
		// Local User Login Check Every Aspect
		//------------------------------------
		if (!$this->error) {
   if(strtoupper($local_data->account_active) !== strtoupper('Y')) {
    $this->error = true;
				$this->error_msg[] = "Account not active yet.";
   }
   if((int)$local_data->account_delete_status > 0) {
    $this->error = true;
				$this->error_msg[] = "Account already deleted";
   }
		}
		# Check Allowed IP Address
		if (!$this->error) {
   $client = $this->accounts->generate_client();
			$fragment_login = [];
			if (!in_array($auth_params['login_purpose'], [
				'reset_password',
				'lost_password',
				'api_login',
				'api'
			])) {
				try {
     $local_data->client = $client;
					$fragment_login['ip_address'] = $this->local_login_fragment_method($local_data, 'login_ip_address');
					if(!isset($fragment_login['ip_address']['status'])) {
						$this->error = true;
						$this->error_msg[] = "Fragment login response not expected as ip-address-check.";
					} else {
						if($fragment_login['ip_address']['status'] !== TRUE) {
							$this->error = true;
							if(isset($fragment_login['ip_address']['errors'])) {
								if(is_array($fragment_login['ip_address']['errors'])) {
         $this->error_msg[] = json_encode($fragment_login['ip_address']['errors']);
								} else {
									$this->error_msg[] = 'Maybe not allowed ip-address to login.';
								}
							}
						}
					}
				} catch(Exception $ex) {
					$this->error = true;
					$this->error_msg[] = "Cannot check fragment login response with exception: {$ex->getMessage()}.";
				}
			}
		}
  //-------------
  // Set Sessions
  //-------------
  // Is Dashboard Login or Other Method
		if (!$this->error) {
			switch($auth_params['login_purpose']) {
				case 'reset_password':
				case 'lost_password':
				case 'api_login':
				case 'api':
					return $this->local_login_success([
      'local_data' => $local_data,
      'client' => $client,
     ]);
				break;
				case 'dashboard':
				default:
					// Is 2FA Enabled or Not
					try {
						$this->set_loginas2fa_session([
							'dashboard_account_seq' => $local_data->seq,
						]);
						if(($local_data->account_2fa_activated === 'Y') && !empty($local_data->account_2fa_secretcode)) {
							return [
								'status' => true,
								'collectData' => [
         'local_data' => $local_data,
         'client' => $client,
        ],
							];
						} else {
							return $this->local_login_success([
        'local_data' => $local_data,
        'client' => $client,
       ]);
						}
					} catch (Exception $ex) {
						$this->error = true;
						$this->error_msg[] = "Cannot create login2fa session params with exception: {$ex->getMessage()}.";
					}
				break;
			}
		}
  
  // Make Response
  $login_results = [
   'status' => false,
   'data' => null,
   'errors' => [],
  ]; 
  if(!$this->error) {
   $login_results['status'] = true;
   $login_results['data'] = $authcenter_response['curl_response'];
   if(isset($login_results['data']->message)) {
    $login_results['message'] = $login_results['data']->message;
   }
   
   if(isset($authcenter_response['logged_app_userdata'])) {
    $login_results['logged_app_userdata'] = $authcenter_response['logged_app_userdata'];
   }
   
   // From Database Apps
   if(isset($app_userdata)) {
    $login_results['app_userdata'] = $app_userdata;
   }
   if(isset($local_data)) {
    $login_results['local_data'] = $local_data;
   }
  } else {
   $login_results['errors'] = $this->error_msg;
  }
  return $login_results;
	}
 //-----------------------------------
 // Update Login
 //-----------------------------------
 private function insert_social_account_login($input_params) {
		//-- Global error
		$new_insert_id = 0;
		$query_params = array(
			'login_id' => (isset($input_params->seq) ? $input_params->seq : ''),
			'login_email' => (isset($input_params->account_email) ? $input_params->account_email : ''),
			'login_username' => (isset($input_params->account_username) ? $input_params->account_username : ''),
			'login_nickname' => (isset($input_params->account_nickname) ? $input_params->account_nickname : ''),
			'local_seq' => (isset($input_params->seq) ? $input_params->seq : ''),
			'login_datetime_first'	=> (isset($input_params->login_datetime) ? $input_params->login_datetime : $this->DateObject->format('Y-m-d H:i:s')),
			'login_datetime_last'	=> (isset($input_params->login_datetime) ? $input_params->login_datetime : $this->DateObject->format('Y-m-d H:i:s')),
		);
		$query_params['login_username'] = (is_string($query_params['login_username']) ? strtolower($query_params['login_username']) : '');
  $query_params['login_username'] .= "@" . $this->base_authcenter['apps']['key'];
		//$query_params['login_username'] .= "@localhost";
		//$query_params['login_username'] .= "@ggpassport";
  try {
   $social_data = $this->get_logged_userdata($query_params['login_username'], 'goodgames', $this->base_authcenter['apps']['key']);
   
   if(!isset($social_data->seq)) {
    $this->db_account->insert($this->base_dash['tables']['accounts']['dashboard_account_social'], $query_params);
    $new_insert_id = $this->db_account->insert_id();
   } else {
    if((int)$social_data->seq > 0) {
     $new_insert_id = (int)$social_data->seq;
    } else if($new_insert_id === 0) {
     $this->db_account->insert($this->base_dash['tables']['accounts']['dashboard_account_social'], $query_params);
     $new_insert_id = $this->db_account->insert_id();
    }
   }
  } catch(Exception $ex) {
   $this->error = true;
   $this->error_msg[] = "Cannot get data of login-data by get-social-dataa function: {$ex->getMessage()}";
  }
		return $new_insert_id;
	}
 
 
 
 
 private function local_login_success(array $collectData) {
  $responses = [
   'status' => false,
   'success' => false,
   'error' => [],
   'local_data' => null,
  ];
		if(!isset($collectData['local_data'])) {
			$this->error = true;
   $this->error_msg[] = "Not have localdata.";
		}
		if(!$this->error) {
			try {
				$collectData['social_account_login_seq'] = $this->insert_social_account_login($collectData['local_data']);
    if($collectData['social_account_login_seq'] === 0) {
     $this->error = true;
     $this->error_msg[] = "returning social-account-login-seq is 0.";
    }
			} catch (Exception $ex) {
				$this->error = true;
				$this->error_msg[] = "not get return from insert_social_account_login: {$ex->getMessage()}";
			}
		}
  if(!$this->error) {
   try {
    $collectData['social_login_seq'] = $this->insert_social_log_login_account($collectData['social_account_login_seq']);
    if((int)$collectData['social_login_seq'] === 0) {
     $this->error = true;
     $this->error_msg[] = 'Return empty value from social log sequence or zero values while doing: local-login.';
    }
   } catch(Exception $ex) {
    $this->error = true;
				$this->error_msg[] = "Cannot login and get social log sequence on local-login with exception: {$ex->getMessage()}";
   }
  }
  if(!$this->error) {
   try {
    $collectData['account_login_social_log_seq'] = $this->update_local_login_account($collectData['local_data']->seq, $collectData['social_login_seq']);
    $collectData['session_params'] = array(
     'social_account_login_seq' => $collectData['social_account_login_seq'],
     'dashboard_account_seq' => $collectData['local_data']->seq,
     'client' => $collectData['client'],
    );
    $collectData['set_logged_userdata'] = $this->set_logged_user_session($collectData['session_params']);
   } catch(Exception $ex) {
    $this->error = true;
				$this->error_msg[] = "Cannot update login session log seq of account with exception: {$ex->getMessage()}";
   }
  }
  if(!$this->error) {
   if($collectData['set_logged_userdata'] !== TRUE) {
    $this->error = true;
				$this->error_msg[] = "Unexpected failed error from server while creating logged-in users.";
   }
  }
  // Response
		if(!$this->error) {
			$responses['status'] = true;
			$responses['success'] = true;
			$responses['local_data'] = $collectData['local_data'];
		} else {
			$responses['error'] = $this->error_msg;
		}
		return $responses;
	}
 
}