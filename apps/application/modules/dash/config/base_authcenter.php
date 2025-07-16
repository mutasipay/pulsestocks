<?php
if(!defined('BASEPATH')) {
 exit("Cannot load script directly.");
}

$config['base_authcenter'] = [
 'base_path' => 'dash',
];
// app-key and app-secret
$config['base_authcenter']['apps'] = [
 'key' => Instance_config::$env_apc['env']['authcenter.keys.' . Instance_config::$env_group['env_env'] . '.appname'],
 'secret' => Instance_config::$env_apc['env']['authcenter.keys.' . Instance_config::$env_group['env_env'] . '.appsecret'],
];
$config['base_authcenter']['api_endpoints'] = [];
foreach(['local', 'dev', 'sandbox', 'prod'] as $env) {
 $config['base_authcenter']['api_endpoints'][$env] = Instance_config::$env_apc['env']['authcenter.' . $env . '.host'];
}
$config['base_authcenter']['api_paths'] = [
 'login' => 'auth/login',
 'profiles' => [
  'email' => 'account/profile'
 ]
];