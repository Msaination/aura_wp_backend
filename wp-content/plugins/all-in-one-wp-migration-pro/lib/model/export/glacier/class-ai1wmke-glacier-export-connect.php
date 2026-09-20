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

class Ai1wmke_Glacier_Export_Connect {

	public static function execute( $params, $glacier = null ) {

		// Set progress
		Ai1wm_Status::info( __( 'Connecting to Amazon Glacier...', AI1WMKE_PLUGIN_NAME ) );

		// Get pre-calculated CRC
		$archive_crc_value = null;
		if ( isset( $params['archive_crc_value'] ) ) {
			$archive_crc_value = $params['archive_crc_value'];
		}

		// Open the archive file for writing
		$archive = new Ai1wm_Compressor( ai1wm_archive_path( $params ) );

		// Append EOF block with pre-calculated CRC
		$archive->close( true, $archive_crc_value );

		// Set Amazon Glacier client
		if ( is_null( $glacier ) ) {
			$glacier = new Ai1wmke_Glacier_Client(
				get_option( 'ai1wmke_glacier_account_id', false ),
				get_option( 'ai1wmke_glacier_access_key', ai1wmke_aws_access_key() ),
				get_option( 'ai1wmke_glacier_secret_key', ai1wmke_aws_secret_key() )
			);
		}

		// Get vault name
		$params['vault_name'] = get_option( 'ai1wmke_glacier_vault_name', ai1wm_archive_vault() );

		// Get region name
		$params['region_name'] = get_option( 'ai1wmke_glacier_region_name', ai1wmke_aws_region_name( AI1WMKE_GLACIER_REGION_NAME ) );

		// Get Region name
		$params['region_name'] = $glacier->get_vault_region( $params['vault_name'], $params['region_name'] );

		// Create vault if does not exist
		if ( ! $glacier->is_vault_available( $params['vault_name'], $params['region_name'] ) ) {
			$glacier->create_vault( $params['vault_name'] );
		}

		// Set file chunk size for upload
		$file_chunk_size = get_option( 'ai1wmke_glacier_file_chunk_size', AI1WMKE_GLACIER_FILE_CHUNK_SIZE );

		// Get upload ID
		$params['upload_id'] = $glacier->upload_multipart( ai1wm_archive_name( $params ), $params['vault_name'], $params['region_name'], $file_chunk_size );

		return $params;
	}
}
