<?php

$port = 8998;
$addr = '127.0.0.1';
//$addr = '95.52.100.5';
$addr = '0.0.0.0';


spl_autoload_register(function($className){
    include __DIR__ . '/' . $className . '.php';
});


function pageContent()
{
	$headers = "HTTP/1.1 200 OK\nDate: Mon, 27 Jul 2009 12:28:53 GMT\nServer: Apache/2.2.14 (Win32)\nLast-Modified: Wed, 22 Jul 2009 19:15:56 GMT\nContent-Length: #content#len#\nContent-Type: text/html\nConnection: Closed";
	$body = "<html>
		<body>
		<h1>Hello, World!</h1>
		<form method=\"post\">
			<input type=\"submit\" name=\"sendBtn\">
			<input type=\"text\" name=\"login\">
		</form>
		</body>
		</html>";
		
	return str_replace('#content#len#', strlen($body), $headers) . "\n\n" . $body . "\0";
}


function parseRequestParams($paramStr)
{
	$params = [];
	if(!$paramStr)
		return $params;
	
	$preg_match_all('/(.*?)=(.*?)\&|$/', $paramStr, $paramArr);
	
}



$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
//socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($sock, $addr, $port);
if(!socket_listen($sock))
	die('err');
socket_set_nonblock($sock);

print "Server Listening on $addr:$port\n"; 

$startTime = time();
$clients = [];


$allowedRequestAddress = [
	'/game',
	'/server765376867'
];

$chutdodn = false;
while(time() - $startTime < 30)
//while(!$chutdodn)
{
	$newClient = socket_accept($sock);
	if($newClient)
	{
		$clientName = '';
		socket_getpeername($newClient, $clientName);
		print "new client $clientName\n";
		$requestStr = '';
		$prevStringIsNull = false;
		$url = '';
		//while(($requestStr = socket_read($newClient, 2048)) !== false)
		
		
		$requestStr = socket_read($newClient, 2048);
		print "$clientName say\n$requestStr \n";
		$requestHeaders = explode("\n", trim($requestStr));
		array_walk($requestHeaders, function(&$value, $key) {
			$value = trim($value);
		});
		preg_match('/^(GET|POST) (.*?) HTTP\/1\.1$/', $requestHeaders[0], $urlFull);
		$url = $urlFull[2];
		
		if($urlFull[1] == 'POST')
		{
			$requestBody = socket_read($newClient, 2048);
		}
		print "end of input \n";
		
		//var_dump(socket_strerror(socket_last_error($newClient)));
		if($url && in_array($url, $allowedRequestAddress))
			socket_write($newClient, pageContent());
		socket_close($newClient);
		print "end client $clientName\n";
		
	}
	$read = $clients;
	$write = $except = $read;
	$status = 0;
	if(count($read))
		$status = socket_select($read, $write, $except, 0);
	foreach($read as $client)
	{
		$clientName = '';
		socket_getpeername($client, $clientName);
		$d = socket_read($client, 1000);
		print "data from $clientName: $d\n";
	}
	foreach($write as $client)
	{
		$clientName = '';
		socket_getpeername($client, $clientName);
		socket_write($client, pageContent());
		unset($clients[array_search($client, $clients)]);
		print "write for $clientName \n";
	}
		
	foreach($clients as $k => $client)
	{
		if(!in_array($client, $read) && !in_array($client, $write))
		{
			//socket_close($client);
			//unset($clients[$k]);
		}
	}
}
socket_close($sock); 
print "end\n";
