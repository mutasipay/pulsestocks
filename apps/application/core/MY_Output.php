<?php
if (!defined('BASEPATH')) {
	exit('Cannot load script directly.');
}
class MY_Output extends CI_Output {
	
	public function _display($output = '') {
		$CI =& get_instance();
		
		if ($output === '') {
			$output = $this->final_output;
		}
		
		// Run checks here (on the Input class, likely) to see if the
        // response expects application/json, text/html, etc.
		/*
        $output = $CI->load->view('includes/header', array(
            'foo1' => 'bar1',
            'foo2' => 'bar2'
            ), TRUE) . $output;

        $output .= $CI->load->view('includes/footer', NULL, TRUE);
		*/
		
		/*
		$output .= "<!--\r\n";
		$output .= json_encode($cookie_headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		
		$output .= "\r\n-->";
		*/
		/*
		$cookie_headers = [
			'cookie'		=> $this->get_header('Set-Cookie'),
			'output'		=> $this->get_cookies_from_output_headers(),
			'content_type'	=> $this->get_header('Content-Type'),
		];
		log_message('error', json_encode($cookie_headers, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
		*/
		parent::_display($output);
    }
	
	public function get_cookies_from_output_headers() {
		$headers =  array_unique(headers_list());
		$cookie_strings = array();
		$contenttype_strings = [];
		foreach($headers as $header) {
			if (preg_match('/^Set-Cookie: (.+)/', $header, $matches_cookies)) {
				$cookie_strings[] = $matches_cookies[1];
			}
			if (preg_match('/^Content-Type: (.+)/', $header, $matches_contenttypes)) {
				$contenttype_strings[] = $matches_contenttypes[1];
			}
		}
		$response = [
			'headers'				=> $headers,
			'cookie_strings'		=> $cookie_strings,
			'contenttype_strings'	=> $contenttype_strings,
		];
		if (!empty($response['contenttype_strings'])) {
			foreach ($response['contenttype_strings'] as $cts) {
				$response['content_type'] = $cts;
			}
		}
		return $response;
		/*
		header_remove('Set-Cookie');
		# Add Set-Cookie Headers
		foreach($cookie_strings as $cookie){
			 header("Set-Cookie: {$cookie}", false);
		}
		*/
	}
}