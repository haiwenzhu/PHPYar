<?php
/**
 * this is a test case for PHPYar client side
 */

require_once("../Client.php");

$client = new PHPYar_Client('http://localhost/PHPYar/test/server.php');
$client->setTimeout(1);
$client->setErrorException(true);
echo $client->call('foo', array('content' => 'client call'));

/*
//concurrent call
function dump_msg($content)
{
	echo $content;
}
$client = new PHPYar_Client();
$client->setErrorException(true);
$client->concurrent_call('http://localhost/PHPYar/test/server.php', 'foo', array('content' => 'first call'), 'dump_msg');
$client->concurrent_call('http://localhost/PHPYar/test/server.php', 'foo', array('content' => 'second call'), 'dump_msg');
$client->concurrent_loop();
*/
