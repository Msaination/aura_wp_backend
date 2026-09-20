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

class Ai1wmke_GCloud_Storage_Export_Incremental_Plugins {

	public static function execute( $params, $gcloud = null ) {

		// Get bucket name
		if ( ! isset( $params['bucket_name'] ) ) {
			$params['bucket_name'] = get_option( 'ai1wmke_gcloud_storage_bucket_name', ai1wm_archive_bucket() );
		}

		// Get folder name
		if ( ! isset( $params['folder_name'] ) ) {
			$params['folder_name'] = get_option( 'ai1wmke_gcloud_storage_folder_name', '' );
		}

		// Set progress
		Ai1wm_Status::info( __( 'Preparing incremental plugin files...', AI1WMKE_PLUGIN_NAME ) );

		// Set GCloud Storage client
		if ( is_null( $gcloud ) ) {
			$gcloud = new Ai1wmke_GCloud_Storage_Client(
				get_option( 'ai1wmke_gcloud_storage_token' ),
				get_option( 'ai1wmke_gcloud_storage_ssl', true )
			);
		}

		// Download incremental files
		if ( ( $incremental_list = ai1wm_open( ai1wm_incremental_plugins_list_path( $params ), 'wb' ) ) ) {
			try {
				$gcloud->get_file( $incremental_list, sprintf( '/%s/incremental-backups/incremental.plugins.list', $params['folder_name'] ), $params['bucket_name'] );
			} catch ( Ai1wmke_Error_Exception $e ) {
			}

			ai1wm_close( $incremental_list );
		}

		$incremental_files = array();

		// Get incremental files
		if ( ( $incremental_list = ai1wm_open( ai1wm_incremental_plugins_list_path( $params ), 'rb' ) ) ) {
			while ( ( $row = ai1wm_getcsv( $incremental_list ) ) !== false ) {
				list( $file_abspath, $file_relpath, $file_size, $file_mtime ) = $row;
				$incremental_files[ $file_abspath ][] = array( $file_relpath, $file_size, $file_mtime ); // phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning
			}

			ai1wm_close( $incremental_list );
		}

		$plugins_files = array();

		// Get plugins files
		if ( ( $plugins_list = ai1wm_open( ai1wm_plugins_list_path( $params ), 'rb' ) ) ) {
			while ( ( $row = ai1wm_getcsv( $plugins_list ) ) !== false ) {
				list( $file_abspath, $file_relpath, $file_size, $file_mtime ) = $row;
				$plugins_files[ $file_abspath ][] = array( $file_relpath, $file_size, $file_mtime ); // phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning
			}

			ai1wm_close( $plugins_list );
		}

		// Compare incremental files
		foreach ( $incremental_files as $file_abspath => $file_attributes ) {
			if ( ! isset( $plugins_files[ $file_abspath ] ) ) {
				unset( $incremental_files[ $file_abspath ] );
			}
		}

		// Compare plugins files
		foreach ( $plugins_files as $file_abspath => $file_attributes ) {
			if ( isset( $incremental_files[ $file_abspath ] ) ) {
				foreach ( $file_attributes as $file_meta ) {
					if ( in_array( $file_meta, $incremental_files[ $file_abspath ] ) ) {
						unset( $plugins_files[ $file_abspath ] );
					}
				}
			}
		}

		// Append plugins files to incremental files
		$incremental_files = array_merge_recursive( $incremental_files, $plugins_files );

		// Write incremental files
		if ( ( $incremental_list = ai1wm_open( ai1wm_incremental_plugins_list_path( $params ), 'wb' ) ) ) {
			foreach ( $incremental_files as $file_abspath => $file_attributes ) {
				foreach ( $file_attributes as $file_meta ) {
					ai1wm_putcsv( $incremental_list, array( $file_abspath, $file_meta[0], $file_meta[1], $file_meta[2] ) );
				}
			}

			ai1wm_close( $incremental_list );
		}

		$total_plugins_files_count = $total_plugins_files_size = 1;

		// Write plugins files
		if ( ( $plugins_list = ai1wm_open( ai1wm_plugins_list_path( $params ), 'wb' ) ) ) {
			foreach ( $plugins_files as $file_abspath => $file_attributes ) {
				foreach ( $file_attributes as $file_meta ) {
					if ( ai1wm_putcsv( $plugins_list, array( $file_abspath, $file_meta[0], $file_meta[1], $file_meta[2] ) ) !== false ) {
						$total_plugins_files_count++;

						// Add current file size
						$total_plugins_files_size += $file_meta[1];
					}
				}
			}

			ai1wm_close( $plugins_list );
		}

		// Set progress
		Ai1wm_Status::info( __( 'Done preparing incremental plugin files.', AI1WMKE_PLUGIN_NAME ) );

		// Set total plugins files count
		$params['total_plugins_files_count'] = $total_plugins_files_count;

		// Set total plugins files size
		$params['total_plugins_files_size'] = $total_plugins_files_size;

		return $params;
	}
}
