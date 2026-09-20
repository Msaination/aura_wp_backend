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

class Ai1wmke_GCloud_Storage_Settings_Controller {

	public static function index() {
		$model = new Ai1wmke_GCloud_Storage_Settings();

		$gcloud_backup_schedules = get_option( 'ai1wmke_gcloud_storage_cron', array() );
		$gcloud_cron_timestamp   = get_option( 'ai1wmke_gcloud_storage_cron_timestamp', time() );
		$last_backup_timestamp   = get_option( 'ai1wmke_gcloud_storage_timestamp', false );

		$last_backup_date = $model->get_last_backup_date( $last_backup_timestamp );
		$next_backup_date = $model->get_next_backup_date( $gcloud_backup_schedules );

		$projects = false;
		if ( $model->get_token() ) {
			try {
				if ( ( $projects = $model->get_projects() ) ) {
					if ( ! in_array( $model->get_project_id(), $projects ) ) {
						$projects[] = $model->get_project_id();
					}
				}
			} catch ( Ai1wmke_Error_Exception $e ) {
				Ai1wm_Message::flash( 'auth', $e->getMessage() );
			}
		}

		Ai1wm_Template::render(
			'settings/index/index-gcloud-storage',
			array(
				'gcloud_backup_schedules' => $gcloud_backup_schedules,
				'gcloud_cron_timestamp'   => $gcloud_cron_timestamp,
				'notify_ok_toggle'        => get_option( 'ai1wmke_gcloud_storage_notify_toggle', false ),
				'notify_error_toggle'     => get_option( 'ai1wmke_gcloud_storage_notify_error_toggle', false ),
				'notify_email'            => get_option( 'ai1wmke_gcloud_storage_notify_email', get_option( 'admin_email', false ) ),
				'last_backup_date'        => $last_backup_date,
				'next_backup_date'        => $next_backup_date,
				'ssl'                     => get_option( 'ai1wmke_gcloud_storage_ssl', true ),
				'token'                   => get_option( 'ai1wmke_gcloud_storage_token', false ),
				'project_id'              => get_option( 'ai1wmke_gcloud_storage_project_id', ai1wm_archive_project() ),
				'bucket_name'             => get_option( 'ai1wmke_gcloud_storage_bucket_name', ai1wm_archive_bucket() ),
				'folder_name'             => get_option( 'ai1wmke_gcloud_storage_folder_name', '' ),
				'file_chunk_size'         => get_option( 'ai1wmke_gcloud_storage_file_chunk_size', AI1WMKE_GCLOUD_STORAGE_FILE_CHUNK_SIZE ),
				'storage_class'           => get_option( 'ai1wmke_gcloud_storage_class', AI1WMKE_GCLOUD_STORAGE_CLASS ),
				'backups'                 => get_option( 'ai1wmke_gcloud_storage_backups', false ),
				'total'                   => get_option( 'ai1wmke_gcloud_storage_total', false ),
				'days'                    => get_option( 'ai1wmke_gcloud_storage_days', false ),
				'incremental'             => get_option( 'ai1wmke_gcloud_storage_incremental', false ),
				'projects'                => $projects,
			),
			AI1WMKE_TEMPLATES_PATH
		);
	}

	public static function picker() {
		Ai1wm_Template::render(
			'settings/picker/picker-gcloud-storage',
			array(),
			AI1WMKE_TEMPLATES_PATH
		);
	}

	public static function token( $params = array() ) {
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_GET );
		}

		if ( isset( $params['_wpnonce'], $params['ai1wmke_gcloud_storage_token'] ) ) {
			if ( wp_verify_nonce( $params['_wpnonce'] ) && current_user_can( 'export' ) ) {
				update_option( 'ai1wmke_gcloud_storage_token', urldecode( $params['ai1wmke_gcloud_storage_token'] ) );

				// Redirect to settings page
				if ( ! defined( 'AI1WMKE_PHPUNIT' ) ) {
					wp_redirect( network_admin_url( 'admin.php?page=ai1wmke_gcloud_storage_settings' ) );
					exit;
				}
			}
		}
	}

	public static function settings( $params = array() ) {
		check_admin_referer( 'ai1wmke_gcloud_storage_settings' );
		if ( ! current_user_can( 'export' ) ) {
			wp_die( __( 'You are not allowed to perform this action.', AI1WMKE_PLUGIN_NAME ) );
		}

		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_POST );
		}

		// Google Cloud Storage update
		if ( isset( $params['ai1wmke_gcloud_storage_update'] ) ) {
			try {

				$model = new Ai1wmke_GCloud_Storage_Settings();

				// Set incremental
				if ( ! empty( $params['ai1wmke_gcloud_storage_incremental'] ) ) {
					$model->set_incremental( 1 );
				} else {
					$model->set_incremental( 0 );
				}

				// Cron timestamp update
				if ( ! empty( $params['ai1wmke_gcloud_storage_cron_timestamp'] ) && ( $cron_timestamp = strtotime( $params['ai1wmke_gcloud_storage_cron_timestamp'], current_time( 'timestamp' ) ) ) ) {
					$model->set_cron_timestamp( strtotime( get_gmt_from_date( date( 'Y-m-d H:i:s', $cron_timestamp ) ) ) );
				} else {
					$model->set_cron_timestamp( time() );
				}

				// Storage class
				if ( isset( $params['ai1wmke_gcloud_storage_class'] ) ) {
					$model->set_storage_class( trim( $params['ai1wmke_gcloud_storage_class'] ) );
				}

				// Project ID
				if ( isset( $params['ai1wmke_gcloud_storage_project_id'] ) ) {
					$model->create_project( trim( $params['ai1wmke_gcloud_storage_project_id'] ) );
					$model->set_project_id( trim( $params['ai1wmke_gcloud_storage_project_id'] ) );
				}

				// Bucket name
				if ( isset( $params['ai1wmke_gcloud_storage_bucket_name'] ) ) {
					$model->create_bucket( trim( $params['ai1wmke_gcloud_storage_bucket_name'] ) );
					$model->set_bucket_name( trim( $params['ai1wmke_gcloud_storage_bucket_name'] ) );
				}

				// Cron update
				if ( ! empty( $params['ai1wmke_gcloud_storage_cron'] ) ) {
					$model->set_cron( (array) $params['ai1wmke_gcloud_storage_cron'] );
				} else {
					$model->set_cron( array() );
				}

				// Set SSL mode
				if ( ! empty( $params['ai1wmke_gcloud_storage_ssl'] ) ) {
					$model->set_ssl( 0 );
				} else {
					$model->set_ssl( 1 );
				}

				// Set number of backups
				if ( ! empty( $params['ai1wmke_gcloud_storage_backups'] ) ) {
					$model->set_backups( (int) $params['ai1wmke_gcloud_storage_backups'] );
				} else {
					$model->set_backups( 0 );
				}

				// Set size of backups
				if ( ! empty( $params['ai1wmke_gcloud_storage_total'] ) && ! empty( $params['ai1wmke_gcloud_storage_total_unit'] ) ) {
					$model->set_total( (int) $params['ai1wmke_gcloud_storage_total'] . trim( $params['ai1wmke_gcloud_storage_total_unit'] ) );
				} else {
					$model->set_total( 0 );
				}

				// Set age of backups
				if ( ! empty( $params['ai1wmke_gcloud_storage_days'] ) ) {
					$model->set_days( (int) $params['ai1wmke_gcloud_storage_days'] );
				} else {
					$model->set_days( 0 );
				}

				// Set file chunk size
				if ( ! empty( $params['ai1wmke_gcloud_storage_file_chunk_size'] ) ) {
					$model->set_file_chunk_size( $params['ai1wmke_gcloud_storage_file_chunk_size'] );
				} else {
					$model->set_file_chunk_size( AI1WMKE_GCLOUD_STORAGE_FILE_CHUNK_SIZE );
				}

				// Folder name
				if ( isset( $params['ai1wmke_gcloud_storage_folder_name'] ) ) {
					$model->set_folder_name( trim( $params['ai1wmke_gcloud_storage_folder_name'] ) );
				}

				// Set notify ok toggle
				$model->set_notify_ok_toggle( isset( $params['ai1wmke_gcloud_storage_notify_toggle'] ) );

				// Set notify error toggle
				$model->set_notify_error_toggle( isset( $params['ai1wmke_gcloud_storage_notify_error_toggle'] ) );

				// Set notify email
				$model->set_notify_email( trim( $params['ai1wmke_gcloud_storage_notify_email'] ) );

				// Set message
				Ai1wm_Message::flash( 'settings', __( 'Your changes have been saved.', AI1WMKE_PLUGIN_NAME ) );

			} catch ( Ai1wmke_Error_Exception $e ) {
				Ai1wm_Message::flash( 'error', $e->getMessage() );
			}
		}

		// Redirect to settings page
		wp_redirect( network_admin_url( 'admin.php?page=ai1wmke_gcloud_storage_settings' ) );
		exit;
	}

	public static function revoke( $params = array() ) {
		check_admin_referer( 'ai1wmke_gcloud_storage_revoke' );
		if ( ! current_user_can( 'export' ) ) {
			wp_die( __( 'You are not allowed to perform this action.', AI1WMKE_PLUGIN_NAME ) );
		}

		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_POST );
		}

		// Google Cloud Storage logout
		if ( isset( $params['ai1wmke_gcloud_storage_logout'] ) ) {
			$model = new Ai1wmke_GCloud_Storage_Settings();
			$model->revoke();
		}

		// Redirect to settings page
		wp_redirect( network_admin_url( 'admin.php?page=ai1wmke_gcloud_storage_settings' ) );
		exit;
	}

	public static function selector( $params = array() ) {
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
		if ( ! empty( $project_id ) ) {
			if ( ! empty( $bucket_name ) ) {

				// Get bucket items
				$items = $gcloud->get_objects_by_bucket( $bucket_name, array( 'delimiter' => '/', 'prefix' => $folder_path ) );

				// Loop over folders and files
				foreach ( $items as $item ) {
					if ( $item['type'] === 'folder' && $item['name'] !== 'incremental-backups' ) {
						$response['items'][] = array(
							'id'          => isset( $item['id'] ) ? strval( $item['id'] ) : null,
							'name'        => isset( $item['name'] ) ? $item['name'] : null,
							'path'        => isset( $item['path'] ) ? $item['path'] : null,
							'unix'        => isset( $item['date'] ) ? $item['date'] : null,
							'date'        => isset( $item['date'] ) ? human_time_diff( $item['date'] ) : null,
							'size'        => isset( $item['bytes'] ) ? ai1wm_size_format( $item['bytes'] ) : null,
							'bytes'       => isset( $item['bytes'] ) ? $item['bytes'] : null,
							'type'        => isset( $item['type'] ) ? $item['type'] : null,
							'project_id'  => $project_id,
							'bucket_name' => $bucket_name,
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

	public static function folder() {
		ai1wm_setup_environment();

		// Set GCloud storage client
		$gcloud = new Ai1wmke_GCloud_Storage_Client(
			get_option( 'ai1wmke_gcloud_storage_token' ),
			get_option( 'ai1wmke_gcloud_storage_ssl', true )
		);

		// Set project ID
		$project_id = get_option( 'ai1wmke_gcloud_storage_project_id', ai1wm_archive_project() );

		try {
			// Create project if does not exist
			if ( ! $gcloud->is_project_available( $project_id ) ) {
				$gcloud->create_project( $project_id );
			}
		} catch ( Ai1wmke_Error_Exception $e ) {
			status_header( 400 );
			echo json_encode(
				array(
					'message' => __( $e->getMessage(), AI1WMKE_PLUGIN_NAME ),
				)
			);
			exit;
		}

		// Get bucket name
		$bucket_name = get_option( 'ai1wmke_gcloud_storage_bucket_name', ai1wm_archive_bucket() );

		// Get storage class
		$storage_class = get_option( 'ai1wmke_gcloud_storage_class', AI1WMKE_GCLOUD_STORAGE_CLASS );

		// Get buckets
		$buckets = $gcloud->get_buckets( $project_id );

		try {
			// If bucket does not exist - create a new bucket
			if ( ! in_array( $bucket_name, $buckets ) && ! $gcloud->is_bucket_available( $bucket_name ) ) {
				try {
					$gcloud->create_bucket( $bucket_name, $project_id, $storage_class );
				} catch ( Ai1wmke_Error_Exception $e ) {
					status_header( 400 );
					echo json_encode(
						array(
							'message' => sprintf( __( 'Failed to create bucket: %s. Error: %s.', AI1WMKE_PLUGIN_NAME ), $bucket_name, $e->getMessage() ),
						)
					);
					exit;
				}
			}
		} catch ( Ai1wmke_Error_Exception $e ) {
			status_header( 400 );
			echo json_encode(
				array(
					'message' => __( $e->getMessage(), AI1WMKE_PLUGIN_NAME ),
				)
			);
			exit;
		}

		// Get folder name
		$folder_name = get_option( 'ai1wmke_gcloud_storage_folder_name', '' );

		echo json_encode( array( 'project_id' => $project_id, 'bucket_name' => $bucket_name, 'folder_name' => $folder_name ) );
		exit;
	}

	public static function init_cron() {
		$model = new Ai1wmke_GCloud_Storage_Settings();
		return $model->init_cron();
	}

	public static function notify_ok_toggle() {
		$model = new Ai1wmke_GCloud_Storage_Settings();
		return $model->get_notify_ok_toggle();
	}

	public static function notify_error_toggle() {
		$model = new Ai1wmke_GCloud_Storage_Settings();
		return $model->get_notify_error_toggle();
	}

	public static function notify_error_subject() {
		$model = new Ai1wmke_GCloud_Storage_Settings();
		return $model->get_notify_error_subject();
	}

	public static function notify_email() {
		$model = new Ai1wmke_GCloud_Storage_Settings();
		return $model->get_notify_email();
	}
}
