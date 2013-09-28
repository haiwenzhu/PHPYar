#PHPYar#
A RPC libaray write in PHP, mainly influenced by [Yar](https://github.com/laruence/yar)

##Requirement##
- PHP5
- CURL

##Usage##
Server side:

    class Api
	{
		public function bar()
		{
			echo $_POST['content'];
		}
	}

	$server = new PHPYar_Server(new Api());
	$server->handle();

Client side:

Sigle call
	require_once("../Client.php");
	
	$client = new PHPYar_Client('http://localhost/PHPYar/test/server.php');
	$client->setTimeout(1);
	$client->setErrorException(true);
	echo $client->call('foo', array('content' => 'client call'));

Concurrent call

    function dump_msg($content)
    {
    	echo $content;
    }
    $client = new PHPYar_Client();
    $client->setErrorException(true);
    $client->concurrent_call('http://localhost/PHPYar/test/server.php', 'foo', array('content' => 'first call'), 'dump_msg');
    $client->concurrent_call('http://localhost/PHPYar/test/server.php', 'foo', array('content' => 'second call'), 'dump_msg');
    $client->concurrent_loop();
