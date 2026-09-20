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

class Ai1wmke_Azure_Storage_Export_Upload {

	public static function execute( $params, $azure = null ) {

		$params['completed'] = false;

		// Validate share name
		if ( ! isset( $params['share_name'] ) ) {
			throw new Ai1wm_Import_Exception( __( 'Microsoft Azure Storage Share Name is not specified.', AI1WMKE_PLUGIN_NAME ) );
		}

		// Set archive offset
		if ( ! isset( $params['archive_offset'] ) ) {
			$params['archive_offset'] = 0;
		}

		// Set archive size
		if ( ! isset( $params['archive_size'] ) ) {
			$params['archive_size'] = ai1wm_archive_bytes( $params );
		}

		// Set file range start
		if ( ! isset( $params['file_range_start'] ) ) {
			$params['file_range_start'] = 0;
		}

		// Set file range end
		if ( ! isset( $params['file_range_end'] ) ) {
			$params['file_range_end'] = min( AI1WMKE_AZURE_STORAGE_FILE_CHUNK_SIZE, $params['archive_size'] ) - 1;
		}

		// Set upload retries
		if ( ! isset( $params['upload_retries'] ) ) {
			$params['upload_retries'] = 0;
		}

		// Set Azure Storage client
		if ( is_null( $azure ) ) {
			$azure = new Ai1wmke_Azure_Storage_Client(
				get_option( 'ai1wmke_azure_storage_account_name', false ),
				get_option( 'ai1wmke_azure_storage_account_key', false )
			);
		}

		// Open the archive file for reading
		$archive = fopen( ai1wm_archive_path( $params ), 'rb' );

		// Read file chunk data
		if ( ( fseek( $archive, $params['archive_offset'] ) !== -1 )
				&& ( $file_chunk_data = fread( $archive, AI1WMKE_AZURE_STORAGE_FILE_CHUNK_SIZE ) ) ) {

			try {

				$params['upload_retries'] += 1;

				// Upload file chunk data
				$azure->upload_file_chunk( $file_chunk_data, sprintf( '/%s/%s', $params['folder_name'], ai1wm_archive_name( $params ) ), $params['share_name'], $params['file_range_start'], $params['file_range_end'] );

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
			if ( $params['archive_size'] <= ( $params['file_range_start'] + AI1WMKE_AZURE_STORAGE_FILE_CHUNK_SIZE ) ) {
				$params['file_range_start'] = $params['archive_size'] - 1;
			} else {
				$params['file_range_start'] = $params['file_range_start'] + AI1WMKE_AZURE_STORAGE_FILE_CHUNK_SIZE;
			}

			// Set file range end
			if ( $params['archive_size'] <= ( $params['file_range_end'] + AI1WMKE_AZURE_STORAGE_FILE_CHUNK_SIZE ) ) {
				$params['file_range_end'] = $params['archive_size'] - 1;
			} else {
				$params['file_range_end'] = $params['file_range_end'] + AI1WMKE_AZURE_STORAGE_FILE_CHUNK_SIZE;
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
							'<i class="ai1wmke-azure-storage-icon"></i> ' .
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

			// Set last backup date
			update_option( 'ai1wmke_azure_storage_timestamp', time() );

			// Unset archive offset
			unset( $params['archive_offset'] );

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
