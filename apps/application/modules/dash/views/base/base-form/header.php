<?php
if(!defined('BASEPATH')) {
 exit('Cannot load script directly.');
}

$page = (isset($page) ? (is_string($page) ? strtolower($page) : 'login') : 'login');
$page = trim($page);

$base_path = 'dash/base';

switch($page) {
 case 'form-login':
 case 'login':
 default:
  $this->load->view('dash/base/base-form/login/01-header-start.php', [
   'page' => $page
  ]);
 break;
}
$this->load->view('dash/base/base-form/login/02-header-form.php');
