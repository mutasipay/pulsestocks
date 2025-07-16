<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Cache extends CI_Cache {
  private $CI;
  
  function __construct() {
    $this->CI = &get_instance();
    $this->CI->load->config('cache');
    
    $cache_driver = $this->CI->config->item('cache_driver');
    $cache_config = $this->CI->config->item($cache_driver);
    if($cache_config['adapter'] == 'file' && isset($cache_config['adapter'])) {
      $this->CI->config->set_item('cache_path', $cache_config['cache_path']);
    }

    parent::__construct($cache_config);
  }
}
