<?php
if(!defined('BASEPATH')) {
 exit('Cannot load script directly.');
}

$sidebar_styles = [
 'nav' => 'nav-item',
 'li' => '',
 'a' => '',
];
$section = '';
switch($page) {
 case 'numbers-add':
 case 'numbers-index':
  $section = 'numbers';
  $sidebar_styles['nav'] = 'nav-item active';
 break;
 case 'settings-users':
 case 'settings-index':
 case 'settings':
  $section = 'settings';
  $sidebar_styles['nav'] = 'nav-item active';
 break;
 case 'utilities-index':
 case 'utilities':
  $section = 'utilities';
 break;
 # balances
 case 'balances-index':
  $section = 'utilities';
  $sidebar_styles['nav'] = 'nav-item active';
 break;
 # providers
 case 'providers-index':
  $section = 'utilities';
  $sidebar_styles['nav'] = 'nav-item active';
 break;
 # profiles
 case 'profile-profile':
 case 'profile-index':
 case 'profile':
  $section = 'profile';
  $sidebar_styles['nav'] = 'nav-item active';
 break;
 case 'dashboard-index':
 case 'dashboard':
  $section = 'dash';
  $sidebar_styles['nav'] = 'nav-item active';
 break;
 default:
  $section = 'dash';
  $sidebar_styles['nav'] = 'nav-item';
 break;
}

?>
<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
 <!-- Sidebar - Brand -->
 <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= base_url('dash/dashboard/dashboard/index');?>">
  <div class="sidebar-brand-icon rotate-n-15">
   <i class="fas fa-laugh-wink"></i>
  </div>
  <div class="sidebar-brand-text mx-3">
   Dashboard
  </div>
 </a>

 <!-- Divider -->
 <hr class="sidebar-divider my-0">
 <!-- Nav Item - Dashboard -->
 <li class="<?= (($section == 'dash') ? 'nav-item active' : 'nav-item');?>">
  <a class="<?= ((($section == 'dash') && ($page == 'dashboard-index')) ? 'nav-link active' : 'nav-link');?>" href="<?= base_url('dash/dashboard/about');?>">
   <i class="fas fa-fw fa-tachometer-alt"></i>
   <span>Dashboard</span>
  </a>
 </li>
 <!-- Nav Item - Profile -->
 <li class="<?= (($section == 'profile') ? 'nav-item active' : 'nav-item');?>">
  <a class="<?= ((($section == 'profile') && (in_array($page, ['profile-profile', 'profile-index']))) ? 'nav-link active' : 'nav-link');?>" href="<?= base_url('dash/profiles/profile');?>">
   <i class="fas fa-fw fa-user"></i>
   <span>Profile</span>
  </a>
 </li>
 <!-- Divider -->
 <hr class="sidebar-divider">

 <!-- Heading -->
 <div class="sidebar-heading">
  Interface
 </div>

 <!-- Nav Item - Pages Collapse Menu -->
 <li class="<?= (($section == 'settings') ? 'nav-item active' : 'nav-item');?>">
  <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseMenus" aria-expanded="true" aria-controls="collapseMenus">
   <i class="fas fa-fw fa-cog"></i>
   <span>Settings</span>
  </a>
  <div id="collapseMenus" class="<?= (($section == 'settings') ? 'collapse show' : 'collapse');?>" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
   <div class="bg-white py-2 collapse-inner rounded">
    <h6 class="collapse-header">Settings:</h6>
    <a class="<?= ((($section == 'settings') && ($page == 'settings-index')) ? 'collapse-item active' : 'collapse-item');?>" href="<?= base_url('dash/settings/settings/index');?>">
     Settings
    </a>
    <a class="<?= ((($section == 'settings') && ($page == 'settings-users')) ? 'collapse-item active' : 'collapse-item');?>" href="<?= base_url('dash/settings/users/index');?>">
     Users
    </a>
   </div>
  </div>
 </li>

 <!-- Nav Item - Utilities Collapse Menu -->
 <li class="<?= (($section == 'utilities') ? 'nav-item active' : 'nav-item');?>">
  <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities" aria-expanded="true" aria-controls="collapseUtilities">
   <i class="fas fa-fw fa-wrench"></i>
   <span>Utilities</span>
  </a>
  <div id="collapseUtilities" class="<?= (($section == 'utilities') ? 'collapse show' : 'collapse');?>" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
   <div class="bg-white py-2 collapse-inner rounded">
    <h6 class="collapse-header">Utilities:</h6>
    <a class="<?= ((($section == 'utilities') && ($page == 'utilities-index')) ? 'collapse-item active' : 'collapse-item');?>" href="<?= base_url('dash/utilities/utilities/index');?>">
     Index
    </a>
    <a class="<?= ((($section == 'utilities') && ($page == 'providers-index')) ? 'collapse-item active' : 'collapse-item');?>" href="<?= base_url('dash/stocks/providers/index');?>">
     Providers
    </a>
    <a class="<?= ((($section == 'utilities') && ($page == 'balances-index')) ? 'collapse-item active' : 'collapse-item');?>" href="<?= base_url('dash/stocks/balances/index');?>">
     Balances
    </a>
   </div>
  </div>
 </li>

 <!-- Divider -->
 <hr class="sidebar-divider">

 <!-- Heading -->
 <div class="sidebar-heading">
  Addons
 </div>

 <!-- Nav Item - Pages Collapse Menu -->
 <li class="<?= (($section == 'numbers') ? 'nav-item active' : 'nav-item');?>">
  <a class="nav-link" href="#" data-toggle="collapse" data-target="#collapsePages" aria-expanded="true" aria-controls="collapsePages">
   <i class="fas fa-fw fa-folder"></i>
   <span>Pages</span>
  </a>
  <div id="collapsePages" class="<?= (($section == 'numbers') ? 'collapse show' : 'collapse');?>" aria-labelledby="headingPages" data-parent="#accordionSidebar">
   <div class="bg-white py-2 collapse-inner rounded">
    <h6 class="collapse-header">Numbers:</h6>
    <a class="<?= ((($section == 'numbers') && ($page == 'numbers-index')) ? 'collapse-item active' : 'collapse-item');?>" href="<?= base_url('dash/stocks/numbers/index');?>">
     List Numbers
    </a>
    <a class="<?= ((($section == 'numbers') && ($page == 'numbers-add')) ? 'collapse-item active' : 'collapse-item');?>" href="<?= base_url('dash/stocks/numbers/add');?>">
     Add Numbers
    </a>
   </div>
  </div>
 </li>

 <!-- Nav Item - Servers -->
 <li class="<?= (($section == 'servers') ? 'nav-item active' : 'nav-item');?>">
  <a class="nav-link" href="charts.html">
   <i class="fas fa-fw fa-chart-area"></i>
   <span>Servers</span>
  </a>
 </li>

 <!-- Divider -->
 <hr class="sidebar-divider d-none d-md-block">

 <!-- Sidebar Toggler (Sidebar) -->
 <div class="text-center d-none d-md-inline">
  <button class="rounded-circle border-0" id="sidebarToggle"></button>
 </div>

</ul>
<!-- End of Sidebar -->