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

class Ai1wmke_Box_Import_Download {

	public static function execute( $params, $box = null ) {

		$params['completed'] = false;

		// Validate folder ID
		if ( ! isset( $params['folder_id'] ) ) {
			throw new Ai1wm_Import_Exception( __( 'Box Folder ID is not specified.', AI1WMKE_PLUGIN_NAME ) );
		}

		// Set archive offset
		if ( ! isset( $params['archive_offset'] ) ) {
			$params['archive_offset'] = 0;
		}

		// Set file chunk number
		if ( ! isset( $params['file_chunk_number'] ) ) {
			$params['file_chunk_number'] = 0;
		}

		// Set download retries
		if ( ! isset( $params['download_retries'] ) ) {
			$params['download_retries'] = 0;
		}

		// Add file chunk ID
		$file_chunks = array();
		if ( ( $multipart = fopen( ai1wm_multipart_path( $params ), 'r' ) ) ) {
			while ( $file_chunk_id = trim( fgets( $multipart ) ) ) {
				$file_chunks[] = $file_chunk_id;
			}

			fclose( $multipart );
		}

		// Set Box client
		if ( is_null( $box ) ) {
			$box = new Ai1wmke_Box_Client(
				get_option( 'ai1wmke_box_token', false ),
				get_option( 'ai1wmke_box_ssl', true )
			);
		}

		// Open the archive file for writing
		$archive = fopen( ai1wm_archive_path( $params ), 'cb' );

		// Write file chunk data
		if ( ( fseek( $archive, $params['archive_offset'] ) !== -1 ) ) {
			try {

				$params['download_retries'] += 1;

				// Download file chunk data
				$box->download_file_chunk( $archive, $file_chunks[ $params['file_chunk_number'] ] );

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

		// Set file chunk number
		$params['file_chunk_number'] += 1;

		// Get progress
		$progress = (int) ( ( $params['file_chunk_number'] / count( $file_chunks ) ) * 100 );

		// Set progress
		if ( defined( 'WP_CLI' ) ) {
			WP_CLI::log( sprintf( __( 'Downloading [%d%% complete]', AI1WMKE_PLUGIN_NAME ), $progress ) );
		} else {
			Ai1wm_Status::progress( $progress );
		}

		// Completed?
		if ( $params['file_chunk_number'] === count( $file_chunks ) ) {

			// Unset folder ID
			unset( $params['folder_id'] );

			// Unset archive offset
			unset( $params['archive_offset'] );

			// Unset file chunk number
			unset( $params['file_chunk_number'] );

			// Unset completed
			unset( $params['completed'] );
		}

		// Close the archive file
		fclose( $archive );

		return $params;
	}
}
