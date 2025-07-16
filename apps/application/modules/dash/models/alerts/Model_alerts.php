<?php
if (!defined('BASEPATH')) {
	exit('Cannot load script directly.');
}

class Model_alerts extends CI_Model {
 protected $error = FALSE, $error_msg = [];
	protected $DateObject;
	protected $base_dash;
	private $cache_params;
 private $db_stock;
	function __construct() {
		parent::__construct();
  $this->load->library('dash/base/Lib_configs', NULL, 'libconf');
		$this->base_dash = $this->libconf->get_base_dash();
		$this->DateObject = $this->libconf->get_dateobject();
  # Load Databases
  $this->db_stock = $this->load->database('stock', true);
  
  
  $this->cache_params = [
   'expired' => 3600,
  ];
	}
 
 public function get_dateobject() {
  return $this->libconf->get_dateobject();
 }
 
 
 
 
 public function get_usersalerts_counts(array $localdata, string $alert_type, bool $use_sql = false) {
  if(!in_array($alert_type, [
   'drafted',
   'accepted',
   'viewed',
   'logged'
  ])) {
   return;
  }
  $this->cache_params['strings'] = [
   'userid' => (int)$localdata['seq'],
   'alert_type' => $alert_type,
   'localdata' => $localdata,
  ];
  $this->cache_params['key'] = sprintf("modelAlerts:get_usersalerts_counts:%s", 
   sha1(json_encode($this->cache_params['strings']))
  );
  try {
   if($use_sql == true) {
    $this->db_stock->select('COUNT(1) AS val_counts');
    $this->db_stock->from("{$this->base_dash['tables']['stocks']['logs']['drafted']} AS s50");
    
    $sql_query = $this->db_stock->get();
    $sql_results = $sql_query->row();
    if(isset($sql_results->val_counts)) {
     $this->cache->redis->save($this->cache_params['key'], serialize($sql_results), $this->cache_params['expired']);
    }
    return $sql_results;
   } else {
    $sql_results = $this->cache->redis->get($this->cache_params['key']);
    if(!$sql_results) {
     return $this->get_usersalerts_counts($localdata, $alert_type, true);
    } else {
     return unserialize($sql_results);
    }
   }
  } catch(Exception $e) {
   throw $e;
  }
 }
 
 public function get_usersalerts_datas(array $localdata, array $alerts, array $params = [], bool $use_sql = false) {
  if(!isset($alerts['alert_type']) || !isset($alerts['alert_processed'])) {
   return;
  }
  if(!in_array($alerts['alert_type'], [
   'drafted',
   'accepted',
   'viewed',
   'logged'
  ])) {
   return;
  }
  $this->cache_params['strings'] = [
   'userid' => (int)$localdata['seq'],
   'alert_type' => $alerts['alert_type'],
   'alert_processed' => $alerts['alert_processed'],
   'localdata' => $localdata,
  ];
  $this->cache_params['key'] = sprintf("modelAlerts:get_usersalerts_datas:%s", 
   sha1(json_encode($this->cache_params['strings']))
  );
  try {
   if($use_sql == true) {
    $this->db_stock->select("s50.*, s40.sim_id, s40.stock_type, s40.stock_amount, s40.stock_userid, s40.stock_created, s40.stock_updated, (CASE
     WHEN s40.stock_type IN('used', 'deduct') THEN 'Someone already use balance of sim item.'
     WHEN s40.stock_type = 'topup' THEN 'Someone already make a topup to sim balance.'
     WHEN s40.stock_type = 'balance' THEN 'Someone set a new balance to a sim item.'
     ELSE 'Someone set a new balance to a sim item.' END) AS draft_message");
    $this->db_stock->from("{$this->base_dash['tables']['stocks']['logs']['drafted']} AS s50");
    $this->db_stock->join("{$this->base_dash['tables']['stocks']['drafts']['items']} AS s40", 's40.id = s50.draft_id', 'LEFT');
    $this->db_stock->where('s50.draft_processed', $alerts['alert_processed']);
    $this->db_stock->order_by('s50.draft_created', 'ASC');
    if(isset($params['start']) && isset($params['limit'])) {
     $this->db_stock->limit($params['limit'], $params['start']);
    }
    $sql_query = $this->db_stock->get();
    $sql_results = $sql_query->result();
    if(is_array($sql_results) && !empty($sql_results)) {
     $this->cache->redis->save($this->cache_params['key'], serialize($sql_results), $this->cache_params['expired']);
    }
    return $sql_results;
   } else {
    $sql_results = $this->cache->redis->get($this->cache_params['key']);
    if(!$sql_results) {
     return $this->get_usersalerts_datas($localdata, $alerts, $params, true);
    } else {
     return unserialize($sql_results);
    }
   }
  } catch(Exception $e) {
   throw $e;
  }
 }
 
 
 
 private function get_useralerts_data_count() {
  
  
 }
}