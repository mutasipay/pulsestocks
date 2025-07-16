<?php
if(!defined('BASEPATH')) {
 exit('No direct script access allowed: Appscripts');
}

class Js extends MY_Controller {
	private $error = FALSE, $error_msg = [];
	protected $DateObject;
	protected $base_dash;
	function __construct() {
		parent::__construct();
		$this->load->model('dash/account/Model_account', 'mod_account');
		$this->DateObject = $this->mod_account->get_dateobject();
	}
	
	
	public function scripts(string $js_module = 'dash', string $js_submodules = 'account', string $js_dirname = 'dash-account', string $js_pathname = 'account-javascripts', string $js_filename = 'login.js') {
  $collectData = [
			'page' => 'dash-appscripts',
			'title' => 'Dash Appscripts',
			'base_path' => 'dash',
			'collect'=> [],
			'js_module' => (!empty($js_module) ? strtolower($js_module) : 'dash'),
   'js_submodules' => (!empty($js_submodules) ? strtolower($js_submodules) : 'account'),
			// Input Url
			'js_dirname'					=> (is_string($js_dirname) ? strtolower($js_dirname) : 'dash-account'),
			'js_pathname'					=> (is_string($js_pathname) ? strtolower($js_pathname) : 'account-javascripts'),
			'js_filename'					=> (is_string($js_filename) ? strtolower($js_filename) : 'login.js'),
		];
		$collectData['js_module'] = str_replace('_', '-', $collectData['js_module']);
  $collectData['js_submodules'] = str_replace('_', '-', $collectData['js_submodules']);
		
		$collectData['js_dirname'] = str_replace('_', '-', $collectData['js_dirname']);
		$collectData['js_pathname'] = str_replace('_', '-', $collectData['js_pathname']);
		$collectData['js_filename'] = str_replace('_', '-', $collectData['js_filename']);
  $collectData['collect']['js_filename'] = trim($collectData['js_filename']);
		//
  // Make ALL Paths
  //
  $collectData['js_realpath'] = ($collectData['js_module'] . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $collectData['js_submodules']);
		$collectData['js_webpath'] = sprintf("/%s/%s/%s/%s",
   $collectData['js_submodules'],
			$collectData['js_dirname'],
			$collectData['js_pathname'],
			$collectData['collect']['js_filename']
		);
		$collectData['js_path'] = sprintf("%s%s%s", 
			(DIRECTORY_SEPARATOR . $collectData['js_dirname']),
			(DIRECTORY_SEPARATOR . $collectData['js_pathname']),
			(DIRECTORY_SEPARATOR . $collectData['collect']['js_filename'])
		);
		//=================================
		$collectData['collect']['js_file_path'] = sprintf("%s%s%s",
			(dirname(APPPATH . 'modules') . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR),
			$collectData['js_realpath'],
   $collectData['js_path']
		);
		$collectData['collect']['js_file_path_name'] = (dirname($collectData['collect']['js_file_path']) . DIRECTORY_SEPARATOR . $collectData['collect']['js_filename']);
  if (!file_exists($collectData['collect']['js_file_path_name'])) {
			$this->error = true;
			$this->error_msg[] = "Js file not exists.";
			$this->error_msg[] = "Location at: [{$collectData['collect']['js_file_path_name'] }]";
		} else {
   $collectData['collect']['js_web_path_name'] = ($collectData['js_module'] . $collectData['js_webpath']);
  }
  // Show As Javascript
  if(!$this->error) {
			$this->output->set_status_header(200);
			$this->output->set_content_type('text/javascript', 'utf-8');
			$this->output->set_header('Cache-Control: no-cache');
			$this->output->set_header('Pragma: no-cache');
			$this->load->view("{$collectData['collect']['js_web_path_name']}", $collectData);
		} else {
			$this->output->set_status_header(503);
			$this->output->set_content_type('application/json');
			$this->output->set_output(json_encode([
				'status'		=> FALSE,
				'errors'		=> $this->error_msg,
			]));
		}
 }
}