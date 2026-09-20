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

class Ai1wmke_GDrive_Import_Controller {

	public static function button() {
		return Ai1wm_Template::get_content(
			'import/button/button-gdrive',
			array( 'token' => get_option( 'ai1wmke_gdrive_token', false ) ),
			AI1WMKE_TEMPLATES_PATH
		);
	}

	public static function picker() {
		Ai1wm_Template::render(
			'import/picker/picker-gdrive',
			array(),
			AI1WMKE_TEMPLATES_PATH
		);
	}

	public static function browser( $params = array() ) {
		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_GET );
		}

		// Set folder ID
		$folder_id = 'root';
		if ( ! empty( $params['folder_id'] ) ) {
			$folder_id = trim( $params['folder_id'] );
		}

		// Set team drive ID
		$team_drive_id = null;
		if ( ! empty( $params['team_drive_id'] ) ) {
			$team_drive_id = trim( $params['team_drive_id'] );
		}

		// Set next page token
		$next_page_token = null;
		if ( ! empty( $params['next_page_token'] ) ) {
			$next_page_token = trim( $params['next_page_token'] );
		}

		// Set GDrive client
		$gdrive = new Ai1wmke_GDrive_Client(
			get_option( 'ai1wmke_gdrive_token', false ),
			get_option( 'ai1wmke_gdrive_ssl', true )
		);

		// Set drive structure
		$response = array( 'items' => array(), 'next_page_token' => null );

		try {

			// List drive or folder
			if ( ! empty( $team_drive_id ) ) {

				// Get drive items
				$result = $gdrive->list_folder_by_id( $folder_id, $team_drive_id, $next_page_token, "mimeType = 'application/vnd.google-apps.folder' or fileExtension = 'wpress'", array( 'orderBy' => 'folder,createdDate desc' ) );

				// Loop over folders and files
				if ( isset( $result['items'] ) ) {
					foreach ( $result['items'] as $item ) {
						$response['items'][] = array(
							'index'         => null,
							'id'            => isset( $item['id'] ) ? strval( $item['id'] ) : null,
							'name'          => isset( $item['name'] ) ? $item['name'] : null,
							'path'          => isset( $item['name'] ) ? $item['name'] : null,
							'date'          => isset( $item['date'] ) ? human_time_diff( $item['date'] ) : null,
							'bytes'         => isset( $item['bytes'] ) ? $item['bytes'] : null,
							'size'          => isset( $item['bytes'] ) ? ai1wm_size_format( $item['bytes'] ) : null,
							'type'          => isset( $item['type'] ) ? $item['type'] : null,
							'ext'           => isset( $item['ext'] ) ? $item['ext'] : null,
							'team_drive_id' => $team_drive_id,
						);
					}
				}

				// Set next page token
				if ( isset( $result['token'] ) ) {
					$response['next_page_token'] = $result['token'];
				}
			} else {

				// Get drives
				$drives = $gdrive->list_team_drives();

				// Loop over drives
				if ( isset( $drives['items'] ) ) {
					foreach ( $drives['items'] as $drive ) {
						$response['items'][] = array(
							'id'   => isset( $drive['id'] ) ? strval( $drive['id'] ) : null,
							'name' => isset( $drive['name'] ) ? $drive['name'] : null,
							'type' => isset( $drive['type'] ) ? $drive['type'] : null,
						);
					}
				}
			}
		} catch ( Ai1wmke_Error_Exception $e ) {
		}

		echo json_encode( $response );
		exit;
	}

	public static function incremental( $params = array() ) {
		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_GET );
		}

		// Set folder ID
		$folder_id = 'root';
		if ( ! empty( $params['folder_id'] ) ) {
			$folder_id = trim( $params['folder_id'] );
		}

		// Set Team Drive ID
		$team_drive_id = null;
		if ( ! empty( $params['team_drive_id'] ) ) {
			$team_drive_id = trim( $params['team_drive_id'] );
		}

		// Set GDrive client
		$gdrive = new Ai1wmke_GDrive_Client(
			get_option( 'ai1wmke_gdrive_token', false ),
			get_option( 'ai1wmke_gdrive_ssl', true )
		);

		try {
			if ( ( $response = $gdrive->list_folder_by_id( $folder_id, $team_drive_id, null, "title = 'incremental.backups.list'" ) ) ) {
				if ( isset( $response['items'][0]['id'] ) ) {
					$file_content = $gdrive->get_file_content( $response['items'][0]['id'] );
				}
			}
		} catch ( Ai1wmke_Error_Exception $e ) {
		}

		$items = array();
		if ( isset( $file_content ) ) {
			foreach ( str_getcsv( $file_content, "\n" ) as $row ) {
				if ( list( $file_index, $file_id, $file_path, $file_size, $file_mtime ) = str_getcsv( $row ) ) {
					$items[] = array(
						'index'         => $file_index,
						'id'            => $file_id,
						'name'          => sprintf( __( 'Restore point %d', AI1WMKE_PLUGIN_NAME ), $file_index ),
						'path'          => $file_path,
						'date'          => get_date_from_gmt( date( 'Y-m-d H:i:s', $file_mtime ), 'M j, Y g:i a' ),
						'size'          => ai1wm_size_format( $file_size ),
						'bytes'         => $file_size,
						'folder_id'     => $folder_id,
						'team_drive_id' => $team_drive_id,
						'type'          => 'application/octet-stream',
					);
				}
			}
		}

		echo json_encode( array( 'items' => array_reverse( $items ), 'cursor' => null ) );
		exit;
	}
}
