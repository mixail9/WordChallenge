<?php

class RequestHttp extends Request
{
	protected $socket = null;
	protected $headers = [];
	protected $body = '';
	protected $url = '';
	protected $method = 'GET';
	protected $urlParams = [];
    
    
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
	

	function __construct($newClient)
	{
		$this->socket = $newClient;
		$headers = socket_read($newClient, 2048);
		$this->setHeadersString($headers);
		if(!$this->url)
			throw new \Exception('wrong headers');
		if($this->method == 'POST')
		{
			$body = socket_read($newClient, 2048);
			$this->setBodyString($body);
		}	
	}
	
	
	
	protected function setHeadersString($headers)
	{
		$this->headers = explode("\n", trim($headers));
		array_walk($this->headers, function(&$value, $key) {
			$value = trim($value);
			if(empty($this->headers[$key]))
				unset($this->headers[$key]);
		});
		preg_match('/^(GET|POST) (.*?) HTTP\/1\.1$/', $this->headers[0], $url);
				
		$this->method = $url[1];
		$this->explodeUrl($url[2]);
	}
	
	protected function setBodyString($body)
	{
		$this->explodeUrlParams($body);
	}

	protected function explodeUrlParams($paramStr)
	{
		if(!$paramStr)
			return false;
			
		$paramStr .= '&';
		preg_match_all('/([a-zA-Z\-_0-9]+)=(.*?)&/', $paramStr, $paramArr);
		foreach($paramArr[1] as $k => $key)
			$this->urlParams[$key] = $paramArr[2][$k];
	}

	protected function explodeUrl($url)
	{
		$delimiter = strpos($url, '?');
		$paramsRaw = '';
		if($delimiter)
		{
			$paramsRaw = substr($url, $delimiter + 1);
			$url = substr($url, 0, $delimiter);
		}
        
		$this->url = $url;
		$this->explodeUrlParams($paramsRaw);
	}
	
	protected function checkAccess()
	{
	}
	
	
	public function getUrl()
	{
		return $this->url;
	}
	
	public function getUrlParams()
	{
		return $this->urlParams;
	}
	
	public function getHeaders()
	{
		return $this->urlParams;
	}
    

			

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


