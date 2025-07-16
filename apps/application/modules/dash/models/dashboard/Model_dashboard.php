<?php
if (!defined('BASEPATH')) {
	exit('Cannot load script directly.');
	
}

class Model_dashboard extends CI_Model {
 protected $error = FALSE, $error_msg = [];
	protected $DateObject;
	protected $base_dash;
	private $cache_params;
 private $db_account, $db_stock;
	function __construct() {
		parent::__construct();
  $this->load->library('dash/base/Lib_configs', NULL, 'libconf');
		$this->base_dash = $this->libconf->get_base_dash();
		$this->DateObject = $this->libconf->get_dateobject();
  # Load Databases
		$this->db_account = $this->libconf->get_connected_databases('db_account', 'account');
  $this->db_stock = $this->libconf->get_connected_databases('db_stock', 'stock');
  
  
  $this->cache_params = [
   'expired' => 3600,
  ];
	}
 
 public function get_dateobject() {
  return $this->libconf->get_dateobject();
 }
 
 
 private function get_members_count(String $count_type, Array $count_params = [], Bool $use_sql = false) {
  $this->cache_params['strings'] = [
   'type' => $count_type,
   'params' => $count_params,
  ];
  $this->cache_params['key'] = sprintf("modelDashboard:get_members_count:%s", sha1(json_encode($this->cache_params['strings'])));
  if(!in_array($count_type, [
   'all',
   'single',
   'params'
  ])) {
   $this->error = true;
   $this->error_msg[] = "Count type no in allowed count-types.";
  }
  if(!$this->error) {
   if($count_type == 'params') {
    if(empty($count_params)) {
     $this->error = true;
     $this->error_msg[] = "Count params cannot be empty while using count-type as params.";
    }
   }
  }
  if(!$this->error) {
   try {
    if($use_sql === true) {
     $this->db_stock->select("COUNT(1) As count_val");
     switch($count_type) {
      case 'params':
       $this->db_stock->from("{$this->base_dash['tables']['members']} AS tbm");
      break;
      case 'all':
      default:
       $this->db_stock->from("{$this->base_dash['tables']['members']} AS tbm");
      break;
     }
     
     if($count_type == 'params') {
      foreach($count_params as $k => $v) {
       if(!in_array($k, [
        'member_dt_created',
        'member_dt_updated',
       ])) {
        $this->db_stock->where("tbm.{$k}", $v);
       }
      }
     }
     $sql_query = $this->db_stock->get();
     $sql_results = $sql_query->row();
     if(isset($sql_results->count_val)) {
      $this->cache->redis->save($this->cache_params['key'], serialize($sql_results), $this->cache_params['expired']);
     }
     return $sql_results;
    } else {
     $sql_results = $this->cache->redis->get($this->cache_params['key']);
     if(!$sql_results) {
      return $this->get_members_count($count_type, $count_params, true);
     } else {
      return unserialize($sql_results);
     }
    }
   } catch(Exception $e) {
    throw $e;
   }
  }
 }
}