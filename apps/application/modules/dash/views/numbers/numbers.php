<?php
if(!defined('BASEPATH')) {
 exit('Cannot load script directly.');
}

$base_path = 'dash/numbers';


$this->load->view('dash/base/header.php', [
 'page' => $page,
 'title' => $title,
]);


switch($page) {
 case 'numbers-add':
  $this->load->view('dash/numbers/dash-numbers/add.php');
 break;
 case 'numbers-index':
 default:
  $this->load->view('dash/numbers/dash-numbers/index.php');
 break;
}



$this->load->view('dash/base/footer.php', [
 'page' => $page,
 'title' => $title,
]);