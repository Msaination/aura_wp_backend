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

class Ai1wmke_Direct_Client {

	/**
	 * Call params
	 *
	 * @var array
	 */
	protected $params = null;

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

	public function __construct( $params, $ssl = true ) {
		$this->params = $params;
		$this->ssl    = $ssl;
		if ( isset( $params['remote_url'] ) ) {
			$this->load_upload_url( $params['remote_url'] );
		}
	}

	/**
	 * Load upload URL
	 *
	 * @param string $url Upload URL
	 *
	 * @return void
	 */
	public function load_upload_url( $url ) {
		$this->upload_url = $url;
	}

	public function send_params( $json = true ) {
		$boundary = uniqid();

		// Raw request
		$post = sprintf( "--%s\r\n", $boundary );
		foreach ( $this->params as $name => $value ) {
			$post .= sprintf( 'Content-Disposition: form-data; name="' . $name . '"' . "\r\n\r\n" );
			$post .= sprintf( "%s\r\n", $value );
			$post .= sprintf( "--%s\r\n", $boundary );
		}

		// Upload file
		$api = $this->create_curl();
		$api->set_ssl( $this->ssl );
		$api->set_base_url( $this->upload_url );
		$api->set_option( CURLOPT_POST, true );
		$api->set_option( CURLOPT_POSTFIELDS, $post );
		$api->set_header( 'Content-Length', strlen( $post ) );
		$api->set_header( 'Content-Type', sprintf( 'multipart/form-data; boundary="%s"', $boundary ) );

		try {
			$response = $api->make_request( $json );
		} catch ( Ai1wmke_Error_Exception $e ) {
			throw $e;
		}

		return $response;
	}

	/**
	 * Upload file chunk
	 *
	 * @param $file_chunk_data
	 *
	 * @return mixed
	 * @throws Ai1wmke_Error_Exception
	 */
	public function upload_file_chunk( $file_chunk_data ) {
		$boundary = uniqid();

		// Raw request
		$post  = sprintf( "--%s\r\n", $boundary );
		$post .= sprintf( "Content-Type: application/octet-binary\r\n" );
		$post .= sprintf( 'Content-Disposition: form-data; name="upload_file"; filename="blob"' . "\r\n\r\n" );
		$post .= sprintf( "%s\r\n", $file_chunk_data );
		$post .= sprintf( "--%s\r\n", $boundary );

		foreach ( $this->params as $name => $value ) {
			$post .= sprintf( 'Content-Disposition: form-data; name="' . $name . '"' . "\r\n\r\n" );
			$post .= sprintf( "%s\r\n", $value );
			$post .= sprintf( "--%s\r\n", $boundary );
		}

		// Upload file
		$api = $this->create_curl();
		$api->set_ssl( $this->ssl );
		$api->set_base_url( $this->upload_url );
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
	 *
	 *
	 * /**
	 * Encode URL path
	 *
	 * @param string $path Base path
	 *
	 * @return string
	 */
	public function rawurlencode_path( $path ) {
		return str_replace( '%7E', '~', rawurlencode( $path ) );
	}

	/**
	 * Encode URL query
	 *
	 * @param array $query Base query
	 *
	 * @return string
	 */
	public function rawurlencode_query( $query ) {
		return str_replace( '%7E', '~', array_map( 'rawurlencode', array_filter( $query, 'is_scalar' ) ) );
	}

	/**
	 * Create cURL instance
	 *
	 * @return Ai1wmke_Direct_Curl
	 */
	protected function create_curl() {
		return new Ai1wmke_Direct_Curl();
	}
}
