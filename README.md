Curlo
=========
Simple Curl wrapper for PHP based on the WordPress HTTP curl class.

*  This class is some-what based on the WordPress HTTP Curl class and HTTP API so some of the code comes from there.

----------

Install
========
You can download this project in either [zip][1] or [tar][2] formats.

You can also clone the project with Git by running:

    $ git clone git://github.com/bainternet/Curlo.git

Or you can use Composer (prefered method) to include the class in your project

    $ php composer.phar require bainternet/curlo

How to Use?
=====

Simple, Include the class file in your application bootstrap (setup/load/configuration or whatever you call it) and start creating HTTP request.

```PHP
//include the class
include_once('path/to/curlo.php');

//instantiate a new curlo
$request  = new Curlo\Curlo();
```
####Simple get Request
```PHP
//get the response of a GET request
$response = $request->get('https://en.bainternet.info');
var_dump($response);
```
####Simple post Request
```PHP
//make a POST request
$response = $request->post('https://en.bainternet.info/login', 
	array(
		'username' => 'Ohad', 
		'password' => 'XXXXXXX'
	)
);
var_dump($response);
```

#### Response Example
```DUMP
array (size=4)
	'headers' => array (size=8)
		'server' => string 'cloudflare-nginx' (length=16)
		'date' => string 'Thu, 17 Sep 2015 15:10:57 GMT' (length=29)
		'content-type' => string 'text/html; charset=UTF-8' (length=24)
		'transfer-encoding' => string 'chunked' (length=7)
		'connection' => string 'keep-alive' (length=10)
		'set-cookie' => string '__cfduid=XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX; expires=Fri, 16-Sep-16 15:10:56 GMT; path=/; domain=.bainternet.info; HttpOnly' (length=132)
		'x-pingback' => string 'http://en.bainternet.info/xmlrpc.php' (length=36)
		'cf-ray' => string 'XXXXXXXXXXXXX-FRA' (length=20)
	'body' => string '<!doctype html>'... (length=65756)
	'response' => array (size=2)
		'code' => int 200
		'message' => string 'OK' (length=2)
	'cookies' => array (size=1)
		0 => object(Curlo\Curlo_Cookie)[2]
			public 'name' => string '__cfduid' (length=8)
			public 'value' => string 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX' (length=43)
			public 'expires' => int 1474038656
			public 'path' => string '/' (length=1)
			public 'domain' => string '.bainternet.info' (length=16)
			public 'httponly' => string '' (length=0)
			....
```



Methods
=======

**add_header** add headers to request

     - @access public
     - @since 0.1
     - @param mixed $header  string with $val set as value or as an array of key|value
	 - @param mixed $val     header value
	 - @return object Curlo instance
```PHP
$request->add_header( 'Accept', 'application/json')
->add_header( 'Content-Type', 'application/json');

//same as above
$request->add_header( array( 
	'Accept'       => 'application/json',
	'Content-Type' => 'application/json'
);
```


License
=======

Since this class is derived from the WordPress HTTP API so are the license and they are GPL http://www.gnu.org/licenses/gpl.html

  [1]: https://github.com/bainternet/Curlo/zipball/master
  [2]: https://github.com/bainternet/Curlo/tarball/master
  [3]: https://github.com/bainternet/Curlo
[![Analytics](https://ga-beacon.appspot.com/UA-50573135-9/Curlo/main)](https://github.com/bainternet/Curlo)