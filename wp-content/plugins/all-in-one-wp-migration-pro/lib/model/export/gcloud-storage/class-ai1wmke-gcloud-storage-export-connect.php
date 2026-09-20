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

class Ai1wmke_GCloud_Storage_Export_Connect {

	public static function execute( $params, $gcloud = null ) {

		// Get storage class
		if ( ! isset( $params['storage_class'] ) ) {
			$params['storage_class'] = get_option( 'ai1wmke_gcloud_storage_class', AI1WMKE_GCLOUD_STORAGE_CLASS );
		}

		// Get project ID
		if ( ! isset( $params['project_id'] ) ) {
			$params['project_id'] = get_option( 'ai1wmke_gcloud_storage_project_id', ai1wm_archive_project() );
		}

		// Get bucket name
		if ( ! isset( $params['bucket_name'] ) ) {
			$params['bucket_name'] = get_option( 'ai1wmke_gcloud_storage_bucket_name', ai1wm_archive_bucket() );
		}

		// Get folder name
		if ( ! isset( $params['folder_name'] ) ) {
			$params['folder_name'] = get_option( 'ai1wmke_gcloud_storage_folder_name', '' );
		}

		// Set progress
		Ai1wm_Status::info( __( 'Connecting to Google Cloud Storage...', AI1WMKE_PLUGIN_NAME ) );

		// Get pre-calculated CRC
		$archive_crc_value = null;
		if ( isset( $params['archive_crc_value'] ) ) {
			$archive_crc_value = $params['archive_crc_value'];
		}

		// Open achive file
		$archive = new Ai1wm_Compressor( ai1wm_archive_path( $params ) );

		// Append EOF block with pre-calculated CRC
		$archive->close( true, $archive_crc_value );

		// Set GCloud Storage client
		if ( is_null( $gcloud ) ) {
			$gcloud = new Ai1wmke_GCloud_Storage_Client(
				get_option( 'ai1wmke_gcloud_storage_token' ),
				get_option( 'ai1wmke_gcloud_storage_ssl', true )
			);
		}

		// Create project if does not exist
		if ( ! $gcloud->is_project_available( $params['project_id'] ) ) {
			$gcloud->create_project( $params['project_id'] );
		}

		// Create bucket if does not exist
		if ( ! $gcloud->is_bucket_available( $params['bucket_name'] ) ) {
			$gcloud->create_bucket( $params['bucket_name'], $params['project_id'], $params['storage_class'] );
		}

		// Set upload URL
		if ( ai1wmke_is_incremental( 'gcloud-storage' ) ) {
			$params['upload_url'] = $gcloud->upload_resumable( sprintf( '/%s/incremental-backups/%s', $params['folder_name'], ai1wm_archive_name( $params ) ), ai1wm_archive_bytes( $params ), $params['bucket_name'] );
		} else {
			$params['upload_url'] = $gcloud->upload_resumable( sprintf( '/%s/%s', $params['folder_name'], ai1wm_archive_name( $params ) ), ai1wm_archive_bytes( $params ), $params['bucket_name'] );
		}

		// Set progress
		Ai1wm_Status::info( __( 'Done connecting to Google Cloud Storage.', AI1WMKE_PLUGIN_NAME ) );

		return $params;
	}
}
