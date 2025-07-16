<?php
if (!defined('BASEPATH')) {
	exit('Cannot lload script directly: Lib_authcenter.');
}
class Lib_authcenter {
	protected $CI;
	private $base_authcenter;
 protected $auth_env = 'local';
	function __construct() {
		$this->CI = &get_instance();
		$this->CI->load->config('dash/base_authcenter');
		$this->base_authcenter = $this->CI->config->item('base_authcenter');
  $this->auth_env = Instance_config::$env_group['env_env'];
	}
	public function get_base_authcenter() {
  return $this->base_authcenter;
 }
	
	public function login_with_email_and_password(Array $login_params, Array $http_headers) {
		if (!isset($login_params['account_email']) || !isset($login_params['account_password'])) {
			return false;
		}
		$curl_headers = [];
		$post_params = [
			'email' => $login_params['account_email'],
			'password' => $login_params['account_password']
		];
		if(!empty($http_headers)) {
			foreach ($http_headers as $h_key => $h_val) {
				$curl_headers[] = sprintf("%s: %s",
					$h_key,
					$h_val
				);
			}
		}
		
		$string_params = json_encode($post_params);
		try {
			$ch = curl_init();
			curl_setopt_array($ch, [
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 45,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_HEADER => FALSE,
			]);
			curl_setopt($ch, CURLOPT_SSLVERSION, 6);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			# Set Header
			curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
			//curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $string_params);
			
			$url = sprintf("%s/%s",
				$this->base_authcenter['api_endpoints'][$this->auth_env],
				$this->base_authcenter['api_paths']['login']
			);
			curl_setopt($ch, CURLOPT_URL, $url);
			$curl_response = curl_exec($ch);
			$curl_info = [
				'header_request' => $curl_headers,
				'header_response' => curl_getinfo($ch, CURLINFO_HEADER_OUT),
				'request_body' => $post_params,
				'mixing' => curl_getinfo($ch),
			];
			
			
			$curl_response = json_decode($curl_response);
			curl_close($ch);
			
			return [
				'curl_info' => $curl_info,
				'curl_response' => $curl_response,
			];
		} catch (Exception $e) {
			throw $e;
		}
	}
	public function get_profiles_email(String $account_email, Array $http_headers) {
		$curl_headers = [];
		if (!empty($http_headers)) {
			foreach ($http_headers as $h_key => $h_val) {
				$curl_headers[] = sprintf("%s: %s",
					$h_key,
					$h_val
				);
			}
		}
		try {
			$ch = curl_init();
			curl_setopt_array($ch, [
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 45,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_HEADER => FALSE,
			]);
			curl_setopt($ch, CURLOPT_SSLVERSION, 6);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			# Set Header
			curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
			
			curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
			curl_setopt($ch, CURLOPT_POST, FALSE);
			
			$url = sprintf("%s/%s",
				$this->base_authcenter['api_endpoints'][$this->auth_env],
				$this->base_authcenter['api_paths']['profiles']['email']
			);
			curl_setopt($ch, CURLOPT_URL, $url);
			$curl_response = curl_exec($ch);
			$curl_info = [
				'header_request'		=> $curl_headers,
				'header_response'		=> curl_getinfo($ch, CURLINFO_HEADER_OUT),
				'request_body'			=> [],
				'mixing'						=> curl_getinfo($ch),
			];
			$curl_response = json_decode($curl_response);
			curl_close($ch);
			
			return [
				'curl_info'				=> $curl_info,
				'curl_response'		=> $curl_response,
			];
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	
	
}