<?php

$port = 8998;
$addr = '0.0.0.0';


spl_autoload_register(function($className){
    include __DIR__ . '/' . $className . '.php';
});





$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if(!socket_bind($sock, $addr, $port))
	die('err');
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

while(time() - $startTime < 600)
//while(!$chutdodn)
{
	try
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
			print $request->getUrl() . "\n";
			print "end of input \n";
		
		$responseString = IOHelper::doAction($request->getUrlParams(), $request->getUrl());
		$request->sendResponse($responseString);
			print "end client $clientName\n";
		
		if($responseString == 'shutdown')
		    break;		
		}
	}
	catch(\Exception $e)
	{
		print 'exception ' . $e->getMessage() . "\n";
		//print_r($e->getTrace());
		print $e->__toString();
		print "\n";
	}
}
socket_close($sock); 
print "end\n";


