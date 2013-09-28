<?php
/**
 * PHPYar is a RPC request library write in php
 * influenced by Laruence's Yar extension,the extension link is https://github.com/laruence/yar
 * 
 * @author haiwenzhu <haiwenzhu@outlook.com>
 * @version 0.1
 */

require_once(dirname(__FILE__) . '/Base.php');

class PHPYar_Client extends PHPYar_Base
{
	private $_uri = null;

	/**
	 * time out setting, see CURLOPT_TIMEOUT constant
	 */
	private $_opt_timeout = null;
	
	/**
	 * connet time out setting, see CURLOPT_CONNECTTIMEOUT constant
	 */
	private $_opt_connect_timeout = null;
	
	private $_concurrent_handles = array();

	public function __construct($uri = null)
	{
		$this->_uri = $uri;
	}

	/**
	 * synchronize call
	 * 
	 * @param string
	 * @param array
	 * @return boolean | string
	 */
	public function call($method, $params = array())
	{
		if (empty($this->_uri)) {
			trigger_error('requier init class with uri param;');
		}
	
		if (($ch = curl_init()) === false) {
			$this->_reportError(curl_error($ch));
			return false;
		}

		$request_params['method'] = $method;
		if (!empty($params)) {
			$request_params['params'] = $params;
		}
		$options = array(
			CURLOPT_URL => $this->_uri,
			CURLOPT_HEADER => false,
			CURLOPT_POST => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POSTFIELDS => http_build_query($request_params)
		);
		if ($this->_opt_timeout !== null) {
			$options[CURLOPT_TIMEOUT] = $this->_opt_timeout;
		}
		if ($this->_opt_connect_timeout !== null) {
			$options[CURLOPT_CONNECTTIMEOUT] = $this->_opt_connect_timeout;
		}
		if (curl_setopt_array($ch, $options) === false) {
			$this->_reportError(curl_error($ch));
			return false;
		}

		if (($return = curl_exec($ch)) === false) {
			$this->_reportError(curl_error($ch));
			return false;
		}
		return $return;
	}

	/**
	 * set concurrent call dest
	 * 
	 * @param string
	 * @param string
	 * @param array
	 * @param string | array
	 * @param array
	 * @return boolean
	 */
	public function concurrent_call($uri, $method, $params, $callback, $callback_params = array())
	{
		if (($ch = curl_init()) === false) {
			$this->_reportError(curl_error($ch));
			return false;
		}

		$request_params['method'] = $method;
		if (!empty($params)) {
			$request_params['params'] = $params;
		}

		$options = array(
			CURLOPT_URL => $uri,
			CURLOPT_HEADER => false,
			CURLOPT_POST => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POSTFIELDS => http_build_query($request_params)
		);
		if ($this->_opt_timeout !== null) {
			$options[CURLOPT_TIMEOUT] = $this->_opt_timeout;
		}
		if ($this->_opt_connect_timeout !== null) {
			$options[CURLOPT_CONNECTTIMEOUT] = $this->_opt_connect_timeout;
		}

		if (!curl_setopt_array($ch, $options)) {
			$this->_reportError(curl_error($ch));
			return false;
		}

		$this->_concurrent_handles[] = array(
			'handle' => $ch,
			'callback' => $callback,
			'callback_params' => $callback_params
		);

		return true;
	}

	/**
	 * loop for concurrent call, call callback when return
	 * @return boolean
	 */
	public function concurrent_loop()
	{
		if (empty($this->_concurrent_handles)) {
			$this->_reportError('call concurrent_call befor call this method');
			return false;
		}

		if (($mh = curl_multi_init()) === false) {
			$this->_reportError(curl_error($mh));
			return false;
		}

		foreach ($this->_concurrent_handles as $val) {
			if (curl_multi_add_handle($mh, $val['handle']) !== 0) {
				$this->_reportError(curl_error($mh));
				return false;
			}
		}
		
		$running = null;
		$ret = null;
		$error_msg = '';
		do {
			$mrc = curl_multi_exec($mh, $running);
			if ($mrc == CURLM_OK) {
				if (($ret = curl_multi_info_read($mh)) !== false) {
					if ($ret['result'] == CURLE_OK) {
						$params = array();
						$params[] = curl_multi_getcontent($ret['handle']);
						foreach ($this->_concurrent_handles as $key => $val) {
							if ($val['handle'] === $ret['handle']) {
								if (!empty($val['callback_params'])) {
									$params = array_merge($params, array_values($val['callback_params']));
								}
								call_user_func_array($val['callback'], $params);
								curl_close($val['handle']);
								unset($this->_concurrent_handles[$key]);
							}
						}
					}
				}
			}
			foreach ($this->_concurrent_handles as $val) {
				$error_msg = curl_error($val['handle']);
				if (!empty($error_msg)) {
					break;
				}
			}
		} while ($mrc == CURLM_CALL_MULTI_PERFORM || $running);

		foreach ($this->_concurrent_handles as $key => $val) {
			curl_close($val['handle']);
			unset($this->_concurrent_handles[$key]);
		}

		if (!empty($error_msg)) {
			$this->_reportError($error_msg);
			return false;
		}
		return true;
	}


	/**
	 * set timeout in seconds
	 * @param integer
	 */
	public function setTimeout($seconds)
	{
		$this->_opt_timeout = $seconds;
	}

	/**
	 * set connect timeout in seconds
	 * @param integer
	 */
	public function setConnectTimeout($seconds)
	{
		$this->_opt_connect_timeout = $seconds;
	}
}
