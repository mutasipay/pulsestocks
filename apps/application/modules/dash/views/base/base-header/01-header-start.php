<?php
if(!defined('BASEPATH')) {
 exit('Cannot load script directly.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="utf-8">
 <meta http-equiv="X-UA-Compatible" content="IE=edge">
 <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
 <meta name="description" content="">
 <meta name="author" content="">

 <title><?= (isset($title) ? $title : 'Dash');?></title>
 <link rel="stylesheet" href="<?= base_templates('vendor/fontawesome-free/css/all.min.css');?>" type="text/css" />
 <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet" />
 <!-- Custom styles for this template-->
 <link href="<?= base_templates('css/sb-admin-2.min.css');?>" rel="stylesheet" />
 <script type="text/javascript">
  const base_url = function(e) {
   const BASEURL = '<?= base_url('');?>';
   return BASEURL.toString().trim() + e.toString().trim();
  };
 </script>
</head>