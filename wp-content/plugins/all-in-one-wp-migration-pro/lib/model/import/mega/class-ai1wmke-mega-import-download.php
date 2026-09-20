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

class Ai1wmke_Mega_Import_Download {

	public static function execute( $params, $mega = null ) {

		$params['completed'] = false;

		// Validate file ID
		if ( ! isset( $params['file_id'] ) ) {
			throw new Ai1wm_Import_Exception( __( 'Mega File ID is not specified.', AI1WMKE_PLUGIN_NAME ) );
		}

		// Validate file size
		if ( ! isset( $params['file_size'] ) ) {
			throw new Ai1wm_Import_Exception( __( 'Mega File Size is not specified.', AI1WMKE_PLUGIN_NAME ) );
		}

		// Set Mega client
		if ( is_null( $mega ) ) {
			$mega = new Ai1wmke_Mega_Client(
				get_option( 'ai1wmke_mega_user_email', false ),
				get_option( 'ai1wmke_mega_user_password', false )
			);
		}

		$mega->load_user_session( get_option( 'ai1wmke_mega_user_session', false ) );

		// Set archive offset
		if ( ! isset( $params['archive_offset'] ) ) {
			$params['archive_offset'] = 0;
		}

		// Set file range start
		if ( ! isset( $params['file_range_start'] ) ) {
			$params['file_range_start'] = 0;
		}

		// Get file chunks
		$file_chunks = Ai1wmke_Mega_Utils::get_chunks( $params['file_size'] );

		// Set file chunk size
		if ( ! isset( $params['file_chunk_size'] ) ) {
			if ( ! empty( $file_chunks[ $params['file_range_start'] ] ) ) {
				$params['file_chunk_size'] = $file_chunks[ $params['file_range_start'] ];
			}
		}

		// Set file range end
		if ( ! isset( $params['file_range_end'] ) ) {
			$params['file_range_end'] = $params['file_chunk_size'] - 1;
		}

		// Set download retries
		if ( ! isset( $params['download_retries'] ) ) {
			$params['download_retries'] = 0;
		}

		// Set download backoff
		if ( ! isset( $params['download_backoff'] ) ) {
			$params['download_backoff'] = 1;
		}

		// Set file key
		if ( ! isset( $params['file_key'] ) ) {
			$params['file_key'] = $mega->get_node_key_by_id( $params['file_id'] );
		}

		// Set download URL
		if ( ! isset( $params['download_url'] ) ) {
			$params['download_url'] = $mega->get_download_url( $params['file_id'] );
		}

		$mega->load_download_url( $params['download_url'] );

		// Open the archive file for writing
		if ( ( $archive = fopen( ai1wm_archive_path( $params ), 'cb' ) ) ) {
			if ( ( fseek( $archive, $params['archive_offset'] ) !== -1 ) ) {
				try {

					$params['download_retries'] += 1;
					$params['download_backoff'] *= 2;

					// Download file chunk data
					$mega->download_file_chunk( $archive, $params['file_key'], $params['file_range_start'], $params['file_range_end'] );

					// Unset download retries
					unset( $params['download_retries'] );
					unset( $params['download_backoff'] );

				} catch ( Ai1wmke_Connect_Exception $e ) {
					sleep( $params['download_backoff'] );
					if ( $params['download_retries'] <= 5 ) {
						return $params;
					}

					throw $e;
				}
			}

			// Set archive offset
			$params['archive_offset'] = ftell( $archive );

			// Set file chunk size
			if ( isset( $file_chunks[ $params['file_range_start'] ] ) ) {
				$params['file_chunk_size'] = $file_chunks[ $params['file_range_start'] ];
			} else {
				$params['file_chunk_size'] = AI1WMKE_MEGA_FILE_CHUNK_SIZE;
			}

			// Set file range start
			if ( $params['file_size'] <= ( $params['file_range_start'] + $params['file_chunk_size'] ) ) {
				$params['file_range_start'] = $params['file_size'] - 1;
			} else {
				$params['file_range_start'] = $params['file_range_start'] + $params['file_chunk_size'];
			}

			// Set file chunk size
			if ( isset( $file_chunks[ $params['file_range_end'] + 1 ] ) ) {
				$params['file_chunk_size'] = $file_chunks[ $params['file_range_end'] + 1 ];
			} else {
				$params['file_chunk_size'] = AI1WMKE_MEGA_FILE_CHUNK_SIZE;
			}

			// Set file range end
			if ( $params['file_size'] <= ( $params['file_range_end'] + $params['file_chunk_size'] ) ) {
				$params['file_range_end'] = $params['file_size'] - 1;
			} else {
				$params['file_range_end'] = $params['file_range_end'] + $params['file_chunk_size'];
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

				// Unset file key
				unset( $params['file_key'] );

				// Unset file size
				unset( $params['file_size'] );

				// Unset download URL
				unset( $params['download_url'] );

				// Unset archive offset
				unset( $params['archive_offset'] );

				// Unset file range start
				unset( $params['file_range_start'] );

				// Unset file chunk size
				unset( $params['file_chunk_size'] );

				// Unset file range end
				unset( $params['file_range_end'] );

				// Unset completed
				unset( $params['completed'] );
			}

			// Close the archive file
			fclose( $archive );
		}

		return $params;
	}
}
