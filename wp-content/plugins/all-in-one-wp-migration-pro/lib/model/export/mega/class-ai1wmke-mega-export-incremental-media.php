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

class Ai1wmke_Mega_Export_Incremental_Media {

	public static function execute( $params, $mega = null ) {

		// Set incremental folder ID
		if ( ! isset( $params['incremental_node_id'] ) ) {
			$params['incremental_node_id'] = get_option( 'ai1wmke_mega_incremental_node_id', null );
		}

		// Set progress
		Ai1wm_Status::info( __( 'Preparing incremental media files...', AI1WMKE_PLUGIN_NAME ) );

		// Set Mega Spaces client
		if ( is_null( $mega ) ) {
			$mega = new Ai1wmke_Mega_Client(
				get_option( 'ai1wmke_mega_user_email', false ),
				get_option( 'ai1wmke_mega_user_password', false )
			);
		}

		$mega->load_user_session( get_option( 'ai1wmke_mega_user_session', false ) );

		// Download incremental files
		if ( ( $incremental_list = ai1wm_open( ai1wm_incremental_media_list_path( $params ), 'wb' ) ) ) {
			try {
				if ( ( $node_item = $mega->get_node_item_by_name( 'incremental.media.list', $params['incremental_node_id'] ) ) ) {
					$mega->load_download_url( $mega->get_download_url( $node_item->get_node_id() ) );
					$mega->download_file( $incremental_list, $node_item->get_key() );
				}
			} catch ( Ai1wmke_Error_Exception $e ) {
			}

			ai1wm_close( $incremental_list );
		}

		$incremental_files = array();

		// Get incremental files
		if ( ( $incremental_list = ai1wm_open( ai1wm_incremental_media_list_path( $params ), 'rb' ) ) ) {
			while ( ( $row = ai1wm_getcsv( $incremental_list ) ) !== false ) {
				list( $file_abspath, $file_relpath, $file_size, $file_mtime ) = $row;
				$incremental_files[ $file_abspath ][] = array( $file_relpath, $file_size, $file_mtime ); // phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning
			}

			ai1wm_close( $incremental_list );
		}

		$media_files = array();

		// Get media files
		if ( ( $media_list = ai1wm_open( ai1wm_media_list_path( $params ), 'rb' ) ) ) {
			while ( ( $row = ai1wm_getcsv( $media_list ) ) !== false ) {
				list( $file_abspath, $file_relpath, $file_size, $file_mtime ) = $row;
				$media_files[ $file_abspath ][] = array( $file_relpath, $file_size, $file_mtime ); // phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning
			}

			ai1wm_close( $media_list );
		}

		// Compare incremental files
		foreach ( $incremental_files as $file_abspath => $file_attributes ) {
			if ( ! isset( $media_files[ $file_abspath ] ) ) {
				unset( $incremental_files[ $file_abspath ] );
			}
		}

		// Compare media files
		foreach ( $media_files as $file_abspath => $file_attributes ) {
			if ( isset( $incremental_files[ $file_abspath ] ) ) {
				foreach ( $file_attributes as $file_meta ) {
					if ( in_array( $file_meta, $incremental_files[ $file_abspath ] ) ) {
						unset( $media_files[ $file_abspath ] );
					}
				}
			}
		}

		// Append media files to incremental files
		$incremental_files = array_merge_recursive( $incremental_files, $media_files );

		// Write incremental files
		if ( ( $incremental_list = ai1wm_open( ai1wm_incremental_media_list_path( $params ), 'wb' ) ) ) {
			foreach ( $incremental_files as $file_abspath => $file_attributes ) {
				foreach ( $file_attributes as $file_meta ) {
					ai1wm_putcsv( $incremental_list, array( $file_abspath, $file_meta[0], $file_meta[1], $file_meta[2] ) );
				}
			}

			ai1wm_close( $incremental_list );
		}

		$total_media_files_count = $total_media_files_size = 1;

		// Write media files
		if ( ( $media_list = ai1wm_open( ai1wm_media_list_path( $params ), 'wb' ) ) ) {
			foreach ( $media_files as $file_abspath => $file_attributes ) {
				foreach ( $file_attributes as $file_meta ) {
					if ( ai1wm_putcsv( $media_list, array( $file_abspath, $file_meta[0], $file_meta[1], $file_meta[2] ) ) !== false ) {
						$total_media_files_count++;

						// Add current file size
						$total_media_files_size += $file_meta[1];
					}
				}
			}

			ai1wm_close( $media_list );
		}

		// Set progress
		Ai1wm_Status::info( __( 'Done preparing incremental media files.', AI1WMKE_PLUGIN_NAME ) );

		// Set total media files count
		$params['total_media_files_count'] = $total_media_files_count;

		// Set total media files size
		$params['total_media_files_size'] = $total_media_files_size;

		return $params;
	}
}
