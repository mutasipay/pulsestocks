<?php
if(!defined('BASEPATH')) {
	exit('No direct script access allowed');
}
class MY_Session extends CI_Session {
	function __construct(array $params = []) {
		// No sessions under CLI
		if (is_cli()) {
			log_message('debug', 'Session: Initialization under CLI aborted.');
			return;
		} elseif ((bool)ini_get('session.auto_start')) {
			log_message('error', 'Session: session.auto_start is enabled in php.ini. Aborting.');
			return;
		} elseif (!empty($params['driver'])) {
			$this->_driver = $params['driver'];
			unset($params['driver']);
		}
		// Note: Using from config
		elseif ($driver = config_item('sess_driver')) {
			$this->_driver = $driver;
		}
		// Note: BC workaround
		elseif (config_item('sess_use_database'))
		{
			log_message('debug', 'Session: "sess_driver" is empty; using BC fallback to "sess_use_database".');
			$this->_driver = 'database';
		} 

		$class = $this->_ci_load_classes($this->_driver);
		// Configuration ...
		$this->_configure($params);
		$this->_config['_sid_regexp'] = $this->_sid_regexp;

		$class   = new $class($this->_config);
		$wrapper = new CI_SessionWrapper($class);
		if (is_php('5.4')) {
			session_set_save_handler($wrapper, TRUE);
		} else {
			session_set_save_handler(
				array($wrapper, 'open'),
				array($wrapper, 'close'),
				array($wrapper, 'read'),
				array($wrapper, 'write'),
				array($wrapper, 'destroy'),
				array($wrapper, 'gc')
			);
			register_shutdown_function('session_write_close');
		}
		
		// Sanitize the cookie, because apparently PHP doesn't do that for userspace handlers
		if (isset($_COOKIE[$this->_config['cookie_name']])
			&& (
				! is_string($_COOKIE[$this->_config['cookie_name']])
				OR ! preg_match('#\A'.$this->_sid_regexp.'\z#', $_COOKIE[$this->_config['cookie_name']])
			)
		) {
			unset($_COOKIE[$this->_config['cookie_name']]);
		}
		/*
		session_start([
			'read_and_close' => true,
		]);
		*/
		session_start();
		
		// Is session ID auto-regeneration configured? (ignoring ajax requests)
		if ((empty($_SERVER['HTTP_X_REQUESTED_WITH']) OR strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest')
			&& ($regenerate_time = config_item('sess_time_to_update')) > 0
		) {
			if ( ! isset($_SESSION['__ci_last_regenerate']))
			{
				$_SESSION['__ci_last_regenerate'] = time();
			}
			elseif ($_SESSION['__ci_last_regenerate'] < (time() - $regenerate_time))
			{
				$this->sess_regenerate((bool) config_item('sess_regenerate_destroy'));
			}
		}
		// Another work-around ... PHP doesn't seem to send the session cookie
		// unless it is being currently created or regenerated
		elseif (isset($_COOKIE[$this->_config['cookie_name']]) && $_COOKIE[$this->_config['cookie_name']] === session_id())
		{
			$expires = empty($this->_config['cookie_lifetime']) ? 0 : time() + $this->_config['cookie_lifetime'];
			if (is_php('7.3')) {
				setcookie(
					$this->_config['cookie_name'],
					session_id(),
					array(
						'expires' => $expires,
						'path' => $this->_config['cookie_path'],
						'domain' => $this->_config['cookie_domain'],
						'secure' => $this->_config['cookie_secure'],
						'httponly' => TRUE,
						'samesite' => $this->_config['cookie_samesite']
					)
				);
			} else {
				$header = 'Set-Cookie: '.$this->_config['cookie_name'].'='.session_id();
				$header .= empty($expires) ? '' : '; Expires='.gmdate('D, d-M-Y H:i:s T', $expires).'; Max-Age='.$this->_config['cookie_lifetime'];
				$header .= '; Path='.$this->_config['cookie_path'];
				$header .= ($this->_config['cookie_domain'] !== '' ? '; Domain='.$this->_config['cookie_domain'] : '');
				$header .= ($this->_config['cookie_secure'] ? '; Secure' : '').'; HttpOnly; SameSite='.$this->_config['cookie_samesite'];
				header($header);
			}

			if ( ! $this->_config['cookie_secure'] && $this->_config['cookie_samesite'] === 'None') {
				log_message('error', "Session: '".$this->_config['cookie_name']."' cookie sent with SameSite=None, but without Secure attribute.'");
			}
		}

		$this->_ci_init_vars();
		log_message('info', "Session: Class initialized using '" . $this->_driver . "' driver.");
		
		session_write_close();
	}
	
	public function userdata_reopen() {
		///ini_set('session.use_only_cookies', true);
		///ini_set('session.use_cookies', true);
		// May be necessary in some situations
		# ini_set('session.use_trans_sid', false);
		///ini_set('session.cache_limiter', null);
		if (session_status() === PHP_SESSION_NONE) {
			ini_set('session.use_only_cookies', true);
			ini_set('session.use_cookies', true);
			ini_set('session.cache_limiter', null);
			
			session_start();
		}
	}
	public function userdata_reclose() {
		session_write_close();
	}
	public function set_userdata($data, $value = NULL) {
		$this->userdata_reopen();
		parent::set_userdata($data, $value);
		$this->userdata_reclose();
	}
	public function unset_userdata($key) {
		$this->userdata_reopen();
		parent::unset_userdata($key);
		$this->userdata_reclose();
	}
	
	
	public function set_flashdata($data, $value = NULL) {
		$this->userdata_reopen();
		parent::set_userdata($data, $value);
		parent::mark_as_flash(is_array($data) ? array_keys($data) : $data);
		$this->userdata_reclose();
	}
	public function unset_flashdata($key = NULL) {
		return $this->unset_userdata($key);
	}
	
	public function sess_destroy() {
		$this->userdata_reopen();
		parent::sess_destroy();
		$this->userdata_reclose();
	}
}


