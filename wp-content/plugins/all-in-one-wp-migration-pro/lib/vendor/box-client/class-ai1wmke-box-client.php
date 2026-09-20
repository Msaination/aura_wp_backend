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

class Ai1wmke_Box_Client {

	const API_URL        = 'https://api.box.com/2.0';
	const API_UPLOAD_URL = 'https://upload.box.com/api/2.0';

	/**
	 * OAuth refresh token
	 *
	 * @var string
	 */
	protected $refresh_token = null;

	/**
	 * OAuth access token
	 *
	 * @var string
	 */
	protected static $access_token = null;

	/**
	 * SSL mode
	 *
	 * @var boolean
	 */
	protected $ssl = null;

	public function __construct( $refresh_token, $ssl = true ) {
		$this->refresh_token = $refresh_token;
		$this->ssl           = $ssl;
	}

	/**
	 * Get folder ID by name
	 *
	 * @param  string  $folder_name Folder name
	 * @param  integer $parent_id   Parent ID
	 * @return integer
	 */
	public function get_folder_id_by_name( $folder_name, $parent_id = 0 ) {
		$api = $this->create_curl();
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_path( "/folders/{$parent_id}/items" );
		$api->set_query( array( 'fields' => 'id,name' ) );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		if ( isset( $response['entries'] ) ) {
			foreach ( $response['entries'] as $entry ) {
				if ( $entry['name'] === $folder_name ) {
					return $entry['id'];
				}
			}
		}
	}

	/**
	 * List folder by ID
	 *
	 * @param  integer $folder_id Folder ID
	 * @return array
	 */
	public function list_folder_by_id( $folder_id ) {
		$api = $this->create_curl();
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_path( "/folders/{$folder_id}/items" );
		$api->set_query( array( 'fields' => 'type,id,name,size,created_at' ) );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		$items = array();
		if ( isset( $response['entries'] ) ) {
			foreach ( $response['entries'] as $entry ) {
				$items[] = array(
					'id'    => isset( $entry['id'] ) ? $entry['id'] : null,
					'name'  => isset( $entry['name'] ) ? $entry['name'] : null,
					'date'  => isset( $entry['created_at'] ) ? strtotime( $entry['created_at'] ) : null,
					'bytes' => isset( $entry['size'] ) ? $entry['size'] : null,
					'ext'   => isset( $entry['name'] ) ? pathinfo( $entry['name'], PATHINFO_EXTENSION ) : null,
					'type'  => isset( $entry['type'] ) ? ( $entry['type'] === 'folder' && pathinfo( $entry['name'], PATHINFO_EXTENSION ) === 'wpress' ? 'file' : $entry['type'] ) : null,
				);
			}
		}

		return $items;
	}

	/**
	 * Create folder
	 *
	 * @param  string  $folder_name Folder name
	 * @param  integer $parent_id   Parent ID
	 * @return integer
	 */
	public function create_folder( $folder_name, $parent_id = 0 ) {
		$api = $this->create_curl();
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_path( '/folders' );
		$api->set_header( 'Content-Type', 'application/json' );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option(
			CURLOPT_POSTFIELDS,
			json_encode(
				array(
					'name'   => $folder_name,
					'parent' => array( 'id' => (string) $parent_id ),
				)
			)
		);

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		if ( isset( $response['id'] ) ) {
			return $response['id'];
		}
	}

	/**
	 * Get folder ID by ID
	 *
	 * @param  string $folder_id Folder ID
	 * @return string
	 */
	public function get_folder_id_by_id( $folder_id ) {
		$api = $this->create_curl();
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_path( "/folders/{$folder_id}" );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			$response = array();
		}

		if ( isset( $response['id'] ) ) {
			return $response['id'];
		}
	}

	/**
	 * Get folder name by ID
	 *
	 * @param  string $folder_id Folder ID
	 * @return string
	 */
	public function get_folder_name_by_id( $folder_id ) {
		$api = $this->create_curl();
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_path( "/folders/{$folder_id}" );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			$response = array();
		}

		if ( isset( $response['name'] ) ) {
			return $response['name'];
		}
	}

	/**
	 * Delete folder
	 *
	 * @param  integer $folder_id Folder ID
	 * @return boolean
	 */
	public function delete_folder( $folder_id ) {
		$api = $this->create_curl();
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_path( "/folders/{$folder_id}" );
		$api->set_query( array( 'recursive' => 'true' ) );
		$api->set_option( CURLOPT_CUSTOMREQUEST, 'DELETE' );

		try {
			$api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Download file
	 *
	 * @param  resource $file_path File stream
	 * @param  integer  $file_id   File ID
	 * @return boolean
	 */
	public function download_file( $file_stream, $file_id ) {
		$api = $this->create_curl();
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_option( CURLOPT_FILE, $file_stream );
		$api->set_path( "/files/{$file_id}/content" );

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
	 * @param  resource $file_path File stream
	 * @param  integer  $file_id   File ID
	 * @return boolean
	 */
	public function download_file_chunk( $file_stream, $file_id ) {
		$api = $this->create_curl();
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_path( "/files/{$file_id}/content" );

		try {
			$file_chunk_data = $api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		// Copy file chunk data into file stream
		if ( fwrite( $file_stream, $file_chunk_data ) === false ) {
			throw new Ai1wmke_Error_Exception( __( 'Unable to save the file from Box', AI1WMKE_PLUGIN_NAME ) );
		}

		return true;
	}

	/**
	 * Upload file
	 *
	 * @param  string  $file_data File data
	 * @param  string  $file_name File name
	 * @param  integer $parent_id Parent ID
	 * @return array
	 */
	public function upload_file( $file_data, $file_name, $parent_id = 0 ) {
		$boundary = uniqid();

		// Raw request
		$post  = sprintf( "--%s\r\n", $boundary );
		$post .= sprintf( "Content-Disposition: form-data; name=\"attributes\"\r\n" );
		$post .= sprintf( "Content-Type: application/json\r\n\r\n" );
		$post .= sprintf( "%s\r\n", json_encode( array( 'name' => $file_name, 'parent' => array( 'id' => $parent_id ) ) ) );
		$post .= sprintf( "--%s\r\n", $boundary );
		$post .= sprintf( "Content-Disposition: form-data; name=\"file\"; filename=\"%s\"\r\n", $file_name );
		$post .= sprintf( "Content-Type: application/octet-stream\r\n\r\n" );
		$post .= sprintf( "%s\r\n", $file_data );
		$post .= sprintf( "--%s\r\n", $boundary );

		// Upload file
		$api = $this->create_curl();
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_UPLOAD_URL );
		$api->set_path( '/files/content' );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option( CURLOPT_POSTFIELDS, $post );
		$api->set_header( 'Content-Length', strlen( $post ) );
		$api->set_header( 'Content-Type', sprintf( 'multipart/form-data; boundary="%s"', $boundary ) );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		return $response;
	}

	/**
	 * Upload file chunk
	 *
	 * @param  string  $file_chunk_data   File chunk data
	 * @param  string  $file_name         File name
	 * @param  integer $parent_id         Parent ID
	 * @param  integer $file_chunk_number File chunk number
	 * @return array
	 */
	public function upload_file_chunk( $file_chunk_data, $file_name, $parent_id = 0, $file_chunk_number = 0 ) {
		$boundary = uniqid();

		// Set file name
		$file_name = sprintf( '%s.%d', $file_name, $file_chunk_number );

		// Raw request
		$post  = sprintf( "--%s\r\n", $boundary );
		$post .= sprintf( "Content-Disposition: form-data; name=\"attributes\"\r\n" );
		$post .= sprintf( "Content-Type: application/json\r\n\r\n" );
		$post .= sprintf( "%s\r\n", json_encode( array( 'name' => $file_name, 'parent' => array( 'id' => $parent_id ) ) ) );
		$post .= sprintf( "--%s\r\n", $boundary );
		$post .= sprintf( "Content-Disposition: form-data; name=\"file\"; filename=\"%s\"\r\n", $file_name );
		$post .= sprintf( "Content-Type: application/octet-stream\r\n\r\n" );
		$post .= sprintf( "%s\r\n", $file_chunk_data );
		$post .= sprintf( "--%s\r\n", $boundary );

		// Upload file
		$api = $this->create_curl();
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_UPLOAD_URL );
		$api->set_path( '/files/content' );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option( CURLOPT_POSTFIELDS, $post );
		$api->set_header( 'Content-Length', strlen( $post ) );
		$api->set_header( 'Content-Type', sprintf( 'multipart/form-data; boundary="%s"', $boundary ) );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		return $response;
	}

	/**
	 * Get account info
	 *
	 * @return array
	 */
	public function get_account_info() {
		$api = $this->create_curl();
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_path( '/users/me' );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		return $response;
	}

	/**
	 * Revoke token
	 *
	 * @return boolean
	 */
	public function revoke() {
		$api = $this->create_curl();
		$api->set_ssl( $this->ssl );
		$api->set_base_url( AI1WMKE_BOX_REVOKE_URL );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option(
			CURLOPT_POSTFIELDS,
			json_encode(
				array(
					'token' => $this->refresh_token,
				)
			)
		);

		try {
			$api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Get access token
	 *
	 * @return string
	 */
	public function get_access_token() {
		// Check static cache AND expiration time (fixes WP-CLI token expiration issue)
		if ( static::$access_token && time() < get_option( 'ai1wmke_box_access_token_expires_in', false ) ) {
			return static::$access_token;
		}

		// Check database AND expiration time
		if ( time() < get_option( 'ai1wmke_box_access_token_expires_in', false ) && ( static::$access_token = get_option( 'ai1wmke_box_access_token', false ) ) ) {
			return static::$access_token;
		}

		$api = $this->create_curl();
		$api->set_header( 'Content-Type', 'application/json' );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( AI1WMKE_BOX_REFRESH_URL );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option(
			CURLOPT_POSTFIELDS,
			json_encode(
				array(
					'token' => $this->refresh_token,
				)
			)
		);

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		if ( isset( $response['access_token'] ) ) {
			static::$access_token = $response['access_token'];
			update_option( 'ai1wmke_box_access_token', $response['access_token'] );
		}

		if ( isset( $response['expires_in'] ) ) {
			update_option( 'ai1wmke_box_access_token_expires_in', time() + ( $response['expires_in'] - 10 * 60 ) );
		}

		if ( isset( $response['refresh_token'] ) && $response['refresh_token'] !== $this->refresh_token ) {
			$this->refresh_token = $response['refresh_token'];
			update_option( 'ai1wmke_box_token', $response['refresh_token'] );
		}

		return static::$access_token;
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
	 * @return Ai1wmke_Box_Curl
	 */
	protected function create_curl() {
		return new Ai1wmke_Box_Curl();
	}
}
