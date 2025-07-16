<?php
if (!defined('BASEPATH')) {
	exit("Cannot load script directly.");
}
$config['base_dash'] = [
	'base_path'			=> 'dash',
];
$config['base_dash']['is_ip_checked'] = true;
$config['base_dash']['allow_login_multiple_devices'] = true;
$config['base_dash']['logged_in_redis_cache'] = 7200;
$config['base_dash']['logged_cache'] = [
	'social_seq' => sprintf("social-account-login-seq:%s", Instance_config::$env_apc['env']['authcenter.keys.' . Instance_config::$env_group['env_env'] . '.appname']),
	'social_data' => sprintf("social-account-login-data:%s", Instance_config::$env_apc['env']['authcenter.keys.' . Instance_config::$env_group['env_env'] . '.appname']),
	'userdata' => sprintf("userdata:%s", Instance_config::$env_apc['env']['authcenter.keys.' . Instance_config::$env_group['env_env'] . '.appname']),
];
$config['base_dash']['logged_cache']['accountdata_prop'] = 'accountdata:properties';





$config['base_dash']['tables'] = [
 'members' => 'stock_s00users_members',
 'drafted' => [
  'stock' => 'stock_s40items_drafted',
 ],
 'stocks' => [
  'index' => [
   'providers' => 's00*',
   'users' => 's0*',
   'sims' => 's1*',
   'stocks' => 's2*',
   'items' => 'S4*',
   'logs' => 's5*',
  ],
 ],
];
$config['base_dash']['tables']['stocks']['providers'] = [
 'country' => 'stock_s000providers_country',
 'provider' => 'stock_s001providers_provider',
];
$config['base_dash']['tables']['stocks']['users'] = [
 'members' => 'stock_s00users_members',
 'member' => 'stock_s00users_members',
 'roles' => 'stock_s01user_roles',
 'privileges' => 'stock_s01user_privileges',
];
$config['base_dash']['tables']['stocks']['sims'] = [
 'sim' => 'stock_s10sims_sim',
];
$config['base_dash']['tables']['stocks']['stocks'] = [
 'stock' => 'stock_s20stocks_stock',
];
$config['base_dash']['tables']['stocks']['drafts'] = [
 'items' => 'stock_s40items_drafted',
];

$config['base_dash']['tables']['stocks']['logs'] = [
 'drafted' => 'stock_s50logs_drafted',
 'members' => 'stock_s55logs_member',
];


$config['base_dash']['tables']['accounts'] = [
 'dashboard_account' => 'dashboard_account',
 'dashboard_account_roles' => 'dashboard_account_roles',
 'dashboard_account_social' => 'dashboard_account_social',
 'dashboard_account_social_log' => 'dashboard_account_social_log',
 'dashboard_account_ip_address' => 'dashboard_account_ip_address',
];

