<?php
if(!defined('BASEPATH')) {
 exit('Cannot load config file directly.');
 
}
$config['cache_driver'] = 'redis';
$config['redis'] = Instance_config::$dashboard_caches['redis'];

