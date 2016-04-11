# cURL-PHP

cURL-PHP is a PHP library which makes it easy to do simple cURL requests 
and makes more complicated cURL requests easier too.

## Requirements

1. PHP 5.4+
2. PHP 5 (configured with cURL enabled)
3. libcurl

## Features

* POST/GET/PATHCH/PUT/DELETE requests over HTTP
* HTTP Authentication
* Follows redirects
* Returns error string
* Provides debug information
* Proxy support
* Cookies

## API calls

These do it all in one line of code to make life easy. They return the body of the page, or FALSE on fail.

##### Hierachy of API object method calling may affect the functionality part.

These methods allow you to build a more complex request.

	// import file
	require 'curlphp/curlphp.php'
	
	// Create an object
	$curl = new curlphp();

	// Option
	$curl->setOption(CURLOPT_BUFFERSIZE, 10);
	$curl->setOption(array(CURLOPT_BUFFERSIZE => 10));

	// More human looking options
	$curl->setOption('buffersize', 10);

    // Headers
	$curl->http_header('Content-Type','application/json');
	$curl->http_header('Content-Length',300);
	
	// SSL Option
	$curl->setSSL(false);
	
	// Login to HTTP user authentication
	$curl->http_login('username', 'password');

	// Cookies - If you do not use post, it will just run a GET request
	$vars = array('foo'=>'bar');
	$curl->set_cookies($vars);

	// Proxy - Request the page through a proxy server
	// Port is optional, defaults to 80
	$curl->proxy('http://example.com', 1080);
	$curl->proxy('http://example.com');

	// Proxy login
	$curl->proxy_login('username', 'password');

	// Execute - returns responce
	echo $curl->executeCurl();

	// Debug data ------------------------------------------------
    $curl->debug();

	// Errors
	$curl->error_code; // int
	$curl->error_string;

	// Information
	$curl->info; // array
	