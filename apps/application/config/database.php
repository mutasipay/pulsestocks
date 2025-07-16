<?php
defined('BASEPATH') OR exit('No direct script access allowed');


$active_group = 'account';
$query_builder = TRUE;

foreach (['local', 'dev', 'sandbox', 'prod'] as $db_env) {
	$db[$db_env] = [
  'hostname' => Instance_config::$env_apc['env']['databases.myarena.payment.mysql.' . $db_env . '.host'],
  'port' => Instance_config::$env_apc['env']['databases.myarena.payment.mysql.' . $db_env . '.port'],
  'username' => Instance_config::$env_apc['env']['databases.myarena.payment.mysql.' . $db_env . '.user'],
  'password' => Instance_config::$env_apc['env']['databases.myarena.payment.mysql.' . $db_env . '.pass'],
  'database' => Instance_config::$env_apc['env']['databases.myarena.payment.mysql.' . $db_env . '.name'],
  'dbdriver' => 'pdo',
  'dbprefix' => '',
  'pconnect' => FALSE,
  'db_debug' => (ENVIRONMENT !== 'production'),
  'cache_on' => FALSE,
  'cachedir' => '',
  'char_set' => 'utf8mb4',
  'dbcollat' => 'utf8mb4_general_ci',
  'swap_pre' => '',
  'encrypt' => FALSE,
  'compress' => FALSE,
  //'stricton' => FALSE,
  'failover' => array(),
  'save_queries' => TRUE
 ];
	$db[$db_env]['dsn'] = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s',
		$db[$db_env]['hostname'],
		$db[$db_env]['port'],
		$db[$db_env]['database'],
		$db[$db_env]['char_set']
	);
}
foreach(['stock', 'account'] as $dbapp) {
 $db[$dbapp] = [
  'hostname' => Instance_config::$dashboard_databases[$dbapp]['hostname'],
  'port' => Instance_config::$dashboard_databases[$dbapp]['port'],
  'username' => Instance_config::$dashboard_databases[$dbapp]['username'],
  'password' => Instance_config::$dashboard_databases[$dbapp]['password'],
  'database' => Instance_config::$dashboard_databases[$dbapp]['database'],
  'dbdriver' => 'pdo',
  'dbprefix' => '',
  'pconnect' => FALSE,
  'db_debug' => (ENVIRONMENT !== 'production'),
  'cache_on' => FALSE,
  'cachedir' => '',
  'char_set' => 'utf8mb4',
  'dbcollat' => 'utf8mb4_general_ci',
  'swap_pre' => '',
  'encrypt' => FALSE,
  'compress' => FALSE,
  //'stricton' => FALSE,
  'failover' => array(),
  'save_queries' => FALSE,
 ];
 $db[$dbapp]['dsn'] = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s',
  $db[$dbapp]['hostname'],
  $db[$dbapp]['port'],
  $db[$dbapp]['database'],
  $db[$dbapp]['char_set']
 );
}
