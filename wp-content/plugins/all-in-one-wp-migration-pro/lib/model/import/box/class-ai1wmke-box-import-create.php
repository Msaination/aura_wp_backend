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

class Ai1wmke_Box_Import_Create {

	public static function execute( $params, $box = null ) {

		// Set progress
		Ai1wm_Status::info( __( 'Creating an empty archive...', AI1WMKE_PLUGIN_NAME ) );

		// Create empty archive file
		$archive = new Ai1wm_Compressor( ai1wm_archive_path( $params ) );
		$archive->close();

		// Set Box client
		if ( is_null( $box ) ) {
			$box = new Ai1wmke_Box_Client(
				get_option( 'ai1wmke_box_token', false ),
				get_option( 'ai1wmke_box_ssl', true )
			);
		}

		// List folder
		$items = $box->list_folder_by_id( $params['folder_id'] );

		// Sort items by name asc
		usort( $items, 'Ai1wmke_Box_Import_Create::sort_by_name_asc' );

		// Add file chunk ID
		if ( ( $multipart = fopen( ai1wm_multipart_path( $params ), 'w' ) ) ) {
			foreach ( $items as $item ) {
				fwrite( $multipart, $item['id'] . PHP_EOL );
			}

			fclose( $multipart );
		}

		// Set progress
		Ai1wm_Status::info( __( 'Done creating an empty archive.', AI1WMKE_PLUGIN_NAME ) );

		return $params;
	}

	public static function sort_by_name_asc( $first_backup, $second_backup ) {
		return strnatcasecmp( $first_backup['name'], $second_backup['name'] );
	}
}
