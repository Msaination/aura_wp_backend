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

class Ai1wmke_Mega_Export_Upload {

	public static function execute( $params, $mega = null ) {

		$params['completed'] = false;

		// Validate node ID
		if ( ! isset( $params['node_id'] ) ) {
			throw new Ai1wm_Import_Exception( __( 'Mega Node ID is not specified.', AI1WMKE_PLUGIN_NAME ) );
		}

		// Validate upload URL
		if ( ! isset( $params['upload_url'] ) ) {
			throw new Ai1wm_Import_Exception( __( 'Mega Upload URL is not specified.', AI1WMKE_PLUGIN_NAME ) );
		}

		// Set archive offset
		if ( ! isset( $params['archive_offset'] ) ) {
			$params['archive_offset'] = 0;
		}

		// Set archive size
		if ( ! isset( $params['archive_size'] ) ) {
			$params['archive_size'] = ai1wm_archive_bytes( $params );
		}

		// Get archive chunks
		$archive_chunks = Ai1wmke_Mega_Utils::get_chunks( ai1wm_archive_bytes( $params ) );

		// Set archive chunk size
		if ( ! isset( $params['archive_chunk_size'] ) ) {
			if ( ! empty( $archive_chunks[ $params['archive_offset'] ] ) ) {
				$params['archive_chunk_size'] = $archive_chunks[ $params['archive_offset'] ];
			}
		}

		// Set file key
		if ( ! isset( $params['file_key'] ) ) {
			$params['file_key'] = array();
		}

		// Set file mac
		if ( ! isset( $params['file_mac'] ) ) {
			$params['file_mac'] = array();
		}

		// Set upload retries
		if ( ! isset( $params['upload_retries'] ) ) {
			$params['upload_retries'] = 0;
		}

		// Set upload backoff
		if ( ! isset( $params['upload_backoff'] ) ) {
			$params['upload_backoff'] = 1;
		}

		// Set Mega client
		if ( is_null( $mega ) ) {
			$mega = new Ai1wmke_Mega_Client(
				get_option( 'ai1wmke_mega_user_email', false ),
				get_option( 'ai1wmke_mega_user_password', false )
			);
		}

		$mega->load_user_session( get_option( 'ai1wmke_mega_user_session', false ) );

		// Open the archive file for reading
		$archive = fopen( ai1wm_archive_path( $params ), 'rb' );

		// Read file chunk data
		if ( ( fseek( $archive, $params['archive_offset'] ) !== -1 )
				&& ( $file_chunk_data = fread( $archive, $params['archive_chunk_size'] ) ) ) {

			$mega->load_upload_url( $params['upload_url'] );

			try {

				$params['upload_retries'] += 1;
				$params['upload_backoff'] *= 2;

				// Upload file chunk data
				$params['upload_id'] = $mega->upload_file_chunk( $file_chunk_data, $params['file_key'], $params['file_mac'], $params['archive_offset'] );

				// Unset upload retries
				unset( $params['upload_retries'] );
				unset( $params['upload_backoff'] );

			} catch ( Ai1wmke_Connect_Exception $e ) {
				sleep( $params['upload_backoff'] );
				if ( $params['upload_retries'] <= 5 ) {
					return $params;
				}

				throw $e;
			}

			// Set archive offset
			$params['archive_offset'] = ftell( $archive );

			// Set archive chunk size
			if ( isset( $archive_chunks[ $params['archive_offset'] ] ) ) {
				$params['archive_chunk_size'] = $archive_chunks[ $params['archive_offset'] ];
			} else {
				$params['archive_chunk_size'] = AI1WMKE_MEGA_FILE_CHUNK_SIZE;
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
							'<i class="ai1wmke-mega-icon"></i> ' .
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

			// Complete upload file chunk data
			if ( ai1wmke_is_incremental( 'mega' ) ) {
				$file_info = $mega->upload_complete( $params['archive'], $params['upload_id'], $params['incremental_node_id'], $params['file_key'], $params['file_mac'] );
			} else {
				$file_info = $mega->upload_complete( $params['archive'], $params['upload_id'], $params['node_id'], $params['file_key'], $params['file_mac'] );
			}

			// Set file ID
			$params['file_id'] = $file_info->get_node_id();

			// Set last backup date
			update_option( 'ai1wmke_mega_timestamp', time() );

			// Unset upload ID
			unset( $params['upload_id'] );

			// Unset upload URL
			unset( $params['upload_url'] );

			// Unset archive offset
			unset( $params['archive_offset'] );

			// Unset archive size
			unset( $params['archive_size'] );

			// Unset archive chunk size
			unset( $params['archive_chunk_size'] );

			// Unset file key
			unset( $params['file_key'] );

			// Unset file mac
			unset( $params['file_mac'] );

			// Unset completed
			unset( $params['completed'] );
		}

		// Close the archive file
		fclose( $archive );

		return $params;
	}
}
