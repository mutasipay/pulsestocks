<?php
if(!defined('BASEPATH')) {
 exit('Cannot load script directly.');
}
?>
<script type="text/javascript">
const login_params = {
 'dates': <?= json_encode($collect['dates']);?>,
 'server': '<?= $collect['login_server'];?>',
};
</script>
<!-- App Script -->
<script type="text/javascript" src="<?= site_url('dash/appscripts/js/scripts/dash/account/dash-account/account-javascripts/login.js');?>"></script>