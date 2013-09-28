<?php
/**
 * PHPYar is a RPC request library write in php
 * influenced by Laruence's Yar extension,the extension link is https://github.com/laruence/yar
 * 
 * @author haiwenzhu <haiwenzhu@outlook.com>
 * @version 0.1
 */

class PHPYar_Base
{
	/**
	 * report error in exception if true
	 */
	public $_error_exception = false;

	private $_error_msg = '';

	private function __construct()
	{
	}

	/**
	 * @param boolean
	 */
	public function setErrorException($flag)
	{
		$this->_error_exception = $flag;
	}

	protected function _reportError($msg)
	{
		$this->_error_msg = $msg;
		if ($this->_error_exception) {
			throw new Exception($msg);
		}
	}

	public function getErrorMsg()
	{
		return $this->_error_msg;
	}
}
