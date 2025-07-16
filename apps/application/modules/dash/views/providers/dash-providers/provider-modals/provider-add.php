<?php
if(!defined('BASEPATH')) {
 exit('Cannot load script directly.');
}



?>
<div class="modal-dialog modal-lg" style="width:72%;" role="document"> 
 <div class="modal-content"> 
  <div class="modal-header"> 
   <h5 class="modal-title">New Providers</h5> 
   <button class="close" type="button" data-dismiss="modal" aria-label="Close"> 
    <span aria-hidden="true"><i class="fa fa-fw fa-times"></i></span> 
   </button> 
  </div> 
  <div class="modal-body"> 
   <form id="frm-add-providers-submit" action="javascript:void(0);" method="post">
    <div class="form-group">
     <label for="inp-country-name">Country Name</label>
     <select id="inp-country-name" class="form-control">
      <option value="" data-country-code="">- Country -</option>
      <?php
      if(!empty($collect['country_data'])) {
       foreach($collect['country_data'] as $c) {
        if($c->country_code == $collect['county_input']->country_code) {
         $is_selected = ' selected="selected"';
        } else {
         $is_selected = '';
        }
        ?><option value="<?= $c->country_name;?>"<?= $is_selected;?> data-country-code="<?= $c->country_code;?>"><?= $c->country_name;?></option><?php
       }
      }
      ?>
     </select>
    </div>
    <div class="form-group">
     <label for="inp-provider-name">Provider Name</label>
     <input id="inp-provider-name" class="form-control" name="inp_provider_name" />
    </div>
   </form>
  </div> 
  <div class="modal-footer"> 
   <button id="btn-add-providers-submit" class="btn btn-primary" type="button">
    <i class="fa fa-fw fa-plus"></i> Submit
   </button> 
   <button class="btn btn-secondary" type="button" data-dismiss="modal">
    <i class="fa fa-fw fa-times"></i> Cancel
   </button> 
  </div> 
 </div> 
</div>