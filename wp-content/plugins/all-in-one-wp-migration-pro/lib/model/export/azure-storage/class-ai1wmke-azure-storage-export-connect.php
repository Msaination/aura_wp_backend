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

class Ai1wmke_Azure_Storage_Export_Connect {

	public static function execute( $params, $azure = null ) {

		// Set progress
		Ai1wm_Status::info( __( 'Connecting to Microsoft Azure Storage...', AI1WMKE_PLUGIN_NAME ) );

		// Get pre-calculated CRC
		$archive_crc_value = null;
		if ( isset( $params['archive_crc_value'] ) ) {
			$archive_crc_value = $params['archive_crc_value'];
		}

		// Open archive file
		$archive = new Ai1wm_Compressor( ai1wm_archive_path( $params ) );

		// Append EOF block with pre-calculated CRC
		$archive->close( true, $archive_crc_value );

		// Set Azure Storage client
		if ( is_null( $azure ) ) {
			$azure = new Ai1wmke_Azure_Storage_Client(
				get_option( 'ai1wmke_azure_storage_account_name', false ),
				get_option( 'ai1wmke_azure_storage_account_key', false )
			);
		}

		// Share name
		$params['share_name'] = get_option( 'ai1wmke_azure_storage_share_name', ai1wm_archive_share() );

		// Create share if does not exist
		if ( ! $azure->is_share_available( $params['share_name'] ) ) {
			$azure->create_share( $params['share_name'] );
		}

		// Get folder name
		$params['folder_name'] = get_option( 'ai1wmke_azure_storage_folder_name', '' );

		// Create upload session
		$azure->create_upload_session( sprintf( '/%s/%s', $params['folder_name'], ai1wm_archive_name( $params ) ), ai1wm_archive_bytes( $params ), $params['share_name'] );

		// Set progress
		Ai1wm_Status::info( __( 'Done connecting to Microsoft Azure Storage.', AI1WMKE_PLUGIN_NAME ) );

		return $params;
	}
}
