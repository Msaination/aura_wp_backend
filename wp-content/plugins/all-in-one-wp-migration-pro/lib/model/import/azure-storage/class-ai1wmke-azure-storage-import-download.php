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

class Ai1wmke_Azure_Storage_Import_Download {

	public static function execute( $params, $azure = null ) {

		$params['completed'] = false;

		// Validate file path
		if ( ! isset( $params['file_path'] ) ) {
			throw new Ai1wm_Import_Exception( __( 'Microsoft Azure Storage File Name is not specified.', AI1WMKE_PLUGIN_NAME ) );
		}

		// Validate file size
		if ( ! isset( $params['file_size'] ) ) {
			throw new Ai1wm_Import_Exception( __( 'Microsoft Azure Storage File Size is not specified.', AI1WMKE_PLUGIN_NAME ) );
		}

		// Validate share name
		if ( ! isset( $params['share_name'] ) ) {
			throw new Ai1wm_Import_Exception( __( 'Microsoft Azure Storage Share Name is not specified.', AI1WMKE_PLUGIN_NAME ) );
		}

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
			$params['file_range_end'] = AI1WMKE_AZURE_STORAGE_FILE_CHUNK_SIZE - 1;
		}

		// Set download retries
		if ( ! isset( $params['download_retries'] ) ) {
			$params['download_retries'] = 0;
		}

		// Set Azure Storage client
		if ( is_null( $azure ) ) {
			$azure = new Ai1wmke_Azure_Storage_Client(
				get_option( 'ai1wmke_azure_storage_account_name', false ),
				get_option( 'ai1wmke_azure_storage_account_key', false )
			);
		}

		// Open the archive file for writing
		$archive = fopen( ai1wm_archive_path( $params ), 'cb' );

		// Write file chunk data
		if ( ( fseek( $archive, $params['archive_offset'] ) !== -1 ) ) {
			try {

				$params['download_retries'] += 1;

				// Download file chunk data
				$azure->download_file_chunk( $archive, $params['file_path'], $params['share_name'], $params['file_range_start'], $params['file_range_end'] );

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
		if ( $params['file_size'] <= ( $params['file_range_start'] + AI1WMKE_AZURE_STORAGE_FILE_CHUNK_SIZE ) ) {
			$params['file_range_start'] = $params['file_size'] - 1;
		} else {
			$params['file_range_start'] = $params['file_range_start'] + AI1WMKE_AZURE_STORAGE_FILE_CHUNK_SIZE;
		}

		// Set file range end
		if ( $params['file_size'] <= ( $params['file_range_end'] + AI1WMKE_AZURE_STORAGE_FILE_CHUNK_SIZE ) ) {
			$params['file_range_end'] = $params['file_size'] - 1;
		} else {
			$params['file_range_end'] = $params['file_range_end'] + AI1WMKE_AZURE_STORAGE_FILE_CHUNK_SIZE;
		}

		// Get progress
		$progress = (int) ( ( $params['file_range_start'] / $params['file_size'] ) * 100 );

		// Set progress
		if ( defined( 'WP_CLI' ) ) {
			WP_CLI::log(
				sprintf(
					__( 'Downloading %s (%s) [%d%% complete]', AI1WMKE_PLUGIN_NAME ),
					$params['file_path'],
					$params['file_size'],
					$progress
				)
			);
		} else {
			Ai1wm_Status::progress( $progress );
		}

		// Completed?
		if ( $params['file_range_start'] === ( $params['file_size'] - 1 ) ) {

			// Unset file path
			unset( $params['file_path'] );

			// Unset file size
			unset( $params['file_size'] );

			// Unset share name
			unset( $params['share_name'] );

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
