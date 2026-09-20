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

class Ai1wmke_Azure_Storage_Client {

	const API_FILE_URL = 'https://%s.file.core.windows.net';

	/**
	 * Microsoft Azure Storage account name
	 *
	 * @var string
	 */
	protected $account_name = null;

	/**
	 * Microsoft Azure Storage account key
	 *
	 * @var string
	 */
	protected $account_key = null;

	/**
	 * SSL mode
	 *
	 * @var boolean
	 */
	protected $ssl = null;

	public function __construct( $account_name, $account_key ) {
		$this->account_name = $account_name;
		$this->account_key  = $account_key;
	}

	/**
	 * Create file share
	 *
	 * @param  string  $share_name Share name
	 * @return boolean
	 */
	public function create_share( $share_name ) {
		$api = $this->create_curl();
		$api->set_account_name( $this->account_name );
		$api->set_account_key( $this->account_key );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_FILE_URL );
		$api->set_path( "/{$share_name}" );
		$api->set_query( array( 'restype' => 'share' ) );
		$api->set_option( CURLOPT_CUSTOMREQUEST, 'PUT' );
		$api->set_header( 'Content-Length', 0 );

		try {
			$api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		return true;
	}

	/**
	 * List file shares
	 *
	 * @return array
	 */
	public function get_shares() {
		$api = $this->create_curl();
		$api->set_account_name( $this->account_name );
		$api->set_account_key( $this->account_key );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_FILE_URL );
		$api->set_query( array( 'comp' => 'list' ) );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$shares = array();
		if ( isset( $response->Shares->Share ) ) {
			foreach ( $response->Shares->Share as $share ) {
				$shares[] = strval( $share->Name );
			}
		}
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		return $shares;
	}

	/**
	 * Checks if a given file share name is available
	 *
	 * @param  string  $share_name Share name
	 * @return boolean
	 */
	public function is_share_available( $share_name ) {
		$api = $this->create_curl();
		$api->set_account_name( $this->account_name );
		$api->set_account_key( $this->account_key );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_FILE_URL );
		$api->set_path( "/{$share_name}" );
		$api->set_query( array( 'restype' => 'share', 'comp' => 'metadata' ) );

		try {
			$api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * List the objects in a share
	 *
	 * @param  string $share_name  Share name
	 * @param  string $folder_path Folder path
	 * @return array
	 */
	public function get_objects_by_share( $share_name, $folder_path = null ) {
		$api = $this->create_curl();
		$api->set_account_name( $this->account_name );
		$api->set_account_key( $this->account_key );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_FILE_URL );
		$api->set_path( $this->sanitize_path( sprintf( '/%s/%s', $share_name, $this->rawurlencode_path( $folder_path ) ) ) );
		$api->set_query( array( 'restype' => 'directory', 'comp' => 'list' ) );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$objects = array();
		if ( isset( $response->Entries->File ) ) {
			foreach ( $response->Entries->File as $file ) {
				if ( isset( $file->Name ) && ( $name = strval( $file->Name ) ) ) {
					if ( ( $file_properties = $this->get_file_properties( $this->sanitize_path( sprintf( '/%s/%s', $folder_path, $name ) ), $share_name ) ) ) {
						$objects[] = array(
							'name'  => $name,
							'path'  => $this->sanitize_path( sprintf( '/%s/%s', $folder_path, $name ) ),
							'date'  => isset( $file_properties['last-modified'] ) ? strtotime( $file_properties['last-modified'] ) : null,
							'bytes' => isset( $file_properties['content-length'] ) ? strval( $file_properties['content-length'] ) : null,
							'type'  => 'file',
						);
					}
				}
			}
		}
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		if ( isset( $response->Entries->Directory ) ) {
			foreach ( $response->Entries->Directory as $directory ) {
				if ( isset( $directory->Name ) && ( $name = strval( $directory->Name ) ) ) {
					$objects[] = array(
						'name' => $name,
						'path' => $this->sanitize_path( sprintf( '/%s/%s', $folder_path, $name ) ),
						'date' => null,
						'type' => 'folder',
					);
				}
			}
		}
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		return $objects;
	}

	/**
	 * Get file properties
	 *
	 * @param  string $file_path  File path
	 * @param  string $share_name Share name
	 * @return array
	 */
	public function get_file_properties( $file_path, $share_name ) {
		$api = $this->create_curl();
		$api->set_account_name( $this->account_name );
		$api->set_account_key( $this->account_key );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_FILE_URL );
		$api->set_path( $this->sanitize_path( sprintf( '/%s/%s', $share_name, $this->rawurlencode_path( $file_path ) ) ) );
		$api->set_option( CURLOPT_HEADER, true );
		$api->set_option( CURLOPT_NOBODY, true );

		try {
			$response = $api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			$response = array();
		}

		return $response;
	}

	/**
	 * Checks if a given folder path exists
	 *
	 * @param  string  $folder_path Folder path
	 * @param  string  $share_name Share name
	 * @return boolean
	 */
	public function folder_exists( $folder_path, $share_name ) {
		$api = $this->create_curl();
		$api->set_account_name( $this->account_name );
		$api->set_account_key( $this->account_key );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_FILE_URL );
		$api->set_path( $this->sanitize_path( sprintf( '/%s/%s', $share_name, $this->rawurlencode_path( $folder_path ) ) ) );
		$api->set_query( array( 'restype' => 'directory', 'comp' => 'metadata' ) );
		$api->set_option( CURLOPT_HEADER, true );
		$api->set_option( CURLOPT_NOBODY, true );

		try {
			$api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Create folder
	 *
	 * @param  string  $folder_path Folder path
	 * @param  string  $share_name Share name
	 * @return boolean
	 */
	public function create_folder( $folder_path, $share_name ) {
		// Optimization: do nothing if folder is there
		if ( $this->folder_exists( $folder_path, $share_name ) ) {
			return true;
		}

		// Ensure that parent folder exists
		$parent = dirname( $folder_path );
		if ( $parent !== '.' && ! $this->folder_exists( $parent, $share_name ) ) {
			$this->create_folder( $parent, $share_name );
		}

		// When all requirements are met create the folder
		$api = $this->create_curl();
		$api->set_account_name( $this->account_name );
		$api->set_account_key( $this->account_key );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_FILE_URL );
		$api->set_path( $this->sanitize_path( sprintf( '/%s/%s', $share_name, $this->rawurlencode_path( $folder_path ) ) ) );
		$api->set_query( array( 'restype' => 'directory' ) );
		$api->set_option( CURLOPT_CUSTOMREQUEST, 'PUT' );
		$api->set_header( 'Content-Length', 0 );

		try {
			$api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		return true;
	}

	/**
	 * Download file chunk
	 *
	 * @param  resource $file_stream      File stream
	 * @param  string   $file_path        File path
	 * @param  string   $share_name       Share name
	 * @param  integer  $file_range_start File range start
	 * @param  integer  $file_range_end   File range end
	 * @return boolean
	 */
	public function download_file_chunk( $file_stream, $file_path, $share_name, $file_range_start = 0, $file_range_end = 0 ) {
		$api = $this->create_curl();
		$api->set_account_name( $this->account_name );
		$api->set_account_key( $this->account_key );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_FILE_URL );
		$api->set_path( $this->sanitize_path( sprintf( '/%s/%s', $share_name, $this->rawurlencode_path( $file_path ) ) ) );
		$api->set_header( 'x-ms-range', sprintf( 'bytes=%d-%d', $file_range_start, $file_range_end ) );

		try {
			$file_chunk_data = $api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		// Copy file chunk data into file stream
		if ( fwrite( $file_stream, $file_chunk_data ) === false ) {
			throw new Ai1wmke_Error_Exception( __( 'Unable to save the file from Microsoft Azure Storage', AI1WMKE_PLUGIN_NAME ) );
		}

		return true;
	}

	/**
	 * Create upload session
	 *
	 * @param  string  $file_path  File path
	 * @param  integer $file_size  File size
	 * @param  string  $share_name Share name
	 * @return boolean
	 */
	public function create_upload_session( $file_path, $file_size, $share_name ) {
		$api = $this->create_curl();
		$api->set_account_name( $this->account_name );
		$api->set_account_key( $this->account_key );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_FILE_URL );
		$api->set_path( $this->sanitize_path( sprintf( '/%s/%s', $share_name, $this->rawurlencode_path( $file_path ) ) ) );
		$api->set_option( CURLOPT_CUSTOMREQUEST, 'PUT' );
		$api->set_header( 'Content-Length', 0 );
		$api->set_header( 'x-ms-content-length', $file_size );
		$api->set_header( 'x-ms-type', 'file' );

		try {
			$api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		return true;
	}

	/**
	 * Upload file chunk
	 *
	 * @param  string  $file_chunk_data  File chunk data
	 * @param  string  $file_path        File path
	 * @param  string  $share_name       Share name
	 * @param  integer $file_range_start File range start
	 * @param  integer $file_range_end   File range end
	 * @return boolean
	 */
	public function upload_file_chunk( $file_chunk_data, $file_path, $share_name, $file_range_start = 0, $file_range_end = 0 ) {
		$api = $this->create_curl();
		$api->set_account_name( $this->account_name );
		$api->set_account_key( $this->account_key );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_FILE_URL );
		$api->set_path( $this->sanitize_path( sprintf( '/%s/%s', $share_name, $this->rawurlencode_path( $file_path ) ) ) );
		$api->set_query( array( 'comp' => 'range' ) );
		$api->set_option( CURLOPT_CUSTOMREQUEST, 'PUT' );
		$api->set_option( CURLOPT_POSTFIELDS, $file_chunk_data );
		$api->set_header( 'Content-Length', strlen( $file_chunk_data ) );
		$api->set_header( 'x-ms-range', sprintf( 'bytes=%d-%d', $file_range_start, $file_range_end ) );
		$api->set_header( 'x-ms-write', 'update' );

		try {
			$api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		return true;
	}

	/**
	 * Deletes a file
	 *
	 * @param  string  $file_path  File path
	 * @param  string  $share_name Share name
	 * @return boolean
	 */
	public function remove_file( $file_path, $share_name ) {
		$api = $this->create_curl();
		$api->set_account_name( $this->account_name );
		$api->set_account_key( $this->account_key );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_FILE_URL );
		$api->set_path( $this->sanitize_path( sprintf( '/%s/%s', $share_name, $this->rawurlencode_path( $file_path ) ) ) );
		$api->set_option( CURLOPT_CUSTOMREQUEST, 'DELETE' );
		$api->set_header( 'Content-Length', 0 );

		try {
			$api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			return true;
		}

		return false;
	}

	/**
	 * Sanitize URL path
	 *
	 * @param  string $path Base path
	 * @return string
	 */
	public function sanitize_path( $path ) {
		return preg_replace( '#[\\\/]+#', '/', $path );
	}

	/**
	 * Encode URL path
	 *
	 * @param  string $path Base path
	 * @return string
	 */
	public function rawurlencode_path( $path ) {
		return str_replace( '%7E', '~', implode( '/', array_map( 'rawurlencode', preg_split( '/\/+/', $path ) ) ) );
	}

	/**
	 * Encode URL query
	 *
	 * @param  array  $query Base query
	 * @return string
	 */
	public function rawurlencode_query( $query ) {
		return str_replace( '%7E', '~', array_map( 'rawurlencode', array_filter( $query, 'is_scalar' ) ) );
	}

	/**
	 * Create cURL instance
	 *
	 * @return Ai1wmke_Azure_Storage_Curl
	 */
	protected function create_curl() {
		return new Ai1wmke_Azure_Storage_Curl();
	}
}
