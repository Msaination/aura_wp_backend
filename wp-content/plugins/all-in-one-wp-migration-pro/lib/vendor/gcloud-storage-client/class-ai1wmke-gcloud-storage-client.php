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

class Ai1wmke_GCloud_Storage_Client {

	const API_URL         = 'https://storage.googleapis.com/storage/v1/b';
	const API_UPLOAD_URL  = 'https://storage.googleapis.com/upload/storage/v1/b';
	const API_CLOUD_URL   = 'https://cloudresourcemanager.googleapis.com/v1';
	const API_ACCOUNT_URL = 'https://accounts.google.com/o/oauth2';

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
	 * Upload URL
	 *
	 * @var string
	 */
	protected $upload_url = null;

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
	 * Load upload URL
	 *
	 * @param  string $url Upload URL
	 * @return void
	 */
	public function load_upload_url( $url ) {
		$this->upload_url = $url;
	}

	/**
	 * List the Google Cloud Storage projects
	 *
	 * @return array
	 */
	public function get_projects() {
		$api = $this->create_curl();
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_CLOUD_URL );
		$api->set_path( '/projects' );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		$projects = array();
		if ( isset( $response['projects'] ) ) {
			foreach ( $response['projects'] as $project ) {
				$projects[ $project['projectNumber'] ] = $project['projectId'];
			}
		}

		return $projects;
	}

	/**
	 * Check if a given project ID is available
	 *
	 * @param  string  $project_id Project ID
	 * @return boolean
	 */
	public function is_project_available( $project_id ) {
		$api = $this->create_curl();
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_CLOUD_URL );
		$api->set_path( "/projects/{$project_id}" );

		try {
			$api->make_request( true );
		} catch ( Ai1wmke_Permission_Denied_Exception $e ) {
			throw new Ai1wmke_Permission_Denied_Exception( sprintf( __( 'Your project name: %s is already taken or you do not have permissions to access it. <a href="https://help.servmask.com/knowledgebase/google-cloud-storage-error-codes/#403" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ), $project_id ) );
		} catch ( Ai1wmke_Error_Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Add a new project
	 *
	 * @param  string  $project_id Project ID
	 * @return boolean
	 */
	public function create_project( $project_id ) {
		$api = $this->create_curl();
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_CLOUD_URL );
		$api->set_path( '/projects' );
		$api->set_header( 'Content-Type', 'application/json' );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option(
			CURLOPT_POSTFIELDS,
			json_encode(
				array(
					'projectId' => $project_id,
				)
			)
		);

		try {
			$api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		return true;
	}

	/**
	 * List the Google Cloud Storage buckets
	 *
	 * @param  string $project_id Project ID
	 * @return array
	 */
	public function get_buckets( $project_id ) {
		$api = $this->create_curl();
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_query( array( 'project' => $project_id ) );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Permission_Denied_Exception $e ) {
		} catch ( Ai1wmke_Error_Exception $e ) {
		}

		$buckets = array();
		if ( isset( $response['items'] ) ) {
			foreach ( $response['items'] as $bucket ) {
				$buckets[] = $bucket['name'];
			}
		}

		return $buckets;
	}

	/**
	 * Check if a given bucket name is available
	 *
	 * @param  string  $bucket_name Bucket name
	 * @return boolean
	 */
	public function is_bucket_available( $bucket_name ) {
		$api = $this->create_curl();
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_path( "/{$bucket_name}" );

		try {
			$api->make_request( true );
		} catch ( Ai1wmke_Permission_Denied_Exception $e ) {
			throw new Ai1wmke_Permission_Denied_Exception( __( 'Please check your bucket permission. Permission Denied. <a href="https://help.servmask.com/knowledgebase/google-cloud-storage-error-codes/#403" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );
		} catch ( Ai1wmke_Error_Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Add a new bucket
	 *
	 * @param  string  $bucket_name   Bucket name
	 * @param  string  $project_id    Project ID
	 * @param  string  $storage_class Storage class
	 * @return boolean
	 */
	public function create_bucket( $bucket_name, $project_id, $storage_class = null ) {
		$api = $this->create_curl();
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_query( array( 'project' => $project_id ) );
		$api->set_header( 'Content-Type', 'application/json' );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option(
			CURLOPT_POSTFIELDS,
			json_encode(
				array(
					'name'         => $bucket_name,
					'storageClass' => $storage_class,
				)
			)
		);

		try {
			$api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		return true;
	}

	/**
	 * Upload resumable file on Google Cloud Storage
	 *
	 * @param  string  $file_name   File name
	 * @param  integer $file_size   File size
	 * @param  string  $bucket_name Bucket name
	 * @return string
	 */
	public function upload_resumable( $file_name, $file_size, $bucket_name ) {
		$api = $this->create_curl();
		$api->set_header( 'Content-Type', 'application/json' );
		$api->set_header( 'X-Upload-Content-Type', 'application/octet-stream' );
		$api->set_header( 'X-Upload-Content-Length', $file_size );
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_UPLOAD_URL );
		$api->set_path( "/{$bucket_name}/o" );
		$api->set_query( $this->rawurlencode_query( array( 'uploadType' => 'resumable', 'name' => $this->sanitize_path( $this->left_trim_forward_slash( $file_name ) ) ) ) );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option( CURLOPT_HEADER, true );

		try {
			$response = $api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		if ( isset( $response['location'] ) ) {
			return $response['location'];
		}
	}

	/**
	 * Upload file chunk
	 *
	 * @param  string  $file_chunk_data  File chunk data
	 * @param  integer $file_size        File size
	 * @param  integer $file_range_start File range start
	 * @param  integer $file_range_end   File range end
	 * @return array
	 */
	public function upload_file_chunk( $file_chunk_data, $file_size, $file_range_start = 0, $file_range_end = 0 ) {
		$api = $this->create_curl();
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( $this->upload_url );
		$api->set_option( CURLOPT_CUSTOMREQUEST, 'PUT' );
		$api->set_option( CURLOPT_POSTFIELDS, $file_chunk_data );
		$api->set_header( 'Content-Length', strlen( $file_chunk_data ) );
		$api->set_header( 'Content-Range', sprintf( 'bytes %d-%d/%d', $file_range_start, $file_range_end, $file_size ) );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		return $response;
	}

	/**
	 * Upload file
	 *
	 * @param  string $file_data   File data
	 * @param  string $file_name   File name
	 * @param  string $bucket_name Bucket name
	 * @return array
	 */
	public function upload_file( $file_data, $file_name, $bucket_name ) {
		$api = $this->create_curl();
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_UPLOAD_URL );
		$api->set_path( "/{$bucket_name}/o" );
		$api->set_query( $this->rawurlencode_query( array( 'uploadType' => 'media', 'name' => $this->sanitize_path( $this->left_trim_forward_slash( $file_name ) ) ) ) );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option( CURLOPT_POSTFIELDS, $file_data );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		return $response;
	}

	/**
	 * Downloads file from Google Cloud Storage
	 *
	 * @param  resource $file_stream      File stream
	 * @param  string   $file_path        File path
	 * @param  string   $bucket_name      Bucket name
	 * @param  integer  $file_range_start File range start
	 * @param  integer  $file_range_end   File range end
	 * @return boolean
	 */
	public function get_file( $file_stream, $file_path, $bucket_name, $file_range_start = 0, $file_range_end = 0 ) {
		$api = $this->create_curl();
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_path( $this->sanitize_path( sprintf( '/%s/o/%s', $bucket_name, $this->rawurlencode_object( $this->sanitize_path( $this->left_trim_forward_slash( $file_path ) ) ) ) ) );
		$api->set_query( array( 'alt' => 'media' ) );

		// Set range header
		if ( $file_range_end > 0 ) {
			$api->set_header( 'Range', sprintf( 'bytes=%d-%d', $file_range_start, $file_range_end ) );
		}

		try {
			$file_chunk_data = $api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		// Copy file chunk data into file stream
		if ( fwrite( $file_stream, $file_chunk_data ) === false ) {
			throw new Ai1wmke_Error_Exception( __( 'Unable to save the file from Google Cloud Storage', AI1WMKE_PLUGIN_NAME ) );
		}

		return true;
	}

	/**
	 * Downloads file content from Google Cloud Storage
	 *
	 * @param  string $file_path   File path
	 * @param  string $bucket_name Bucket name
	 * @return string
	 */
	public function get_file_content( $file_path, $bucket_name ) {
		$api = $this->create_curl();
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_path( $this->sanitize_path( sprintf( '/%s/o/%s', $bucket_name, $this->rawurlencode_object( $this->sanitize_path( $this->left_trim_forward_slash( $file_path ) ) ) ) ) );
		$api->set_query( array( 'alt' => 'media' ) );

		try {
			$response = $api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		return $response;
	}

	/**
	 * List the objects in a bucket
	 *
	 * @param  string $bucket_name Bucket name
	 * @param  array  $query       Query options
	 * @return array
	 */
	public function get_objects_by_bucket( $bucket_name, $query = array() ) {
		$api = $this->create_curl();
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_path( "/{$bucket_name}/o" );
		$api->set_query( $this->rawurlencode_query( $query ) );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
		}

		$objects = array();
		if ( isset( $response['items'] ) ) {
			foreach ( $response['items'] as $item ) {
				if ( substr( $item['name'], -1 ) !== '/' ) {
					$objects[] = array(
						'id'    => isset( $item['id'] ) ? $item['id'] : null,
						'name'  => isset( $item['name'] ) ? basename( $item['name'] ) : null,
						'path'  => isset( $item['name'] ) ? $item['name'] : null,
						'date'  => isset( $item['timeCreated'] ) ? strtotime( $item['timeCreated'] ) : null,
						'bytes' => isset( $item['size'] ) ? $item['size'] : null,
						'type'  => 'file',
					);
				}
			}
		}

		if ( isset( $response['prefixes'] ) ) {
			foreach ( $response['prefixes'] as $item ) {
				$objects[] = array(
					'name' => isset( $item ) ? basename( $item ) : null,
					'path' => isset( $item ) ? $item : null,
					'date' => null,
					'type' => 'folder',
				);
			}
		}

		return $objects;
	}

	/**
	 * Deletes a file or folder
	 *
	 * @param  string $file_path   File path
	 * @param  string $bucket_name Bucket name
	 * @return boolean
	 */
	public function delete( $file_path, $bucket_name ) {
		$api = $this->create_curl();
		$api->set_access_token( $this->get_access_token() );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_URL );
		$api->set_path( $this->sanitize_path( sprintf( '/%s/o/%s', $bucket_name, $this->rawurlencode_object( $this->sanitize_path( $this->left_trim_forward_slash( $file_path ) ) ) ) ) );
		$api->set_option( CURLOPT_CUSTOMREQUEST, 'DELETE' );

		try {
			$api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Get account info
	 */
	public function get_account_info() {
	}

	/**
	 * Revoke token
	 *
	 * @return boolean
	 */
	public function revoke() {
		$api = $this->create_curl();
		$api->set_ssl( $this->ssl );
		$api->set_base_url( self::API_ACCOUNT_URL );
		$api->set_path( '/revoke' );
		$api->set_query( $this->rawurlencode_query( array( 'token' => $this->refresh_token ) ) );

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
		if ( static::$access_token && time() < get_option( 'ai1wmke_gcloud_storage_access_token_expires_in', false ) ) {
			return static::$access_token;
		}

		// Check database AND expiration time
		if ( time() < get_option( 'ai1wmke_gcloud_storage_access_token_expires_in', false ) && ( static::$access_token = get_option( 'ai1wmke_gcloud_storage_access_token', false ) ) ) {
			return static::$access_token;
		}

		$api = $this->create_curl();
		$api->set_header( 'Content-Type', 'application/json' );
		$api->set_ssl( $this->ssl );
		$api->set_base_url( AI1WMKE_GCLOUD_STORAGE_REFRESH_URL );
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
			update_option( 'ai1wmke_gcloud_storage_access_token', $response['access_token'] );
		}

		if ( isset( $response['expires_in'] ) ) {
			update_option( 'ai1wmke_gcloud_storage_access_token_expires_in', time() + ( $response['expires_in'] - 10 * 60 ) );
		}

		if ( isset( $response['refresh_token'] ) && $response['refresh_token'] !== $this->refresh_token ) {
			$this->refresh_token = $response['refresh_token'];
			update_option( 'ai1wmke_gcloud_storage_token', $response['refresh_token'] );
		}

		return static::$access_token;
	}

	/**
	 * Left trim URL path forward slash
	 *
	 * @param  string $path Base path
	 * @return string
	 */
	public function left_trim_forward_slash( $path ) {
		return ltrim( $path, '/' );
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
	 * Encode URL object name
	 *
	 * @param  string $object Object name
	 * @return string
	 */
	public function rawurlencode_object( $object ) {
		return str_replace( '%7E', '~', rawurlencode( $object ) );
	}

	/**
	 * Encode URL query
	 *
	 * @param  array $query Base query
	 * @return array
	 */
	public function rawurlencode_query( $query ) {
		return array_map( array( $this, 'rawurlencode_object' ), array_filter( $query, 'is_scalar' ) );
	}

	/**
	 * Create cURL instance
	 *
	 * @return Ai1wmke_GCloud_Storage_Curl
	 */
	protected function create_curl() {
		return new Ai1wmke_GCloud_Storage_Curl();
	}
}
