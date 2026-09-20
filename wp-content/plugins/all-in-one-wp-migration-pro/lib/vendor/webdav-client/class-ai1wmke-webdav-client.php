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

class Ai1wmke_WebDAV_Client {

	/**
	 * WebDAV server hostname
	 *
	 * @var string
	 */
	protected $hostname = null;

	/**
	 * WebDAV client username
	 *
	 * @var string
	 */
	protected $username = null;

	/**
	 * WebDAV client password
	 *
	 * @var string
	 */
	protected $password = null;

	/**
	 * WebDAV client authentication
	 *
	 * @var string
	 */
	protected $authentication = null;

	/**
	 * WebDAV server directory
	 *
	 * @var string
	 */
	protected $directory = '/';

	/**
	 * WebDAV server port
	 *
	 * @var integer
	 */
	protected $port = 80;

	/**
	 * WebDAV SSL mode
	 *
	 * @var boolean
	 */
	protected $ssl = false;

	/**
	 * WebDAV handler
	 *
	 * @var resource|null
	 */
	protected $handler = null;

	/**
	 * cURL messages
	 *
	 * @var array
	 */
	protected $messages = array(
		// [Informational 1xx]
		100 => '100 Continue',
		101 => '101 Switching Protocols',
		102 => '102 Processing',

		// [Successful 2xx]
		200 => '200 OK',
		201 => '201 Created',
		202 => '202 Accepted',
		203 => '203 Non-Authoritative Information',
		204 => '204 No Content',
		205 => '205 Reset Content',
		206 => '206 Partial Content',
		207 => '207 Multi-Status',
		208 => '208 Already Reported',

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
		422 => '422 Unprocessable Entity',
		423 => '423 Locked',
		424 => '424 Failed Dependency',

		// [Server Error 5xx]
		500 => '500 Internal Server Error',
		501 => '501 Not Implemented',
		502 => '502 Bad Gateway',
		503 => '503 Service Unavailable',
		504 => '504 Gateway Timeout',
		505 => '505 HTTP Version Not Supported',
		507 => '507 Insufficient Storage',
		508 => '508 Loop Detected',
	);

	public function __construct( $type, $hostname, $username, $password, $authentication, $directory, $port ) {
		if ( ! extension_loaded( 'curl' ) ) {
			throw new Ai1wmke_Error_Exception( __( 'WebDAV Extension requires PHP cURL extension. <a href="https://help.servmask.com/knowledgebase/curl-missing-in-php-installation/" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );
		}
		if ( ! extension_loaded( 'libxml' ) ) {
			throw new Ai1wmke_Error_Exception( __( 'WebDAV Extension requires PHP libxml extension. <a href="https://help.servmask.com/knowledgebase/libxml-missing-in-php-installation/" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );
		}

		if ( ! extension_loaded( 'simplexml' ) ) {
			throw new Ai1wmke_Error_Exception( __( 'WebDAV Extension requires PHP SimpleXML extension. <a href="https://help.servmask.com/knowledgebase/simplexml-missing-in-php-installation/" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );
		}

		$this->ssl            = $type === 'webdavs';
		$this->hostname       = $hostname;
		$this->username       = $username;
		$this->password       = $password;
		$this->authentication = $authentication;
		$this->directory      = $directory;
		$this->port           = $port;
	}

	/**
	 * Create folder
	 *
	 * @param  string $folder_path Folder path
	 * @return boolean
	 */
	public function create_folder( $folder_path ) {
		$folder_path = $this->sanitize_path( sprintf( '/%s/%s', $this->directory, $folder_path ) );

		try {
			$this->make_request( $folder_path, array( CURLOPT_CUSTOMREQUEST => 'MKCOL' ) );
		} catch ( Ai1wmke_Error_Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieve files and folders metadata
	 *
	 * @param  string $folder_path Folder path
	 * @return array
	 */
	public function list_folder( $folder_path ) {
		try {
			$response = $this->raw_list_folder( $folder_path );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		$xml = simplexml_load_string( $response );

		$items = array();
		foreach ( $xml->response as $element ) {
			$prop = $element->propstat->prop;
			$name = (string) $element->href;
			$name = trim( $name, '/' );

			if ( strpos( $name, '/' ) !== false ) {
				$parts = explode( '/', $name );
				$name  = end( $parts );
			}

			if ( preg_match( '/(.+?)-(\d+?)-(\d+?)-(.+?)\.wpress/', $name, $matches ) ) {
				$date = isset( $matches[2] ) && isset( $matches[3] ) ? strtotime( "{$matches[2]} {$matches[3]}" ) : null;
			} else {
				$date = strtotime( isset( $prop->creationdate ) ? (string) $prop->creationdate : (string) $prop->getlastmodified );
			}

			$type      = isset( $prop->resourcetype->collection ) ? 'folder' : 'file';
			$ext       = pathinfo( $name, PATHINFO_EXTENSION );
			$is_wpress = $type === 'folder' && $ext === 'wpress';

			$items[] = array(
				'name'  => urldecode( $name ),
				'path'  => $this->sanitize_path( $path = sprintf( '/%s/%s', $folder_path, $name ) ),
				'date'  => $date,
				'bytes' => isset( $prop->getcontentlength ) ? (string) $prop->getcontentlength : ( $is_wpress ? $this->get_folder_size( $path ) : '' ),
				'type'  => $is_wpress ? 'file' : $type,
				'ext'   => $ext,
			);
		}

		// Skip the current folder
		array_shift( $items );
		return $items;
	}

	/**
	 * Retrieve files and folders raw metadata
	 *
	 * @param  string $folder_path Folder path
	 * @return string
	 */
	public function raw_list_folder( $folder_path ) {
		$folder_path = $this->sanitize_path( sprintf( '/%s/%s/', $this->directory, $folder_path ) );

		try {
			$xml = $this->make_request( $folder_path, array( CURLOPT_CUSTOMREQUEST => 'PROPFIND', CURLOPT_HTTPHEADER => array( 'Depth: 1' ) ) );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		// Stripping ns prefixes from XML tags
		$xml = preg_replace( '/<(\w+?):/', '<', $xml );

		return preg_replace( '/<\/(\w+?):/', '</', $xml );
	}

	/**
	 * Get folder size in bytes
	 *
	 * @param  string $folder_path Folder path
	 * @return int
	 */
	public function get_folder_size( $folder_path ) {
		$bytes = 0;

		try {
			$response = $this->raw_list_folder( $folder_path );
		} catch ( Ai1wmke_Error_Exception $e ) {
			return $bytes;
		}

		$xml = simplexml_load_string( $response );

		foreach ( $xml->response as $element ) {
			$name = (string) $element->href;
			if ( $name === $folder_path ) {
				continue;
			}

			$bytes += isset( $element->propstat->prop->getcontentlength ) ? $element->propstat->prop->getcontentlength : 0;
		}

		return $bytes;
	}

	/**
	 * Upload file
	 *
	 * @param  string  $local_file_path  Local file path
	 * @param  string  $remote_file_path Remote file path
	 * @param  integer $file_size        File size
	 * @return boolean
	 */
	public function upload_file( $local_file_path, $remote_file_path, $file_size ) {
		$remote_file_path = $this->sanitize_path( sprintf( '/%s/%s', $this->directory, $remote_file_path ) );

		// Upload file from file stream
		if ( ( $file_stream = fopen( $local_file_path, 'rb' ) ) ) {
			$this->remote_file_path = $remote_file_path;

			try {

				$options = array(
					CURLOPT_PUT        => true,
					CURLOPT_INFILE     => $file_stream,
					CURLOPT_INFILESIZE => $file_size,
				);

				/**
				 * The $handler parameter was added in PHP version 5.5.0 breaking backwards compatibility.
				 * If we are using PHP version lower than 5.5.0, we need to shift the arguments.
				 *
				 * @see http://php.net/manual/en/function.curl-setopt.php#refsect1-function.curl-setopt-changelog
				 */
				if ( version_compare( PHP_VERSION, '5.5.0', '>=' ) ) {
					$options[ CURLOPT_NOPROGRESS ]       = false;
					$options[ CURLOPT_PROGRESSFUNCTION ] = array( $this, 'upload_file_progress_callback_php55' );
				} elseif ( version_compare( PHP_VERSION, '5.3.0', '>=' ) ) {
					$options[ CURLOPT_NOPROGRESS ]       = false;
					$options[ CURLOPT_PROGRESSFUNCTION ] = array( $this, 'upload_file_progress_callback_php53' );
				}

				$this->make_request( $remote_file_path, $options );
			} catch ( Ai1wmke_Error_Exception $e ) {
				throw $e;
			}

			fclose( $file_stream );
		}

		return true;
	}

	/**
	 * Upload file chunk
	 *
	 * @param  string  $file_chunk_data   File chunk data
	 * @param  string  $file_name         File name
	 * @param  string  $remote_folder     Parent ID
	 * @param  integer $file_chunk_number File chunk number
	 * @return array
	 */
	public function upload_file_chunk( $file_chunk_data, $file_name, $remote_folder, $file_chunk_number = 0 ) {

		// Set file name
		$file_name  = sprintf( '%s.%d', $file_name, $file_chunk_number );
		$remote_url = sprintf( '/%s/%s/%s', $this->directory, $remote_folder, $file_name );

		// Copy file chunk data into file chunk stream
		if ( ( $file_chunk_stream = fopen( 'php://temp', 'wb+' ) ) ) {
			if ( ( $file_chunk_size = fwrite( $file_chunk_stream, $file_chunk_data ) ) ) {
				rewind( $file_chunk_stream );

				try {
					$this->make_request(
						$remote_url,
						array(
							CURLOPT_PUT        => true,
							CURLOPT_INFILE     => $file_chunk_stream,
							CURLOPT_INFILESIZE => $file_chunk_size,
						)
					);
				} catch ( Ai1wmke_Error_Exception $e ) {
					throw $e;
				}
			}

			fclose( $file_chunk_stream );
		}

		return true;
	}

	/**
	 * Upload file progress callback (PHP >= 5.5.0)
	 *
	 * @param  resource $handler              cURL handler
	 * @param  integer  $download_file_size   Download file size
	 * @param  integer  $download_file_offset Download file offset
	 * @param  integer  $upload_file_size     Upload file size
	 * @param  integer  $upload_file_offset   Upload file offset
	 * @return integer
	 */
	public function upload_file_progress_callback_php55( $handler, $download_file_size, $download_file_offset, $upload_file_size, $upload_file_size_offset ) {
		if ( $upload_file_size > 0 ) {
			// Get progress
			$upload_file_progress = (int) ( ( $upload_file_size_offset / $upload_file_size ) * 100 );

			// Get file base name
			$upload_file_name = basename( $this->remote_file_path );

			// Get human readable file size
			$upload_file_size = ai1wm_size_format( $upload_file_size );

			// Set progress
			Ai1wm_Status::info(
				sprintf(
					__(
						'<i class="ai1wmke-webdav-icon"></i> ' .
						'Uploading <strong>%s</strong> (%s)<br />%d%% complete',
						AI1WMKE_PLUGIN_NAME
					),
					$upload_file_name,
					$upload_file_size,
					$upload_file_progress
				)
			);
		}

		return 0;
	}

	/**
	 * Upload file progress callback (PHP >= 5.3.0, PHP <= 5.5.0)
	 *
	 * @param  integer  $download_file_size   Download file size
	 * @param  integer  $download_file_offset Download file offset
	 * @param  integer  $upload_file_size     Upload file size
	 * @param  integer  $upload_file_offset   Upload file offset
	 * @return integer
	 */
	public function upload_file_progress_callback_php53( $download_file_size, $download_file_offset, $upload_file_size, $upload_file_size_offset ) {
		if ( $upload_file_size > 0 ) {
			// Get progress
			$upload_file_progress = (int) ( ( $upload_file_size_offset / $upload_file_size ) * 100 );

			// Get file base name
			$upload_file_name = basename( $this->remote_file_path );

			// Get human readable file size
			$upload_file_size = ai1wm_size_format( $upload_file_size );

			// Set progress
			Ai1wm_Status::info(
				sprintf(
					__(
						'<i class="ai1wmke-webdav-icon"></i> ' .
						'Uploading <strong>%s</strong> (%s)<br />%d%% complete',
						AI1WMKE_PLUGIN_NAME
					),
					$upload_file_name,
					$upload_file_size,
					$upload_file_progress
				)
			);
		}

		return 0;
	}

	/**
	 * Download file
	 *
	 * @param  string  $local_file_path  Local file path
	 * @param  string  $remote_file_path Remote file path
	 * @return boolean
	 */
	public function download_file( $local_file_path, $remote_file_path ) {
		$remote_file_path = $this->sanitize_path( sprintf( '/%s/%s', $this->directory, $remote_file_path ) );

		// Download file to file stream
		if ( ( $file_stream = fopen( $local_file_path, 'wb' ) ) ) {
			$this->remote_file_path = $remote_file_path;

			try {

				$options = array( CURLOPT_FILE => $file_stream );

				/**
				 * The $handler parameter was added in PHP version 5.5.0 breaking backwards compatibility.
				 * If we are using PHP version lower than 5.5.0, we need to shift the arguments.
				 *
				 * @see http://php.net/manual/en/function.curl-setopt.php#refsect1-function.curl-setopt-changelog
				 */
				if ( version_compare( PHP_VERSION, '5.5.0', '>=' ) ) {
					$options[ CURLOPT_NOPROGRESS ]       = false;
					$options[ CURLOPT_PROGRESSFUNCTION ] = array( $this, 'download_file_progress_callback_php55' );
				} elseif ( version_compare( PHP_VERSION, '5.3.0', '>=' ) ) {
					$options[ CURLOPT_NOPROGRESS ]       = false;
					$options[ CURLOPT_PROGRESSFUNCTION ] = array( $this, 'download_file_progress_callback_php53' );
				}

				$this->make_request( $remote_file_path, $options );
			} catch ( Ai1wmke_Error_Exception $e ) {
				throw $e;
			}

			fclose( $file_stream );
		}

		return true;
	}

	/**
	 * Download file progress callback (PHP >= 5.5.0)
	 *
	 * @param  resource $handler              cURL handler
	 * @param  integer  $download_file_size   Download file size
	 * @param  integer  $download_file_offset Download file offset
	 * @param  integer  $upload_file_size     Upload file size
	 * @param  integer  $upload_file_offset   Upload file offset
	 * @return integer
	 */
	public function download_file_progress_callback_php55( $handler, $download_file_size, $download_file_offset, $upload_file_size, $upload_file_size_offset ) {
		if ( $download_file_size > 0 ) {
			// Get progress
			$progress = (int) ( ( $download_file_offset / $download_file_size ) * 100 );

			// Set progress
			Ai1wm_Status::progress( $progress );
		}

		return 0;
	}

	/**
	 * Download file progress callback (PHP >= 5.3.0, PHP <= 5.5.0)
	 *
	 * @param  integer  $download_file_size   Download file size
	 * @param  integer  $download_file_offset Download file offset
	 * @param  integer  $upload_file_size     Upload file size
	 * @param  integer  $upload_file_offset   Upload file offset
	 * @return integer
	 */
	public function download_file_progress_callback_php53( $download_file_size, $download_file_offset, $upload_file_size, $upload_file_size_offset ) {
		if ( $download_file_size > 0 ) {
			// Get progress
			$progress = (int) ( ( $download_file_offset / $download_file_size ) * 100 );

			// Set progress
			Ai1wm_Status::progress( $progress );
		}

		return 0;
	}

	/**
	 * Download file chunk
	 *
	 * @param  resource $file_stream      File stream
	 * @param  string   $remote_file_path Remote file path
	 * @return boolean
	 */
	public function download_file_chunk( $file_stream, $remote_file_path ) {
		$remote_file_path = $this->sanitize_path( sprintf( '/%s/%s', $this->directory, $remote_file_path ) );

		try {
			$this->make_request( $remote_file_path, array( CURLOPT_FILE => $file_stream ) );
		} catch ( Ai1wmke_Operation_Timedout_Exception $e ) {
			// This is needed in order to simulate chunked download process
		}

		return true;
	}

	/**
	 * Remove file
	 *
	 * @param  string  $file_path File path
	 * @return boolean
	 */
	public function remove_file( $file_path ) {
		$file_path = $this->sanitize_path( sprintf( '/%s/%s', $this->directory, $file_path ) );

		try {
			$this->make_request( $file_path, array( CURLOPT_CUSTOMREQUEST => 'DELETE' ) );
		} catch ( Ai1wmke_Error_Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Remove folder recursively
	 *
	 * @param  string  $folder_path Folder path
	 * @return boolean
	 */
	public function remove_folder( $folder_path ) {
		// First we are trying to delete folder recursively
		if ( $this->remove_file( $folder_path ) ) {
			return true;
		}

		// If server does not support recursive deletion we need to delete all children first
		$items = $this->list_folder( $folder_path );
		foreach ( $items as $item ) {
			$this->remove_folder( $item['path'] );
		}

		// And finally when all the content deleted, we delete the empty folder
		return $this->remove_file( $folder_path );
	}

	/**
	 * Test WebDAV connection
	 *
	 * @return boolean
	 */
	public function test_connection() {
		try {
			$this->list_folder( '' );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		return true;
	}

	/**
	 * Make cURL request
	 *
	 * @param  string $path    FTP path
	 * @param  array  $options cURL options
	 * @return mixed
	 */
	protected function make_request( $path, $options = array() ) {
		$this->handler = curl_init();

		// Set HTTP URL
		$schema = $this->ssl ? 'https' : 'http';
		curl_setopt( $this->handler, CURLOPT_URL, sprintf( '%s://%s:%d/%s', $schema, $this->hostname, $this->port, $this->left_trim_forward_slash( $path ) ) );

		// Set username and password
		curl_setopt( $this->handler, CURLOPT_USERPWD, sprintf( '%s:%s', $this->username, $this->password ) );

		// Set authentication
		curl_setopt( $this->handler, CURLOPT_HTTPAUTH, $this->get_curl_auth() );

		// Set default configuration
		curl_setopt( $this->handler, CURLOPT_HEADER, false );
		curl_setopt( $this->handler, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $this->handler, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $this->handler, CURLOPT_CONNECTTIMEOUT, 120 );
		curl_setopt( $this->handler, CURLOPT_TIMEOUT, 0 );

		$proxy = new WP_HTTP_Proxy();
		if ( $proxy->is_enabled() ) {
			curl_setopt( $this->handler, CURLOPT_PROXY, $proxy->host() );
			curl_setopt( $this->handler, CURLOPT_PROXYPORT, $proxy->port() );
			if ( $proxy->use_authentication() ) {
				curl_setopt( $this->handler, CURLOPT_PROXYUSERPWD, $proxy->authentication() );
			}
		}

		// Add additional options to connect to WEBDAV with SSL if SSL was selected
		if ( $this->ssl ) {
			curl_setopt( $this->handler, CURLOPT_SSL_VERIFYPEER, true );
			curl_setopt( $this->handler, CURLOPT_CAINFO, __DIR__ . DIRECTORY_SEPARATOR . 'certs' . DIRECTORY_SEPARATOR . 'cacert.pem' );
			curl_setopt( $this->handler, CURLOPT_CAPATH, __DIR__ . DIRECTORY_SEPARATOR . 'certs' );
		}

		// Apply cURL options
		foreach ( $options as $name => $value ) {
			curl_setopt( $this->handler, $name, $value );
		}

		// HTTP request
		$response = curl_exec( $this->handler );
		if ( $response === false ) {
			if ( ( $errno = curl_errno( $this->handler ) ) ) {
				switch ( $errno ) {
					case 6:
					case 7:
						throw new Ai1wmke_Error_Exception( __( 'Unable to connect to WebDAV server. Please check your WebDAV hostname and port settings. <a href="https://help.servmask.com/knowledgebase/webdav-extension-error-codes/#invalid-hostname" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

					case 9:
						throw new Ai1wmke_Error_Exception( __( 'Unable to change WebDAV directory. Please ensure that you have permission on the server. <a href="https://help.servmask.com/knowledgebase/webdav-extension-error-codes/#invalid-directory" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

					case 23:
						throw new Ai1wmke_Write_Error_Exception( __( 'Unable to download file from WebDAV server. Please ensure that you have enough disk space. <a href="https://help.servmask.com/knowledgebase/webdav-extension-error-codes/#write-error" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

					case 28:
						throw new Ai1wmke_Operation_Timedout_Exception( __( 'Connecting to WebDAV server timed out. Please check WebDAV hostname, port, username and password settings. <a href="https://help.servmask.com/knowledgebase/webdav-extension-error-codes/#operation-timedout" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

					case 67:
						throw new Ai1wmke_Error_Exception( __( 'Unable to login to WebDAV server. Please check your username, password and authentication type settings. <a href="https://help.servmask.com/knowledgebase/webdav-extension-error-codes/#invalid-credentials" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );

					default:
						throw new Ai1wmke_Connect_Exception( sprintf( __( 'Unable to connect to WebDAV. Error code: %s. <a href="https://help.servmask.com/knowledgebase/webdav-extension-error-codes/#%s" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ), $errno, $errno ) );
				}
			}
		}

		// HTTP errors
		$http_code = curl_getinfo( $this->handler, CURLINFO_HTTP_CODE );
		if ( $http_code === 429 ) {
			throw new Ai1wmke_Rate_Limit_Exception( sprintf( __( 'Too many requests. Please try again later. Error code: %s <a href="https://help.servmask.com/knowledgebase/webdav-extension-error-codes/#%s" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ), $http_code, $http_code ) );
		}

		if ( $http_code >= 500 ) {
			throw new Ai1wmke_Internal_Server_Error_Exception( sprintf( __( 'Internal Server Error. Please try again later. Error code: %s <a href="https://help.servmask.com/knowledgebase/webdav-extension-error-codes/#%s" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ), $http_code, $http_code ) );
		}

		if ( $http_code >= 300 ) {
			if ( isset( $this->messages[ $http_code ] ) ) {
				throw new Ai1wmke_Error_Exception( sprintf( __( '%s. <a href="https://help.servmask.com/knowledgebase/webdav-extension-error-codes/#%s" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ), $this->messages[ $http_code ], $http_code ) );
			} else {
				throw new Ai1wmke_Error_Exception( sprintf( __( 'Error code: %s. <a href="https://help.servmask.com/knowledgebase/webdav-extension-error-codes/#%s" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ), $http_code, $http_code ) );
			}
		}

		return $response;
	}

	/**
	 * Get CURL HTTP authentication
	 *
	 * @return string
	 */
	public function get_curl_auth() {
		if ( ! $this->authentication ) {
			$this->authentication = AI1WMKE_WEBDAV_AUTHENTICATION;
		}
		switch ( $this->authentication ) {
			case 'basic':
				return CURLAUTH_BASIC;

			case 'digest':
				return CURLAUTH_DIGEST;

			case 'ntlm':
				return CURLAUTH_NTLM;

			default:
				throw new Ai1wmke_Error_Exception( __( 'Unsupported authentication type <a href="https://help.servmask.com/knowledgebase/webdav-extension-error-codes/#unsupported-authentication" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );
		}
	}

	/**
	 * Left trim WebDAV path forward slash
	 *
	 * @param  string $path Base path
	 * @return string
	 */
	public function left_trim_forward_slash( $path ) {
		return ltrim( $path, '/' );
	}

	/**
	 * Sanitize WebDAV path
	 *
	 * @param  string $path Base path
	 * @return string
	 */
	public function sanitize_path( $path ) {
		return preg_replace( '#[\\\/]+#', '/', $path );
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
