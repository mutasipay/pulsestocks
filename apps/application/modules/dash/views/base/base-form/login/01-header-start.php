<?php
if(!defined('BASEPATH')) {
 exit('Cannot load script directly.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta http-equiv="X-UA-Compatible" content="IE=edge" />
 <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
 <meta name="description" content="Login to Stocks System" />
 <meta name="author" content="SB Admin - 2" />
 <title><?= (isset($title) ? $title : 'Login to Stocks System');?></title>
 <!-- Custom fonts for this template-->
 <link href="<?= base_templates('vendor/fontawesome-free/css/all.min.css');?>" rel="stylesheet" type="text/css" />
 <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet" />

 <!-- Custom styles for this template-->
 <link type="text/css" href="<?= base_templates('css/sb-admin-2.min.css');?>" rel="stylesheet" />
 
 <script type="text/javascript">
  const base_url = function(e) {
   const BASEURL = '<?= base_url('');?>';
   return BASEURL.toString().trim() + e.toString().trim();
  };
 </script>
 <!-- md5string -->
 <script type="text/javascript" src="<?= site_url('dash/appscripts/js/scripts/dash/base/base-javascripts/account/login-md5.js');?>"></script>
 <!-- sha256crypt -->
 <script type="text/javascript" src="<?= site_url('dash/appscripts/js/scripts/dash/base/base-javascripts/account/login-sha256.js');?>"></script>
</head>