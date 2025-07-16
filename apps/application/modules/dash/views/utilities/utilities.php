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
 case 'utilities-index':
 case 'utilities':
 default:
  $this->load->view('dash/utilities/dash-utilities/index.php');
 break;
}



$this->load->view('dash/base/footer.php', [
 'page' => $page,
 'title' => $title,
]);