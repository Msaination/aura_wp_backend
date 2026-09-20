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

class Ai1wmke_Mega_Export_Incremental_Backups {

	public static function execute( $params, $mega = null ) {

		// Set incremental folder ID
		if ( ! isset( $params['incremental_node_id'] ) ) {
			$params['incremental_node_id'] = get_option( 'ai1wmke_mega_incremental_node_id', null );
		}

		// Set progress
		Ai1wm_Status::info( __( 'Preparing incremental backup files...', AI1WMKE_PLUGIN_NAME ) );

		// Set Mega Spaces client
		if ( is_null( $mega ) ) {
			$mega = new Ai1wmke_Mega_Client(
				get_option( 'ai1wmke_mega_user_email', false ),
				get_option( 'ai1wmke_mega_user_password', false )
			);
		}

		$mega->load_user_session( get_option( 'ai1wmke_mega_user_session', false ) );

		// Download incremental files
		if ( ( $incremental_list = ai1wm_open( ai1wm_incremental_backups_list_path( $params ), 'wb' ) ) ) {
			try {
				if ( ( $node_item = $mega->get_node_item_by_name( 'incremental.backups.list', $params['incremental_node_id'] ) ) ) {
					$mega->load_download_url( $mega->get_download_url( $node_item->get_node_id() ) );
					$mega->download_file( $incremental_list, $node_item->get_key() );
				}
			} catch ( Ai1wmke_Error_Exception $e ) {
			}

			ai1wm_close( $incremental_list );
		}

		$incremental_files = array();

		// Get incremental files
		if ( ( $incremental_list = ai1wm_open( ai1wm_incremental_backups_list_path( $params ), 'rb' ) ) ) {
			while ( ( $row = ai1wm_getcsv( $incremental_list ) ) !== false ) {
				list( $file_index, $file_id, $file_path, $file_size, $file_mtime ) = $row;
				$incremental_files[ $file_index ] = array( $file_id, $file_path, $file_size, $file_mtime ); // phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning
			}

			ai1wm_close( $incremental_list );
		}

		// Append backup file to incremental files
		$incremental_files[] = array( $params['file_id'], ai1wm_archive_name( $params ), ai1wm_archive_bytes( $params ), ai1wm_archive_mtime( $params ) );

		// Write incremental files
		if ( ( $incremental_list = ai1wm_open( ai1wm_incremental_backups_list_path( $params ), 'wb' ) ) ) {
			foreach ( $incremental_files as $file_index => $file_meta ) {
				ai1wm_putcsv( $incremental_list, array( $file_index, $file_meta[0], $file_meta[1], $file_meta[2], $file_meta[3] ) );
			}

			ai1wm_close( $incremental_list );
		}

		// Upload incremental content files
		if ( ( $incremental_content_list = ai1wm_open( ai1wm_incremental_content_list_path( $params ), 'rb' ) ) ) {
			if ( ( $incremental_content_files = ai1wm_read( $incremental_content_list, filesize( ai1wm_incremental_content_list_path( $params ) ) ) ) !== false ) {
				$mega->load_upload_url( $mega->get_upload_url( filesize( ai1wm_incremental_content_list_path( $params ) ) ) );
				$mega->upload_file( $incremental_content_files, 'incremental.content.list', $params['incremental_node_id'] );
			}

			ai1wm_close( $incremental_content_list );
		}

		// Upload incremental media files
		if ( ( $incremental_media_list = ai1wm_open( ai1wm_incremental_media_list_path( $params ), 'rb' ) ) ) {
			if ( ( $incremental_media_files = ai1wm_read( $incremental_media_list, filesize( ai1wm_incremental_media_list_path( $params ) ) ) ) !== false ) {
				$mega->load_upload_url( $mega->get_upload_url( filesize( ai1wm_incremental_media_list_path( $params ) ) ) );
				$mega->upload_file( $incremental_media_files, 'incremental.media.list', $params['incremental_node_id'] );
			}

			ai1wm_close( $incremental_media_list );
		}

		// Upload incremental plugins files
		if ( ( $incremental_plugins_list = ai1wm_open( ai1wm_incremental_plugins_list_path( $params ), 'rb' ) ) ) {
			if ( ( $incremental_plugins_files = ai1wm_read( $incremental_plugins_list, filesize( ai1wm_incremental_plugins_list_path( $params ) ) ) ) !== false ) {
				$mega->load_upload_url( $mega->get_upload_url( filesize( ai1wm_incremental_plugins_list_path( $params ) ) ) );
				$mega->upload_file( $incremental_plugins_files, 'incremental.plugins.list', $params['incremental_node_id'] );
			}

			ai1wm_close( $incremental_plugins_list );
		}

		// Upload incremental themes files
		if ( ( $incremental_themes_list = ai1wm_open( ai1wm_incremental_themes_list_path( $params ), 'rb' ) ) ) {
			if ( ( $incremental_themes_files = ai1wm_read( $incremental_themes_list, filesize( ai1wm_incremental_themes_list_path( $params ) ) ) ) !== false ) {
				$mega->load_upload_url( $mega->get_upload_url( filesize( ai1wm_incremental_themes_list_path( $params ) ) ) );
				$mega->upload_file( $incremental_themes_files, 'incremental.themes.list', $params['incremental_node_id'] );
			}

			ai1wm_close( $incremental_themes_list );
		}

		// Upload incremental backups files
		if ( ( $incremental_backups_list = ai1wm_open( ai1wm_incremental_backups_list_path( $params ), 'rb' ) ) ) {
			if ( ( $incremental_backups_files = ai1wm_read( $incremental_backups_list, filesize( ai1wm_incremental_backups_list_path( $params ) ) ) ) !== false ) {
				$mega->load_upload_url( $mega->get_upload_url( filesize( ai1wm_incremental_backups_list_path( $params ) ) ) );
				$mega->upload_file( $incremental_backups_files, 'incremental.backups.list', $params['incremental_node_id'] );
			}

			ai1wm_close( $incremental_backups_list );
		}

		// Set progress
		Ai1wm_Status::info( __( 'Done preparing incremental backup files.', AI1WMKE_PLUGIN_NAME ) );

		return $params;
	}
}
