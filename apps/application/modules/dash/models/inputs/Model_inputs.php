<?php
if(!defined('BASEPATH')) {
 exit('Cannot load script directly to models.');
}
class Model_inputs extends CI_Model {
	function __construct() {
		parent::__construct();
  # PHP Inputs
  $this->load->library('dash/inputs/Lib_phpinput', null, 'phpinput');
	}
 
 public function get_php_input_requests() {
		return $this->phpinput->get_php_input_request();
	}
}