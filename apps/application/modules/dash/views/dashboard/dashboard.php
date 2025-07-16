<?php
if(!defined('BASEPATH')) {
 exit('Cannot load script directly.');
}

$base_path = 'dash/numbers';


$this->load->view('dash/base/header.php', [
 'page' => $page,
 'title' => $title,
 'collect' => $collect,
]);


switch($page) {
 case 'dashboard-home':
  $this->load->view('dash/dashboard/dash-dashboard/home.php');
 break;
 case 'dashboard-about':
  $this->load->view('dash/dashboard/dash-dashboard/about.php');
 break;
 case 'dashboard-index':
 case 'dashboard':
 default:
  $this->load->view('dash/dashboard/dash-dashboard/index.php');
 break;
}



$this->load->view('dash/base/footer.php', [
 'page' => $page,
 'title' => $title,
 'collect' => $collect,
]);


$this->load->view('dash/base/base-footer/06-footer-blank.php');



$this->load->view('dash/base/base-footer/07-footer-end.php');