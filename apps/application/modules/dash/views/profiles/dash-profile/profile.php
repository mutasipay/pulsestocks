<?php
if(!defined('BASEPATH')) {
 exit('Cannot load script directly.');
}
?>
<style type="text/css">
 .table-wrapped{
  table-layout: fixed;
 }
 .table-wrapped > tr > td {
  word-wrap:break-word;
  max-width:100%;
 }
 .table-wrapped > tr > th.tdmaxwidth {
  max-width:16px;
 }
 .table-wrapped > tr > td.tdmaxwidth {
  max-width:16px;
 }
</style>
<!-- Begin Page Content -->
<div class="container-fluid">
 <!-- Page Heading -->
 <h1 class="h3 mb-4 text-gray-800">Profiles</h1>
 <div class="row">
  <div class="col-lg-12 col-md-12">
   <div class="card shadow mb-0">
    <div class="card-header py-2">
     <h5 class="m-0 font-weight-bold text-primary">Profiles</h5>
    </div>
    <div class="card-body">
     <div class="row">
      <div class="col-md-4">
       <div class="table-responsive">
        <table id="tbl-profiles-profile" class="table table-stripped table-bordered table-condensed table-wrapped">
         <thead>
          <tr>
           <th class="text-center tdmaxwidth">#</th>
           <th class="text-center">Prop.</th>
           <th class="text-center">Value</th>
          </tr>
         </thead>
         <tbody>
          <?php
          if(isset($collect['users']['localdata'])) {
           foreach($collect['users']['localdata'] as $k => $v) {
            ?>
            <tr>
             <td class="tdmaxwidth"></td>
             <td><?= $k;?></td>
             <td class="tdwrapped"><?= (is_string($v) ? $v : json_encode($v));?></td>
            </tr>
            <?php
           }
          }
          ?>
         </tbody>
        </table>
       </div>
      </div>
      <div class="col-md-8">
      
      </div>
     </div>
    </div>
   </div>
  </div>
 </div>
 
</div>
<!-- /.container-fluid -->