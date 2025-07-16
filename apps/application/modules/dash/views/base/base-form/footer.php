<?php
if(!defined('BASEPATH')) {
 exit('Cannot load script directly.');
}

$page = (isset($page) ? (is_string($page) ? strtolower($page) : 'login') : 'login');
$page = trim($page);

$base_path = 'dash/base';


$this->load->view('dash/base/base-form/login/04-footer-start.php');
switch($page) {
 case 'form-login':
 case 'login':
 default:
  $this->load->view('dash/base/base-form/login/05-footer-script.php', [
   'page' => $page
  ]);
 break;
}
