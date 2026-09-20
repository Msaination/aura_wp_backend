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

class Ai1wmke_Dropbox_Export_Connect {

	public static function execute( $params, $dropbox = null ) {

		// Set progress
		Ai1wm_Status::info( __( 'Connecting to Dropbox...', AI1WMKE_PLUGIN_NAME ) );

		// Get pre-calculated CRC
		$archive_crc_value = null;
		if ( isset( $params['archive_crc_value'] ) ) {
			$archive_crc_value = $params['archive_crc_value'];
		}

		// Open the archive file for writing
		$archive = new Ai1wm_Compressor( ai1wm_archive_path( $params ) );

		// Append EOF block with pre-calculated CRC
		$archive->close( true, $archive_crc_value );

		// Set Dropbox client
		if ( is_null( $dropbox ) ) {
			$dropbox = new Ai1wmke_Dropbox_Client(
				get_option( 'ai1wmke_dropbox_token', false ),
				get_option( 'ai1wmke_dropbox_ssl', true )
			);
		}

		// Get folder path
		$params['folder_path'] = get_option( 'ai1wmke_dropbox_folder_path', ai1wm_archive_folder() );

		// Create folder path
		if ( ai1wmke_is_incremental( 'dropbox' ) ) {
			if ( ! $dropbox->get_folder_path_by_path( sprintf( '/%s/incremental-backups', $params['folder_path'] ) ) ) {
				$dropbox->create_folder( sprintf( '/%s/incremental-backups', $params['folder_path'] ) );
			}
		} else {
			if ( ! $dropbox->get_folder_path_by_path( sprintf( '/%s', $params['folder_path'] ) ) ) {
				$dropbox->create_folder( sprintf( '/%s', $params['folder_path'] ) );
			}
		}

		// Set progress
		Ai1wm_Status::info( __( 'Done connecting to Dropbox.', AI1WMKE_PLUGIN_NAME ) );

		return $params;
	}
}
