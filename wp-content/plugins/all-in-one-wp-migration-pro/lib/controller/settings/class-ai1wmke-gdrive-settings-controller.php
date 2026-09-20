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

class Ai1wmke_GDrive_Settings_Controller {

	public static function index() {
		$model = new Ai1wmke_GDrive_Settings();

		$gdrive_backup_schedules = get_option( 'ai1wmke_gdrive_cron', array() );
		$gdrive_cron_timestamp   = get_option( 'ai1wmke_gdrive_cron_timestamp', time() );
		$last_backup_timestamp   = get_option( 'ai1wmke_gdrive_timestamp', false );

		$last_backup_date = $model->get_last_backup_date( $last_backup_timestamp );
		$next_backup_date = $model->get_next_backup_date( $gdrive_backup_schedules );

		$user = wp_get_current_user();

		Ai1wm_Template::render(
			'settings/index/index-gdrive',
			array(
				'gdrive_backup_schedules' => $gdrive_backup_schedules,
				'gdrive_cron_timestamp'   => $gdrive_cron_timestamp,
				'notify_ok_toggle'        => get_option( 'ai1wmke_gdrive_notify_toggle', false ),
				'notify_error_toggle'     => get_option( 'ai1wmke_gdrive_notify_error_toggle', false ),
				'notify_email'            => get_option( 'ai1wmke_gdrive_notify_email', get_option( 'admin_email', false ) ),
				'last_backup_date'        => $last_backup_date,
				'next_backup_date'        => $next_backup_date,
				'folder_id'               => get_option( 'ai1wmke_gdrive_folder_id', false ),
				'team_drive_id'           => get_option( 'ai1wmke_gdrive_team_drive_id', AI1WMKE_GDRIVE_TEAM_DRIVE_ID ),
				'file_chunk_size'         => get_option( 'ai1wmke_gdrive_file_chunk_size', AI1WMKE_GDRIVE_FILE_CHUNK_SIZE ),
				'ssl'                     => get_option( 'ai1wmke_gdrive_ssl', true ),
				'timestamp'               => get_option( 'ai1wmke_gdrive_timestamp', false ),
				'token'                   => get_option( 'ai1wmke_gdrive_token', false ),
				'backups'                 => get_option( 'ai1wmke_gdrive_backups', false ),
				'total'                   => get_option( 'ai1wmke_gdrive_total', false ),
				'days'                    => get_option( 'ai1wmke_gdrive_days', false ),
				'incremental'             => get_option( 'ai1wmke_gdrive_incremental', false ),
				'lock_mode'               => get_option( 'ai1wmke_gdrive_lock_mode', false ),
				'user_display_name'       => $user->display_name,
			),
			AI1WMKE_TEMPLATES_PATH
		);
	}

	public static function picker() {
		Ai1wm_Template::render(
			'settings/picker/picker-gdrive',
			array(),
			AI1WMKE_TEMPLATES_PATH
		);
	}

	public static function token( $params = array() ) {
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_GET );
		}

		if ( isset( $params['_wpnonce'], $params['ai1wmke_gdrive_token'] ) ) {
			if ( wp_verify_nonce( $params['_wpnonce'] ) && current_user_can( 'export' ) ) {
				if ( isset( $params['ai1wmke_gdrive_app_folder'] ) && $params['ai1wmke_gdrive_app_folder'] === 'yes' ) {
					update_option( 'ai1wmke_gdrive_app_folder', true );
				} else {
					delete_option( 'ai1wmke_gdrive_app_folder' );
				}

				update_option( 'ai1wmke_gdrive_token', urldecode( $params['ai1wmke_gdrive_token'] ) );

				// Redirect to settings page
				if ( ! defined( 'AI1WMKE_PHPUNIT' ) ) {
					wp_redirect( network_admin_url( 'admin.php?page=ai1wmke_gdrive_settings' ) );
					exit;
				}
			}
		}
	}

	public static function settings( $params = array() ) {
		check_admin_referer( 'ai1wmke_gdrive_settings' );
		if ( ! current_user_can( 'export' ) ) {
			wp_die( __( 'You are not allowed to perform this action.', AI1WMKE_PLUGIN_NAME ) );
		}

		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_POST );
		}

		// Google Drive update
		if ( isset( $params['ai1wmke_gdrive_update'] ) ) {
			try {

				$model = new Ai1wmke_GDrive_Settings();

				// Set incremental
				if ( ! empty( $params['ai1wmke_gdrive_incremental'] ) ) {
					$model->set_incremental( 1 );
				} else {
					$model->set_incremental( 0 );
				}

				// Cron timestamp update
				if ( ! empty( $params['ai1wmke_gdrive_cron_timestamp'] ) && ( $cron_timestamp = strtotime( $params['ai1wmke_gdrive_cron_timestamp'], current_time( 'timestamp' ) ) ) ) {
					$model->set_cron_timestamp( strtotime( get_gmt_from_date( date( 'Y-m-d H:i:s', $cron_timestamp ) ) ) );
				} else {
					$model->set_cron_timestamp( time() );
				}

				// Cron update
				if ( ! empty( $params['ai1wmke_gdrive_cron'] ) ) {
					$model->set_cron( (array) $params['ai1wmke_gdrive_cron'] );
				} else {
					$model->set_cron( array() );
				}

				// Set SSL mode
				if ( ! empty( $params['ai1wmke_gdrive_ssl'] ) ) {
					$model->set_ssl( 0 );
				} else {
					$model->set_ssl( 1 );
				}

				// Set number of backups
				if ( ! empty( $params['ai1wmke_gdrive_backups'] ) ) {
					$model->set_backups( (int) $params['ai1wmke_gdrive_backups'] );
				} else {
					$model->set_backups( 0 );
				}

				// Set size of backups
				if ( ! empty( $params['ai1wmke_gdrive_total'] ) && ! empty( $params['ai1wmke_gdrive_total_unit'] ) ) {
					$model->set_total( (int) $params['ai1wmke_gdrive_total'] . trim( $params['ai1wmke_gdrive_total_unit'] ) );
				} else {
					$model->set_total( 0 );
				}

				// Set age of backups
				if ( ! empty( $params['ai1wmke_gdrive_days'] ) ) {
					$model->set_days( (int) $params['ai1wmke_gdrive_days'] );
				} else {
					$model->set_days( 0 );
				}

				// Set file chunk size
				if ( ! empty( $params['ai1wmke_gdrive_file_chunk_size'] ) ) {
					$model->set_file_chunk_size( $params['ai1wmke_gdrive_file_chunk_size'] );
				} else {
					$model->set_file_chunk_size( AI1WMKE_GDRIVE_FILE_CHUNK_SIZE );
				}

				// Set folder ID
				$model->set_folder_id( trim( $params['ai1wmke_gdrive_folder_id'] ) );

				// Set incremental folder ID
				$model->set_incremental_folder_id( trim( $params['ai1wmke_gdrive_incremental_folder_id'] ) );

				// Set team drive ID
				$model->set_team_drive_id( trim( $params['ai1wmke_gdrive_team_drive_id'] ) );

				// Set notify ok toggle
				$model->set_notify_ok_toggle( isset( $params['ai1wmke_gdrive_notify_toggle'] ) );

				// Set notify error toggle
				$model->set_notify_error_toggle( isset( $params['ai1wmke_gdrive_notify_error_toggle'] ) );

				// Set notify email
				$model->set_notify_email( trim( $params['ai1wmke_gdrive_notify_email'] ) );

				// Set lock mode
				if ( ! empty( $params['ai1wmke_gdrive_lock_mode'] ) ) {
					$model->set_lock_mode( 1 );
				} else {
					$model->set_lock_mode( 0 );
				}

				// Set settings capability
				if ( ( $user = wp_get_current_user() ) ) {
					$user->add_cap( 'ai1wmke_gdrive_admin', $model->get_lock_mode() );
				}

				// Set message
				Ai1wm_Message::flash( 'settings', __( 'Your changes have been saved.', AI1WMKE_PLUGIN_NAME ) );

			} catch ( Ai1wmke_Error_Exception $e ) {
				Ai1wm_Message::flash( 'error', $e->getMessage() );
			}
		}

		// Redirect to settings page
		wp_redirect( network_admin_url( 'admin.php?page=ai1wmke_gdrive_settings' ) );
		exit;
	}

	public static function revoke( $params = array() ) {
		check_admin_referer( 'ai1wmke_gdrive_revoke' );
		if ( ! current_user_can( 'export' ) ) {
			wp_die( __( 'You are not allowed to perform this action.', AI1WMKE_PLUGIN_NAME ) );
		}

		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_POST );
		}

		// Google Drive logout
		if ( isset( $params['ai1wmke_gdrive_logout'] ) ) {
			$model = new Ai1wmke_GDrive_Settings();
			$model->revoke();
		}

		// Redirect to settings page
		wp_redirect( network_admin_url( 'admin.php?page=ai1wmke_gdrive_settings' ) );
		exit;
	}

	public static function account() {
		ai1wm_setup_environment();

		try {
			$model = new Ai1wmke_GDrive_Settings();
			if ( ( $account = $model->get_account() ) ) {
				echo json_encode( $account );
				exit;
			}
		} catch ( Ai1wmke_Error_Exception $e ) {
			status_header( 400 );
			echo json_encode( array( 'message' => $e->getMessage() ) );
			exit;
		}
	}

	public static function selector( $params = array() ) {
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
			$team_drive_id = $params['team_drive_id'];
		}

		// Set next page token
		$next_page_token = null;
		if ( ! empty( $params['next_page_token'] ) ) {
			$next_page_token = trim( $params['next_page_token'] );
		}

		// Set GDrive client
		$gdrive = new Ai1wmke_GDrive_Client(
			get_option( 'ai1wmke_gdrive_token' ),
			get_option( 'ai1wmke_gdrive_ssl', true )
		);

		// Set drive structure
		$response = array( 'items' => array(), 'next_page_token' => null );

		try {

			// List drive or folder
			if ( ! empty( $team_drive_id ) ) {

				// Get drive items
				$result = $gdrive->list_folder_by_id( $folder_id, $team_drive_id, $next_page_token, "title != 'incremental-backups' and mimeType = 'application/vnd.google-apps.folder'", array( 'orderBy' => 'folder,createdDate desc' ) );

				// Loop over folders and files
				if ( isset( $result['items'] ) ) {
					foreach ( $result['items'] as $item ) {
						$response['items'][] = array(
							'id'            => isset( $item['id'] ) ? strval( $item['id'] ) : null,
							'name'          => isset( $item['name'] ) ? $item['name'] : null,
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
			status_header( 400 );
			echo json_encode( array( 'message' => $e->getMessage() ) );
			exit;
		}

		echo json_encode( $response );
		exit;
	}

	public static function folder() {
		ai1wm_setup_environment();

		try {
			// Set GDrive client
			$gdrive = new Ai1wmke_GDrive_Client(
				get_option( 'ai1wmke_gdrive_token' ),
				get_option( 'ai1wmke_gdrive_ssl', true )
			);

			// Get folder ID
			$folder_id = get_option( 'ai1wmke_gdrive_folder_id', false );

			// Get team drive ID
			$team_drive_id = get_option( 'ai1wmke_gdrive_team_drive_id', AI1WMKE_GDRIVE_TEAM_DRIVE_ID );

			// Create folder
			if ( ! ( $folder_id = $gdrive->get_folder_id_by_id( $folder_id, $team_drive_id ) ) ) {
				if ( ! ( $folder_id = $gdrive->get_folder_id_by_name( ai1wm_archive_folder(), 'root', $team_drive_id ) ) ) {
					$folder_id = $gdrive->create_folder( ai1wm_archive_folder(), 'root', $team_drive_id );
				}
			}

			// Set folder ID
			update_option( 'ai1wmke_gdrive_folder_id', $folder_id );

			// Get folder name
			if ( ! ( $folder_name = $gdrive->get_folder_name_by_id( $folder_id, $team_drive_id ) ) ) {
				status_header( 400 );
				echo json_encode(
					array(
						'message' => __(
							'We were unable to retrieve your backup folder details. ' .
							'Google servers are overloaded at the moment. ' .
							'Please wait for a few minutes and try again by refreshing the page.',
							AI1WMKE_PLUGIN_NAME
						),
					)
				);
				exit;
			}
		} catch ( Ai1wmke_Error_Exception $e ) {
			status_header( 400 );
			echo json_encode( array( 'message' => $e->getMessage() ) );
			exit;
		}

		echo json_encode(
			array(
				'id'         => $folder_id,
				'name'       => $folder_name,
				'app_folder' => get_option( 'ai1wmke_gdrive_app_folder', false ),
			)
		);
		exit;
	}

	public static function init_cron() {
		$model = new Ai1wmke_GDrive_Settings();
		return $model->init_cron();
	}

	public static function notify_ok_toggle() {
		$model = new Ai1wmke_GDrive_Settings();
		return $model->get_notify_ok_toggle();
	}

	public static function notify_error_toggle() {
		$model = new Ai1wmke_GDrive_Settings();
		return $model->get_notify_error_toggle();
	}

	public static function notify_error_subject() {
		$model = new Ai1wmke_GDrive_Settings();
		return $model->get_notify_error_subject();
	}

	public static function notify_email() {
		$model = new Ai1wmke_GDrive_Settings();
		return $model->get_notify_email();
	}
}
