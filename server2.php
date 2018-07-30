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
		
		$request = new RequestHttp($newClient);
		print_r($request->getUrl());
		
		print "end of input \n";
		
		if($request->getUrl() && in_array($request->getUrl(), $allowedRequestAddress))
			socket_write($newClient, pageContent());
		socket_close($newClient);
		print "end client $clientName\n";
		
	}
}
socket_close($sock); 
print "end\n";


