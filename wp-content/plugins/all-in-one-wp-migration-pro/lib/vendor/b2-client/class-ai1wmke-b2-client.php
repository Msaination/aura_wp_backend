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

class Ai1wmke_B2_Client {

	const API_URL = 'https://api.backblazeb2.com';

	/**
	 * Backblaze B2 key ID
	 *
	 * @var string
	 */
	protected $key_id = null;

	/**
	 * Backblaze B2 account ID
	 *
	 * @var string
	 */
	protected $account_id = null;

	/**
	 * Backblaze B2 bucket ID
	 *
	 * @var string
	 */
	protected $bucket_id = null;

	/**
	 * Backblaze B2 application key
	 *
	 * @var string
	 */
	protected $application_key = null;

	/**
	 * Authorization token
	 *
	 * @var string
	 */
	protected $authorization_token = null;

	/**
	 * The base URL to use for all API calls except for uploading and downloading files
	 *
	 * @var string
	 */
	protected $api_url = null;

	/**
	 * The URL that can be used to upload files to this bucket
	 *
	 * @var string
	 */
	protected $upload_url = null;

	/**
	 * The base URL to use for downloading files
	 *
	 * @var string
	 */
	protected $download_url = null;

	public function __construct( $key_id, $application_key ) {
		$this->key_id          = $key_id;
		$this->application_key = $application_key;
	}

	/**
	 * Authorize account using provided account ID and application key
	 *
	 * @return void
	 */
	public function authorize_account() {
		$api = $this->create_curl();
		$api->set_base_url( self::API_URL );
		$api->set_path( '/b2api/v3/b2_authorize_account' );
		$api->set_basic_authorization( base64_encode( $this->key_id . ':' . $this->application_key ) );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		// Set account ID
		if ( isset( $response['accountId'] ) ) {
			$this->load_account_id( $response['accountId'] );
		}

		// Set authorization token
		if ( isset( $response['authorizationToken'] ) ) {
			$this->load_authorization_token( $response['authorizationToken'] );
		}

		// Set API URL
		if ( isset( $response['apiInfo']['storageApi']['apiUrl'] ) ) {
			$this->load_api_url( $response['apiInfo']['storageApi']['apiUrl'] );
		}

		// Set download URL
		if ( isset( $response['apiInfo']['storageApi']['downloadUrl'] ) ) {
			$this->load_download_url( $response['apiInfo']['storageApi']['downloadUrl'] );
		}

		// Set bucket ID
		if ( isset( $response['apiInfo']['storageApi']['bucketId'] ) ) {
			$this->load_bucket_id( $response['apiInfo']['storageApi']['bucketId'] );
		}
	}

	/**
	 * Load account ID
	 *
	 * @param  string $account_id Account ID
	 * @return void
	 */
	public function load_account_id( $account_id ) {
		$this->account_id = $account_id;
	}

	/**
	 * Load bucket ID
	 *
	 * @param  string $bucket_id Bucket ID
	 * @return void
	 */
	public function load_bucket_id( $bucket_id ) {
		$this->bucket_id = $bucket_id;
	}

	/**
	 * Load authorization token
	 *
	 * @param  string $authorization_token Authorization token
	 * @return void
	 */
	public function load_authorization_token( $authorization_token ) {
		$this->authorization_token = $authorization_token;
	}

	/**
	 * Load API URL
	 *
	 * @param  string $api_url API URL
	 * @return void
	 */
	public function load_api_url( $api_url ) {
		$this->api_url = $api_url;
	}

	/**
	 * Load upload URL
	 *
	 * @param  string $upload_url Upload URL
	 * @return void
	 */
	public function load_upload_url( $upload_url ) {
		$this->upload_url = $upload_url;
	}

	/**
	 * Load download URL
	 *
	 * @param  string $download_url Download URL
	 * @return void
	 */
	public function load_download_url( $download_url ) {
		$this->download_url = $download_url;
	}

	/**
	 * Get download URL
	 *
	 * @return string Download URL
	 */
	public function get_download_url() {
		return $this->download_url;
	}

	/**
	 * List the Backblaze B2 buckets
	 *
	 * @return array
	 */
	public function list_buckets() {
		$api = $this->create_curl();
		$api->set_base_url( $this->api_url );
		$api->set_path( '/b2api/v3/b2_list_buckets' );
		$api->set_authorization_token( $this->authorization_token );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option(
			CURLOPT_POSTFIELDS,
			json_encode(
				array(
					'accountId'   => $this->account_id,
					'bucketId'    => $this->bucket_id,
					'bucketTypes' => array( 'all' ),
				)
			)
		);

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Unauthorized_Exception $e ) {
			// listBuckets
		}

		$buckets = array();
		if ( isset( $response['buckets'] ) ) {
			foreach ( $response['buckets'] as $bucket ) {
				$buckets[ $bucket['bucketId'] ] = $bucket['bucketName'];
			}
		}

		return $buckets;
	}

	/**
	 * Add a new bucket
	 *
	 * @param  string $bucket_name Bucket name
	 * @return string
	 */
	public function create_bucket( $bucket_name ) {
		$api = $this->create_curl();
		$api->set_base_url( $this->api_url );
		$api->set_path( '/b2api/v3/b2_create_bucket' );
		$api->set_authorization_token( $this->authorization_token );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option(
			CURLOPT_POSTFIELDS,
			json_encode(
				array(
					'accountId'  => $this->account_id,
					'bucketName' => $bucket_name,
					'bucketType' => 'allPrivate',
				)
			)
		);

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		if ( isset( $response['bucketId'] ) ) {
			return $response['bucketId'];
		}
	}

	/**
	 * Delete a given bucket (only buckets that contain no version of any files can be deleted)
	 *
	 * @param  string  $bucket_id Bucket ID
	 * @return boolean
	 */
	public function delete_bucket( $bucket_id ) {
		$api = $this->create_curl();
		$api->set_base_url( $this->api_url );
		$api->set_path( '/b2api/v3/b2_delete_bucket' );
		$api->set_authorization_token( $this->authorization_token );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option(
			CURLOPT_POSTFIELDS,
			json_encode(
				array(
					'accountId' => $this->account_id,
					'bucketId'  => $bucket_id,
				)
			)
		);

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Prepares for uploading the parts of a large file
	 *
	 * @param  string $file_name File name
	 * @param  string $bucket_id Bucket ID
	 * @return string
	 */
	public function start_large_file( $file_name, $bucket_id ) {
		$api = $this->create_curl();
		$api->set_base_url( $this->api_url );
		$api->set_path( '/b2api/v3/b2_start_large_file' );
		$api->set_authorization_token( $this->authorization_token );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option(
			CURLOPT_POSTFIELDS,
			json_encode(
				array(
					'bucketId'    => $bucket_id,
					'fileName'    => $this->sanitize_path( $this->rawurlencode_path( $this->left_trim_forward_slash( $file_name ) ) ),
					'contentType' => 'application/octet-stream',
				)
			)
		);

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		if ( isset( $response['fileId'] ) ) {
			return $response['fileId'];
		}
	}

	/**
	 * Get an URL to use for uploading files
	 *
	 * @param  string $file_id File ID
	 * @return array
	 */
	public function get_upload_part_url( $file_id ) {
		$api = $this->create_curl();
		$api->set_base_url( $this->api_url );
		$api->set_path( '/b2api/v3/b2_get_upload_part_url' );
		$api->set_authorization_token( $this->authorization_token );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option(
			CURLOPT_POSTFIELDS,
			json_encode(
				array(
					'fileId' => $file_id,
				)
			)
		);

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		return $response;
	}

	/**
	 * Get an URL to upload a single file
	 *
	 * @param  string $bucket_id Bucket ID
	 * @return array
	 */
	public function get_upload_url( $bucket_id ) {
		$api = $this->create_curl();
		$api->set_base_url( $this->api_url );
		$api->set_path( '/b2api/v3/b2_get_upload_url' );
		$api->set_authorization_token( $this->authorization_token );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option(
			CURLOPT_POSTFIELDS,
			json_encode(
				array(
					'bucketId' => $bucket_id,
				)
			)
		);

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		return $response;
	}

	/**
	 * Uploads a whole file to Backblaze B2
	 *
	 * @param  string  $file_data File data
	 * @param  integer $file_name File name
	 * @return string
	 */
	public function upload_file( $file_data, $file_name ) {
		$api = $this->create_curl();
		$api->set_base_url( $this->upload_url );
		$api->set_authorization_token( $this->authorization_token );
		$api->set_header( 'X-Bz-File-Name', $this->sanitize_path( $this->rawurlencode_path( $this->left_trim_forward_slash( $file_name ) ) ) );
		$api->set_header( 'Content-Type', 'application/octet-stream' );
		$api->set_header( 'Content-Length', strlen( $file_data ) );
		$api->set_header( 'X-Bz-Content-Sha1', sha1( $file_data ) );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option( CURLOPT_POSTFIELDS, $file_data );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		if ( isset( $response['fileId'] ) ) {
			return $response['fileId'];
		}
	}

	/**
	 * Uploads one part of a large file to Backblaze B2, using an file ID obtained from b2_start_large_file
	 *
	 * @param  string  $file_chunk_data   File chunk data
	 * @param  integer $file_chunk_number File chunk number
	 * @return string
	 */
	public function upload_part( $file_chunk_data, $file_chunk_number = 1 ) {
		$api = $this->create_curl();
		$api->set_base_url( $this->upload_url );
		$api->set_authorization_token( $this->authorization_token );
		$api->set_header( 'X-Bz-Part-Number', intval( $file_chunk_number ) );
		$api->set_header( 'Content-Length', strlen( $file_chunk_data ) );
		$api->set_header( 'X-Bz-Content-Sha1', sha1( $file_chunk_data ) );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option( CURLOPT_POSTFIELDS, $file_chunk_data );

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		if ( isset( $response['contentSha1'] ) ) {
			return $response['contentSha1'];
		}
	}

	/**
	 * Converts the parts that have been uploaded into a single Backblaze B2 file
	 *
	 * @param  array  $file_chunks File chunks
	 * @param  string $file_id     File ID
	 * @return string
	 */
	public function finish_large_file( $file_chunks, $file_id ) {
		$api = $this->create_curl();
		$api->set_base_url( $this->api_url );
		$api->set_path( '/b2api/v3/b2_finish_large_file' );
		$api->set_authorization_token( $this->authorization_token );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option(
			CURLOPT_POSTFIELDS,
			json_encode(
				array(
					'fileId'        => $file_id,
					'partSha1Array' => $file_chunks,
				)
			)
		);

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		if ( isset( $response['fileId'] ) ) {
			return $response['fileId'];
		}
	}

	/**
	 * Lists the names of all files in a bucket, starting at a given name
	 *
	 * @param  string $bucket_id   Bucket ID
	 * @param  string $folder_path Folder path
	 * @return array
	 */
	public function list_file_names( $bucket_id, $folder_path = null ) {
		$api = $this->create_curl();
		$api->set_base_url( $this->api_url );
		$api->set_authorization_token( $this->authorization_token );
		$api->set_path( '/b2api/v3/b2_list_file_names' );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option(
			CURLOPT_POSTFIELDS,
			json_encode(
				array(
					'bucketId'     => $bucket_id,
					'maxFileCount' => 10000,
					'delimiter'    => '/',
					'prefix'       => $folder_path,
				)
			)
		);

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		$items = array();
		if ( isset( $response['files'] ) ) {
			foreach ( $response['files'] as $file ) {
				$items[] = array(
					'id'    => isset( $file['fileId'] ) ? $file['fileId'] : null,
					'name'  => isset( $file['fileName'] ) ? basename( $file['fileName'] ) : null,
					'path'  => isset( $file['fileName'] ) ? $file['fileName'] : null,
					'date'  => isset( $file['uploadTimestamp'] ) && intval( $file['uploadTimestamp'] ) > 0 ? intval( $file['uploadTimestamp'] / 1000 ) : null, // Always 0 when the action is "folder"
					'bytes' => isset( $file['contentLength'] ) && isset( $file['action'] ) && $file['action'] !== 'folder' ? $file['contentLength'] : null, // Always 0 when the action is "folder"
					'type'  => isset( $file['action'] ) ? $file['action'] : null,
				);
			}
		}

		return $items;
	}

	/**
	 * Deletes one version of a file from Backblaze B2
	 *
	 * @param  string  $file_id   File ID
	 * @param  string  $file_name File name
	 * @return boolean
	 */
	public function delete_file( $file_id, $file_name ) {
		$api = $this->create_curl();
		$api->set_base_url( $this->api_url );
		$api->set_path( '/b2api/v3/b2_delete_file_version' );
		$api->set_authorization_token( $this->authorization_token );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option(
			CURLOPT_POSTFIELDS,
			json_encode(
				array(
					'fileId'   => $file_id,
					'fileName' => $this->sanitize_path( $this->rawurlencode_path( $this->left_trim_forward_slash( $file_name ) ) ),
				)
			)
		);

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Downloads one file from Backblaze B2 by ID
	 *
	 * @param  resource $file_stream      File stream
	 * @param  string   $file_id          File ID
	 * @param  integer  $file_range_start File range start
	 * @param  integer  $file_range_end   File range end
	 * @return boolean
	 */
	public function download_file_by_id( $file_stream, $file_id, $file_range_start = 0, $file_range_end = 0 ) {
		$api = $this->create_curl();
		$api->set_base_url( $this->download_url );
		$api->set_path( '/b2api/v3/b2_download_file_by_id' );
		$api->set_query( $this->rawurlencode_query( array( 'fileId' => $file_id ) ) );
		$api->set_authorization_token( $this->authorization_token );

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
			throw new Ai1wmke_Error_Exception( __( 'Unable to save the file from Backblaze B2', AI1WMKE_PLUGIN_NAME ) );
		}

		return true;
	}

	/**
	 * Downloads one file from Backblaze B2 by name
	 *
	 * @param  resource $file_stream      File stream
	 * @param  string   $file_name        File name
	 * @param  integer  $file_range_start File range start
	 * @param  integer  $file_range_end   File range end
	 * @return boolean
	 */
	public function download_file_by_name( $file_stream, $file_name, $file_range_start = 0, $file_range_end = 0 ) {
		$api = $this->create_curl();
		$api->set_base_url( $this->download_url );
		$api->set_path( $this->sanitize_path( $this->rawurlencode_path( $file_name ) ) );
		$api->set_authorization_token( $this->authorization_token );

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
			throw new Ai1wmke_Error_Exception( __( 'Unable to save the file from Backblaze B2', AI1WMKE_PLUGIN_NAME ) );
		}

		return true;
	}

	/**
	 * Downloads file content from Backblaze B2
	 *
	 * @param  string $file_name File name
	 * @return string
	 */
	public function download_file_content( $file_name ) {
		$api = $this->create_curl();
		$api->set_base_url( $this->download_url );
		$api->set_path( $this->sanitize_path( $this->rawurlencode_path( $file_name ) ) );
		$api->set_authorization_token( $this->authorization_token );

		try {
			$response = $api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		return $response;
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
	 * @return Ai1wmke_B2_Curl
	 */
	protected function create_curl() {
		return new Ai1wmke_B2_Curl();
	}
}
