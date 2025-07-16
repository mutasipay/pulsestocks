<?php
if(!defined('BASEPATH')) {
 exit('Cannot load script directly.');
}


if(!function_exists('base_templates')) {
 function base_templates($path = '') {
  $BASEURL = base_url(Instance_config::$dashboard_templates['rootpath'] . "/" . Instance_config::$dashboard_templates['basepath'] . "/" . Instance_config::$dashboard_templates['name']); // baseUrl or assetsURL [https://assets.domain.com]
  if(!empty($path)) {
   $BASEURL .= "/{$path}";
  }
  return $BASEURL;
 }
}
if(!function_exists('base_permalink')) {
	function base_permalink($url, $length = 128) {
		$url = strtolower($url);
		$url = preg_replace('/&.+?;/', '', $url);
		$url = preg_replace('/\s+/', '_', $url);
		$url = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '_', $url);
		$url = preg_replace('|%|', '_', $url);
		$url = preg_replace('/&#?[a-z0-9]+;/i', '', $url);
		$url = preg_replace('/[^%A-Za-z0-9 \_\-]/', '_', $url);
		$url = preg_replace('|_+|', '-', $url);
		$url = preg_replace('|-+|', '-', $url);
		$url = trim($url, '-');
		$url = (strlen($url) > $length) ? substr($url, 0, $length) : $url;
		return $url;
	}
}