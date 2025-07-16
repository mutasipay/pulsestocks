<?php
if(!defined('BASEPATH')) {
 exit('Cannot load script directly.');
}

$base_path = 'dash/account';


$this->load->view('dash/base/base-form/header.php', [
 'page' => $page,
 'title' => $title,
]);


switch($page) {
 case 'form-login':
 case 'login':
 default:
  $this->load->view('dash/account/dash-account/login.php');
 break;
}



$this->load->view('dash/base/base-form/footer.php', [
 'page' => $page,
 'title' => $title,
]);

switch($page) {
 case 'form-login':
 case 'login':
 default:
  $this->load->view('dash/account/dash-account/account-includes/login.php');
 break;
}


$this->load->view('dash/base/base-form/login/06-footer-end.php');
