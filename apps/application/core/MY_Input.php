<?php
if (!defined('BASEPATH')) {
	exit('Cannot load script directly on MY-Input.');
}
class MY_Input extends CI_Input {
	
	public function set_cookie($name, $value = '', $expire = '', $domain = '', $path = '/', $prefix = '', $secure = NULL, $httponly = NULL, $samesite = NULL) {
		if (is_array($value) || (is_object($value) && !is_callable($value))) {
			$value = serialize($value);
		}
		parent::set_cookie($name, $value, $expire, $domain, $path, $prefix, $secure, $httponly, $samesite);
	}
	public function cookie($index = NULL, $xss_clean = NULL) {
		$return = parent::cookie($index, $xss_clean);
		if (@unserialize($return) != FALSE) {
			$return = unserialize($return);
		}
		return $return;
	}
}
