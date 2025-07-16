<?php
if(!defined('BASEPATH')) {
 exit('Cannot load script directly.');
}


?>
<!-- Page level plugins -->
<script type="text/javascript" src="<?= base_templates('vendor/datatables/jquery.dataTables.min.js');?>"></script>
<script type="text/javascript" src="<?= base_templates('vendor/datatables/dataTables.bootstrap4.min.js');?>"></script>

<!-- App Script -->
<script type="text/javascript">
const provider_params = {
 'dates': <?= json_encode($collect['dates']);?>,
 'users': {
  'localdata': <?= json_encode([
   'account_email' => $collect['users']['localdata']['account_email'],
   'seq' => $collect['users']['localdata']['seq']
  ]);?>,
  'userdata': {}
 },
 'country_data': <?= (isset($collect['country_data']) ? json_encode($collect['country_data']) : json_encode($collect));?>,
 'country_codes': <?= (isset($collect['country_codes']) ? json_encode($collect['country_codes']) : '[]');?>,
};
</script>
<script type="text/javascript" src="<?= site_url('dash/appscripts/js/scripts/dash/providers/dash-providers/provider-javascripts/index.js');?>"></script>