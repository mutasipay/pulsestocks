<?php
if(!defined('BASEPATH')) {
 exit('Cannot load script directly to models.');
}
class Model_providers extends CI_Model {
 protected $error = FALSE, $error_msg = [];
	protected $DateObject;
	protected $base_dash;
	private $cache_params;
 private $userdata, $localdata;
 private $db_account, $db_stock;
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
  $this->db_stock = $this->load->database('stock', true);
	}
 
 
 public function create_paging_parameters(Int $count, Array $params = [
		'limit'			=> 10,
		'start'			=> 0
	]) {
		$paging_parameters = [];
		try {
			$params['start'] = (int)$params['start'];
			$params['limit'] = (int)$params['limit'];
			$params['page'] = 1;
			if (is_numeric($count)) {
				$count = (int)$count;
			} else {
				$count = 0;
			}
			if (($count > 0) && ($params['limit'] < $count)) {
				$pages = ($count / $params['limit']);
				/*
				do {
					$paging_parameters[] = [
						'page'			=> $params['page'],
						'start'			=> $params['start'],
						'limit'			=> $params['limit']
					];
					$params['page'] += 1;
					$params['start'] += $params['limit'];
				} while ($params['page'] < $pages);
				*/
			}
			return [
				'paging_parameters' => $paging_parameters,
				'start' => $params['start'],
				'limit' => $params['limit'],
				'page' => $params['page']
			];
		} catch (Exception $e) {
			throw $e;
		}
	}
 public function get_country_data(string $country_code = '', bool $use_sql = false) {
  $cache_params = [
   'expired' => $this->cache_params['expired']
  ];
  $cache_params['strings'] = [
   'country_code' => $country_code,
  ];
  $cache_params['key'] = sprintf("modelProviders:get_country_data:%s",
   sha1(json_encode($cache_params['strings']))
  );
  try {
   if($use_sql === true) {
    $this->db_stock->select('s000.*')->from("{$this->base_dash['tables']['stocks']['providers']['country']} AS s000");
    if(!empty($country_code)) {
     $this->db_stock->where('s000.country_code', $country_code);
    } else {
     $this->db_stock->where('s000.country_activated', 'Y');
    }
    $sql_query = $this->db_stock->get();
    $sql_results = $sql_query->result();
    if(!empty($sql_results)) {
     $this->cache->redis->save($cache_params['key'], serialize($sql_results), $cache_params['expired']);
    }
    return $sql_results;
   } else {
    $sql_results = $this->cache->redis->get($cache_params['key']);
    if(!$sql_results) {
     return $this->get_country_data($country_code, true);
    } else {
     return unserialize($sql_results);
    }
   }
  } catch(Exception $e) {
   throw $e;
  }
 }
 
 
 public function get_providers_counts(bool $use_sql = false) {
  $cache_params = [
   'expired' => $this->cache_params['expired']
  ];
  $cache_params['strings'] = [
   'params' => [],
   'date' => $this->DateObject->format('Ymd')
  ];
  $cache_params['key'] = sprintf("modelProviders:get_providers_counts:%s",
   sha1(json_encode($cache_params['strings']))
  );
  try {
   if($use_sql === true) {
    $this->db_stock->select('COUNT(1) AS val_counts')->from($this->base_dash['tables']['stocks']['providers']['provider']);
    $sql_query = $this->db_stock->get();
    $sql_results = $sql_query->row();
    if(isset($sql_results->val_counts)) {
     $this->cache->redis->save($cache_params['key'], serialize($sql_results), $cache_params['expired']);
    }
    return $sql_results;
   } else {
    $sql_results = $this->cache->redis->get($cache_params['key']);
    if(!$sql_results) {
     return $this->get_providers_counts(true);
    } else {
     return unserialize($sql_results);
    }
   }
  } catch(Exception $e) {
   throw $e;
  }
 }
 
 private function get_providers_data_count(array $params, bool $use_sql = false) {
  $cache_params = [
   'expired' => $this->cache_params['expired']
  ];
  $cache_params['strings'] = [
   'params' => $params,
   'date' => $this->DateObject->format('Ymd')
  ];
  $cache_params['key'] = sprintf("modelProviders:get_providers_data_count:%s",
   sha1(json_encode($cache_params['strings']))
  );
  try {
   if($use_sql === true) {
    $search_textcolumns = (isset($params['text_columns']) ? $params['text_columns'] : []);
    $search_columns = array_values($search_textcolumns);
    $sql_searches = [];
    $sql_search = '';
    
    $this->db_stock->select('COUNT(1) AS val_counts');
    $this->db_stock->from("{$this->base_dash['tables']['stocks']['providers']['provider']} AS s001");
    
    // Search Text
				if((isset($params['search_params']['search_text']) && is_string($params['search_params']['search_text'])) && (isset($params['search_params']['column']) && is_string($params['search_params']['column']))) {
					if(!empty($params['search_params']['search_text'])) {
						$search_permalinks = base_permalink($params['search_params']['search_text'], 128);
						$search_arrays = explode('-', $search_permalinks);
						if(!empty($search_arrays) && !empty($params['search_params']['column'])) {
							$text_column = strtolower($params['search_params']['column']);
							$sql_search .= "(";
       if(!empty($search_columns)) {
        $search_columns = array_filter($search_columns, function($e) {
         return (substr($e, 0, 4) == 's001');
        });
       }
							for($i = 0, $iSearchs = count($search_arrays); $i < $iSearchs; $i++) {
        if($i > 0) {
         $sql_search .= " AND ";
        }
        if(!empty($search_columns)) {
         $sql_str = sprintf(" (%s LIKE CONCAT('%%', '%s', '%%'))",
          implode(
           sprintf(" LIKE CONCAT('%%', '%s', '%%') OR ", $this->db_stock->escape_str($search_arrays[$i])), 
           $search_columns
          ),
          $this->db_stock->escape_str($search_arrays[$i])
         );
         $sql_searches[] = $sql_str;
         $sql_search .= $sql_str;
        } else {
         if(isset($search_textcolumns[$text_column])) {
          $sql_str = sprintf("%s LIKE CONCAT('%%', '%s', '%%')",
           $search_textcolumns[$text_column],
           $this->db_stock->escape_str($search_arrays[$i])
          );
          $sql_searches[] = $sql_str;
          $sql_search .= $sql_str;
         }
        }
       }
							$sql_search .= ")";
						}
						if(!empty($sql_search)) {
							$this->db_stock->where($sql_search, NULL, FALSE);
						}
					}
				}
    
    
    
    if(isset($params['country_data']->id)) {
     $this->db_stock->where('s001.provider_countryid', $params['country_data']->id);
    }
    if(isset($params['provider_activated'])) {
     if(in_array($params['provider_activated'], ['Y', 'N', 'y', 'n'])) {
      $this->db_stock->where('UPPER(s001.provider_activated)', strtoupper($params['provider_activated']));
     }
    }
    
    
    $sql_query = $this->db_stock->get();
    $sql_results = $sql_query->row();
    if(isset($sql_results->val_counts)) {
     $this->cache->redis->save($cache_params['key'], serialize($sql_results), $cache_params['expired']);
    }
    return $sql_results;
   } else {
    $sql_results = $this->cache->redis->get($cache_params['key']);
    if(!$sql_results) {
     return $this->get_providers_data_count($params, true);
    } else {
     return unserialize($sql_results);
    }
   }
  } catch(Exception $e) {
   throw $e;
  }
 }
 private function get_providers_data_data(array $params, array $paging, bool $use_sql = false) {
  $cache_params = [
   'expired' => $this->cache_params['expired']
  ];
  $cache_params['strings'] = [
   'params' => $params,
   'paging' => $paging,
   'date' => $this->DateObject->format('Ymd')
  ];
  $cache_params['key'] = sprintf("modelProviders:get_providers_data_data:%s",
   sha1(json_encode($cache_params['strings']))
  );
  try {
   if($use_sql === true) {
    $search_textcolumns = (isset($params['text_columns']) ? $params['text_columns'] : []);
    $search_columns = array_values($search_textcolumns);
    $sql_searches = [];
    $sql_search = '';
    $this->db_stock->select("s001.*, s000.country_code, s000.country_name, s000.country_activated, s000.id AS country_id");
    $this->db_stock->from("{$this->base_dash['tables']['stocks']['providers']['provider']} AS s001");
    $this->db_stock->join("{$this->base_dash['tables']['stocks']['providers']['country']} AS s000", 's000.id = s001.provider_countryid', 'LEFT');
    // Search Text
				if((isset($params['search_params']['search_text']) && is_string($params['search_params']['search_text'])) && (isset($params['search_params']['column']) && is_string($params['search_params']['column']))) {
					if(!empty($params['search_params']['search_text'])) {
						$search_permalinks = base_permalink($params['search_params']['search_text'], 128);
						$search_arrays = explode('-', $search_permalinks);
						if(!empty($search_arrays) && !empty($params['search_params']['column'])) {
							$text_column = strtolower($params['search_params']['column']);
							$sql_search .= "(";
       /*
       if(!empty($search_columns)) {
        $search_columns = array_filter($search_columns, function($e) {
         return (substr($e, 0, 4) == 's001');
        });
       }
       */
							for($i = 0, $iSearchs = count($search_arrays); $i < $iSearchs; $i++) {
        if($i > 0) {
         $sql_search .= " AND ";
        }
        if(!empty($search_columns)) {
         $sql_str = sprintf(" (%s LIKE CONCAT('%%', '%s', '%%'))",
          implode(
           sprintf(" LIKE CONCAT('%%', '%s', '%%') OR ", $this->db_stock->escape_str($search_arrays[$i])), 
           $search_columns
          ),
          $this->db_stock->escape_str($search_arrays[$i])
         );
         $sql_searches[] = $sql_str;
         $sql_search .= $sql_str;
        } else {
         if(isset($search_textcolumns[$text_column])) {
          $sql_str = sprintf("%s LIKE CONCAT('%%', '%s', '%%')",
           $search_textcolumns[$text_column],
           $this->db_stock->escape_str($search_arrays[$i])
          );
          $sql_searches[] = $sql_str;
          $sql_search .= $sql_str;
         }
        }
       }
							$sql_search .= ")";
						}
						if(!empty($sql_search)) {
							$this->db_stock->where($sql_search, NULL, FALSE);
						}
					}
				}
    // Sorting and Limit
    if(isset($params['order']['by']) && isset($params['order']['sort'])) {
     $order_by = $params['order']['by'];
     if(isset($search_textcolumns[$order_by])) {
      $this->db_stock->order_by($search_textcolumns[$order_by], $params['order']['sort']);
     } else {
      $this->db_stock->order_by('s001.id', $params['order']['sort']);
     }
    }
    if(isset($paging['start']) && isset($paging['limit'])) {
     $this->db_stock->limit($paging['limit'], $paging['start']);
    }
    
    $sql_query = $this->db_stock->get();
    $sql_results = $sql_query->result();
    if(!empty($sql_results)) {
     $this->cache->redis->save($cache_params['key'], serialize($sql_results), $cache_params['expired']);
    }
    return $sql_results;
   } else {
    $sql_results = $this->cache->redis->get($cache_params['key']);
    if(!$sql_results) {
     return $this->get_providers_data_data($params, $paging, true);
    } else {
     return unserialize($sql_results);
    }
   }
  } catch(Exception $e) {
   throw $e;
  }
 }
 
 
 
 public function get_providers_datas(array $provider_params, bool $use_sql = false) {
  $provider_params['text_columns'] = [
   'id' => 's001.id',
   'country_code' => 's000.country_code',
   'country_name' => 's000.country_name',
   'provider_countrycode' => 's001.provider_countrycode',
   'provider_code' => 's001.provider_code',
   'provider_name' => 's001.provider_name',
   'provider_activated' => 's001.provider_activated',
  ];
  $cache_params = [
   'expired' => $this->cache_params['expired']
  ];
  $cache_params['strings'] = [
   'provider_params' => $provider_params,
   'date' => $this->DateObject->format('Ymd')
  ];
  $cache_params['key'] = sprintf("modelProviders:get_providers_datas:%s",
   sha1(json_encode($cache_params['strings']))
  );
  try {
   if($use_sql === true) {
    $provider_datas = [
     'status' => false,
    ];
    // Count Filtered
    $provider_datas['count'] = $this->get_providers_data_count($provider_params);
    if(isset($provider_datas['count']->val_counts)) {
     $provider_datas['paging'] = $this->create_paging_parameters((int)$provider_datas['count']->val_counts, $provider_params['paging_params']);
     $provider_datas['data'] = $this->get_providers_data_data($provider_params, $provider_datas['paging']);
    } else {
     $provider_datas['data'] = [];
    }
    if(!empty($provider_datas['data'])) {
     $provider_datas['status'] = true;
     $this->cache->redis->save($cache_params['key'], serialize($provider_datas), $cache_params['expired']);
    }
    return $provider_datas;
   } else {
    $provider_datas = $this->cache->redis->get($cache_params['key']);
    if(!$provider_datas) {
     return $this->get_providers_datas($provider_params, true);
    } else {
     return unserialize($provider_datas);
    }
   }
  } catch(Exception $e) {
   throw $e;
  }
 }
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
}