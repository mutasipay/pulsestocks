<?php
if(!defined('BASEPATH')) {
 exit('Cannot load script directly.');
}

$base_path = 'dash/profiles';


$this->load->view('dash/base/header.php', [
 'page' => $page,
 'title' => $title,
 'collect' => $collect,
]);


switch($page) {
 case 'profile-profile':
 case 'profile-index':
 case 'profile':
 default:
  $this->load->view('dash/profiles/dash-profile/profile.php');
 break;
}



$this->load->view('dash/base/footer.php', [
 'page' => $page,
 'title' => $title,
 'collect' => $collect,
]);


$this->load->view('dash/base/base-footer/06-footer-blank.php');



$this->load->view('dash/base/base-footer/07-footer-end.php');