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

class Ai1wmke_Glacier_Import_Download {

	public static function execute( $params, $glacier = null ) {

		$params['completed'] = false;

		// Validate job ID
		if ( ! isset( $params['job_id'] ) ) {
			throw new Ai1wm_Import_Exception( __( 'Amazon Glacier Job ID is not specified.', AI1WMKE_PLUGIN_NAME ) );
		}

		// Validate file size
		if ( ! isset( $params['file_size'] ) ) {
			throw new Ai1wm_Import_Exception( __( 'Amazon Glacier File Size is not specified.', AI1WMKE_PLUGIN_NAME ) );
		}

		// Validate vault name
		if ( ! isset( $params['vault_name'] ) ) {
			throw new Ai1wm_Import_Exception( __( 'Amazon Glacier Vault Name is not specified.', AI1WMKE_PLUGIN_NAME ) );
		}

		// Validate region name
		if ( ! isset( $params['region_name'] ) ) {
			throw new Ai1wm_Import_Exception( __( 'Amazon Glacier Region Name is not specified.', AI1WMKE_PLUGIN_NAME ) );
		}

		// Set file chunk size for download
		$file_chunk_size = get_option( 'ai1wmke_glacier_file_chunk_size', AI1WMKE_GLACIER_FILE_CHUNK_SIZE );

		// Set archive offset
		if ( ! isset( $params['archive_offset'] ) ) {
			$params['archive_offset'] = 0;
		}

		// Set file range start
		if ( ! isset( $params['file_range_start'] ) ) {
			$params['file_range_start'] = 0;
		}

		// Set file range end
		if ( ! isset( $params['file_range_end'] ) ) {
			$params['file_range_end'] = $file_chunk_size - 1;
		}

		// Set download retries
		if ( ! isset( $params['download_retries'] ) ) {
			$params['download_retries'] = 0;
		}

		// Set Amazon Glacier client
		if ( is_null( $glacier ) ) {
			$glacier = new Ai1wmke_Glacier_Client(
				get_option( 'ai1wmke_glacier_account_id', false ),
				get_option( 'ai1wmke_glacier_access_key', ai1wmke_aws_access_key() ),
				get_option( 'ai1wmke_glacier_secret_key', ai1wmke_aws_secret_key() )
			);
		}

		// Open the archive file for writing
		$archive = fopen( ai1wm_archive_path( $params ), 'cb' );

		// Write file chunk data
		if ( ( fseek( $archive, $params['archive_offset'] ) !== -1 ) ) {
			try {

				$params['download_retries'] += 1;

				// Download file chunk data
				$glacier->get_file( $archive, $params['job_id'], $params['vault_name'], $params['region_name'], $params['file_range_start'], $params['file_range_end'] );

				// Unset download retries
				unset( $params['download_retries'] );

			} catch ( Ai1wmke_Connect_Exception $e ) {
				if ( $params['download_retries'] <= 3 ) {
					return $params;
				}

				throw $e;
			}
		}

		// Set archive offset
		$params['archive_offset'] = ftell( $archive );

		// Set file range start
		if ( $params['file_size'] <= ( $params['file_range_start'] + $file_chunk_size ) ) {
			$params['file_range_start'] = $params['file_size'] - 1;
		} else {
			$params['file_range_start'] = $params['file_range_start'] + $file_chunk_size;
		}

		// Set file range end
		if ( $params['file_size'] <= ( $params['file_range_end'] + $file_chunk_size ) ) {
			$params['file_range_end'] = $params['file_size'] - 1;
		} else {
			$params['file_range_end'] = $params['file_range_end'] + $file_chunk_size;
		}

		// Get progress
		$progress = (int) ( ( $params['file_range_start'] / $params['file_size'] ) * 100 );

		// Set progress
		if ( defined( 'WP_CLI' ) ) {
			WP_CLI::log( sprintf( __( 'Downloading [%d%% complete]', AI1WMKE_PLUGIN_NAME ), $progress ) );
		} else {
			Ai1wm_Status::progress( $progress );
		}

		// Completed?
		if ( $params['file_range_start'] === ( $params['file_size'] - 1 ) ) {

			// Unset job ID
			unset( $params['job_id'] );

			// Unset file size
			unset( $params['file_size'] );

			// Unset vault name
			unset( $params['vault_name'] );

			// Unset region name
			unset( $params['region_name'] );

			// Unset archive offset
			unset( $params['archive_offset'] );

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
