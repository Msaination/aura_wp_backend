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

class Ai1wmke_Mega_Curl {

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
	protected $path = null;

	/**
	 * Base query
	 *
	 * @var array
	 */
	protected $query = array();

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
	protected $headers = array( 'User-Agent' => 'All-in-One WP Migration' );

	/**
	 * Lower-cased response headers captured during make_request.
	 *
	 * @var array<string, string>
	 */
	protected $response_headers = array();

	/**
	 * Guard against infinite hashcash retry loop.
	 *
	 * @var bool
	 */
	protected $hashcash_retried = false;

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
			throw new Ai1wmke_Error_Exception( __( 'Mega Extension requires PHP cURL extension. <a href="https://help.servmask.com/knowledgebase/curl-missing-in-php-installation/" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );
		}

		if ( ! extension_loaded( 'bcmath' ) ) {
			throw new Ai1wmke_Error_Exception( __( 'Mega Extension requires PHP BCMath extension. <a href="https://help.servmask.com/knowledgebase/bcmath-missing-in-php-installation/" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );
		}

		if ( ! extension_loaded( 'openssl' ) && ! extension_loaded( 'mcrypt' ) ) {
			if ( version_compare( PHP_VERSION, '5.4.0', '>=' ) ) {
				throw new Ai1wmke_Error_Exception( __( 'Mega Extension requires PHP OpenSSL extension. <a href="https://help.servmask.com/knowledgebase/openssl-missing-in-php-installation/" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );
			} else {
				throw new Ai1wmke_Error_Exception( __( 'Mega Extension requires PHP Mcrypt extension. <a href="https://help.servmask.com/knowledgebase/mcrypt-missing-in-php-installation/" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );
			}
		}

		// Default configuration
		$this->set_option( CURLOPT_HEADER, false );
		$this->set_option( CURLOPT_RETURNTRANSFER, true );
		$this->set_option( CURLOPT_FOLLOWLOCATION, true );
		$this->set_option( CURLOPT_SSL_VERIFYHOST, false );
		$this->set_option( CURLOPT_SSL_VERIFYPEER, false );
		$this->set_option( CURLOPT_CONNECTTIMEOUT, 120 );
		$this->set_option( CURLOPT_TIMEOUT, 0 );

		// Enable WP proxy
		$proxy = new WP_HTTP_Proxy();
		if ( $proxy->is_enabled() ) {
			$this->set_option( CURLOPT_PROXY, $proxy->host() );
			$this->set_option( CURLOPT_PROXYPORT, $proxy->port() );
			if ( $proxy->use_authentication() ) {
				$this->set_option( CURLOPT_PROXYUSERPWD, $proxy->authentication() );
			}
		}
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
		return $this->base_url;
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
	 * Make cURL request
	 *
	 * @param  boolean $parse_as_json JSON parse
	 * @return mixed
	 */
	public function make_request( $parse_as_json = false ) {
		$this->handler = curl_init();

		// Set base URL
		if ( $this->get_query() ) {
			$this->set_option( CURLOPT_URL, $this->get_base_url() . $this->get_path() . '?' . build_query( $this->get_query() ) );
		} else {
			$this->set_option( CURLOPT_URL, $this->get_base_url() . $this->get_path() );
		}

		// Apply cURL headers
		$http_headers = array();
		foreach ( $this->headers as $name => $value ) {
			$http_headers[] = "$name: $value";
		}

		$this->set_option( CURLOPT_HTTPHEADER, $http_headers );

		// Capture response headers (needed for 402 hashcash challenge)
		$this->response_headers = array();
		$headers_ref            = &$this->response_headers;
		curl_setopt(
			$this->handler,
			CURLOPT_HEADERFUNCTION,
			function ( $ch, $header ) use ( &$headers_ref ) {
				if ( strpos( $header, ':' ) !== false ) {
					list( $name, $value )                       = explode( ':', $header, 2 );
					$headers_ref[ strtolower( trim( $name ) ) ] = trim( $value );
				}
				return strlen( $header );
			}
		);

		// Apply cURL options
		foreach ( $this->options as $name => $value ) {
			curl_setopt( $this->handler, $name, $value );
		}

		// HTTP request
		$response = curl_exec( $this->handler );
		if ( $response === false ) {
			if ( ( $errno = curl_errno( $this->handler ) ) ) {
				throw new Ai1wmke_Connect_Exception( sprintf( __( 'Unable to connect to Mega. Error code: %s. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#%s" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ), $errno, $errno ) );
			}
		}

		// HTTP headers
		if ( $this->get_option( CURLOPT_HEADER ) ) {
			$headers  = substr( $response, 0, curl_getinfo( $this->handler, CURLINFO_HEADER_SIZE ) );
			$response = substr( $response, curl_getinfo( $this->handler, CURLINFO_HEADER_SIZE ) );
		}

		// Handle errors
		$http_code = curl_getinfo( $this->handler, CURLINFO_HTTP_CODE );
		if ( $http_code === 429 ) {
			throw new Ai1wmke_Rate_Limit_Exception( sprintf( __( 'Too many requests. Please try again later. Error code: %s <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#%s" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ), $http_code, $http_code ) );
		}

		if ( $http_code >= 500 ) {
			throw new Ai1wmke_Internal_Server_Error_Exception( sprintf( __( 'Internal Server Error. Please try again later. Error code: %s <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#%s" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ), $http_code, $http_code ) );
		}

		// Mega's proof-of-work throttling: 402 + X-Hashcash header means compute
		// the PoW and retry once with the solution in the X-Hashcash request header.
		if ( $http_code === 402 && ! $this->hashcash_retried ) {
			$hashcash_challenge = $this->get_response_header( 'x-hashcash' );
			if ( $hashcash_challenge !== null ) {
				$this->hashcash_retried = true;
				$this->set_header( 'X-Hashcash', Ai1wmke_Mega_Utils::generate_hashcash_token( $hashcash_challenge ) );
				if ( $this->handler !== null && PHP_VERSION_ID < 80000 ) {
					curl_close( $this->handler );
				}
				$this->handler = null;

				return $this->make_request( $parse_as_json );
			}
		}

		if ( $http_code >= 200 ) {
			if ( ( $data = (array) json_decode( $response, true ) ) ) {
				if ( isset( $data[0] ) && is_int( $data[0] ) && $data[0] !== 0 ) {
					switch ( $data[0] ) {
						case -1:
							throw new Ai1wmke_Error_Exception( __( 'Internal error. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#internal-error" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

						case -2:
							throw new Ai1wmke_Bad_Arguments_Exception( __( 'Bad arguments. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#bad-arguments" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

						case -3:
							throw new Ai1wmke_Error_Exception( __( 'Request failed, retry with exponential backoff. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#request-failed-retry-with-exponential-backoff" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

						case -4:
							throw new Ai1wmke_Error_Exception( __( 'Too many requests, slow down. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#too-many-requests-slow-down" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

						case -5:
							throw new Ai1wmke_Error_Exception( __( 'Request failed permanently. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#request-failed-permanently" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

						case -6:
							throw new Ai1wmke_Error_Exception( __( 'Too many requests for this resource. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#too-many-requests-for-this-resource" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

						case -7:
							throw new Ai1wmke_Error_Exception( __( 'Resource access out of rage. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#resource-access-out-of-range" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

						case -8:
							throw new Ai1wmke_Error_Exception( __( 'Resource expired. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#resource-expired" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

						case -9:
							throw new Ai1wmke_Resource_Does_Not_Exist_Exception( __( 'Resource does not exist. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#resource-does-not-exist" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

						case -10:
							throw new Ai1wmke_Error_Exception( __( 'Circular linkage. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#circular-linkage" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

						case -11:
							throw new Ai1wmke_Error_Exception( __( 'Access denied. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#access-denied" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

						case -12:
							throw new Ai1wmke_Error_Exception( __( 'Resource already exists. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#resource-already-exists" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

						case -13:
							throw new Ai1wmke_Error_Exception( __( 'Request incomplete. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#request-incomplete" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

						case -14:
							throw new Ai1wmke_Cryptographic_Error_Exception( __( 'Cryptographic error. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#cryptographic-error" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

						case -15:
							throw new Ai1wmke_Error_Exception( __( 'Bad session ID. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#bad-session-id" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

						case -16:
							throw new Ai1wmke_Error_Exception( __( 'Resource administratively blocked. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#resource-administratively-blocked" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

						case -17:
							throw new Ai1wmke_Error_Exception( __( 'Quota exceeded. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#quota-exceeded" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

						case -18:
							throw new Ai1wmke_Error_Exception( __( 'Resource temporarily not available. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#resource-temporarily-not-available" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

						case -19:
							throw new Ai1wmke_Error_Exception( __( 'Too many connections on this resource. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#too-many-connections-on-this-resource" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

						case -20:
							throw new Ai1wmke_Error_Exception( __( 'File could not be written to. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#file-could-not-be-written-to" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

						case -21:
							throw new Ai1wmke_Error_Exception( __( 'File could not be read from. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#file-could-not-be-read-from" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

						case -22:
							throw new Ai1wmke_Error_Exception( __( 'Invalid or missing application key. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#invalid-or-missing-application-key" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

						case -23:
							throw new Ai1wmke_Error_Exception( __( 'SSL verification failed. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#ssl-verification-failed" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

						case -24:
							throw new Ai1wmke_Error_Exception( __( 'Not enough quota. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#not-enough-quota" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

						case -26:
							throw new Ai1wmke_Error_Exception( __( 'Multi-factor authentication required. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#multi-factor-authentication-required" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

						default:
							throw new Ai1wmke_Error_Exception( sprintf( __( 'Error code: %s. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#%s" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ), $data[0], $data[0] ) );
					}
				}
			}
		}

		// HTTP errors
		if ( $http_code >= 400 ) {
			if ( isset( $this->messages[ $http_code ] ) ) {
				throw new Ai1wmke_Error_Exception( sprintf( __( '%s. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#%s" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ), $this->messages[ $http_code ], $http_code ) );
			} else {
				throw new Ai1wmke_Error_Exception( sprintf( __( 'Error code: %s. <a href="https://help.servmask.com/knowledgebase/mega-error-codes/#%s" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ), $http_code, $http_code ) );
			}
		}

		// HTTP headers
		if ( $this->get_option( CURLOPT_HEADER ) ) {
			return $this->http_parse_headers( $headers );
		}

		// JSON response
		if ( $parse_as_json ) {
			return json_decode( $response, true );
		}

		return $response;
	}

	/**
	 * Get a captured response header value by lower-cased name.
	 *
	 * @param  string      $name Lower-cased header name.
	 * @return string|null
	 */
	protected function get_response_header( $name ) {
		if ( array_key_exists( $name, $this->response_headers ) ) {
			return $this->response_headers[ $name ];
		}
		return null;
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
}
