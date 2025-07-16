<?php
if(!defined('BASEPATH')) {
 exit('Cannot load script directly.');
}
$page = (isset($page) ? $page : '');
$base_path = 'dash/balances';


$this->load->view('dash/base/header.php', [
 'page' => $page,
 'title' => $title,
 'collect' => $collect,
]);


switch($page) {
 case 'balances-index':
 case 'balances':
 default:
  $this->load->view('dash/balances/dash-balances/index.php');
 break;
}



$this->load->view('dash/base/footer.php', [
 'page' => $page,
 'title' => $title,
 'collect' => $collect,
]);


$this->load->view('dash/base/base-footer/06-footer-blank.php');



$this->load->view('dash/base/base-footer/07-footer-end.php');