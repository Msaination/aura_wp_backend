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

class Ai1wmke_GCloud_Storage_Import_Incremental_Prepare {

	public static function execute( $params, $gcloud = null ) {

		// Set progress
		Ai1wm_Status::info( __( 'Preparing incremental backup files...', AI1WMKE_PLUGIN_NAME ) );

		// Set GCloud Storage client
		if ( is_null( $gcloud ) ) {
			$gcloud = new Ai1wmke_GCloud_Storage_Client(
				get_option( 'ai1wmke_gcloud_storage_token' ),
				get_option( 'ai1wmke_gcloud_storage_ssl', true )
			);
		}

		// Download incremental files
		if ( ( $incremental_list = ai1wm_open( ai1wm_incremental_backups_list_path( $params ), 'wb' ) ) ) {
			try {
				$gcloud->get_file( $incremental_list, sprintf( '/%s/incremental.backups.list', $params['folder_path'] ), $params['bucket_name'] );
			} catch ( Ai1wmke_Error_Exception $e ) {
			}

			ai1wm_close( $incremental_list );
		}

		$incremental_files = array();

		// Get incremental files
		if ( ( $incremental_list = ai1wm_open( ai1wm_incremental_backups_list_path( $params ), 'rb' ) ) ) {
			while ( ( $row = ai1wm_getcsv( $incremental_list ) ) !== false ) {
				list( $file_index, $file_path, $file_size, $file_mtime ) = $row;
				$incremental_files[ $file_index ] = array( $file_path, $file_size, $file_mtime ); // phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning
			}

			ai1wm_close( $incremental_list );
		}

		$total_backups_files_size = 1;

		// Get total backups files size
		if ( isset( $params['file_index'] ) ) {
			for ( $i = 0; $i <= $params['file_index']; $i++ ) {
				$total_backups_files_size += $incremental_files[ $i ][1];
			}
		}

		// Set total backups files size
		$params['total_backups_files_size'] = $total_backups_files_size;

		// Set file path
		$params['file_path'] = $incremental_files[0][0];

		// Set file size
		$params['file_size'] = $incremental_files[0][1];

		// Set progress
		Ai1wm_Status::info( __( 'Done preparing incremental backup files.', AI1WMKE_PLUGIN_NAME ) );

		return $params;
	}
}
