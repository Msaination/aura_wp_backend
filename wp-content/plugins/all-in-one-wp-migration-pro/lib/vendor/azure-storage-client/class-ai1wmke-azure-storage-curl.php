<?php
/**
 * Copyright (C) 2014-2025 ServMask Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Attribution: This code is part of the All-in-One WP Migration plugin, developed by
 *
 * ███████╗███████╗██████╗ ██╗   ██╗███╗   ███╗ █████╗ ███████╗██╗  ██╗
 * ██╔════╝██╔════╝██╔══██╗██║   ██║████╗ ████║██╔══██╗██╔════╝██║ ██╔╝
 * ███████╗█████╗  ██████╔╝██║   ██║██╔████╔██║███████║███████╗█████╔╝
 * ╚════██║██╔══╝  ██╔══██╗╚██╗ ██╔╝██║╚██╔╝██║██╔══██║╚════██║██╔═██╗
 * ███████║███████╗██║  ██║ ╚████╔╝ ██║ ╚═╝ ██║██║  ██║███████║██║  ██╗
 * ╚══════╝╚══════╝╚═╝  ╚═╝  ╚═══╝  ╚═╝     ╚═╝╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Kangaroos cannot jump here' );
}

class Ai1wmke_Azure_Storage_Curl {

	/**
	 * Base URL
	 *
	 * @var string
	 */
	protected $base_url = null;

	/**
	 * Base path
	 *
	 * @var string
	 */
	protected $path = '/';

	/**
	 * Base query
	 *
	 * @var array
	 */
	protected $query = array();

	/**
	 * Account name
	 *
	 * @var string
	 */
	protected $account_name = null;

	/**
	 * Account key
	 *
	 * @var string
	 */
	protected $account_key = null;

	/**
	 * HTTP method
	 *
	 * @var string
	 */
	protected $method = 'GET';

	/**
	 * Current date (header)
	 *
	 * @var string
	 */
	protected $x_ms_date = null;

	/**
	 * Current version (header)
	 *
	 * @var string
	 */
	protected $x_ms_version = '2018-03-28';

	/**
	 * cURL SSL
	 *
	 * @var boolean
	 */
	protected $ssl = true;

	/**
	 * cURL handler
	 *
	 * @var resource|null
	 */
	protected $handler = null;

	/**
	 * cURL options
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * cURL headers
	 *
	 * @var array
	 */
	protected $headers = array(
		'Content-Encoding'    => null,
		'Content-Language'    => null,
		'Content-Length'      => null,
		'Content-MD5'         => null,
		'Content-Type'        => null,
		'Date'                => null,
		'If-Modified-Since'   => null,
		'If-Match'            => null,
		'If-None-Match'       => null,
		'If-Unmodified-Since' => null,
		'Range'               => null,
	);

	/**
	 * cURL raw headers
	 *
	 * @var array
	 */
	protected $raw_headers = array();

	/**
	 * cURL messages
	 *
	 * @var array
	 */
	protected $messages = array(
		// [Informational 1xx]
		100 => '100 Continue',
		101 => '101 Switching Protocols',

		// [Successful 2xx]
		200 => '200 OK',
		201 => '201 Created',
		202 => '202 Accepted',
		203 => '203 Non-Authoritative Information',
		204 => '204 No Content',
		205 => '205 Reset Content',
		206 => '206 Partial Content',

		// [Redirection 3xx]
		300 => '300 Multiple Choices',
		301 => '301 Moved Permanently',
		302 => '302 Found',
		303 => '303 See Other',
		304 => '304 Not Modified',
		305 => '305 Use Proxy',
		306 => '306 (Unused)',
		307 => '307 Temporary Redirect',

		// [Client Error 4xx]
		400 => '400 Bad Request',
		401 => '401 Unauthorized',
		402 => '402 Payment Required',
		403 => '403 Forbidden',
		404 => '404 Not Found',
		405 => '405 Method Not Allowed',
		406 => '406 Not Acceptable',
		407 => '407 Proxy Authentication Required',
		408 => '408 Request Timeout',
		409 => '409 Conflict',
		410 => '410 Gone',
		411 => '411 Length Required',
		412 => '412 Precondition Failed',
		413 => '413 Request Entity Too Large',
		414 => '414 Request-URI Too Long',
		415 => '415 Unsupported Media Type',
		416 => '416 Requested Range Not Satisfiable',
		417 => '417 Expectation Failed',

		// [Server Error 5xx]
		500 => '500 Internal Server Error',
		501 => '501 Not Implemented',
		502 => '502 Bad Gateway',
		503 => '503 Service Unavailable',
		504 => '504 Gateway Timeout',
		505 => '505 HTTP Version Not Supported',
	);

	public function __construct() {
		if ( ! extension_loaded( 'curl' ) ) {
			throw new Ai1wmke_Error_Exception( __( 'Microsoft Azure Storage Extension requires PHP cURL extension. <a href="https://help.servmask.com/knowledgebase/curl-missing-in-php-installation/" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );
		}

		if ( ! extension_loaded( 'libxml' ) ) {
			throw new Ai1wmke_Error_Exception( __( 'Microsoft Azure Storage Extension requires PHP libxml extension. <a href="https://help.servmask.com/knowledgebase/libxml-missing-in-php-installation/" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );
		}

		if ( ! extension_loaded( 'simplexml' ) ) {
			throw new Ai1wmke_Error_Exception( __( 'Microsoft Azure Storage Extension requires PHP SimpleXML extension. <a href="https://help.servmask.com/knowledgebase/simplexml-missing-in-php-installation/" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );
		}

		// Default configuration
		$this->set_option( CURLOPT_HEADER, false );
		$this->set_option( CURLOPT_FRESH_CONNECT, true );
		$this->set_option( CURLOPT_RETURNTRANSFER, true );
		$this->set_option( CURLOPT_FOLLOWLOCATION, true );
		$this->set_option( CURLOPT_CONNECTTIMEOUT, 120 );
		$this->set_option( CURLOPT_TIMEOUT, 0 );

		// Enable SSL support
		$this->set_option( CURLOPT_CAINFO, __DIR__ . DIRECTORY_SEPARATOR . 'certs' . DIRECTORY_SEPARATOR . 'cacert.pem' );
		$this->set_option( CURLOPT_CAPATH, __DIR__ . DIRECTORY_SEPARATOR . 'certs' );

		// Enable WP proxy
		$proxy = new WP_HTTP_Proxy();
		if ( $proxy->is_enabled() ) {
			$this->set_option( CURLOPT_PROXY, $proxy->host() );
			$this->set_option( CURLOPT_PROXYPORT, $proxy->port() );
			if ( $proxy->use_authentication() ) {
				$this->set_option( CURLOPT_PROXYUSERPWD, $proxy->authentication() );
			}
		}

		// Get current timestamp value (UTC)
		$this->x_ms_date = $this->get_timestamp();
	}

	/**
	 * Set account name
	 *
	 * @param  string $account_name Account name
	 * @return object
	 */
	public function set_account_name( $account_name ) {
		$this->account_name = $account_name;
		return $this;
	}

	/**
	 * Get account name
	 *
	 * @return string
	 */
	public function get_account_name() {
		return $this->account_name;
	}

	/**
	 * Set account key
	 *
	 * @param  string $account_key Account key
	 * @return object
	 */
	public function set_account_key( $account_key ) {
		$this->account_key = $account_key;
		return $this;
	}

	/**
	 * Get account key
	 *
	 * @return string
	 */
	public function get_account_key() {
		return $this->account_key;
	}

	/**
	 * Set SSL mode
	 *
	 * @param  boolean $value SSL mode
	 * @return object
	 */
	public function set_ssl( $value ) {
		$this->ssl = $value;
		return $this;
	}

	/**
	 * Get SSL mode
	 *
	 * @return boolean
	 */
	public function get_ssl() {
		return $this->ssl;
	}

	/**
	 * Set base URL
	 *
	 * @param  string $value Base URL
	 * @return object
	 */
	public function set_base_url( $value ) {
		$this->base_url = $value;
		return $this;
	}

	/**
	 * Get base URL
	 *
	 * @return string
	 */
	public function get_base_url() {
		return sprintf( $this->base_url, $this->account_name );
	}

	/**
	 * Set base path
	 *
	 * @param  string $value Base path
	 * @return object
	 */
	public function set_path( $value ) {
		$this->path = $value;
		return $this;
	}

	/**
	 * Get base path
	 *
	 * @return string
	 */
	public function get_path() {
		return $this->path;
	}

	/**
	 * Set base query
	 *
	 * @param  array  $value Base query
	 * @return object
	 */
	public function set_query( $value ) {
		$this->query = $value;
		return $this;
	}

	/**
	 * Get base query
	 *
	 * @return array
	 */
	public function get_query() {
		return $this->query;
	}

	/**
	 * Set cURL option
	 *
	 * @param  mixed  $name  cURL option name
	 * @param  mixed  $value cURL option value
	 * @return object
	 */
	public function set_option( $name, $value ) {
		$this->options[ $name ] = $value;
		return $this;
	}

	/**
	 * Get cURL option
	 *
	 * @param  mixed $name cURL option name
	 * @return mixed
	 */
	public function get_option( $name ) {
		return isset( $this->options[ $name ] ) ? $this->options[ $name ] : null;
	}

	/**
	 * Set cURL header
	 *
	 * @param  string $name  cURL header name
	 * @param  string $value cURL header value
	 * @return object
	 */
	public function set_header( $name, $value ) {
		$this->headers[ $name ] = $value;
		return $this;
	}

	/**
	 * Get cURL header
	 *
	 * @param  string $name cURL header name
	 * @return string
	 */
	public function get_header( $name ) {
		return isset( $this->headers[ $name ] ) ? $this->headers[ $name ] : null;
	}

	/**
	 * Set cURL raw header
	 *
	 * @param  string $name  cURL header name
	 * @param  string $value cURL header value
	 * @return object
	 */
	public function set_raw_header( $name, $value ) {
		$this->raw_headers[ $name ] = $value;
		return $this;
	}

	/**
	 * Get cURL raw header
	 *
	 * @param  string $name cURL header name
	 * @return string
	 */
	public function get_raw_header( $name ) {
		return isset( $this->raw_headers[ $name ] ) ? $this->raw_headers[ $name ] : null;
	}

	/**
	 * Make cURL request
	 *
	 * @param  boolean $parse_as_xml XML parse
	 * @return mixed
	 */
	public function make_request( $parse_as_xml = false ) {
		$this->handler = curl_init();

		// Set base URL
		if ( $this->get_query() ) {
			$this->set_option( CURLOPT_URL, $this->get_base_url() . $this->get_path() . '?' . build_query( $this->get_query() ) );
		} else {
			$this->set_option( CURLOPT_URL, $this->get_base_url() . $this->get_path() );
		}

		// HTTP Method
		if ( ( $method = $this->get_option( CURLOPT_CUSTOMREQUEST ) ) ) {
			$this->method = strtoupper( $method );
		} elseif ( $this->get_option( CURLOPT_NOBODY ) ) {
			$this->method = 'HEAD';
		} elseif ( $this->get_option( CURLOPT_POST ) ) {
			$this->method = 'POST';
		} elseif ( $this->get_option( CURLOPT_PUT ) ) {
			$this->method = 'PUT';
		}

		// Set current timestamp
		$this->set_header( 'x-ms-date', $this->x_ms_date );

		// Set curent version
		$this->set_header( 'x-ms-version', $this->x_ms_version );

		// Execute Task 1: Create a Canonical Request for Signature (2018-03-28)
		$canonical_url = $this->prepare_canonical_request( $this->method, $this->path, $this->query, $this->headers );

		// Execute Task 2: Create a String to Sign for Signature (2018-03-28)
		$string_to_sign = $this->prepare_string_to_sign( $canonical_url );

		// Execute Task 2: Calculate the Azure Signature (2018-03-28)
		$signature = $this->calculate_signature( $string_to_sign );

		// Set authorization
		$this->set_raw_header( 'Authorization', $this->build_authorization_string( $signature ) );

		// Apply cURL headers
		$http_headers = array();
		foreach ( $this->headers as $name => $value ) {
			$http_headers[] = "$name: $value";
		}

		// Apply cURL raw headers
		foreach ( $this->raw_headers as $name => $value ) {
			$http_headers[] = "$name: $value";
		}

		$this->set_option( CURLOPT_HTTPHEADER, $http_headers );
		$this->set_option( CURLOPT_SSL_VERIFYPEER, $this->get_ssl() );

		// Apply cURL options
		foreach ( $this->options as $name => $value ) {
			curl_setopt( $this->handler, $name, $value );
		}

		// HTTP request
		$response = curl_exec( $this->handler );
		if ( $response === false ) {
			if ( ( $errno = curl_errno( $this->handler ) ) ) {
				throw new Ai1wmke_Connect_Exception( sprintf( __( 'Unable to connect to Microsoft Azure Storage. Error code: %s. <a href="https://help.servmask.com/knowledgebase/microsoft-azure-storage-error-codes/#%s" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ), $errno, $errno ) );
			}
		}

		// HTTP headers
		if ( $this->get_option( CURLOPT_HEADER ) ) {
			$headers  = substr( $response, 0, curl_getinfo( $this->handler, CURLINFO_HEADER_SIZE ) );
			$response = substr( $response, curl_getinfo( $this->handler, CURLINFO_HEADER_SIZE ) );
		}

		// Disable libxml errors
		libxml_use_internal_errors( true );

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$http_code = curl_getinfo( $this->handler, CURLINFO_HTTP_CODE );
		if ( $http_code === 429 ) {
			throw new Ai1wmke_Rate_Limit_Exception( sprintf( __( 'Too many requests. Please try again later. Error code: %s <a href="https://help.servmask.com/knowledgebase/microsoft-azure-storage-error-codes/#%s" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ), $http_code, $http_code ) );
		}

		if ( $http_code >= 500 ) {
			throw new Ai1wmke_Internal_Server_Error_Exception( sprintf( __( 'Internal Server Error. Please try again later. Error code: %s <a href="https://help.servmask.com/knowledgebase/microsoft-azure-storage-error-codes/#%s" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ), $http_code, $http_code ) );
		}

		if ( $http_code >= 400 ) {
			if ( ( $data = simplexml_load_string( $response ) ) ) {
				if ( isset( $data->Code ) ) {
					switch ( $data->Code ) {
						case 'AuthenticationFailed':
							if ( isset( $data->Message ) ) {
								throw new Ai1wmke_Authentication_Failed_Exception( sprintf( __( 'The secret key that you have provided is incorrect. %s. <a href="https://help.servmask.com/knowledgebase/microsoft-azure-storage-error-codes/#AuthenticationFailed" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ), $data->Message ) );
							} else {
								throw new Ai1wmke_Authentication_Failed_Exception( sprintf( __( 'The secret key that you have provided is incorrect. Error code: %s. <a href="https://help.servmask.com/knowledgebase/microsoft-azure-storage-error-codes/#AuthenticationFailed" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ), $data->Code ) );
							}

						case 'InvalidResourceName':
							if ( isset( $data->Message ) ) {
								throw new Ai1wmke_Invalid_Resource_Name_Exception( sprintf( __( 'The share name contains invalid characters. %s. <a href="https://help.servmask.com/knowledgebase/microsoft-azure-storage-error-codes/#InvalidResourceName" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ), $data->Message ) );
							} else {
								throw new Ai1wmke_Invalid_Resource_Name_Exception( sprintf( __( 'The share name contains invalid characters. Error code: %s. <a href="https://help.servmask.com/knowledgebase/microsoft-azure-storage-error-codes/#InvalidResourceName" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ), $data->Code ) );
							}

						default:
							if ( isset( $data->Message ) ) {
								throw new Ai1wmke_Error_Exception( sprintf( __( '%s. <a href="https://help.servmask.com/knowledgebase/microsoft-azure-storage-error-codes/#%s" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ), $data->Message, $data->Code ) );
							} else {
								throw new Ai1wmke_Error_Exception( sprintf( __( 'Error code: %s. <a href="https://help.servmask.com/knowledgebase/microsoft-azure-storage-error-codes/#%s" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ), $data->Code, $data->Code ) );
							}
					}
				}
			}
		}
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		// HTTP errors
		if ( $http_code >= 400 ) {
			if ( isset( $this->messages[ $http_code ] ) ) {
				throw new Ai1wmke_Error_Exception( sprintf( __( '%s. <a href="https://help.servmask.com/knowledgebase/microsoft-azure-storage-error-codes/#%s" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ), $this->messages[ $http_code ], $http_code ) );
			} else {
				throw new Ai1wmke_Error_Exception( sprintf( __( 'Error code: %s. <a href="https://help.servmask.com/knowledgebase/microsoft-azure-storage-error-codes/#%s" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ), $http_code, $http_code ) );
			}
		}

		// HTTP headers
		if ( $this->get_option( CURLOPT_HEADER ) ) {
			return $this->http_parse_headers( $headers );
		}

		// XML response
		if ( $parse_as_xml ) {
			return simplexml_load_string( $response );
		}

		return $response;
	}

	/**
	 * Parse HTTP headers
	 *
	 * @param  string $headers HTTP headers
	 * @return array
	 */
	public function http_parse_headers( $headers ) {
		$headers = preg_split( '/(\r|\n)+/', $headers, -1, PREG_SPLIT_NO_EMPTY );

		$parse_headers = array();
		for ( $i = 1; $i < count( $headers ); $i++ ) {
			if ( strpos( $headers[ $i ], ':' ) !== false ) {
				list( $key, $raw_value ) = explode( ':', $headers[ $i ], 2 );

				$key   = strtolower( trim( $key ) );
				$value = trim( $raw_value );
				if ( array_key_exists( $key, $parse_headers ) ) {
					// See HTTP RFC Sec 4.2 Paragraph 5
					// http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.2
					// If a header appears more than once, it must also be able to
					// be represented as a single header with a comma-separated
					// list of values.  We transform accordingly.
					$parse_headers[ $key ] .= ',' . $value;
				} else {
					$parse_headers[ $key ] = $value;
				}
			}
		}

		return $parse_headers;
	}

	/**
	 * Destroy cURL handler
	 *
	 * @return void
	 */
	public function __destruct() {
		if ( $this->handler !== null && PHP_VERSION_ID < 80000 ) {
			curl_close( $this->handler );
		}

		$this->handler = null;
	}

	/**
	 * Task 1: Create a Canonical Request for Signature (2018-03-28)
	 *
	 * @param  string $method  HTTP request method (GET, PUT, POST, etc.)
	 * @param  string $path    HTTP path
	 * @param  array  $query   HTTP query
	 * @param  array  $headers HTTP headers
	 * @return string
	 */
	protected function prepare_canonical_request( $method, $path, $query = array(), $headers = array() ) {
		$canonical_url = array();

		// Step 1.1 Start with the HTTP request method (GET, PUT, POST, etc.)
		$canonical_url[] = $method;

		// Step 1.2 Set the canonical headers
		$canonical_headers = array();
		foreach ( $headers as $key => $value ) {
			$canonical_headers[ strtolower( $key ) ] = trim( strval( $value ) );
		}

		// Step 1.3 Sort the canonical headers
		ksort( $canonical_headers );

		// Step 1.4 Add the canonical headers
		foreach ( $canonical_headers as $key => $value ) {
			if ( stripos( $key, 'x-ms-' ) === 0 ) {
				$canonical_url[] = sprintf( '%s:%s', $key, $value );
			} else {
				// In the current version, the Content-Length field must be an empty string if the content length of the request is zero.
				// In version 2014-02-14 and earlier, the content length was included even if zero. See below for more information on the old behavior.
				if ( strcasecmp( $key, 'Content-Length' ) === 0 && intval( $value ) === 0 ) {
					$canonical_url[] = null;
				} else {
					$canonical_url[] = sprintf( '%s', $value );
				}
			}
		}

		// Step 1.5 Add the account name and path
		$canonical_url[] = '/' . $this->account_name . $path;

		// Step 1.6 Set the canonical query parameters
		$canonical_query = array();
		foreach ( $query as $key => $value ) {
			$canonical_query[ strtolower( $key ) ] = trim( $value );
		}

		// Step 1.7 Sort the canonical query parameters
		ksort( $canonical_query );

		// Step 1.8 Add the canonical query string
		foreach ( $canonical_query as $key => $value ) {
			$canonical_url[] = sprintf( '%s:%s', $key, $value );
		}

		return implode( "\n", $canonical_url );
	}

	/**
	 * Task 2: Create a String to Sign for Signature (2018-03-28)
	 *
	 * @param  string $canonical_url Canonical URL
	 * @return string
	 */
	protected function prepare_string_to_sign( $canonical_url ) {
		return mb_convert_encoding( $canonical_url, 'UTF-8', 'ISO-8859-1' );
	}

	/**
	 * Task 3: Calculate the Azure Signature (2018-03-28)
	 *
	 * @param  string $string_to_sign String to sign
	 * @return string
	 */
	protected function calculate_signature( $string_to_sign ) {
		// Step 3.1 Calculate the signature
		$signature = hash_hmac( 'sha256', $string_to_sign, base64_decode( $this->account_key ), true );

		// Step 3.2 Base64 the signature
		$str_base64_signature = base64_encode( $signature );

		return $str_base64_signature;
	}

	/**
	 * Build string for Authorization header
	 *
	 * @param  string $str_signature Signature
	 * @return string
	 */
	protected function build_authorization_string( $str_signature ) {
		return 'SharedKey ' . $this->account_name . ':' . $str_signature;
	}

	/**
	 * Get timestamp (format: Tue, 03 Jul 2018 21:06:23 GMT)
	 *
	 * @return string
	 */
	protected function get_timestamp() {
		return gmdate( 'D, d M Y H:i:s T' );
	}
}
