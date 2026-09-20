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

class Ai1wmke_GCloud_Storage_Import_Controller {

	public static function button() {
		return Ai1wm_Template::get_content(
			'import/button/button-gcloud-storage',
			array( 'token' => get_option( 'ai1wmke_gcloud_storage_token', false ) ),
			AI1WMKE_TEMPLATES_PATH
		);
	}

	public static function picker() {
		Ai1wm_Template::render(
			'import/picker/picker-gcloud-storage',
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

		// Set project ID
		$project_id = null;
		if ( isset( $params['project_id'] ) ) {
			$project_id = trim( $params['project_id'] );
		}

		// Set bucket name
		$bucket_name = null;
		if ( isset( $params['bucket_name'] ) ) {
			$bucket_name = trim( $params['bucket_name'] );
		}

		// Set folder path
		$folder_path = null;
		if ( isset( $params['folder_path'] ) ) {
			$folder_path = trim( $params['folder_path'] );
		}

		// Set GCloud storage client
		$gcloud = new Ai1wmke_GCloud_Storage_Client(
			get_option( 'ai1wmke_gcloud_storage_token' ),
			get_option( 'ai1wmke_gcloud_storage_ssl', true )
		);

		// Set project structure
		$response = array( 'items' => array(), 'num_hidden_files' => 0 );

		// Loop over items
		if ( $project_id ) {

			// Loop over buckets
			if ( $bucket_name ) {

				// Get bucket items
				$items = $gcloud->get_objects_by_bucket( $bucket_name, array( 'delimiter' => '/', 'prefix' => $folder_path ) );

				// Loop over folders and files
				foreach ( $items as $item ) {
					if ( $item['type'] === 'folder' || pathinfo( $item['name'], PATHINFO_EXTENSION ) === 'wpress' ) {
						$response['items'][] = array(
							'index'       => null,
							'id'          => isset( $item['id'] ) ? $item['id'] : null,
							'name'        => isset( $item['name'] ) ? $item['name'] : null,
							'path'        => isset( $item['path'] ) ? $item['path'] : null,
							'unix'        => isset( $item['date'] ) ? $item['date'] : null,
							'date'        => isset( $item['date'] ) ? human_time_diff( $item['date'] ) : null,
							'size'        => isset( $item['bytes'] ) ? ai1wm_size_format( $item['bytes'] ) : null,
							'bytes'       => isset( $item['bytes'] ) ? $item['bytes'] : null,
							'type'        => isset( $item['type'] ) ? $item['type'] : null,
							'project_id'  => $project_id,
							'bucket_name' => $bucket_name,
							'folder_path' => $folder_path,
						);
					} else {
						$response['num_hidden_files']++;
					}
				}

				// Sort items by type desc and date desc
				Ai1wmke_File_Sorter::sort( $response['items'], Ai1wmke_File_Sorter::by_type_desc_date_desc( 'unix' ) );

			} else {

				// Get buckets
				$buckets = $gcloud->get_buckets( $project_id );

				// Loop over buckets
				foreach ( $buckets as $bucket_name ) {
					$response['items'][] = array(
						'id'         => $bucket_name,
						'name'       => $bucket_name,
						'project_id' => $project_id,
						'type'       => 'bucket',
					);
				}
			}
		} else {

			// Get projects
			$projects = $gcloud->get_projects();

			// Loop over projects
			foreach ( $projects as $project_id ) {
				$response['items'][] = array(
					'id'   => $project_id,
					'name' => $project_id,
					'type' => 'project',
				);
			}
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

		// Set bucket name
		$bucket_name = null;
		if ( isset( $params['bucket_name'] ) ) {
			$bucket_name = trim( $params['bucket_name'] );
		}

		// Set folder path
		$folder_path = null;
		if ( isset( $params['folder_path'] ) ) {
			$folder_path = trim( $params['folder_path'] );
		}

		// Set GCloud client
		$gcloud = new Ai1wmke_GCloud_Storage_Client(
			get_option( 'ai1wmke_gcloud_storage_token' ),
			get_option( 'ai1wmke_gcloud_storage_ssl', true )
		);

		try {
			$file_content = $gcloud->get_file_content( sprintf( '/%s/incremental.backups.list', $folder_path ), $bucket_name );
		} catch ( Ai1wmke_Error_Exception $e ) {
		}

		$items = array();
		if ( isset( $file_content ) ) {
			foreach ( str_getcsv( $file_content, "\n" ) as $row ) {
				if ( list( $file_index, $file_path, $file_size, $file_mtime ) = str_getcsv( $row ) ) {
					$items[] = array(
						'index'       => $file_index,
						'name'        => sprintf( __( 'Restore point %d', AI1WMKE_PLUGIN_NAME ), $file_index ),
						'path'        => $file_path,
						'date'        => get_date_from_gmt( date( 'Y-m-d H:i:s', $file_mtime ), 'M j, Y g:i a' ),
						'size'        => ai1wm_size_format( $file_size ),
						'bytes'       => $file_size,
						'bucket_name' => $bucket_name,
						'folder_path' => $folder_path,
						'type'        => 'file',
					);
				}
			}
		}

		echo json_encode( array( 'items' => array_reverse( $items ), 'cursor' => null ) );
		exit;
	}
}
