<?php

$port = 8998;
$addr = '127.0.0.1';
//$addr = '95.52.100.5';
$addr = '0.0.0.0';

$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($sock, $addr, $port);
if(!socket_listen($sock))
	die('err');
socket_set_nonblock($sock);

print "Server Listening on $addr:$port\n"; 

$startTime = time();
$clients = [];

while(time() - $startTime < 120)
{
	$newClient = socket_accept($sock);
	if($newClient)
	{
		$clients[] = $newClient;
		print "new client \n";
	}
	$read = $clients;
	$status = 0;
	if(count($read))
		$status = socket_select($read, $write, $except, 0);
	foreach($read as $client)
	{
		$d = socket_read($client, 1000);
		if($d)
			print "data: $d\n";
	}
}
socket_close($sock); 
print "end\n";
