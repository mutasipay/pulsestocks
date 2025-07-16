<?php
if (!defined('BASEPATH')) {
	exit('Cannot load script directly.');
	
}

class Model_numbers extends CI_Model {
 protected $error = FALSE, $error_msg = [];
	protected $DateObject;
	protected $base_dash;
	
	function __construct() {
		parent::__construct();
  $this->load->library('dash/base/Lib_configs', NULL, 'libconf');
		$this->base_dash = $this->libconf->get_base_dash();
		
		$this->DateObject = $this->libconf->get_dateobject();
  # Load Databases
		$this->db_app = $this->load->database(Instance_config::$env_group['env_env'], TRUE);
	}
 
 
}