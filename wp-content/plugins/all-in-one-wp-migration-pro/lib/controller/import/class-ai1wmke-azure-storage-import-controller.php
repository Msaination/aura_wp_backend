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

class Ai1wmke_Azure_Storage_Import_Controller {

	public static function button() {
		return Ai1wm_Template::get_content(
			'import/button/button-azure-storage',
			array( 'account_name' => get_option( 'ai1wmke_azure_storage_account_name', false ) ),
			AI1WMKE_TEMPLATES_PATH
		);
	}

	public static function picker() {
		Ai1wm_Template::render(
			'import/picker/picker-azure-storage',
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

		// Set bucket name
		$share_name = null;
		if ( isset( $params['share_name'] ) ) {
			$share_name = trim( $params['share_name'] );
		}

		// Set folder path
		$folder_path = null;
		if ( isset( $params['folder_path'] ) ) {
			$folder_path = trim( $params['folder_path'] );
		}

		// Set Azure Storage client
		$azure = new Ai1wmke_Azure_Storage_Client(
			get_option( 'ai1wmke_azure_storage_account_name', false ),
			get_option( 'ai1wmke_azure_storage_account_key', false )
		);

		// Set share structure
		$response = array( 'items' => array(), 'num_hidden_files' => 0 );

		// Loop over items
		if ( $share_name ) {

			// Get share items
			$items = $azure->get_objects_by_share( $share_name, $folder_path );

			// Loop over folders and files
			foreach ( $items as $item ) {
				if ( $item['type'] === 'folder' || pathinfo( $item['name'], PATHINFO_EXTENSION ) === 'wpress' ) {
					$response['items'][] = array(
						'name'       => isset( $item['name'] ) ? $item['name'] : null,
						'label'      => isset( $item['name'] ) ? $item['name'] : null,
						'path'       => isset( $item['path'] ) ? $item['path'] : null,
						'unix'       => isset( $item['date'] ) ? $item['date'] : null,
						'date'       => isset( $item['date'] ) ? human_time_diff( $item['date'] ) : null,
						'size'       => isset( $item['bytes'] ) ? ai1wm_size_format( $item['bytes'] ) : null,
						'bytes'      => isset( $item['bytes'] ) ? $item['bytes'] : null,
						'type'       => isset( $item['type'] ) ? $item['type'] : null,
						'share_name' => $share_name,
					);
				} else {
					$response['num_hidden_files']++;
				}
			}

			// Sort items by type desc and date desc
			Ai1wmke_File_Sorter::sort( $response['items'], Ai1wmke_File_Sorter::by_type_desc_date_desc( 'unix' ) );

		} else {

			// Get shares
			$shares = $azure->get_shares();

			// Loop over shares
			foreach ( $shares as $share_name ) {
				$response['items'][] = array(
					'name'  => $share_name,
					'label' => $share_name,
					'type'  => 'share',
				);
			}
		}

		echo json_encode( $response );
		exit;
	}
}
