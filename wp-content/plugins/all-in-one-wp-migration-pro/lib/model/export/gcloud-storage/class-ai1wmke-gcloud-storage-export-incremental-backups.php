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

class Ai1wmke_GCloud_Storage_Export_Incremental_Backups {

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
				$gcloud->get_file( $incremental_list, sprintf( '/%s/incremental-backups/incremental.backups.list', $params['folder_name'] ), $params['bucket_name'] );
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

		// Append backup file to incremental files
		$incremental_files[] = array( ai1wm_archive_name( $params ), ai1wm_archive_bytes( $params ), ai1wm_archive_mtime( $params ) );

		// Write incremental files
		if ( ( $incremental_list = ai1wm_open( ai1wm_incremental_backups_list_path( $params ), 'wb' ) ) ) {
			foreach ( $incremental_files as $file_index => $file_meta ) {
				ai1wm_putcsv( $incremental_list, array( $file_index, $file_meta[0], $file_meta[1], $file_meta[2] ) );
			}

			ai1wm_close( $incremental_list );
		}

		// Upload incremental content files
		if ( ( $incremental_content_list = ai1wm_open( ai1wm_incremental_content_list_path( $params ), 'rb' ) ) ) {
			if ( ( $incremental_content_files = ai1wm_read( $incremental_content_list, filesize( ai1wm_incremental_content_list_path( $params ) ) ) ) !== false ) {
				$gcloud->upload_file( $incremental_content_files, sprintf( '/%s/incremental-backups/incremental.content.list', $params['folder_name'] ), $params['bucket_name'] );
			}

			ai1wm_close( $incremental_content_list );
		}

		// Upload incremental media files
		if ( ( $incremental_media_list = ai1wm_open( ai1wm_incremental_media_list_path( $params ), 'rb' ) ) ) {
			if ( ( $incremental_media_files = ai1wm_read( $incremental_media_list, filesize( ai1wm_incremental_media_list_path( $params ) ) ) ) !== false ) {
				$gcloud->upload_file( $incremental_media_files, sprintf( '/%s/incremental-backups/incremental.media.list', $params['folder_name'] ), $params['bucket_name'] );
			}

			ai1wm_close( $incremental_media_list );
		}

		// Upload incremental plugins files
		if ( ( $incremental_plugins_list = ai1wm_open( ai1wm_incremental_plugins_list_path( $params ), 'rb' ) ) ) {
			if ( ( $incremental_plugins_files = ai1wm_read( $incremental_plugins_list, filesize( ai1wm_incremental_plugins_list_path( $params ) ) ) ) !== false ) {
				$gcloud->upload_file( $incremental_plugins_files, sprintf( '/%s/incremental-backups/incremental.plugins.list', $params['folder_name'] ), $params['bucket_name'] );
			}

			ai1wm_close( $incremental_plugins_list );
		}

		// Upload incremental themes files
		if ( ( $incremental_themes_list = ai1wm_open( ai1wm_incremental_themes_list_path( $params ), 'rb' ) ) ) {
			if ( ( $incremental_themes_files = ai1wm_read( $incremental_themes_list, filesize( ai1wm_incremental_themes_list_path( $params ) ) ) ) !== false ) {
				$gcloud->upload_file( $incremental_themes_files, sprintf( '/%s/incremental-backups/incremental.themes.list', $params['folder_name'] ), $params['bucket_name'] );
			}

			ai1wm_close( $incremental_themes_list );
		}

		// Upload incremental backups files
		if ( ( $incremental_backups_list = ai1wm_open( ai1wm_incremental_backups_list_path( $params ), 'rb' ) ) ) {
			if ( ( $incremental_backups_files = ai1wm_read( $incremental_backups_list, filesize( ai1wm_incremental_backups_list_path( $params ) ) ) ) !== false ) {
				$gcloud->upload_file( $incremental_backups_files, sprintf( '/%s/incremental-backups/incremental.backups.list', $params['folder_name'] ), $params['bucket_name'] );
			}

			ai1wm_close( $incremental_backups_list );
		}

		// Set progress
		Ai1wm_Status::info( __( 'Done preparing incremental backup files.', AI1WMKE_PLUGIN_NAME ) );

		return $params;
	}
}
