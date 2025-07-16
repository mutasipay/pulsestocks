<?php
if(!defined('BASEPATH')) {
 exit('Cannot load script directly.');
}

$base_path = 'dash/numbers';


$this->load->view('dash/base/header.php', [
 'page' => $page,
 'title' => $title,
 'data' => $collect
]);


switch($page) {
 case 'settings-users':
  $this->load->view('dash/settings/dash-settings/users.php');
 break;
 case 'settings-index':
 case 'settings':
 default:
  $this->load->view('dash/settings/dash-settings/index.php');
 break;
}


$this->load->view('dash/base/footer.php', [
 'page' => $page,
 'title' => $title,
]);


switch($page) {
 case 'settings-users':
  $this->load->view('dash/settings/dash-settings/users.php');
 break;
 case 'settings-index':
 case 'settings':
 default:
  $this->load->view('dash/base/base-footer/06-footer-blank.php');
 break;
}

$this->load->view('dash/base/base-footer/07-footer-end.php');