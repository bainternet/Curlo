<?php
namespace Curlo;
/**
* Curlo_Cookie
* Curlo Cookie class
*/
class Curlo_Cookie{
	/**
	 * $name 
	 * cookie name
	 * @var string
	 */
	public $name;
	/**
	 * $value 
	 * cookie value
	 * @var string
	 */
	public $value;
	/**
	 * $expires 
	 * cookie expiration date
	 * @var string
	 */
	public $expires;
	/**
	 * $path 
	 * cookie path
	 * @var string
	 */
	public $path;
	/**
	 * $domain
	 * cookie domain 
	 * @var string
	 */
	public $domain;
	/**
	 * __construct 
	 * class constructor
	 * @param string $data          cookie datat to be pharsed
	 * @param string $requested_url url of the requested cookie
	 */
	function __construct( $data, $requested_url = '' ){
		if ( $requested_url ){
			$arrURL = @parse_url( $requested_url );
		}
		if ( isset( $arrURL['host'] ) ){
			$this->domain = $arrURL['host'];
		}
		$this->path = isset( $arrURL['path'] ) ? $arrURL['path'] : '/';
		if (  '/' != substr( $this->path, -1 ) ){
			$this->path = dirname( $this->path ) . '/';
		} 
		if ( is_string( $data ) ) {
			// Assume it's a header string direct from a previous request.
			$pairs = explode( ';', $data );
 
			// Special handling for first pair; name=value. Also be careful of "=" in value.
			$name        = trim( substr( $pairs[0], 0, strpos( $pairs[0], '=' ) ) );
			$value       = substr( $pairs[0], strpos( $pairs[0], '=' ) + 1 );
			$this->name  = $name;
			$this->value = urldecode( $value );
 
			// Removes name=value from items.
			array_shift( $pairs );
 
			// Set everything else as a property.
			foreach ( $pairs as $pair ) {
				$pair = rtrim($pair);
 
				// Handle the cookie ending in ; which results in a empty final pair.
				if ( empty($pair) ){
					continue;
				}
 
				list( $key, $val ) = strpos( $pair, '=' ) ? explode( '=', $pair ) : array( $pair, '' );
				$key = strtolower( trim( $key ) );
				if ( 'expires' == $key ){
					$val = strtotime( $val );
				}
				$this->$key = $val;
			}
		} else {
			if ( !isset( $data['name'] ) ){
				return; 
			}
			// Set properties based directly on parameters.
			foreach ( array( 'name', 'value', 'path', 'domain', 'port' ) as $field ) {
				if ( isset( $data[ $field ] ) ){
					$this->$field = $data[ $field ];
				}
			} 
			if ( isset( $data['expires'] ) ){
				$this->expires = is_int( $data['expires'] ) ? $data['expires'] : strtotime( $data['expires'] );
			}else{
				$this->expires = null;
			}
		}
	}

	/**
	 * getHeaderValue
	 * Convert cookie name and value back to header string.
	 * @return string Header encoded cookie name and value.
	 */
	public function getHeaderValue() {
		if ( ! isset( $this->name ) || ! isset( $this->value ) )  return '';
		return $this->name . '=' . $this->value;
	}
 
	/**
	 * getFullHeader 
	 * Retrieve cookie header for usage in the rest of the API.
	 * @return string
	 */
	public function getFullHeader() {
		return 'Cookie: ' . $this->getHeaderValue();
	}
}