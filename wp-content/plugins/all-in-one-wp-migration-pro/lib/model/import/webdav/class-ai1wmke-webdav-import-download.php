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

class Ai1wmke_WebDAV_Import_Download {

	public static function execute( $params, $webdav = null ) {

		$params['completed'] = false;

		// Validate file path
		if ( ! isset( $params['file_path'] ) ) {
			throw new Ai1wm_Import_Exception( __( 'WebDAV File Path is not specified.', AI1WMKE_PLUGIN_NAME ) );
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

		// Read file chunk names
		$file_chunks = array();
		if ( ( $multipart = fopen( ai1wm_multipart_path( $params ), 'r' ) ) ) {
			while ( $file_chunk_name = trim( fgets( $multipart ) ) ) {
				$file_chunks[] = $file_chunk_name;
			}

			fclose( $multipart );
		}

		// Set WebDAV client
		if ( is_null( $webdav ) ) {
			$webdav = new Ai1wmke_WebDAV_Client(
				get_option( 'ai1wmke_webdav_type', AI1WMKE_WEBDAV_TYPE ),
				get_option( 'ai1wmke_webdav_hostname', false ),
				get_option( 'ai1wmke_webdav_username', false ),
				get_option( 'ai1wmke_webdav_password', false ),
				get_option( 'ai1wmke_webdav_authentication', AI1WMKE_WEBDAV_AUTHENTICATION ),
				get_option( 'ai1wmke_webdav_directory', false ),
				get_option( 'ai1wmke_webdav_port', AI1WMKE_WEBDAV_PORT )
			);
		}

		// Open the archive file for writing
		$archive = fopen( ai1wm_archive_path( $params ), 'cb' );

		// Write file chunk data
		if ( ( fseek( $archive, $params['archive_offset'] ) !== -1 ) ) {
			try {

				$params['download_retries'] += 1;

				// Download file chunk data
				$webdav->download_file_chunk( $archive, sprintf( '%s/%s', $params['file_path'], $file_chunks[ $params['file_chunk_number'] ] ) );

				// Unset download retries
				unset( $params['download_retries'] );

			} catch ( Ai1wmke_Connect_Exception $e ) {
				if ( $params['download_retries'] <= 3 ) {
					return $params;
				}

				throw $e;
			}
		}

		fflush( $archive );

		// Set archive offset
		$params['archive_offset'] = ai1wm_archive_bytes( $params );

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
			unset( $params['file_path'] );

			// Unset archive offset
			unset( $params['archive_offset'] );

			// Unset file chunk number
			unset( $params['file_chunk_number'] );

			// Unset completed
			unset( $params['completed'] );

			// Unset file size
			unset( $params['file_size'] );

		}

		// Close the archive file
		fclose( $archive );

		return $params;
	}
}
