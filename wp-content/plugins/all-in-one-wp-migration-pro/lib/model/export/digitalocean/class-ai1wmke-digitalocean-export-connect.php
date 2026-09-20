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

class Ai1wmke_DigitalOcean_Export_Connect {

	public static function execute( $params, $digitalocean = null ) {

		// Get storage class
		if ( ! isset( $params['storage_class'] ) ) {
			$params['storage_class'] = get_option( 'ai1wmke_digitalocean_storage_class', AI1WMKE_DIGITALOCEAN_STORAGE_CLASS );
		}

		// Get bucket encryption
		if ( ! isset( $params['encryption'] ) ) {
			$params['encryption'] = get_option( 'ai1wmke_digitalocean_encryption', false );
		}

		// Get bucket name
		if ( ! isset( $params['bucket_name'] ) ) {
			$params['bucket_name'] = get_option( 'ai1wmke_digitalocean_bucket_name', ai1wm_archive_bucket() );
		}

		// Get region name
		if ( ! isset( $params['region_name'] ) ) {
			$params['region_name'] = get_option( 'ai1wmke_digitalocean_region_name', ai1wmke_aws_region_name( AI1WMKE_DIGITALOCEAN_REGION_NAME ) );
		}

		// Get folder name
		if ( ! isset( $params['folder_name'] ) ) {
			$params['folder_name'] = get_option( 'ai1wmke_digitalocean_folder_name', '' );
		}

		// Set progress
		Ai1wm_Status::info( __( 'Connecting to DigitalOcean Spaces...', AI1WMKE_PLUGIN_NAME ) );

		// Get pre-calculated CRC
		$archive_crc_value = null;
		if ( isset( $params['archive_crc_value'] ) ) {
			$archive_crc_value = $params['archive_crc_value'];
		}

		// Open the archive file for writing
		$archive = new Ai1wm_Compressor( ai1wm_archive_path( $params ) );

		// Append EOF block with pre-calculated CRC
		$archive->close( true, $archive_crc_value );

		// Set DigitalOcean Spaces client
		if ( is_null( $digitalocean ) ) {
			$digitalocean = new Ai1wmke_DigitalOcean_Client(
				get_option( 'ai1wmke_digitalocean_access_key', ai1wmke_aws_access_key() ),
				get_option( 'ai1wmke_digitalocean_secret_key', ai1wmke_aws_secret_key() )
			);
		}

		// Get region name
		$params['region_name'] = $digitalocean->get_bucket_region( $params['bucket_name'], $params['region_name'] );

		// Create bucket if does not exist
		if ( ! $digitalocean->is_bucket_available( $params['bucket_name'], $params['region_name'] ) ) {
			$digitalocean->create_bucket( $params['bucket_name'], $params['region_name'] );
		}

		// Get upload ID
		if ( ai1wmke_is_incremental( 'digitalocean' ) ) {
			$params['upload_id'] = $digitalocean->upload_multipart( sprintf( '/%s/incremental-backups/%s', $params['folder_name'], ai1wm_archive_name( $params ) ), $params['bucket_name'], $params['region_name'], $params['storage_class'], $params['encryption'] );
		} else {
			$params['upload_id'] = $digitalocean->upload_multipart( sprintf( '/%s/%s', $params['folder_name'], ai1wm_archive_name( $params ) ), $params['bucket_name'], $params['region_name'], $params['storage_class'], $params['encryption'] );
		}

		// Set progress
		Ai1wm_Status::info( __( 'Done connecting to DigitalOcean Spaces.', AI1WMKE_PLUGIN_NAME ) );

		return $params;
	}
}
