<?php
if(!defined('BASEPATH')) { 
 exit('Cannot load script directly.');
}


class Lib_configs {
 protected $CI;
 protected $base_config;
 protected $base_dash;
 private $DateObject;
 protected $connected_databases = [
  'local', 'dev', 'sandbox', 'prod',
  'stock', 'account',
 ];
 function __construct() {
  $this->CI = &get_instance();
  $this->CI->load->config('dash/base_dash');
  $this->base_dash = $this->CI->config->item('base_dash');
  # Load helpers for dash
  $this->CI->load->helper('dash/base_dashboard');
  # Make DateObject
  $this->DateObject = \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'), new DateTimeZone(Instance_config::$timezone));
  # Load Databases:
  $this->db_app = $this->CI->load->database(Instance_config::$env_group['env_env'], TRUE);
  $this->db_stock = $this->CI->load->database('stock', TRUE);
  $this->db_account = $this->CI->load->database('account', TRUE);
 }
 
 public function get_base_dash() {
  return $this->base_dash;
 }

 public function get_dateobject() {
  return $this->DateObject;
 }
 
 
 public function get_connected_databases(String $conn_alias, String $conn_name) {
  try {
   $conn_name = trim(strtolower($conn_name));
   $conn_alias = trim($conn_alias);
   if(!in_array($conn_name, [
    $this->connected_databases
   ])) {
    return false;
   }
   if(!isset($this->CI->{$conn_alias})) {
    $this->CI->{$conn_alias} = $this->CI->load->database($conn_name, TRUE);
   }
   return $this->CI->{$conn_alias};
  } catch(Exception $e) {
   return $e;
  }
 }
}