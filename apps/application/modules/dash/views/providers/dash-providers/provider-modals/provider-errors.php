<?php
if(!defined('BASEPATH')) {
 exit('Cannot load script directly.');
}



?>
<div class="modal-dialog modal-lg" style="width:72%;" role="document"> 
 <div class="modal-content"> 
  <div class="modal-header"> 
   <h5 class="modal-title">Error OCcured</h5> 
   <button class="close" type="button" data-dismiss="modal" aria-label="Close"> 
    <span aria-hidden="true"><i class="fa fa-fw fa-times"></i></span> 
   </button> 
  </div> 
  <div class="modal-body">
   <?php
   if(isset($collect['errors'])) {
    ?>
    <ul class="list-group text-danger">
     <?php
     foreach($collect['errors'] as $err_msg) {
      if(!is_string($err_msg)) {
       $err_msg = json_encode($err_msg);
      }
      ?><li class="list-group-item"><?= $err_msg;?></li><?php
     }
     ?>
    </ul>
    <?php
   }
   ?>
  </div> 
  <div class="modal-footer"> 
   <button class="btn btn-secondary" type="button" data-dismiss="modal">
    <i class="fa fa-fw fa-times"></i> Cancel
   </button> 
  </div> 
 </div> 
</div>