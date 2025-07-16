<?php
if(!defined('BASEPATH')) {
exit('No direct script access allowed.');
}

class Providers extends MY_Dashboard {
	private $error = FALSE, $error_msg = [];
	protected $DateObject;
	protected $base_dash;
	function __construct() {
		parent::__construct();
  $this->load->model('dash/dashboard/Model_dashboard', 'mod_dashboard');
		$this->DateObject = $this->mod_dashboard->get_dateobject();
  # Load Model Alerts
  $this->load->model('dash/stocks/Model_providers', 'mod_providers');
  # PHP Inputs
  $this->load->model('dash/inputs/Model_inputs', 'mod_inputs');
  # Load Input Security Check
		$this->load->helper('security');
		$this->load->library('form_validation');
  
	}
 
 public function index() {
  $collectData = [
   'page' => 'providers-index',
   'collect' => [
    'users' => [
     'localdata' => $this->localdata,
     'userdata' => $this->userdata,
    ],
   ],
   'error' => false,
   'title' => 'Stocks: Providers | Dash',
  ];
  $collectData['collect']['dates'] = [
   'current' => $this->DateObject->format('Y-m-d H:i:s'),
  ];
  //-------------------------------
  $collectData['json_response'] = [
   'status' => false,
  ];
  # Get Country Data
  if(!$this->error) {
   try {
    $collectData['collect']['country_data'] = $this->mod_providers->get_country_data();
    if(is_array($collectData['collect']['country_data']) && !empty($collectData['collect']['country_data'])) {
     $collectData['collect']['country_codes'] = array_column($collectData['collect']['country_data'], 'country_code');
    }
   } catch(Exception $ex) {
    $this->error = true;
    $this->error_msg[] = "Cannot get country-data from model-providers with exception: {$ex->getMessage()}.";
   }
  }
  
  
  $this->load->view('dash/providers/providers', $collectData);
 }
 
 
 
 public function data() {
  $collectData = [
   'page' => 'providers-data',
   'collect' => [
    'users' => [
     'localdata' => $this->localdata,
     'userdata' => $this->userdata,
    ],
   ],
   'error' => false,
   'title' => 'Stocks: Providers | Dash',
  ];
  $collectData['collect']['dates'] = [
   'current' => $this->DateObject->format('Y-m-d H:i:s'),
  ];
  //-------------------------------
  $collectData['json_response'] = [
   'status' => false,
   'data' => [],
   'errors' => [],
   'recordsTotal' => 0,
   'recordsFiltered' => 0,
   'pageLength' => 5,
  ];
  try {
   $collectData['collect']['php_inputs'] = $this->mod_inputs->get_php_input_requests();
   $collectData['collect']['input_params'] = [];
   $collectData['collect']['providers_params'] = [
				'paging_params' 	=> [
					'limit' => 10,
					'start' => 0,
				]
   ];
   $collectData['collect']['providers_params']['order'] = [
    'by' => 'id',
    'sort' => 'DESC'
   ];
   $collectData['collect']['providers_params']['order_bies'] = [
    'no' => 'id',
    'id' => 'id',
    'country_name' => 'country_name',
    'provider_countrycode' => 'country_code',
    'provider_code' => 'provider_code',
    'provider_name' => 'provider_name',
    'provider_activated' => 'provider_activated',
   ];
   $collectData['collect']['providers_params']['search_params'] = [
    'column' => 'id',
    'search_text' => ''
   ];
   $collectData['collect']['providers_params']['order_sorts'] = [
    'keys' => array_keys($collectData['collect']['providers_params']['order_bies']),
    'values' => array_values($collectData['collect']['providers_params']['order_bies']),
    'sorts' => [
     'ASC',
     'DESC'
    ],
   ];
   if(isset($collectData['collect']['php_inputs']['body']['form_search']['start'])) {
    if(is_numeric($collectData['collect']['php_inputs']['body']['form_search']['start'])) {
     $collectData['collect']['php_inputs']['body']['form_search']['start'] = (int)$collectData['collect']['php_inputs']['body']['form_search']['start'];
     $collectData['collect']['providers_params']['paging_params']['start'] = $collectData['collect']['php_inputs']['body']['form_search']['start'];
    }
   }
   if(isset($collectData['collect']['php_inputs']['body']['form_search']['limit'])) {
    if(is_numeric($collectData['collect']['php_inputs']['body']['form_search']['limit'])) {
     $collectData['collect']['php_inputs']['body']['form_search']['limit'] = (int)$collectData['collect']['php_inputs']['body']['form_search']['limit'];
     $collectData['collect']['providers_params']['paging_params']['limit'] = $collectData['collect']['php_inputs']['body']['form_search']['limit'];
    }
   }
  } catch(Exception $ex) {
   $this->error = true;
   $this->error_msg[] = "Canot get PHP Inputs with exception: {$ex->getMessage()}";
  }
  
  # Get Country Data
  if(!$this->error) {
   try {
    $collectData['collect']['country_data'] = $this->mod_providers->get_country_data();
   } catch(Exception $ex) {
    $this->error = true;
    $this->error_msg[] = "Cannot get country-data from model-providers with exception: {$ex->getMessage()}.";
   }
  }
  # Validate Param Inputs
  if(!$this->error) {
   $collectData['json_response']['params'] = [
    'paging' => $collectData['collect']['providers_params']['paging_params']
   ];
   if(isset($collectData['collect']['php_inputs']['body']['form_search']['names'])) {
    if(is_array($collectData['collect']['php_inputs']['body']['form_search']['names']) && !empty($collectData['collect']['php_inputs']['body']['form_search']['names'])) {
     $collectData['collect']['php_inputs']['body']['form_search']['names'] = array_values($collectData['collect']['php_inputs']['body']['form_search']['names']);
     if(isset($collectData['collect']['php_inputs']['body']['form_search']['orders']['by']) && is_string($collectData['collect']['php_inputs']['body']['form_search']['orders']['by'])) {
      $collectData['collect']['php_inputs']['body']['form_search']['orders']['by'] = strtolower($collectData['collect']['php_inputs']['body']['form_search']['orders']['by']);
      if(in_array($collectData['collect']['php_inputs']['body']['form_search']['orders']['by'], $collectData['collect']['providers_params']['order_sorts']['keys'])) {
       $collectData['collect']['providers_params']['order']['by'] = $collectData['collect']['php_inputs']['body']['form_search']['orders']['by'];
      }
     }
     if(isset($collectData['collect']['php_inputs']['body']['form_search']['orders']['sort']) && is_string($collectData['collect']['php_inputs']['body']['form_search']['orders']['sort'])) {
      $collectData['collect']['php_inputs']['body']['form_search']['orders']['sort'] = strtoupper($collectData['collect']['php_inputs']['body']['form_search']['orders']['sort']);
      if(in_array($collectData['collect']['php_inputs']['body']['form_search']['orders']['sort'], $collectData['collect']['providers_params']['order_sorts']['sorts'])) {
       $collectData['collect']['providers_params']['order']['sort'] = $collectData['collect']['php_inputs']['body']['form_search']['orders']['sort'];
      }
     }
    }
   }
   if(isset($collectData['collect']['php_inputs']['body']['form_search']['search_columns'])) {
    if(is_array($collectData['collect']['php_inputs']['body']['form_search']['search_columns']) && !empty($collectData['collect']['php_inputs']['body']['form_search']['search_columns'])) {
     $collectData['collect']['php_inputs']['body']['form_search']['search_columns'] = array_values($collectData['collect']['php_inputs']['body']['form_search']['search_columns']);
     if(is_string($collectData['collect']['php_inputs']['body']['form_search']['search_columns'][0])) {
      $collectData['collect']['php_inputs']['body']['form_search']['search_columns'][0] = strtolower($collectData['collect']['php_inputs']['body']['form_search']['search_columns'][0]);
      if(in_array($collectData['collect']['php_inputs']['body']['form_search']['search_columns'][0], $collectData['collect']['providers_params']['order_sorts']['values'])) {
       $collectData['collect']['providers_params']['search_params']['column'] = $collectData['collect']['php_inputs']['body']['form_search']['search_columns'][0];
      }
     }
    }
   }
   if(isset($collectData['collect']['php_inputs']['body']['form_search']['search_text'])) {
    if(is_string($collectData['collect']['php_inputs']['body']['form_search']['search_text'])) {
     $collectData['collect']['php_inputs']['body']['form_search']['search_text'] = trim($collectData['collect']['php_inputs']['body']['form_search']['search_text']);
     $collectData['collect']['providers_params']['search_params']['search_text'] .= $collectData['collect']['php_inputs']['body']['form_search']['search_text'];
    }
   }
  }
  
  if(!$this->error) {
   try {
    $collectData['collect']['providers_datas'] = [
     'counts' => $this->mod_providers->get_providers_counts(),
    ];
    if(!isset($collectData['collect']['providers_datas']['counts']->val_counts)) {
     $this->error = true;
     $this->error_msg[] = "Not have value counts as expected.";
    }
   } catch(Exception $ex) {
    $this->error = true;
    $this->error_msg[] = "Cannot count all prioviders data with exception: {$ex->getMessage()}.";
   }
  }
  
  if(!$this->error) {
   $collectData['json_response']['recordsTotal'] = (int)$collectData['collect']['providers_datas']['counts']->val_counts;
   if($collectData['json_response']['recordsTotal'] > 0) {
    try {
     $collectData['collect']['providers_datas']['datas'] = $this->mod_providers->get_providers_datas($collectData['collect']['providers_params']);
     if(isset($collectData['collect']['providers_datas']['datas']['count']->val_counts)) {
      $collectData['json_response']['recordsFiltered'] = (int)$collectData['collect']['providers_datas']['datas']['count']->val_counts;
     }
     if(isset($collectData['collect']['providers_datas']['datas']['data'])) {
      if(is_array($collectData['collect']['providers_datas']['datas']['data']) && !empty($collectData['collect']['providers_datas']['datas']['data'])) {
       $collectData['json_response']['data'] = $collectData['collect']['providers_datas']['datas']['data'];
      }
     }
    } catch(Exception $ex) {
     $this->error = true;
     $this->error_msg[] = "Cannot get datas [count and data] of providers with exception: {$ex->getMessage()}.";
    }
   }
  }
  
  
  if(!$this->error) {
   $collectData['json_response']['status'] = true;
  }
  
  
  $this->output->set_content_type('application/json');
  $this->output->set_output(json_encode($collectData['json_response']));
 }
 
 
 public function add(string $addType = 'form') {
  $collectData = [
   'page' => 'providers-add',
   'collect' => [
    'users' => [
     'localdata' => $this->localdata,
     'userdata' => $this->userdata,
    ],
   ],
   'error' => false,
   'title' => 'Stocks: Providers: Add | Dash',
  ];
  $collectData['collect']['dates'] = [
   'current' => $this->DateObject->format('Y-m-d H:i:s'),
  ];
  //-------------------------------
  $collectData['collect']['add_type'] = strtolower($addType);
  $collectData['collect']['type_allow'] = [
   'form',
   'action',
   'submit'
  ];
  if(!in_array($collectData['collect']['add_type'], $collectData['collect']['type_allow'])) {
   $this->error = true;
   $this->error_msg[] = sprintf("Add Type is not allowed, only allow: %s", implode(', ', $collectData['collect']['type_allow']));
  }
  if(!$this->error) {
   try {
    $collectData['collect']['country_data'] = $this->mod_providers->get_country_data();
    if(is_array($collectData['collect']['country_data']) && !empty($collectData['collect']['country_data'])) {
     $collectData['collect']['country_codes'] = array_column($collectData['collect']['country_data'], 'country_code');
    }
   } catch(Exception $ex) {
    $this->error = true;
    $this->error_msg[] = "Cannot get country-data from model-providers with exception: {$ex->getMessage()}.";
   }
  }
  if(!$this->error) {
   $this->form_validation->set_rules('country_code', 'Country Name or Country Code', 'required|max_length[16]|trim|xss_clean');
   if($this->form_validation->run() == FALSE) {
				$this->error = true;
				$this->error_msg[] = validation_errors();
			}
  }
  if(!$this->error) {
   $collectData['collect']['input_params'] = [
    'country_code' => $this->input->post('country_code'),
   ];
   if(!in_array($collectData['collect']['input_params']['country_code'], $collectData['collect']['country_codes'])) {
    $this->error = true;
    $this->error_msg[] = "Country code given not in allowed activated country yet.";
   }
  }
  if(!$this->error) {
   try {
    $collectData['collect']['county_params'] = $this->mod_providers->get_country_data($collectData['collect']['input_params']['country_code'], true);
    if(!is_array($collectData['collect']['county_params'])) {
     $this->error = true;
     $this->error_msg[] = "Get single country code should be in array cause results() from model-providers.";
    }
   } catch(Exception $ex) {
    $this->error = true;
    $this->error_msg[] = "Cannot get country-code given with exception: {$ex->getMesage()}.";
   }
  }
  if(!$this->error) {
   $collectData['collect']['county_input'] = $collectData['collect']['county_params'][0];
   if(!isset($collectData['collect']['county_input']->id) || !isset($collectData['collect']['county_input']->country_code)) {
    $this->error = true;
    $this->error_msg[] = "Should have expected country::id and country::code.";
   }
  }
  
  
  if(!$this->error) {
   $this->load->view('dash/providers/dash-providers/provider-modals/provider-add.php', $collectData);
  } else {
   $this->load->view('dash/providers/dash-providers/provider-modals/provider-errors.php', [
    'collect' => [
     'status' => false,
     'errors' => $this->error_msg,
    ],
   ]);
  }
 }

}