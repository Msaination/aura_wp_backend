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

class Ai1wmke_GDrive_Export_Connect {

	public static function execute( $params, $gdrive = null ) {

		// Set progress
		Ai1wm_Status::info( __( 'Connecting to Google Drive...', AI1WMKE_PLUGIN_NAME ) );

		// Get pre-calculated CRC
		$archive_crc_value = null;
		if ( isset( $params['archive_crc_value'] ) ) {
			$archive_crc_value = $params['archive_crc_value'];
		}

		// Open the archive file for writing
		$archive = new Ai1wm_Compressor( ai1wm_archive_path( $params ) );

		// Append EOF block with pre-calculated CRC
		$archive->close( true, $archive_crc_value );

		// Set GDrive client
		if ( is_null( $gdrive ) ) {
			$gdrive = new Ai1wmke_GDrive_Client(
				get_option( 'ai1wmke_gdrive_token', false ),
				get_option( 'ai1wmke_gdrive_ssl', true )
			);
		}

		// Get archive size
		$params['archive_size'] = ai1wm_archive_bytes( $params );

		// Get folder ID
		$params['folder_id'] = get_option( 'ai1wmke_gdrive_folder_id', false );

		// Get Team Drive ID
		$params['team_drive_id'] = get_option( 'ai1wmke_gdrive_team_drive_id', AI1WMKE_GDRIVE_TEAM_DRIVE_ID );

		// Create folder
		if ( ! ( $params['folder_id'] = $gdrive->get_folder_id_by_id( $params['folder_id'], $params['team_drive_id'] ) ) ) {
			if ( ! ( $params['folder_id'] = $gdrive->get_folder_id_by_name( ai1wm_archive_folder(), 'root', $params['team_drive_id'] ) ) ) {
				$params['folder_id'] = $gdrive->create_folder( ai1wm_archive_folder(), 'root', $params['team_drive_id'] );
			}
		}

		// Create incremental folder
		if ( ai1wmke_is_incremental( 'gdrive' ) ) {
			if ( ! ( $params['incremental_folder_id'] = $gdrive->get_folder_id_by_name( 'incremental-backups', $params['folder_id'], $params['team_drive_id'] ) ) ) {
				$params['incremental_folder_id'] = $gdrive->create_folder( 'incremental-backups', $params['folder_id'], $params['team_drive_id'] );
			}

			if ( ! empty( $params['incremental_folder_id'] ) ) {
				update_option( 'ai1wmke_gdrive_incremental_folder_id', $params['incremental_folder_id'] );
			}

			if ( ! isset( $params['upload_url'] ) ) {
				$params['upload_url'] = $gdrive->upload_resumable( ai1wm_archive_name( $params ), $params['archive_size'], $params['incremental_folder_id'], $params['team_drive_id'] );
			}
		} else {
			if ( ! isset( $params['upload_url'] ) ) {
				$params['upload_url'] = $gdrive->upload_resumable( ai1wm_archive_name( $params ), $params['archive_size'], $params['folder_id'], $params['team_drive_id'] );
			}
		}

		// Set progress
		Ai1wm_Status::info( __( 'Done connecting to Google Drive.', AI1WMKE_PLUGIN_NAME ) );

		return $params;
	}
}
