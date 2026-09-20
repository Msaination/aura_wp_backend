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

class Ai1wmke_DigitalOcean_Client {

	const API_URL        = 'https://%s.digitaloceanspaces.com';
	const API_BUCKET_URL = 'https://%s.%s.digitaloceanspaces.com';
	const API_REGION_URL = 'https://%s.%s.digitaloceanspaces.com';

	/**
	 * DigitalOcean Spaces access key
	 *
	 * @var string
	 */
	protected $access_key = null;

	/**
	 * DigitalOcean Spaces secret key
	 *
	 * @var string
	 */
	protected $secret_key = null;

	public function __construct( $access_key, $secret_key ) {
		$this->access_key = $access_key;
		$this->secret_key = $secret_key;
	}

	/**
	 * Get account info
	 *
	 * @return mixed
	 */
	public function get_account_info() {
	}

	/**
	 * Add a new bucket
	 *
	 * @param  string  $bucket_name Bucket name
	 * @param  string  $region_name Region name
	 * @return boolean
	 */
	public function create_bucket( $bucket_name, $region_name = null ) {
		if ( $region_name ) {
			$post = sprintf( '<CreateBucketConfiguration><LocationConstraint>%s</LocationConstraint></CreateBucketConfiguration>', $region_name );
		} else {
			$post = null;
		}

		// Create new bucket
		$api = $this->create_curl();
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_bucket_name( $bucket_name );
		$api->set_option( CURLOPT_CUSTOMREQUEST, 'PUT' );
		$api->set_option( CURLOPT_POSTFIELDS, $post );
		$api->set_header( 'Content-Type', 'application/xml' );
		$api->set_header( 'Content-Length', 0 );

		// Set region name
		if ( $region_name ) {
			$api->set_region_name( $region_name );
			$api->set_base_url( self::API_REGION_URL );
		} else {
			$api->set_base_url( self::API_BUCKET_URL );
		}

		try {
			$api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		return true;
	}

	/**
	 * Get region name
	 *
	 * @param  string $bucket_name Bucket name
	 * @param  string $region_name Region name
	 * @return string
	 */
	public function get_bucket_region( $bucket_name, $region_name = null ) {
		$api = $this->create_curl();
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_base_url( self::API_URL );
		$api->set_path( "/{$bucket_name}" );
		$api->set_query( $this->rawurlencode_query( array( 'location' => '' ) ) );

		// Set region name
		if ( $region_name ) {
			$api->set_region_name( $region_name );
		}

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_No_Such_Bucket_Exception $e ) {
		}

		if ( isset( $response ) ) {
			return strval( $response );
		}
	}

	/**
	 * Get regions
	 *
	 * @return array
	 */
	public function get_regions() {
		// TODO: When DigitalOcean Spaces provides a method that returns all regions
		// we should refactor the code below with proper API request
		$regions = array(
			'nyc3' => 'New York 3',
			'ams3' => 'Amsterdam 3',
			'atl1' => 'Atlanta 1',
			'syd1' => 'Sydney 1',
			'sfo2' => 'San Francisco 2',
			'sfo3' => 'San Francisco 3',
			'tor1' => 'Toronto 1',
			'lon1' => 'London 1',
			'fra1' => 'Frankfurt 1',
			'sgp1' => 'Singapore 1',
			'blr1' => 'Bangalore 1',
		);

		return $regions;
	}

	/**
	 * Check if a given bucket name is available
	 *
	 * @param  string  $bucket_name Bucket name
	 * @param  string  $region_name Region name
	 * @return boolean
	 */
	public function is_bucket_available( $bucket_name, $region_name = null ) {
		$api = $this->create_curl();
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_bucket_name( $bucket_name );
		$api->set_option( CURLOPT_HEADER, true );
		$api->set_option( CURLOPT_NOBODY, true );

		// Set region name
		if ( $region_name ) {
			$api->set_region_name( $region_name );
			$api->set_base_url( self::API_REGION_URL );
		} else {
			$api->set_base_url( self::API_BUCKET_URL );
		}

		try {
			$api->make_request();
		} catch ( Ai1wmke_Access_Denied_Exception $e ) {
			throw new Ai1wmke_Access_Denied_Exception( __( 'Please check your bucket policy. Access Denied. <a href="https://help.servmask.com/knowledgebase/digitalocean-error-codes/#AccessDenied" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );
		} catch ( Ai1wmke_All_Access_Disabled_Exception $e ) {
			throw new Ai1wmke_All_Access_Disabled_Exception( __( 'Please check your bucket policy. All Access Disabled. <a href="https://help.servmask.com/knowledgebase/digitalocean-error-codes/#AllAccessDisabled" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );
		} catch ( Ai1wmke_Error_Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Remove a given bucket (all objects in the bucket must be removed prior to removing the bucket)
	 *
	 * @param  string  $bucket_name Bucket name
	 * @param  string  $region_name Region name
	 * @return boolean
	 */
	public function remove_bucket( $bucket_name, $region_name = null ) {
		$api = $this->create_curl();
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_bucket_name( $bucket_name );
		$api->set_option( CURLOPT_CUSTOMREQUEST, 'DELETE' );

		// Set region name
		if ( $region_name ) {
			$api->set_region_name( $region_name );
			$api->set_base_url( self::API_REGION_URL );
		} else {
			$api->set_base_url( self::API_BUCKET_URL );
		}

		try {
			$api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * List the DigitalOcean Spaces buckets
	 *
	 * @param  string $region_name Region name
	 * @return array
	 */
	public function get_buckets( $region_name = null ) {
		$api = $this->create_curl();
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_base_url( self::API_URL );

		// Set region name
		if ( $region_name ) {
			$api->set_region_name( $region_name );
		}

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Invalid_Access_Key_Id_Exception $e ) {
			throw new Ai1wmke_Invalid_Access_Key_Id_Exception( __( 'The access key that you have provided is incorrect. <a href="https://help.servmask.com/knowledgebase/digitalocean-error-codes/#InvalidAccessKeyId" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );
		} catch ( Ai1wmke_Signature_Does_Not_Match_Exception $e ) {
			throw new Ai1wmke_Signature_Does_Not_Match_Exception( __( 'The secret key that you have provided is incorrect. <a href="https://help.servmask.com/knowledgebase/digitalocean-error-codes/#SignatureDoesNotMatch" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );
		} catch ( Ai1wmke_Access_Denied_Exception $e ) {
			// In case user doesn't have ListAllMyBuckets permission
		} catch ( Ai1wmke_All_Access_Disabled_Exception $e ) {
		}

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$buckets = array();
		if ( isset( $response->Buckets->Bucket ) ) {
			foreach ( $response->Buckets->Bucket as $bucket ) {
				$buckets[] = strval( $bucket->Name );
			}
		}
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		return $buckets;
	}

	/**
	 * List the objects in a bucket
	 *
	 * @param  string $bucket_name Bucket name
	 * @param  string $region_name Region name
	 * @param  array  $query       Query options
	 * @return array
	 */
	public function get_objects_by_bucket( $bucket_name, $region_name = null, $query = array() ) {
		$api = $this->create_curl();
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_bucket_name( $bucket_name );
		$api->set_query( $this->rawurlencode_query( $query ) );

		// Set region name
		if ( $region_name ) {
			$api->set_region_name( $region_name );
			$api->set_base_url( self::API_REGION_URL );
		} else {
			$api->set_base_url( self::API_BUCKET_URL );
		}

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
		}

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$objects = array();
		if ( isset( $response->Contents ) ) {
			foreach ( $response->Contents as $item ) {
				if ( substr( $item->Key, -1 ) !== '/' ) {
					$objects[] = array(
						'name'  => isset( $item->Key ) ? basename( $item->Key ) : null,
						'path'  => isset( $item->Key ) ? strval( $item->Key ) : null,
						'date'  => isset( $item->LastModified ) ? strtotime( $item->LastModified ) : null,
						'bytes' => isset( $item->Size ) ? strval( $item->Size ) : null,
						'type'  => 'file',
					);
				}
			}
		}
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		if ( isset( $response->CommonPrefixes ) ) {
			foreach ( $response->CommonPrefixes as $item ) {
				$objects[] = array(
					'name' => isset( $item->Prefix ) ? basename( $item->Prefix ) : null,
					'path' => isset( $item->Prefix ) ? strval( $item->Prefix ) : null,
					'date' => null,
					'type' => 'folder',
				);
			}
		}
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		return $objects;
	}

	/**
	 * Upload file
	 *
	 * @param  string  $file_data   File data
	 * @param  string  $file_path   File path
	 * @param  string  $bucket_name Bucket name
	 * @param  string  $region_name Region name
	 * @return boolean
	 */
	public function upload_file( $file_data, $file_path, $bucket_name, $region_name = null ) {
		$api = $this->create_curl();
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_bucket_name( $bucket_name );
		$api->set_path( $this->rawurlencode_path( "/{$file_path}" ) );
		$api->set_option( CURLOPT_CUSTOMREQUEST, 'PUT' );
		$api->set_option( CURLOPT_POSTFIELDS, $file_data );
		$api->set_header( 'Expect', '100-continue' );
		$api->set_header( 'Content-Type', 'application/octet-stream' );

		// Set region name
		if ( $region_name ) {
			$api->set_region_name( $region_name );
			$api->set_base_url( self::API_REGION_URL );
		} else {
			$api->set_base_url( self::API_BUCKET_URL );
		}

		try {
			$api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		return true;
	}

	/**
	 * Upload multipart file on DigitalOcean Spaces
	 *
	 * @param  string $file_path     File path
	 * @param  string $bucket_name   Bucket name
	 * @param  string $region_name   Region name
	 * @param  string $storage_class Storage class
	 * @param  string $encryption    Bucket encryption
	 * @return string
	 */
	public function upload_multipart( $file_path, $bucket_name, $region_name = null, $storage_class = null, $encryption = null ) {
		$api = $this->create_curl();
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_bucket_name( $bucket_name );
		$api->set_path( $this->rawurlencode_path( "/{$file_path}" ) );
		$api->set_query( $this->rawurlencode_query( array( 'uploads' => '' ) ) );
		$api->set_option( CURLOPT_POST, true );
		$api->set_header( 'Content-Type', 'application/octet-stream' );
		$api->set_raw_header( 'Content-Length', null );
		$api->set_raw_header( 'Transfer-Encoding', null );

		// Set storage class
		if ( $storage_class ) {
			$api->set_header( 'x-amz-storage-class', $storage_class );
		}

		// Set bucket encryption
		if ( $encryption ) {
			$api->set_header( 'x-amz-server-side-encryption', $encryption );
		}

		// Set region name
		if ( $region_name ) {
			$api->set_region_name( $region_name );
			$api->set_base_url( self::API_REGION_URL );
		} else {
			$api->set_base_url( self::API_BUCKET_URL );
		}

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		if ( isset( $response->UploadId ) ) {
			return strval( $response->UploadId );
		}
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	/**
	 * Upload file chunk
	 *
	 * @param  string  $file_chunk_data   File chunk data
	 * @param  string  $file_path         File path
	 * @param  string  $upload_id         Upload ID
	 * @param  string  $bucket_name       Bucket name
	 * @param  string  $region_name       Region name
	 * @param  integer $file_chunk_number File chunk number
	 * @return string
	 */
	public function upload_file_chunk( $file_chunk_data, $file_path, $upload_id, $bucket_name, $region_name = null, $file_chunk_number = 1 ) {
		$api = $this->create_curl();
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_bucket_name( $bucket_name );
		$api->set_path( $this->rawurlencode_path( "/{$file_path}" ) );
		$api->set_query( $this->rawurlencode_query( array( 'partNumber' => $file_chunk_number, 'uploadId' => $upload_id ) ) );
		$api->set_option( CURLOPT_CUSTOMREQUEST, 'PUT' );
		$api->set_option( CURLOPT_POSTFIELDS, $file_chunk_data );
		$api->set_option( CURLOPT_HEADER, true );
		$api->set_header( 'Content-Type', 'application/octet-stream' );

		// Set region name
		if ( $region_name ) {
			$api->set_region_name( $region_name );
			$api->set_base_url( self::API_REGION_URL );
		} else {
			$api->set_base_url( self::API_BUCKET_URL );
		}

		try {
			$response = $api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		if ( isset( $response['etag'] ) ) {
			return $response['etag'];
		}
	}

	/**
	 * Upload complete file on DigitalOcean Spaces
	 *
	 * @param  array  $file_chunks File chunks
	 * @param  string $file_path   File path
	 * @param  string $upload_id   Upload ID
	 * @param  string $bucket_name Bucket name
	 * @param  string $region_name Region name
	 * @return object
	 */
	public function upload_complete( $file_chunks, $file_path, $upload_id, $bucket_name, $region_name = null ) {
		// Combine parts
		$post = '<CompleteMultipartUpload>';

		// Add file chunk ETag
		foreach ( $file_chunks as $i => $etag ) {
			$post .= sprintf( '<Part><PartNumber>%d</PartNumber><ETag>%s</ETag></Part>', $i + 1, $etag );
		}

		$post .= '</CompleteMultipartUpload>';

		// Upload complete
		$api = $this->create_curl();
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_bucket_name( $bucket_name );
		$api->set_path( $this->rawurlencode_path( "/{$file_path}" ) );
		$api->set_query( $this->rawurlencode_query( array( 'uploadId' => $upload_id ) ) );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option( CURLOPT_POSTFIELDS, $post );
		$api->set_header( 'Content-Type', 'application/octet-stream' );

		// Set region name
		if ( $region_name ) {
			$api->set_region_name( $region_name );
			$api->set_base_url( self::API_REGION_URL );
		} else {
			$api->set_base_url( self::API_BUCKET_URL );
		}

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		return $response;
	}

	/**
	 * Download file from DigitalOcean Spaces
	 *
	 * @param  resource $file_stream      File stream
	 * @param  string   $file_path        File path
	 * @param  string   $bucket_name      Bucket name
	 * @param  string   $region_name      Region name
	 * @param  integer  $file_range_start File range start
	 * @param  integer  $file_range_end   File range end
	 * @return boolean
	 */
	public function get_file( $file_stream, $file_path, $bucket_name, $region_name = null, $file_range_start = 0, $file_range_end = 0 ) {
		$api = $this->create_curl();
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_bucket_name( $bucket_name );
		$api->set_path( $this->rawurlencode_path( "/{$file_path}" ) );

		// Set range header
		if ( $file_range_end > 0 ) {
			$api->set_header( 'Range', sprintf( 'bytes=%d-%d', $file_range_start, $file_range_end ) );
		}

		// Set region name
		if ( $region_name ) {
			$api->set_region_name( $region_name );
			$api->set_base_url( self::API_REGION_URL );
		} else {
			$api->set_base_url( self::API_BUCKET_URL );
		}

		try {
			$file_chunk_data = $api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		// Copy file chunk data into file stream
		if ( fwrite( $file_stream, $file_chunk_data ) === false ) {
			throw new Ai1wmke_Error_Exception( __( 'Unable to save the file from DigitalOcean Spaces', AI1WMKE_PLUGIN_NAME ) );
		}

		return true;
	}

	/**
	 * Get file content from DigitalOcean Spaces
	 *
	 * @param  string $file_path   File path
	 * @param  string $bucket_name Bucket name
	 * @param  string $region_name Region name
	 * @return string
	 */
	public function get_file_content( $file_path, $bucket_name, $region_name = null ) {
		$api = $this->create_curl();
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_bucket_name( $bucket_name );
		$api->set_path( $this->rawurlencode_path( "/{$file_path}" ) );

		// Set region name
		if ( $region_name ) {
			$api->set_region_name( $region_name );
			$api->set_base_url( self::API_REGION_URL );
		} else {
			$api->set_base_url( self::API_BUCKET_URL );
		}

		try {
			$response = $api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		return $response;
	}

	/**
	 * Remove file
	 *
	 * @param  string  $file_path   File path
	 * @param  string  $bucket_name Bucket name
	 * @param  string  $region_name Region name
	 * @return boolean
	 */
	public function remove_file( $file_path, $bucket_name, $region_name = null ) {
		$api = $this->create_curl();
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_bucket_name( $bucket_name );
		$api->set_path( $this->rawurlencode_path( "/{$file_path}" ) );
		$api->set_option( CURLOPT_CUSTOMREQUEST, 'DELETE' );

		// Set region name
		if ( $region_name ) {
			$api->set_region_name( $region_name );
			$api->set_base_url( self::API_REGION_URL );
		} else {
			$api->set_base_url( self::API_BUCKET_URL );
		}

		try {
			$api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			return false;
		}

		return true;
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
	 * @return Ai1wmke_DigitalOcean_Curl
	 */
	protected function create_curl() {
		return new Ai1wmke_DigitalOcean_Curl();
	}
}
