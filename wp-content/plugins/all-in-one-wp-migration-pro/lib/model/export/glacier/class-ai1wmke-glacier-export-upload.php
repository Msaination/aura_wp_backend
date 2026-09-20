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

class Ai1wmke_Glacier_Export_Upload {

	public static function execute( $params, $glacier = null ) {

		$params['completed'] = false;

		// Validate vault name
		if ( ! isset( $params['vault_name'] ) ) {
			throw new Ai1wm_Import_Exception( __( 'Amazon Glacier Vault Name is not specified.', AI1WMKE_PLUGIN_NAME ) );
		}

		// Validate region name
		if ( ! isset( $params['region_name'] ) ) {
			throw new Ai1wm_Import_Exception( __( 'Amazon Glacier Region Name is not specified.', AI1WMKE_PLUGIN_NAME ) );
		}

		// Validate upload ID
		if ( ! isset( $params['upload_id'] ) ) {
			throw new Ai1wm_Import_Exception( __( 'Amazon Glacier Upload ID is not specified.', AI1WMKE_PLUGIN_NAME ) );
		}

		// Set archive offset
		if ( ! isset( $params['archive_offset'] ) ) {
			$params['archive_offset'] = 0;
		}

		// Set multipart offset
		if ( ! isset( $params['multipart_offset'] ) ) {
			$params['multipart_offset'] = 0;
		}

		// Set archive size
		if ( ! isset( $params['archive_size'] ) ) {
			$params['archive_size'] = ai1wm_archive_bytes( $params );
		}

		// Set file chunk size for upload
		$file_chunk_size = get_option( 'ai1wmke_glacier_file_chunk_size', AI1WMKE_GLACIER_FILE_CHUNK_SIZE );

		// Set file range start
		if ( ! isset( $params['file_range_start'] ) ) {
			$params['file_range_start'] = 0;
		}

		// Set file range end
		if ( ! isset( $params['file_range_end'] ) ) {
			$params['file_range_end'] = min( $file_chunk_size, ai1wm_archive_bytes( $params ) ) - 1;
		}

		// Set upload retries
		if ( ! isset( $params['upload_retries'] ) ) {
			$params['upload_retries'] = 0;
		}

		// Set Amazon Glacier client
		if ( is_null( $glacier ) ) {
			$glacier = new Ai1wmke_Glacier_Client(
				get_option( 'ai1wmke_glacier_account_id', false ),
				get_option( 'ai1wmke_glacier_access_key', ai1wmke_aws_access_key() ),
				get_option( 'ai1wmke_glacier_secret_key', ai1wmke_aws_secret_key() )
			);
		}

		// Open the archive file for reading
		$archive = fopen( ai1wm_archive_path( $params ), 'rb' );

		// Read file chunk data
		if ( ( fseek( $archive, $params['archive_offset'] ) !== -1 )
				&& ( $file_chunk_data = fread( $archive, $file_chunk_size ) ) ) {

			try {

				$params['upload_retries'] += 1;

				// Upload file chunk data
				$file_chunk_sha256 = $glacier->upload_file_chunk( $file_chunk_data, $params['archive_size'], $params['upload_id'], $params['vault_name'], $params['region_name'], $params['file_range_start'], $params['file_range_end'] );

				// Add file chunk SHA256
				if ( ( $multipart = fopen( ai1wm_multipart_path( $params ), 'cb' ) ) ) {
					if ( fseek( $multipart, $params['multipart_offset'] ) !== -1 ) {
						fwrite( $multipart, $file_chunk_sha256 . PHP_EOL );
					}

					$params['multipart_offset'] = ftell( $multipart );

					fclose( $multipart );
				}

				// Unset upload retries
				unset( $params['upload_retries'] );

			} catch ( Ai1wmke_Connect_Exception $e ) {
				if ( $params['upload_retries'] <= 3 ) {
					return $params;
				}

				throw $e;
			}

			// Set archive offset
			$params['archive_offset'] = ftell( $archive );

			// Set file range start
			if ( $params['archive_size'] <= ( $params['file_range_start'] + $file_chunk_size ) ) {
				$params['file_range_start'] = $params['archive_size'] - 1;
			} else {
				$params['file_range_start'] = $params['file_range_start'] + $file_chunk_size;
			}

			// Set file range end
			if ( $params['archive_size'] <= ( $params['file_range_end'] + $file_chunk_size ) ) {
				$params['file_range_end'] = $params['archive_size'] - 1;
			} else {
				$params['file_range_end'] = $params['file_range_end'] + $file_chunk_size;
			}

			// Set archive details
			$name = ai1wm_archive_name( $params );
			$size = ai1wm_archive_size( $params );

			// Get progress
			$progress = (int) ( ( $params['archive_offset'] / $params['archive_size'] ) * 100 );

			// Set progress
			if ( defined( 'WP_CLI' ) ) {
				WP_CLI::log(
					sprintf(
						__( 'Uploading %s (%s) [%d%% complete]', AI1WMKE_PLUGIN_NAME ),
						$name,
						$size,
						$progress
					)
				);
			} else {
				Ai1wm_Status::info(
					sprintf(
						__(
							'<i class="ai1wmke-glacier-icon"></i> ' .
							'Uploading <strong>%s</strong> (%s)<br />%d%% complete',
							AI1WMKE_PLUGIN_NAME
						),
						$name,
						$size,
						$progress
					)
				);
			}
		} else {

			// Add file chunk SHA256
			$file_chunks = array();
			if ( ( $multipart = fopen( ai1wm_multipart_path( $params ), 'r' ) ) ) {
				while ( $file_chunk_sha256 = trim( fgets( $multipart ) ) ) {
					$file_chunks[] = pack( 'H*', $file_chunk_sha256 );
				}

				fclose( $multipart );
			}

			// Complete upload file chunk data
			$glacier->upload_complete( $file_chunks, $params['archive_size'], $params['upload_id'], $params['vault_name'], $params['region_name'] );

			// Set last backup date
			update_option( 'ai1wmke_glacier_timestamp', time() );

			// Unset upload ID
			unset( $params['upload_id'] );

			// Unset archive offset
			unset( $params['archive_offset'] );

			// Unset multipart offset
			unset( $params['multipart_offset'] );

			// Unset archive size
			unset( $params['archive_size'] );

			// Unset file range start
			unset( $params['file_range_start'] );

			// Unset file range end
			unset( $params['file_range_end'] );

			// Unset completed
			unset( $params['completed'] );
		}

		// Close the archive file
		fclose( $archive );

		return $params;
	}
}
