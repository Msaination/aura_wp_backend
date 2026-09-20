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

class Ai1wmke_Box_Export_Connect {

	public static function execute( $params, $box = null ) {

		// Set progress
		Ai1wm_Status::info( __( 'Connecting to Box...', AI1WMKE_PLUGIN_NAME ) );

		// Get pre-calculated CRC
		$archive_crc_value = null;
		if ( isset( $params['archive_crc_value'] ) ) {
			$archive_crc_value = $params['archive_crc_value'];
		}

		// Open the archive file for writing
		$archive = new Ai1wm_Compressor( ai1wm_archive_path( $params ) );

		// Append EOF block with pre-calculated CRC
		$archive->close( true, $archive_crc_value );

		// Set Box client
		if ( is_null( $box ) ) {
			$box = new Ai1wmke_Box_Client(
				get_option( 'ai1wmke_box_token', false ),
				get_option( 'ai1wmke_box_ssl', true )
			);
		}

		// Get parent ID
		$params['parent_id'] = get_option( 'ai1wmke_box_folder_id', false );

		// Create folder
		if ( ! ( $params['parent_id'] = $box->get_folder_id_by_id( $params['parent_id'] ) ) ) {
			if ( ! ( $params['parent_id'] = $box->get_folder_id_by_name( ai1wm_archive_folder() ) ) ) {
				$params['parent_id'] = $box->create_folder( ai1wm_archive_folder() );
			}
		}

		// Set folder ID
		$params['folder_id'] = $box->create_folder( ai1wm_archive_name( $params ), $params['parent_id'] );

		// Set progress
		Ai1wm_Status::info( __( 'Done connecting to Box.', AI1WMKE_PLUGIN_NAME ) );

		return $params;
	}
}
