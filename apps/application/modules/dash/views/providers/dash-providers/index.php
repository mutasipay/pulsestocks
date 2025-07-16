<?php
if(!defined('BASEPATH')) {
 exit('Cannot load script directly.');
}
?>
<!-- Begin Page Content -->
<div class="container-fluid">
 <!-- Page Heading -->
 <h1 class="h3 mb-4 text-gray-800">Providers</h1>
 <div class="row">
  <div class="col-md-4">
   <div class="card card-blue card-primary">
    <div class="card-header">
     <h5 class="font-weight-bold text-primary">Filter Forms</h5>
    </div>
    <div class="card-body">
     <form id="frm-providers-search-params" method="post" action="javascript:void(0);">
      <div class="form-row">
       <div class="col-md-6">
        <div class="form-group">
         <select id="opt-country-code" class="form-control">
          <option value="" selected="selected">- Country -</option>
          <?php
          if(!empty($collect['country_data'])) {
           foreach($collect['country_data'] as $c) {
            ?><option value="<?= $c->country_code;?>"><?= $c->country_name;?></option><?php
           }
          }
          ?>
         </select>
        </div>
       </div>
       <div class="col-md-6">
        <div class="form-group">
         <button type="button" id="btn-country-code" class="btn btn-md btn-primary">
          <i class="fa fa-fw fa-search"></i> Search
         </button>
        </div>
       </div>
      </div>
     </form>
    </div>
   </div>
  </div>
  <div class="col-md-4">
   <div class="card card-blue card-primary">
    <div class="card-header">
     <h5 class="font-weight-bold text-primary">Add Forms</h5>
    </div>
    <div class="card-body">
     <form id="frm-add-providers" method="post" action="javascript:void(0);">
      <div class="form-row">
       <div class="col-md-6">
        <div class="form-group">
         <select class="form-control" id="add-providers-country-code">
          <option value="" selected="selected">- Country -</option>
          <?php
          if(!empty($collect['country_data'])) {
           foreach($collect['country_data'] as $c) {
            ?><option value="<?= $c->country_code;?>"><?= $c->country_name;?></option><?php
           }
          }
          ?>
         </select>
        </div>
       </div>
       <div class="col-md-6">
        <div class="form-group">
         <button type="button" id="btn-add-providers" class="btn btn-md btn-primary">
          <i class="fa fa-fw fa-plus"></i> Add
         </button>
        </div>
       </div>
       
       <div class="col-md-12">
        <ul id="error-providers-add" class="list-group text-danger"></ul>
       </div>
      </div>
     </form>
    </div>
   </div>
  </div>
 </div>
 <div class="row">
  <div class="col-lg-12 col-md-12">
   <div class="card card-blue card-primary">
    <div class="card-header">
     <h5 class="font-weight-bold text-primary">Providers</h5>
    </div>
    <div class="card-body">
     <div class="row">
      <div class="col-lg-10 col-md-10">
       <div class="table-responsive">
        <table id="tbl-provider-data" class="table table-bordered table-stripped table-condensed">
         <thead>
          <tr>
           <th>#</th>
           <th>Country</th>
           <th>Code</th>
           <th>Name</th>
           <th>Activated</th>
           <th class="text-center">Actions</th>
          </tr>
         </thead>
         <tbody></tbody>
        </table>
       </div>
      </div>
     </div>
    </div>
   </div>
  </div>
 </div>
 
</div>
<!-- /.container-fluid -->