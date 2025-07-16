<?php
if (!defined('BASEPATH')) {
	exit('Cannot open script directly');
}
class Lib_phpinput {
	public $php_input_request = [];
	function __construct() {
		$pir = self::apache_php_input_request();
		$this->set_php_input_request($pir);
	}
	private function set_php_input_request($php_input) {
		$this->php_input_request = $php_input;
	}
	public function get_php_input_request() {
		return $this->php_input_request;
	}
	private static function apache_headers() {
		try {
			if (function_exists('apache_request_headers')) {
				$headers = apache_request_headers();
				$out = array();
				foreach ($headers AS $key => $value) {
					$key = str_replace(" ", "-", ucwords(strtolower(str_replace("-", " ", $key))));
					$out[$key] = $value;
				}
				if	(isset($_SERVER['CONTENT_TYPE'])) {
					$out['Content-Type'] = $_SERVER['CONTENT_TYPE'];
				}
				if (isset($_ENV['CONTENT_TYPE'])) {
					$out['Content-Type'] = $_ENV['CONTENT_TYPE'];
				}
			} else {
				$out = array();
				if	(isset($_SERVER['CONTENT_TYPE'])) {
					$out['Content-Type'] = $_SERVER['CONTENT_TYPE'];
				}
				if (isset($_ENV['CONTENT_TYPE'])) {
					$out['Content-Type'] = $_ENV['CONTENT_TYPE'];
				}
				if (isset($_SERVER)) {
					if (count($_SERVER) > 0) {
						foreach ($_SERVER as $key => $value) {
							if (substr($key, 0, 5) == "HTTP_") {
								$key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
								$out[$key] = $value;
							}
						}
					}
				}
			}
			return $out;
		} catch (Exception $e) {
			throw $e;
		}
	}
	public static function parse_raw_http_request($content_type) {
		$input = file_get_contents('php://input');
		preg_match('/boundary=(.*)$/', $content_type, $bound_matches);
		$boundary = (isset($bound_matches[1]) ? $bound_matches[1] : '');
		$a_blocks = preg_split("/-+{$boundary}/", $input);
		array_pop($a_blocks);
		$a_data = array();
		// loop data blocks
		$i = 0;
		foreach ($a_blocks as $id => $block) {
			if (empty($block)) {
				continue;
			}
			// you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char
			// parse uploaded files
			if (strpos($block, 'application/octet-stream') !== FALSE) {
				// match "name", then everything after "stream" (optional) except for prepending newlines 
				preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
			} else {
				// match "name" and optional value in between newline sequences
				preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
			}
			if (isset($matches[1]) && isset($matches[2])) {
				$a_data[$matches[1]] = $matches[2];
			}
			$i += 1;
		}
		return $a_data;
	}
	private static function apache_php_input_request() {
		###############################
		# Request Input
		###############################
		try {
			$RequestInputParams = array();
			$RequestInput = array();
			$incomingHeaders = self::apache_headers();
			if (isset($incomingHeaders['Content-Type'])) {
				if ((!is_array($incomingHeaders['Content-Type'])) && (!is_object($incomingHeaders['Content-Type']))) {
					$incomingHeaders['Content-Type'] = strtolower($incomingHeaders['Content-Type']);
					if (strpos($incomingHeaders['Content-Type'], 'application/json') !== FALSE) {
						$RequestInput = file_get_contents("php://input");
						if (!$RequestInputJson = json_decode($RequestInput, true)) {
							parse_str($RequestInput, $RequestInputParams);
						} else {
							$RequestInputParams = $RequestInputJson;
						}
					} else if (strpos($incomingHeaders['Content-Type'], 'application/x-www-form-urlencoded') !== FALSE) {
						$RequestInput = file_get_contents("php://input");
						parse_str($RequestInput, $RequestInputParams);
					} else if (strpos($incomingHeaders['Content-Type'], 'application/xml') !== FALSE) {
						$RequestInput = file_get_contents("php://input");
						$RequestInputParams = $RequestInput;
					} else if (strpos($incomingHeaders['Content-Type'], 'multipart/form-data') !== FALSE) {
						$RequestInput = self::parse_raw_http_request($incomingHeaders['Content-Type']);
						$RequestInputParams = $RequestInput;
						if ($_SERVER['REQUEST_METHOD'] == 'POST') {
							if (isset($_POST) && (count($_POST) > 0)) {
								foreach ($_POST as $k => $v) {
									$RequestInputParams = array_merge(array($k => $v), $RequestInputParams);
								}
							}
							if (isset($_FILES) && (count($_FILES) > 0)) {
								foreach ($_FILES as $k => $v) {
									$RequestInputParams = array_merge(array($k => $v), $RequestInputParams);
								}
							}
						}
					} else {
						$RequestInput['kontol'] = 'latos';
						self::parse_raw_http_request($incomingHeaders['Content-Type'], $RequestInput);
						$RequestInputParams = $RequestInput;
						if ($_SERVER['REQUEST_METHOD'] == 'POST') {
							if (isset($_POST) && (count($_POST) > 0)) {
								foreach ($_POST as $k => $v) {
									$RequestInputParams = array_merge(array($k => $v), $RequestInputParams);
								}
							}
							if (isset($_FILES) && (count($_FILES) > 0)) {
								foreach ($_FILES as $k => $v) {
									$RequestInputParams = array_merge(array($k => $v), $RequestInputParams);
								}
							}
						}
					}
				}
			} else {
				$RequestInput = file_get_contents("php://input");
				parse_str($RequestInput, $RequestInputParams);
			}
			$params['input'] = $RequestInput;
			$params['header'] = $incomingHeaders;
			$params['body'] = $RequestInputParams;
			return $params;
		} catch (Exception $e) {
			throw $e;
		}
	}
}
