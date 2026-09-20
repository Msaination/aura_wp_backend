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

class Ai1wmke_Mega_Export_Connect {

	public static function execute( $params, $mega = null ) {

		// Set progress
		Ai1wm_Status::info( __( 'Connecting to Mega...', AI1WMKE_PLUGIN_NAME ) );

		// Get pre-calculated CRC
		$archive_crc_value = null;
		if ( isset( $params['archive_crc_value'] ) ) {
			$archive_crc_value = $params['archive_crc_value'];
		}

		// Open the archive file for writing
		$archive = new Ai1wm_Compressor( ai1wm_archive_path( $params ) );

		// Append EOF block with pre-calculated CRC
		$archive->close( true, $archive_crc_value );

		// Set Mega client
		if ( is_null( $mega ) ) {
			$mega = new Ai1wmke_Mega_Client(
				get_option( 'ai1wmke_mega_user_email', false ),
				get_option( 'ai1wmke_mega_user_password', false )
			);
		}

		$mega->load_user_session( get_option( 'ai1wmke_mega_user_session', false ) );

		// Create folder if does not exist
		if ( ! ( $node_item = $mega->get_node_item_by_id( get_option( 'ai1wmke_mega_node_id', false ) ) ) ) {
			if ( ! ( $node_item = $mega->get_node_item_by_name( ai1wm_archive_folder() ) ) ) {
				$node_item = $mega->create( ai1wm_archive_folder() );
			}
		}

		if ( ai1wmke_is_incremental( 'mega' ) ) {
			if ( ! ( $incremental_node_item = $mega->get_node_item_by_name( 'incremental-backups', $node_item->get_node_id() ) ) ) {
				$incremental_node_item = $mega->create( 'incremental-backups', $node_item->get_node_id() );
			}

			// Get incremental node ID
			$params['incremental_node_id'] = $incremental_node_item->get_node_id();
		}

		// Get node ID
		$params['node_id'] = $node_item->get_node_id();

		// Get upload URL
		$params['upload_url'] = $mega->get_upload_url( ai1wm_archive_bytes( $params ) );

		// Set progress
		Ai1wm_Status::info( __( 'Done connecting to Mega.', AI1WMKE_PLUGIN_NAME ) );

		return $params;
	}
}
