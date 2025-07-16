<?php
if(!defined('BASEPATH')) {
 exit("Cannot load script directly.");
}
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
class Lib_token2fa {
 protected $CI;
 protected $base_signature;
 private $DateObject;
 function __construct() {
  $this->CI = &get_instance();
  $this->CI->load->config('dash/base_signature');
  $this->base_signature = $this->CI->config->item('base_signature');
  $this->DateObject = \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'), new DateTimeZone(Instance_config::$timezone));
 }
 
 
 public function generate_google2fa_qrcode(array $generate_params): string {
		try {
			$google2fa = new Google2FA();
			$qrcode_text = $google2fa->getQRCodeUrl($generate_params[0], $generate_params[1], $generate_params[2]);
			return $qrcode_text;
		} catch (Exception $e) {
			throw $e;
		}
	}
}


