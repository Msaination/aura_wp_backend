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

class Ai1wmke_B2_Export_Upload {

	public static function execute( $params, $b2 = null ) {

		$params['completed'] = false;

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

		// Set file chunk number
		if ( ! isset( $params['file_chunk_number'] ) ) {
			$params['file_chunk_number'] = 1;
		}

		// Set upload retries
		if ( ! isset( $params['upload_retries'] ) ) {
			$params['upload_retries'] = 0;
		}

		// Set upload backoff
		if ( ! isset( $params['upload_backoff'] ) ) {
			$params['upload_backoff'] = 1;
		}

		// Set start retries
		if ( ! isset( $params['start_retries'] ) ) {
			$params['start_retries'] = 0;
		}

		// Set start backoff
		if ( ! isset( $params['start_backoff'] ) ) {
			$params['start_backoff'] = 1;
		}

		// Set Backblaze B2 client
		if ( is_null( $b2 ) ) {
			$b2 = new Ai1wmke_B2_Client(
				get_option( 'ai1wmke_b2_account_id', false ),
				get_option( 'ai1wmke_b2_application_key', false )
			);
		}

		$b2->authorize_account();

		// Open the archive file for reading
		$archive = fopen( ai1wm_archive_path( $params ), 'rb' );

		// Set file chunk size for upload
		$file_chunk_size = get_option( 'ai1wmke_b2_file_chunk_size', AI1WMKE_B2_FILE_CHUNK_SIZE );

		// Read file chunk data
		if ( ( fseek( $archive, $params['archive_offset'] ) !== -1 )
				&& ( $file_chunk_data = fread( $archive, $file_chunk_size ) ) ) {

			if ( $params['archive_size'] <= $file_chunk_size ) {
				try {

					$params['upload_retries'] += 1;
					$params['upload_backoff'] *= 2;

					// Get new upload part URL
					if ( ( $upload_part = $b2->get_upload_url( $params['bucket_id'] ) ) ) {
						if ( isset( $upload_part['uploadUrl'] ) ) {
							$b2->load_upload_url( $upload_part['uploadUrl'] );
						}

						if ( isset( $upload_part['authorizationToken'] ) ) {
							$b2->load_authorization_token( $upload_part['authorizationToken'] );
						}
					}

					// Upload file chunk data
					if ( ai1wmke_is_incremental( 'b2' ) ) {
						$params['file_id'] = $b2->upload_file( $file_chunk_data, sprintf( '/%s/incremental-backups/%s', $params['folder_name'], ai1wm_archive_name( $params ) ) );
					} else {
						$params['file_id'] = $b2->upload_file( $file_chunk_data, sprintf( '/%s/%s', $params['folder_name'], ai1wm_archive_name( $params ) ) );
					}

					// Unset upload retries
					unset( $params['upload_retries'] );
					unset( $params['upload_backoff'] );

				} catch ( Ai1wmke_Error_Exception $e ) {
					sleep( $params['upload_backoff'] );
					if ( $params['upload_retries'] <= 5 ) {
						return $params;
					}

					throw $e;
				}
			} else {
				if ( ! isset( $params['upload_url'] ) ) {
					try {

						$params['start_retries'] += 1;
						$params['start_backoff'] *= 2;

						// Set file ID
						if ( ai1wmke_is_incremental( 'b2' ) ) {
							$params['large_file_id'] = $b2->start_large_file( sprintf( '/%s/incremental-backups/%s', $params['folder_name'], ai1wm_archive_name( $params ) ), $params['bucket_id'] );
						} else {
							$params['large_file_id'] = $b2->start_large_file( sprintf( '/%s/%s', $params['folder_name'], ai1wm_archive_name( $params ) ), $params['bucket_id'] );
						}

						// Get new upload part URL
						if ( ( $upload_part = $b2->get_upload_part_url( $params['large_file_id'] ) ) ) {
							if ( isset( $upload_part['uploadUrl'] ) ) {
								$params['upload_url'] = $upload_part['uploadUrl'];
							}

							if ( isset( $upload_part['authorizationToken'] ) ) {
								$params['upload_authorization_token'] = $upload_part['authorizationToken'];
							}
						}

						// Unset start retries
						unset( $params['start_retries'] );
						unset( $params['start_backoff'] );

					} catch ( Ai1wmke_Error_Exception $e ) {
						sleep( $params['start_backoff'] );
						if ( $params['start_retries'] <= 5 ) {
							return $params;
						}

						throw $e;
					}
				}

				$b2->load_upload_url( $params['upload_url'] );
				$b2->load_authorization_token( $params['upload_authorization_token'] );

				try {

					$params['upload_retries'] += 1;
					$params['upload_backoff'] *= 2;

					// Upload file chunk data
					$file_chunk_sha1 = $b2->upload_part( $file_chunk_data, $params['file_chunk_number'] );

					// Add file chunk SHA1
					if ( ( $multipart = fopen( ai1wm_multipart_path( $params ), 'cb' ) ) ) {
						if ( fseek( $multipart, $params['multipart_offset'] ) !== -1 ) {
							fwrite( $multipart, $file_chunk_sha1 . PHP_EOL );
						}

						$params['multipart_offset'] = ftell( $multipart );

						fclose( $multipart );
					}

					// Set file chunk number
					$params['file_chunk_number'] += 1;

					// Unset upload retries
					unset( $params['upload_retries'] );
					unset( $params['upload_backoff'] );

				} catch ( Ai1wmke_Error_Exception $e ) {
					if ( isset( $params['upload_backoff'] ) ) {
						sleep( $params['upload_backoff'] );
					}

					try {

						$b2->authorize_account();

						// Get new upload part URL (503 Service Unavailable)
						if ( ( $upload_part = $b2->get_upload_part_url( $params['large_file_id'] ) ) ) {
							if ( isset( $upload_part['uploadUrl'] ) ) {
								$params['upload_url'] = $upload_part['uploadUrl'];
							}

							if ( isset( $upload_part['authorizationToken'] ) ) {
								$params['upload_authorization_token'] = $upload_part['authorizationToken'];
							}
						}
					} catch ( Ai1wmke_Error_Exception $e ) {
					}

					if ( $params['upload_retries'] <= 5 ) {
						return $params;
					}

					throw $e;
				}
			}

			// Set archive offset
			$params['archive_offset'] = ftell( $archive );

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
							'<i class="ai1wmke-b2-icon"></i> ' .
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

			// Add file chunk SHA1
			if ( isset( $params['large_file_id'] ) ) {
				$file_chunks = array();
				if ( ( $multipart = fopen( ai1wm_multipart_path( $params ), 'r' ) ) ) {
					while ( $file_chunk_sha1 = trim( fgets( $multipart ) ) ) {
						$file_chunks[] = $file_chunk_sha1;
					}

					fclose( $multipart );
				}

				// Finish upload file chunk data
				$params['file_id'] = $b2->finish_large_file( $file_chunks, $params['large_file_id'] );
			}

			// Set last backup date
			update_option( 'ai1wmke_b2_timestamp', time() );

			// Unset large file ID
			unset( $params['large_file_id'] );

			// Unset upload URL
			unset( $params['upload_url'] );

			// Unset upload authorization token
			unset( $params['upload_authorization_token'] );

			// Unset archive offset
			unset( $params['archive_offset'] );

			// Unset multipart offset
			unset( $params['multipart_offset'] );

			// Unset archive size
			unset( $params['archive_size'] );

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
