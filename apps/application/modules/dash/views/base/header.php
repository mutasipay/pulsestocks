<?php
if(!defined('BASEPATH')) {
 exit('Cannot load script directly.');
}

$page = (isset($page) ? (is_string($page) ? strtolower($page) : 'base') : 'base');
$page = trim($page);

$base_path = 'dash/base';

$this->load->view('dash/base/base-header/01-header-start.php');
$this->load->view('dash/base/base-header/02-header-begin.php');


$this->load->view('dash/base/base-header/03-header-sidebar.php');



$this->load->view('dash/base/base-header/04-header-wrapper.php');

$this->load->view('dash/base/base-header/05-header-topbar.php');

