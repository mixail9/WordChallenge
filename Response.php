<?php 


class Response
{
	protected $socket;
	const exampleHeaders = "HTTP/1.1 200 OK\nDate: Mon, 27 Jul 2009 12:28:53 GMT\nServer: Apache/2.2.14 (Win32)\nLast-Modified: Wed, 22 Jul 2009 19:15:56 GMT\nContent-Length: #content#len#\nContent-Type: text/html\nConnection: Closed";
	const exampleBody = "<html>
		<body>
		<h1>Hello, World!</h1>
		<form method=\"post\">
			<input type=\"submit\" name=\"sendBtn\">
			<input type=\"text\" name=\"login\">
		</form>
		</body>
		</html>";
			

	public function sendResponsePart($str)
	{
		socket_write($this->socket, $str);
	}
	
	public function sendResponse($body, $headers = '')
	{
        if(!$headers)
            $headers = str_replace('#content#len#', strlen($body), static::exampleHeaders);
        elseif(is_array($headers))
            $headers = implode("\n", $headers);
		static::sendResponsePart($headers . "\n\n" . $body);
		socket_close($this->socket);
	}
	
	public function sendResponseJson($data, $headers = '')
	{
		$this->sendResponse(json_encode($data, $headers));
	}
	

}
