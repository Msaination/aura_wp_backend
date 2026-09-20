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

class Ai1wmke_B2_Export_Connect {

	public static function execute( $params, $b2 = null ) {

		// Set progress
		Ai1wm_Status::info( __( 'Connecting to Backblaze B2...', AI1WMKE_PLUGIN_NAME ) );

		// Get pre-calculated CRC
		$archive_crc_value = null;
		if ( isset( $params['archive_crc_value'] ) ) {
			$archive_crc_value = $params['archive_crc_value'];
		}

		// Open the archive file for writing
		$archive = new Ai1wm_Compressor( ai1wm_archive_path( $params ) );

		// Append EOF block with pre-calculated CRC
		$archive->close( true, $archive_crc_value );

		// Set Backblaze B2 client
		if ( is_null( $b2 ) ) {
			$b2 = new Ai1wmke_B2_Client(
				get_option( 'ai1wmke_b2_account_id', false ),
				get_option( 'ai1wmke_b2_application_key', false )
			);
		}

		$b2->authorize_account();

		// Get bucket name
		$params['bucket_name'] = get_option( 'ai1wmke_b2_bucket_name', ai1wm_archive_bucket() );

		// Bucket already exists?
		$buckets = $b2->list_buckets();
		if ( ! ( $params['bucket_id'] = array_search( $params['bucket_name'], $buckets ) ) ) {
			$params['bucket_id'] = $b2->create_bucket( $params['bucket_name'] );
		}

		// Get folder name
		$params['folder_name'] = get_option( 'ai1wmke_b2_folder_name', '' );

		// Set progress
		Ai1wm_Status::info( __( 'Done connecting to Backblaze B2.', AI1WMKE_PLUGIN_NAME ) );

		return $params;
	}
}
