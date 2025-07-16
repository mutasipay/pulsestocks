<?php
if(!defined('BASEPATH')) {
 exit('Cannot load script directly.');
}
$page = (isset($page) ? $page : '');
$base_path = 'dash/providers';


$this->load->view('dash/base/header.php', [
 'page' => $page,
 'title' => $title,
 'collect' => $collect,
]);


switch($page) {
 case 'providers-index':
 case 'providers':
 default:
  $this->load->view('dash/providers/dash-providers/index.php');
 break;
}



$this->load->view('dash/base/footer.php', [
 'page' => $page,
 'title' => $title,
 'collect' => $collect,
]);


switch($page) {
 case 'providers-index':
 case 'providers':
 default:
  $this->load->view('dash/providers/dash-providers/provider-includes/provider-index.php');
 break;
}
$this->load->view('dash/base/base-footer/06-footer-blank.php');



$this->load->view('dash/base/base-footer/07-footer-end.php');