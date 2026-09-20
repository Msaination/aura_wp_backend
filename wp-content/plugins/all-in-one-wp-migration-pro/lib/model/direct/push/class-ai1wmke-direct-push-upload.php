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

class Ai1wmke_Direct_Push_Upload {

	public static function execute( $params, $direct_client = null, $token_client = null ) {
		$params['completed'] = false;

		// Validate upload URL
		if ( ! isset( $params['upload_url'] ) ) {
			throw new Ai1wmke_Push_Exception( __( 'Direct Upload URL is not specified.', AI1WMKE_PLUGIN_NAME ) );
		}

		// Set Token client
		if ( is_null( $token_client ) ) {
			$token_client = new Ai1wmke_Direct_Token_Client();
		}

		// Set archive offset
		if ( ! isset( $params['archive_offset'] ) ) {
			$params['archive_offset'] = 0;
		}

		// Set archive size
		if ( ! isset( $params['archive_size'] ) ) {
			$params['archive_size'] = ai1wm_archive_bytes( $params );
		}

		// Set file chunk size for upload
		if ( ! isset( $params['file_chunk_size'] ) ) {
			$params['file_chunk_size'] = get_option( 'ai1wmke_direct_file_chunk_size', AI1WMKE_DIRECT_FILE_CHUNK_SIZE );
		}

		// Set file range start
		if ( ! isset( $params['file_range_start'] ) ) {
			$params['file_range_start'] = 0;
		}

		// Set file range end
		if ( ! isset( $params['file_range_end'] ) ) {
			$params['file_range_end'] = min( $params['file_chunk_size'], $params['archive_size'] ) - 1;
		}

		// Set upload retries
		if ( ! isset( $params['upload_retries'] ) ) {
			$params['upload_retries'] = 0;
		}

		// Set Direct client
		if ( is_null( $direct_client ) ) {
			if ( isset( $params['push']['params'] ) ) {
				$upload_params = $params['push']['params'];
			} else {
				// Overwrite params so we set it for export
				$upload_params = array_merge(
					$params,
					array(
						'storage'             => strrev( $params['storage'] ),
						'secret_key'          => $token_client->get_key_from_token( $params['site_url'] ),
						'action'              => 'ai1wm_import',
						'ai1wm_manual_import' => true,
						'priority'            => 5,
					)
				);

				unset( $upload_params['direct_push'] );
			}

			$direct_client = new Ai1wmke_Direct_Client( $upload_params, get_option( 'ai1wmke_direct_ssl', false ) );
		}

		// Open the archive file for reading
		$archive = fopen( ai1wm_archive_path( $params ), 'rb' );

		// Read file chunk data
		if ( ( fseek( $archive, $params['archive_offset'] ) !== - 1 ) && ( $file_chunk_data = fread( $archive, $params['file_chunk_size'] ) ) ) {
			$direct_client->load_upload_url( $params['upload_url'] );

			try {

				$params['upload_retries'] += 1;

				// Upload file chunk data
				$response = $direct_client->upload_file_chunk( $file_chunk_data );

				// Only update params when response includes params and not on file upload
				if ( isset( $response['priority'] ) ) {
					$params['push'] = array( 'params' => $response );
				}
			} catch ( Ai1wmke_Error_Exception $e ) {
				// File too big reduce chunk size
				if ( $e->getCode() === 413 ) {
					$params['file_chunk_size'] = ceil( $params['file_chunk_size'] / 2 );

					return $params;
				}

				if ( $params['upload_retries'] <= 3 ) {
					return $params;
				}

				throw $e;
			}

			// Set archive offset
			$params['archive_offset'] = ftell( $archive );

			// Set file range start
			if ( $params['archive_size'] <= ( $params['file_range_start'] + $params['file_chunk_size'] ) ) {
				$params['file_range_start'] = $params['archive_size'] - 1;
			} else {
				$params['file_range_start'] = $params['file_range_start'] + $params['file_chunk_size'];
			}

			// Set file range end
			if ( $params['archive_size'] <= ( $params['file_range_end'] + $params['file_chunk_size'] ) ) {
				$params['file_range_end'] = $params['archive_size'] - 1;
			} else {
				$params['file_range_end'] = $params['file_range_end'] + $params['file_chunk_size'];
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
			update_option( 'ai1wmke_direct_timestamp', time() );

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
