<?php
/**
 * PHPYar is a RPC request library write in php
 * influenced by Laruence's Yar extension,the extension link is https://github.com/laruence/yar
 * 
 * @author haiwenzhu <haiwenzhu@outlook.com>
 * @version 0.1
 */
require_once(dirname(__FILE__) . '/Base.php');

class PHPYar_Server extends PHPYar_Base
{
	/**
	 * the request handle object
	 */
	private $_object = null;

	public function __construct($object = null)
	{
		$this->_object = $object;
	}

	/**
	 * receive rpc request and dispatch to handler boject
	 */
	public function handle()
	{
		if (!isset($_SERVER['REQUEST_METHOD'])) {
			$this->_reportError('script must running in web server');
		}

		$method = $this->_getParam('method', '');
		$params = $this->_getParam('params', array());
		$this->_exportParams($params);

		if (method_exists($this->_object, $method)) {
			call_user_func_array(array($this->_object, $method), $params);
		} else {
			$this->_reportError("method {$method} dose not exists");
		}
	}

	/**
	 * get post or get params
	 * @param string
	 * @param string
	 * @return string
	 */
	private function _getParam($name, $default_value = '')
	{
		if (isset($_POST[$name])) {
			$default_value = $_POST[$name];		
			unset($_POST[$name]);
		} elseif (isset($_GET[$name])) {
			$default_value = $_GET[$name];
			unset($_GET[$name]);
		}
		return $default_value;
	}

	/**
	 * export params to $_POST array
	 * @param array
	 */
	private function _exportParams($params)
	{
		foreach ($params as $key => $val) {
			$_POST[$key] = $val;
		}
	}
}
