<?php
namespace Curlo;
/**
 * Curlo
 *
 * Simple Curl wrapper for PHP based on the WordPress HTTP curl class.
 * 
 * @author Ohad Raz <admin@bainternet.info>
 * @version 0.0.1
 * 
 */
class Curlo{
	/**
	 * $referer 
	 * @var string
	 */
	public $referer             = "https://en.bainternet.info";
	/**
	 * $user_agent 
	 * @var string
	 */
	public $user_agent          = 'CURLO-PHP/0.1';
	/**
	 * $followlocation 
	 * follow redirects 
	 * @var boolean
	 */
	public $followlocation      = true;
	/**
	 * $max_redirections
	 * @var integer
	 */
	public $max_redirections    = 5;
	/**
	 * $header 
	 * @var boolean
	 */
	public $header              = false;
	/**
	 * $timeout 
	 * timeout in seconds
	 * The maximum number of seconds to allow 
	 * cURL functions to execute.
	 * @var integer
	 */
	public $timeout             = 5;
	/**
	 * $connection_timeout 
	 * The number of seconds to wait while trying to connect. 
	 * Use 0 to wait indefinitely.
	 * @var integer
	 */
	public $connection_timeout  = 0;
	/**
	 * $ssl_verifypeer
	 * @var boolean
	 */
	public $ssl_verifypeer      = false;
	/**
	 * $ssl_verifyhost
	 * @var boolean
	 */
	public $ssl_verifyhost      = false;
	/**
	 * $proxy
	 * @var boolean
	 */
	public $proxy               = false;
	/**
	 * $blocking 
	 * @var boolean
	 */
	public $blocking            = true;
	/**
	 * [$options description]
	 * @var array
	 */
	public $options             = array();
	/**
	 * $headers
	 * @var array
	 */
	public $headers             = array();
	/**
	 * $reponse
	 * @var array
	 */
	public $reponse             = array();
	/**
	 * $max_body_length 
	 * The maximum amount of data to receive 
	 * from the remote server.
	 * @var boolean
	 */
	public $max_body_length     = false;
	/**
	 * $bytes_written_total
	 * @var integer
	 */
	public $bytes_written_total = 0;
	/**
	 * $url 
	 * @var string
	 */
	public $url                 = '';
	/**
	 * $error 
	 * @var mixed
	 */
	public $error;
	/**
	 * $error_number 
	 * @var [mixed
	 */
	public $error_number;
	/**
	 * $curl 
	 * curl handle
	 * @var null
	 */
	private $curl               = null;

	/**
	 * __construct 
	 * @param array $args class properies settings (optional)
	 */
	public function __construct( $args = array() ){
		if (!extension_loaded('curl')) {
			die('CURL extension not found!');
		}
		foreach ($args as $key => $value) {
			$this->$key = $value;
		}
	}

	/**
	 * init 
	 * prepare curl for request
	 * @param  string $url
	 * @param  string $method 
	 * @return instance
	 */
	public function init( $url, $method ){
		$this->url = $url;
		$this->curl  = curl_init();
		$this->option( CURLOPT_URL, $url );

		if ( !$this->ssl_verifypeer ){
			$this->option( CURLOPT_SSL_VERIFYPEER, FALSE );
		}

		$this->option( CURLOPT_RETURNTRANSFER, TRUE );
		//user agent
		$this->option( CURLOPT_USERAGENT, $this->user_agent );
		//timeout
		$timeout = (int) ceil( $this->timeout );
		$this->option( CURLOPT_TIMEOUT, $timeout );
		//connection timeout
		$connection_timeout = (int) ceil($this->connection_timeout);
		$this->option( CURLOPT_CONNECTTIMEOUT, $connection_timeout );
		//redirections
		$this->option( CURLOPT_MAXREDIRS , $this->max_redirections );
		$this->option( CURLOPT_FOLLOWLOCATION, $this->followlocation );
		//referer
		$this->option( CURLOPT_REFERER, $this->referer );
		//set method
		switch ( strtoupper( $method ) ) {
			case 'HEAD':
				$this->option( CURLOPT_NOBODY, true );
				break;
			case 'POST':
				$this->option( CURLOPT_POST, true );
				break;
			case 'PUT':
				$this->option( CURLOPT_CUSTOMREQUEST, 'PUT' );
				break;
			default:
				$this->option( CURLOPT_CUSTOMREQUEST, strtoupper($method ) );
				break;
		}

		//set proxy ?
		if ( $this->proxy ){
			if ( is_array( $this->proxy) && isset( $this->proxy['ip'] ) && isset( $this->proxy['port'] ) ){
				$this->option( CURLOPT_PROXY, $this->proxy['ip'] );
				$this->option( CURLOPT_PROXYPORT, $this->proxy['port'] );
			}
			elseif ( false === strpos($this->proxy, ":") ){
				$proxy = explode( ':', $this->proxy );
				$this->option( CURLOPT_PROXY, $proxy[0] );
				$this->option( CURLOPT_PROXYPORT, $proxy[1] );
			}
		}

		//headers
		if ( ! empty( $this->headers ) ) {
			$this->option( CURLOPT_HTTPHEADER, $this->headers );
		}

		$this->option( CURLOPT_HEADER, $this->header );

		if ( true === $this->blocking ) {
			$this->option( CURLOPT_HEADERFUNCTION, array( $this, 'build_response_headers' ) );
			$this->option( CURLOPT_WRITEFUNCTION, array( $this, 'build_response_body' ) );
		}
		//custom curl options see http://goo.gl/67qOFs and use Curlo::option
		curl_setopt_array($this->curl, $this->options );

		//reset response
		$this->response = array('headers' => '','body' => '');
		return $this;
	}

	/**
	 * option 
	 * set curl options
	 * @param  string $name  curl option name
	 * @param  mixed  $value curl option value
	 * @return instance
	 */
	public function option( $name, $value ){
		$this->options[$name] = $value;
		return $this;
	}

	/**
	 * add_header 
	 * add header to request
	 * @param mixed $header  string with $val set as value or as an array of key|value
	 * @param mixed $val     header value
	 */
	public function add_header($header, $val = null ){
		if ( is_array( $header ) ){
			foreach ($header as $name => $value) {
				$this->add_header( $name, $value );
			}
		}else{
			$this->headers[] = $val ? $header . ': ' . $val : $header;
		}
		return $this;
	}
	public function get( $url ){
		$this->init($url, 'GET' );
		return $this->exec();
	}

	public function delete( $url ){
		$this->init($url, 'DELETE' );
		return $this->exec();
	}

	public function post( $url, $content = NULL ){
		$this->option( CURLOPT_POSTFIELDS, $content );
		$this->init( $url, 'POST' );
		return $this->exec();
	}

	public function put( $url, $content ){
		$this->option( CURLOPT_POSTFIELDS, $content );
		$this->init( $url, 'PUT' );
		return $this->exec();
	}

	public function head( $url ){
		$this->init( $url, 'HEAD' );
		return $this->exec();
	}

	function non_blocking_exec(){
		curl_exec( $this->curl );
		$this->error_number = curl_errno( $this->curl );
		$this->error        = curl_error( $this->curl );
		if ( $this->error ) {
			curl_close( $this->curl );
			return array(
				'error'      => true,
				'message'    => $this->error,
				'error_code' =>  $this->error_number
			);
		}
		$code = curl_getinfo( $this->curl, CURLINFO_HTTP_CODE );
		if ( in_array( $code, array( 301, 302 ) ) ) {
			curl_close( $this->curl );
			return array(
				'error'    => true,
				'message'  => 'Too many redirects.',
				'response' => array(
					'code' =>  $code
				)
			);
		}
		curl_close( $this->curl );
		return array( 'headers' => array(), 'body' => '', 'response' => array('code' => false, 'message' => false), 'cookies' => array() );
	}

	function exec(){
		if ( ! $this->blocking ) {
			return $this->non_blocking_exec();
		}
		curl_exec( $this->curl );
		$theHeaders                = $this->process_headers( $this->response['headers'], $this->url );
		$theBody                   = $this->response['body'];
		$bytes_written_total       = $this->bytes_written_total;
		$this->headers             = '';
		$this->body                = '';
		$this->bytes_written_total = 0;
		$this->error_number = curl_errno( $this->curl );
		$this->error        = curl_error( $this->curl );
		// If an error occurred, or, no response.
		if ( $this->error_number || ( 0 == strlen( $theBody ) && empty( $theHeaders['headers'] ) ) ) {
			if ( CURLE_WRITE_ERROR  == $this->error_number ) {
				if ( ! $this->max_body_length || $this->max_body_length != $bytes_written_total ) {
					curl_close( $this->curl );
					return array(
						'error'      => true,
						'message'    => 'http_request_failed - ' . $this->error,
						'error_code' =>  $this->error_number
					);
				}
			} else {
				if ( $this->error ) {
					curl_close( $this->curl );
					return array(
						'error'      => true,
						'message'    => $this->error,
						'error_code' =>  $this->error_number
					);
				}
			}
			$code = curl_getinfo( $this->curl, CURLINFO_HTTP_CODE );
			if ( in_array( $code, array( 301, 302 ) ) ) {
				curl_close( $this->curl );
				return array(
					'error'    => true,
					'message'  => 'Too many redirects.',
					'response' => array(
						'code' =>  $code
					)
				);
			}
		}
		curl_close( $this->curl );
		$this->response = array(
			'headers'  => $theHeaders['headers'],
			'body'     => $theBody,
			'response' => $theHeaders['response'],
			'cookies'  => $theHeaders['cookies']
		);
		return $this->response;
	}

	/**
	 * Grab the headers of the cURL request
	 *
	 * Each header is sent individually to this callback, so we append to the 
	 * $this->response[header] property for temporary storage
	 */
	function build_response_headers($curl_handle, $headers ) {
		$this->response['headers'] .= $headers;
		return strlen( $headers );
	}

	function build_response_body( $curl_handle, $data ) {
		$this->response['body'] .= $data;
		
		$data_length = strlen( $data );
		$this->bytes_written_total += $data_length; 
		// if ( $this->max_body_length && ( $this->bytes_written_total + $data_length ) > $this->max_body_length ) {
		// 	$data_length = ( $this->max_body_length - $this->bytes_written_total );
		// 	$data = substr( $data, 0, $data_length );
		// }
		// $this->response['body'] .= $data;
		// $bytes_written = $data_length;
		// $this->bytes_written_total += $bytes_written;
		// Upon event of this function returning less than strlen( $data ) curl will error with CURLE_WRITE_ERROR.
		return $data_length;
	}

	function process_headers($headers, $url = '' ) {
		// Split headers, one per array element.
		if ( is_string($headers) ) {
			// Tolerate line terminator: CRLF = LF (RFC 2616 19.3).
			$headers = str_replace("\r\n", "\n", $headers);
			/*
			 * Unfold folded header fields. LWS = [CRLF] 1*( SP | HT ) <US-ASCII SP, space (32)>,
			 * <US-ASCII HT, horizontal-tab (9)> (RFC 2616 2.2).
			 */
			$headers = preg_replace('/\n[ \t]/', ' ', $headers);
			// Create the headers array.
			$headers = explode("\n", $headers);
		}
	 
		$response = array('code' => 0, 'message' => '');
	 
		/*
		 * If a redirection has taken place, The headers for each page request may have been passed.
		 * In this case, determine the final HTTP header and parse from there.
		 */
		for ( $i = count($headers)-1; $i >= 0; $i-- ) {
			if ( !empty($headers[$i]) && false === strpos($headers[$i], ':') ) {
				$headers = array_splice($headers, $i);
				break;
			}
		}
	 
		$cookies = array();
		$newheaders = array();
		foreach ( (array) $headers as $tempheader ) {
			if ( empty($tempheader) )
				continue;
	 
			if ( false === strpos($tempheader, ':') ) {
				$stack = explode(' ', $tempheader, 3);
				$stack[] = '';
				list( , $response['code'], $response['message']) = $stack;
				continue;
			}
	 
			list($key, $value) = explode(':', $tempheader, 2);
	 
			$key = strtolower( $key );
			$value = trim( $value );
	 
			if ( isset( $newheaders[ $key ] ) ) {
				if ( ! is_array( $newheaders[ $key ] ) )
					$newheaders[$key] = array( $newheaders[ $key ] );
				$newheaders[ $key ][] = $value;
			} else {
				$newheaders[ $key ] = $value;
			}
			if ( 'set-cookie' == $key ){
				if (! class_exists( 'Curlo\Curlo_Cookie' ) ){
					include_once(__DIR__.'/curlo.cookie.php');
				}
				$cookies[] = new Curlo_Cookie( $value, $url );
			}
		}
	 
		// Cast the Response Code to an int
		$response['code'] = intval( $response['code'] );
	 
		return array('response' => $response, 'headers' => $newheaders, 'cookies' => $cookies);
	}
}