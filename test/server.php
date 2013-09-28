<?php
/**
 * this is a test case for PHPYar server side
 */

require_once('../Server.php');

class Api
{
	public function foo()
	{
		echo $_POST['content'];
	}
}

$server = new PHPYar_Server(new Api());
$server->setErrorException(true);
$server->handle();
