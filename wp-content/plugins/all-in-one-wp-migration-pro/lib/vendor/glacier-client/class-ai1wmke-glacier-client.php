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

class Ai1wmke_Glacier_Client {

	const API_URL = 'http://glacier.%s.amazonaws.com';

	/**
	 * Amazon Glacier account ID
	 *
	 * @var string
	 */
	protected $account_id = null;

	/**
	 * Amazon Glacier access key
	 *
	 * @var string
	 */
	protected $access_key = null;

	/**
	 * Amazon Glacier secret key
	 *
	 * @var string
	 */
	protected $secret_key = null;

	public function __construct( $account_id, $access_key, $secret_key ) {
		$this->account_id = $account_id;
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
	 * List vaults
	 *
	 * @param  string $region_name Region name
	 * @param  array  $query       Query options
	 * @return array
	 */
	public function get_vaults( $region_name = null, $query = array() ) {
		$api = $this->create_curl();
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_base_url( self::API_URL );
		$api->set_path( sprintf( '/%s/vaults', $this->account_id ) );
		$api->set_query( $this->rawurlencode_query( $query ) );

		// Set region name
		if ( $region_name ) {
			$api->set_region_name( $region_name );
		}

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		$vaults = array();
		if ( isset( $response['VaultList'] ) ) {
			foreach ( $response['VaultList'] as $vault ) {
				$vaults[] = array(
					'arn'   => isset( $vault['VaultARN'] ) ? $vault['VaultARN'] : null,
					'name'  => isset( $vault['VaultName'] ) ? $vault['VaultName'] : null,
					'date'  => isset( $vault['LastInventoryDate'] ) ? strtotime( $vault['LastInventoryDate'] ) : null,
					'bytes' => isset( $vault['SizeInBytes'] ) ? $vault['SizeInBytes'] : null,
					'type'  => 'vault',
				);
			}
		}

		return $vaults;
	}

	/**
	 * Add a new vault
	 *
	 * @param  string  $vault_name  Vault name
	 * @param  string  $region_name Region name
	 * @return boolean
	 */
	public function create_vault( $vault_name, $region_name = null ) {
		$api = $this->create_curl();
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_base_url( self::API_URL );
		$api->set_path( sprintf( '/%s/vaults/%s', $this->account_id, $vault_name ) );
		$api->set_option( CURLOPT_CUSTOMREQUEST, 'PUT' );
		$api->set_header( 'Content-Length', 0 );

		// Set region name
		if ( $region_name ) {
			$api->set_region_name( $region_name );
		}

		try {
			$api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		return true;
	}

	/**
	 * Initiate inventory retrieval job
	 *
	 * @param  string $vault_name  Vault name
	 * @param  string $region_name Region name
	 * @return string
	 */
	public function initiate_inventory_retrieval( $vault_name, $region_name = null ) {
		$api = $this->create_curl();
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_base_url( self::API_URL );
		$api->set_path( sprintf( '/%s/vaults/%s/jobs', $this->account_id, $vault_name ) );
		$api->set_header( 'Content-Type', 'application/json' );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option( CURLOPT_HEADER, true );
		$api->set_option(
			CURLOPT_POSTFIELDS,
			json_encode(
				array(
					'Type'        => 'inventory-retrieval',
					'Description' => 'inventory-retrieval-job',
				)
			)
		);

		// Set region name
		if ( $region_name ) {
			$api->set_region_name( $region_name );
		}

		try {
			$response = $api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		if ( isset( $response['x-amz-job-id'] ) ) {
			return strval( $response['x-amz-job-id'] );
		}
	}

	/**
	 * Initiate archive retrieval job
	 *
	 * @param  string $vault_name  Vault name
	 * @param  string $region_name Region name
	 * @return string
	 */
	public function initiate_archive_retrieval( $archive_id, $vault_name, $region_name = null ) {
		$api = $this->create_curl();
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_base_url( self::API_URL );
		$api->set_path( sprintf( '/%s/vaults/%s/jobs', $this->account_id, $vault_name ) );
		$api->set_header( 'Content-Type', 'application/json' );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option( CURLOPT_HEADER, true );
		$api->set_option(
			CURLOPT_POSTFIELDS,
			json_encode(
				array(
					'Type'        => 'archive-retrieval',
					'Description' => 'archive-retrieval-job',
					'Tier'        => 'Standard',
					'ArchiveId'   => $archive_id,
				)
			)
		);

		// Set region name
		if ( $region_name ) {
			$api->set_region_name( $region_name );
		}

		try {
			$response = $api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		if ( isset( $response['x-amz-job-id'] ) ) {
			return strval( $response['x-amz-job-id'] );
		}
	}

	/**
	 * Get job output
	 *
	 * @param  string  $job_id      Job ID
	 * @param  string  $vault_name  Vault name
	 * @param  string  $region_name Region name
	 * @return array
	 */
	public function get_job_output( $job_id, $vault_name, $region_name = null ) {
		$api = $this->create_curl();
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_base_url( self::API_URL );
		$api->set_path( sprintf( '/%s/vaults/%s/jobs/%s/output', $this->account_id, $vault_name, $job_id ) );
		$api->set_header( 'Content-Type', 'application/json' );

		// Set region name
		if ( $region_name ) {
			$api->set_region_name( $region_name );
		}

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		$objects = array();
		if ( isset( $response['ArchiveList'] ) ) {
			foreach ( $response['ArchiveList'] as $item ) {
				$objects[] = array(
					'id'    => isset( $item['ArchiveId'] ) ? $item['ArchiveId'] : null,
					'name'  => isset( $item['ArchiveDescription'] ) ? $item['ArchiveDescription'] : null,
					'date'  => isset( $item['CreationDate'] ) ? strtotime( $item['CreationDate'] ) : null,
					'bytes' => isset( $item['Size'] ) ? $item['Size'] : null,
					'type'  => 'archive',
				);
			}
		}

		return $objects;
	}

	/**
	 * Get inventory retrieval jobs
	 *
	 * @param  string $vault_name  Vault name
	 * @param  string $region_name Region name
	 * @return array
	 */
	public function get_inventory_retrieval_jobs( $vault_name, $region_name = null ) {
		$api = $this->create_curl();
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_base_url( self::API_URL );
		$api->set_path( sprintf( '/%s/vaults/%s/jobs', $this->account_id, $vault_name ) );
		$api->set_header( 'Content-Type', 'application/json' );

		// Set region name
		if ( $region_name ) {
			$api->set_region_name( $region_name );
		}

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		$objects = array();
		if ( isset( $response['JobList'] ) ) {
			foreach ( $response['JobList'] as $item ) {
				if ( $item['Action'] === 'InventoryRetrieval' ) {
					$objects[] = array(
						'id'         => isset( $item['JobId'] ) ? $item['JobId'] : null,
						'archive_id' => isset( $item['ArchiveId'] ) ? $item['ArchiveId'] : null,
						'name'       => isset( $item['JobDescription'] ) ? $item['JobDescription'] : null,
						'date'       => isset( $item['CreationDate'] ) ? strtotime( $item['CreationDate'] ) : null,
						'status'     => isset( $item['StatusCode'] ) ? $item['StatusCode'] : null,
						'completed'  => isset( $item['Completed'] ) ? $item['Completed'] : null,
						'type'       => 'job',
					);
				}
			}
		}

		return $objects;
	}

	/**
	 * Get archive retrieval jobs
	 *
	 * @param  string $vault_name  Vault name
	 * @param  string $region_name Region name
	 * @return array
	 */
	public function get_archive_retrieval_jobs( $vault_name, $region_name = null ) {
		$api = $this->create_curl();
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_base_url( self::API_URL );
		$api->set_path( sprintf( '/%s/vaults/%s/jobs', $this->account_id, $vault_name ) );
		$api->set_header( 'Content-Type', 'application/json' );

		// Set region name
		if ( $region_name ) {
			$api->set_region_name( $region_name );
		}

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		$objects = array();
		if ( isset( $response['JobList'] ) ) {
			foreach ( $response['JobList'] as $item ) {
				if ( $item['Action'] === 'ArchiveRetrieval' ) {
					$objects[ $item['ArchiveId'] ] = array(
						'id'        => isset( $item['JobId'] ) ? $item['JobId'] : null,
						'name'      => isset( $item['JobDescription'] ) ? $item['JobDescription'] : null,
						'date'      => isset( $item['CreationDate'] ) ? strtotime( $item['CreationDate'] ) : null,
						'status'    => isset( $item['StatusCode'] ) ? $item['StatusCode'] : null,
						'completed' => isset( $item['Completed'] ) ? $item['Completed'] : null,
						'type'      => 'job',
					);
				}
			}
		}

		return $objects;
	}

	/**
	 * Get vault region
	 *
	 * @param  string $vault_name  Vault name
	 * @param  string $region_name Region name
	 * @return string
	 */
	public function get_vault_region( $vault_name, $region_name = null ) {
		$api = $this->create_curl();
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_base_url( self::API_URL );
		$api->set_path( sprintf( '/%s/vaults/%s', $this->account_id, $vault_name ) );

		// Set region name
		if ( $region_name ) {
			$api->set_region_name( $region_name );
		}

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Resource_Not_Found_Exception $e ) {
		}

		if ( isset( $response['VaultARN'] ) ) {
			if ( preg_match( '/arn:aws:glacier:(.*?):/', $response['VaultARN'], $matches ) ) {
				return $matches[1];
			}
		}
	}

	/**
	 * Get regions
	 *
	 * @return array
	 */
	public function get_regions() {
		// TODO: When Amazon Glacier provides a method that returns all regions
		// we should refactor the code below with proper API request
		$regions = array(
			'us-east-1'      => 'US East (N. Virginia)',
			'us-east-2'      => 'US East (Ohio)',
			'us-west-1'      => 'US West (N. California)',
			'us-west-2'      => 'US West (Oregon)',
			'af-south-1'     => 'Africa (Cape Town)',
			'ap-east-1'      => 'Asia Pacific (Hong Kong)',
			'ap-south-1'     => 'Asia Pacific (Mumbai)',
			'ap-northeast-3' => 'Asia Pacific (Osaka)',
			'ap-northeast-2' => 'Asia Pacific (Seoul)',
			'ap-southeast-1' => 'Asia Pacific (Singapore)',
			'ap-southeast-2' => 'Asia Pacific (Sydney)',
			'ap-northeast-1' => 'Asia Pacific (Tokyo)',
			'ca-central-1'   => 'Canada (Central)',
			'eu-central-1'   => 'Europe (Frankfurt)',
			'eu-west-1'      => 'Europe (Ireland)',
			'eu-west-2'      => 'Europe (London)',
			'eu-south-1'     => 'Europe (Milan)',
			'eu-west-3'      => 'Europe (Paris)',
			'eu-north-1'     => 'Europe (Stockholm)',
			'me-south-1'     => 'Middle East (Bahrain)',
			'sa-east-1'      => 'South America (São Paulo)',
		);

		return $regions;
	}

	/**
	 * Check if a given vault name is available
	 *
	 * @param  string  $vault_name  Vault name
	 * @param  string  $region_name Region name
	 * @return boolean
	 */
	public function is_vault_available( $vault_name, $region_name = null ) {
		$api = $this->create_curl();
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_base_url( self::API_URL );
		$api->set_path( sprintf( '/%s/vaults/%s', $this->account_id, $vault_name ) );

		// Set region name
		if ( $region_name ) {
			$api->set_region_name( $region_name );
		}

		try {
			$api->make_request();
		} catch ( Ai1wmke_Access_Denied_Exception $e ) {
			throw new Ai1wmke_Access_Denied_Exception( __( 'Please check your vault policy. Access Denied. <a href="https://help.servmask.com/knowledgebase/amazon-glacier-extension-error-codes/#AccessDenied" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ) );
		} catch ( Ai1wmke_Error_Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Upload multipart file on Amazon Glacier
	 *
	 * @param  string $file_name       File name
	 * @param  string $vault_name      Vault name
	 * @param  string $region_name     Region name
	 * @param  int    $file_chunk_size File chunk size
	 * @return string
	 */
	public function upload_multipart( $file_name, $vault_name, $region_name = null, $file_chunk_size = AI1WMKE_GLACIER_FILE_CHUNK_SIZE ) {
		$api = $this->create_curl();
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_base_url( self::API_URL );
		$api->set_path( sprintf( '/%s/vaults/%s/multipart-uploads', $this->account_id, $vault_name ) );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option( CURLOPT_HEADER, true );
		$api->set_header( 'x-amz-part-size', $file_chunk_size );
		$api->set_header( 'x-amz-archive-description', $file_name );
		$api->set_header( 'Content-Type', 'application/octet-stream' );
		$api->set_raw_header( 'Content-Length', null );
		$api->set_raw_header( 'Transfer-Encoding', null );

		// Set region name
		if ( $region_name ) {
			$api->set_region_name( $region_name );
		}

		try {
			$response = $api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		if ( isset( $response['x-amz-multipart-upload-id'] ) ) {
			return strval( $response['x-amz-multipart-upload-id'] );
		}
	}

	/**
	 * Upload file chunk
	 *
	 * @param  string  $file_chunk_data  File chunk data
	 * @param  integer $file_size        File size
	 * @param  string  $upload_id        Upload ID
	 * @param  string  $vault_name       Vault name
	 * @param  string  $region_name      Region name
	 * @param  integer $file_range_start File range start
	 * @param  integer $file_range_end   File range end
	 * @return string
	 */
	public function upload_file_chunk( $file_chunk_data, $file_size, $upload_id, $vault_name, $region_name = null, $file_range_start = 0, $file_range_end = 0 ) {
		$file_chunk_sha256 = array();
		if ( ( $file_chunk_parts = str_split( $file_chunk_data, 1024 * 1024 ) ) ) {
			foreach ( $file_chunk_parts as $file_chunk_part ) {
				$file_chunk_sha256[] = hash( 'sha256', $file_chunk_part, true );
			}
		}

		$tree_hash_sha256 = $this->calculate_tree_hash_sha256( $file_chunk_sha256 );

		// Upload file chunk data
		$api = $this->create_curl();
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_base_url( self::API_URL );
		$api->set_path( sprintf( '/%s/vaults/%s/multipart-uploads/%s', $this->account_id, $vault_name, $upload_id ) );
		$api->set_option( CURLOPT_CUSTOMREQUEST, 'PUT' );
		$api->set_option( CURLOPT_POSTFIELDS, $file_chunk_data );
		$api->set_option( CURLOPT_HEADER, true );
		$api->set_header( 'Content-Type', 'application/octet-stream' );
		$api->set_header( 'Content-Length', strlen( $file_chunk_data ) );
		$api->set_header( 'Content-Range', sprintf( 'bytes %d-%d/%d', $file_range_start, $file_range_end, $file_size ) );
		$api->set_header( 'x-amz-sha256-tree-hash', $tree_hash_sha256 );

		// Set region name
		if ( $region_name ) {
			$api->set_region_name( $region_name );
		}

		try {
			$api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		return $tree_hash_sha256;
	}

	/**
	 * Upload complete file on Amazon Glacier
	 *
	 * @param  array  $file_chunks File chunks
	 * @param  string $file_size   File size
	 * @param  string $upload_id   Upload ID
	 * @param  string $vault_name  Vault name
	 * @param  string $region_name Region name
	 * @return array
	 */
	public function upload_complete( $file_chunks, $file_size, $upload_id, $vault_name, $region_name = null ) {
		$tree_hash_sha256 = $this->calculate_tree_hash_sha256( $file_chunks );

		// Upload multipart complete
		$api = $this->create_curl();
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_base_url( self::API_URL );
		$api->set_path( sprintf( '/%s/vaults/%s/multipart-uploads/%s', $this->account_id, $vault_name, $upload_id ) );
		$api->set_option( CURLOPT_POST, true );
		$api->set_header( 'Content-Type', 'application/octet-stream' );
		$api->set_header( 'Transfer-Encoding', 'chunked' );
		$api->set_header( 'x-amz-archive-size', $file_size );
		$api->set_header( 'x-amz-sha256-tree-hash', $tree_hash_sha256 );

		// Set region name
		if ( $region_name ) {
			$api->set_region_name( $region_name );
		}

		try {
			$response = $api->make_request( true );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		return $response;
	}

	/**
	 * Download file from Amazon Glacier
	 *
	 * @param  resource $file_stream      File stream
	 * @param  string   $job_id           Job ID
	 * @param  string   $vault_name       Vault name
	 * @param  string   $region_name      Region name
	 * @param  integer  $file_range_start File range start
	 * @param  integer  $file_range_end   File range end
	 * @return boolean
	 */
	public function get_file( $file_stream, $job_id, $vault_name, $region_name = null, $file_range_start = 0, $file_range_end = 0 ) {
		$api = $this->create_curl();
		$api->set_access_key( $this->access_key );
		$api->set_secret_key( $this->secret_key );
		$api->set_base_url( self::API_URL );
		$api->set_path( sprintf( '/%s/vaults/%s/jobs/%s/output', $this->account_id, $vault_name, $job_id ) );
		$api->set_header( 'Range', sprintf( 'bytes=%d-%d', $file_range_start, $file_range_end ) );

		// Set region name
		if ( $region_name ) {
			$api->set_region_name( $region_name );
		}

		try {
			$file_chunk_data = $api->make_request();
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		// Copy file chunk data into file stream
		if ( fwrite( $file_stream, $file_chunk_data ) === false ) {
			throw new Ai1wmke_Error_Exception( __( 'Unable to save the file from Amazon Glacier', AI1WMKE_PLUGIN_NAME ) );
		}

		return true;
	}

	/**
	 * Cauculate tree hash (SHA256)
	 *
	 * @param  array  $file_chunk_sha256 List of SHA256 hashes
	 * @return string
	 */
	public function calculate_tree_hash_sha256( $file_chunk_sha256 ) {
		do {
			$file_chunk_sha256 = array_chunk( $file_chunk_sha256, 2 );
			for ( $i = 0; $i < count( $file_chunk_sha256 ); $i++ ) {
				if ( isset( $file_chunk_sha256[ $i ][1] ) ) {
					$file_chunk_sha256[ $i ] = hash( 'sha256', implode( array( $file_chunk_sha256[ $i ][0], $file_chunk_sha256[ $i ][1] ) ), true );
				} else {
					$file_chunk_sha256[ $i ] = $file_chunk_sha256[ $i ][0];
				}
			}
		} while ( count( $file_chunk_sha256 ) > 1 );

		// Get root tree hash
		if ( isset( $file_chunk_sha256[0] ) ) {
			return bin2hex( $file_chunk_sha256[0] );
		}
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
	 * @return Ai1wmke_Glacier_Curl
	 */
	protected function create_curl() {
		return new Ai1wmke_Glacier_Curl();
	}
}
