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

class Ai1wmke_Box_Settings_Controller {

	public static function index() {
		$model = new Ai1wmke_Box_Settings();

		$box_backup_schedules  = get_option( 'ai1wmke_box_cron', array() );
		$box_cron_timestamp    = get_option( 'ai1wmke_box_cron_timestamp', time() );
		$last_backup_timestamp = get_option( 'ai1wmke_box_timestamp', false );

		$last_backup_date = $model->get_last_backup_date( $last_backup_timestamp );
		$next_backup_date = $model->get_next_backup_date( $box_backup_schedules );

		Ai1wm_Template::render(
			'settings/index/index-box',
			array(
				'box_backup_schedules' => $box_backup_schedules,
				'box_cron_timestamp'   => $box_cron_timestamp,
				'notify_ok_toggle'     => get_option( 'ai1wmke_box_notify_toggle', false ),
				'notify_error_toggle'  => get_option( 'ai1wmke_box_notify_error_toggle', false ),
				'notify_email'         => get_option( 'ai1wmke_box_notify_email', get_option( 'admin_email', false ) ),
				'last_backup_date'     => $last_backup_date,
				'next_backup_date'     => $next_backup_date,
				'folder_id'            => get_option( 'ai1wmke_box_folder_id', false ),
				'file_chunk_size'      => get_option( 'ai1wmke_box_file_chunk_size', AI1WMKE_BOX_FILE_CHUNK_SIZE ),
				'ssl'                  => get_option( 'ai1wmke_box_ssl', true ),
				'timestamp'            => get_option( 'ai1wmke_box_timestamp', false ),
				'token'                => get_option( 'ai1wmke_box_token', false ),
				'backups'              => get_option( 'ai1wmke_box_backups', false ),
				'total'                => get_option( 'ai1wmke_box_total', false ),
				'days'                 => get_option( 'ai1wmke_box_days', false ),
			),
			AI1WMKE_TEMPLATES_PATH
		);
	}

	public static function picker() {
		Ai1wm_Template::render(
			'settings/picker/picker-box',
			array(),
			AI1WMKE_TEMPLATES_PATH
		);
	}

	public static function token( $params = array() ) {
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_GET );
		}

		if ( isset( $params['_wpnonce'], $params['ai1wmke_box_token'] ) ) {
			if ( wp_verify_nonce( $params['_wpnonce'] ) && current_user_can( 'export' ) ) {
				update_option( 'ai1wmke_box_token', urldecode( $params['ai1wmke_box_token'] ) );

				// Redirect to settings page
				if ( ! defined( 'AI1WMKE_PHPUNIT' ) ) {
					wp_redirect( network_admin_url( 'admin.php?page=ai1wmke_box_settings' ) );
					exit;
				}
			}
		}
	}

	public static function settings( $params = array() ) {
		check_admin_referer( 'ai1wmke_box_settings' );
		if ( ! current_user_can( 'export' ) ) {
			wp_die( __( 'You are not allowed to perform this action.', AI1WMKE_PLUGIN_NAME ) );
		}

		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_POST );
		}

		// Box update
		if ( isset( $params['ai1wmke_box_update'] ) ) {
			$model = new Ai1wmke_Box_Settings();

			// Cron timestamp update
			if ( ! empty( $params['ai1wmke_box_cron_timestamp'] ) && ( $cron_timestamp = strtotime( $params['ai1wmke_box_cron_timestamp'], current_time( 'timestamp' ) ) ) ) {
				$model->set_cron_timestamp( strtotime( get_gmt_from_date( date( 'Y-m-d H:i:s', $cron_timestamp ) ) ) );
			} else {
				$model->set_cron_timestamp( time() );
			}

			// Cron update
			if ( ! empty( $params['ai1wmke_box_cron'] ) ) {
				$model->set_cron( (array) $params['ai1wmke_box_cron'] );
			} else {
				$model->set_cron( array() );
			}

			// Set SSL mode
			if ( ! empty( $params['ai1wmke_box_ssl'] ) ) {
				$model->set_ssl( 0 );
			} else {
				$model->set_ssl( 1 );
			}

			// Set number of backups
			if ( ! empty( $params['ai1wmke_box_backups'] ) ) {
				$model->set_backups( (int) $params['ai1wmke_box_backups'] );
			} else {
				$model->set_backups( 0 );
			}

			// Set size of backups
			if ( ! empty( $params['ai1wmke_box_total'] ) && ! empty( $params['ai1wmke_box_total_unit'] ) ) {
				$model->set_total( (int) $params['ai1wmke_box_total'] . trim( $params['ai1wmke_box_total_unit'] ) );
			} else {
				$model->set_total( 0 );
			}

			// Set age of backups
			if ( ! empty( $params['ai1wmke_box_days'] ) ) {
				$model->set_days( (int) $params['ai1wmke_box_days'] );
			} else {
				$model->set_days( 0 );
			}

			// Set file chunk size
			if ( ! empty( $params['ai1wmke_box_file_chunk_size'] ) ) {
				$model->set_file_chunk_size( $params['ai1wmke_box_file_chunk_size'] );
			} else {
				$model->set_file_chunk_size( AI1WMKE_BOX_FILE_CHUNK_SIZE );
			}

			// Set folder ID
			$model->set_folder_id( trim( $params['ai1wmke_box_folder_id'] ) );

			// Set notify ok toggle
			$model->set_notify_ok_toggle( isset( $params['ai1wmke_box_notify_toggle'] ) );

			// Set notify error toggle
			$model->set_notify_error_toggle( isset( $params['ai1wmke_box_notify_error_toggle'] ) );

			// Set notify email
			$model->set_notify_email( trim( $params['ai1wmke_box_notify_email'] ) );

			// Set message
			Ai1wm_Message::flash( 'settings', __( 'Your changes have been saved.', AI1WMKE_PLUGIN_NAME ) );
		}

		// Redirect to settings page
		wp_redirect( network_admin_url( 'admin.php?page=ai1wmke_box_settings' ) );
		exit;
	}

	public static function revoke( $params = array() ) {
		check_admin_referer( 'ai1wmke_box_revoke' );
		if ( ! current_user_can( 'export' ) ) {
			wp_die( __( 'You are not allowed to perform this action.', AI1WMKE_PLUGIN_NAME ) );
		}

		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_POST );
		}

		// Box logout
		if ( isset( $params['ai1wmke_box_logout'] ) ) {
			$model = new Ai1wmke_Box_Settings();
			$model->revoke();
		}

		// Redirect to settings page
		wp_redirect( network_admin_url( 'admin.php?page=ai1wmke_box_settings' ) );
		exit;
	}

	public static function account() {
		ai1wm_setup_environment();

		try {
			$model = new Ai1wmke_Box_Settings();
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
		$folder_id = null;
		if ( isset( $params['folder_id'] ) ) {
			$folder_id = $params['folder_id'];
		}

		try {
			// Set Box client
			$box = new Ai1wmke_Box_Client(
				get_option( 'ai1wmke_box_token' ),
				get_option( 'ai1wmke_box_ssl', true )
			);

			// List folder
			$items = $box->list_folder_by_id( $folder_id, array( 'orderBy' => 'folder,title' ) );
		} catch ( Ai1wmke_Error_Exception $e ) {
			status_header( 400 );
			echo json_encode( array( 'message' => $e->getMessage() ) );
			exit;
		}

		// Set folder structure
		$response = array( 'items' => array(), 'num_hidden_files' => 0 );

		// Set folder items
		foreach ( $items as $item ) {
			if ( $item['type'] === 'folder' ) {
				$response['items'][] = array(
					'id'    => isset( $item['id'] ) ? strval( $item['id'] ) : null,
					'name'  => isset( $item['name'] ) ? $item['name'] : null,
					'date'  => isset( $item['date'] ) ? human_time_diff( $item['date'] ) : null,
					'bytes' => isset( $item['bytes'] ) ? $item['bytes'] : null,
					'size'  => isset( $item['bytes'] ) ? ai1wm_size_format( $item['bytes'] ) : null,
					'type'  => isset( $item['type'] ) ? $item['type'] : null,
					'ext'   => isset( $item['ext'] ) ? $item['ext'] : null,
				);
			} else {
				$response['num_hidden_files']++;
			}
		}

		echo json_encode( $response );
		exit;
	}

	public static function folder() {
		ai1wm_setup_environment();

		try {
			// Set Box client
			$box = new Ai1wmke_Box_Client(
				get_option( 'ai1wmke_box_token' ),
				get_option( 'ai1wmke_box_ssl', true )
			);

			// Get folder ID
			$folder_id = get_option( 'ai1wmke_box_folder_id', false );

			// Create folder
			if ( ! ( $folder_id = $box->get_folder_id_by_id( $folder_id ) ) ) {
				if ( ! ( $folder_id = $box->get_folder_id_by_name( ai1wm_archive_folder() ) ) ) {
					$folder_id = $box->create_folder( ai1wm_archive_folder() );
				}
			}

			// Set folder ID
			update_option( 'ai1wmke_box_folder_id', $folder_id );

			// Get folder name
			if ( ! ( $folder_name = $box->get_folder_name_by_id( $folder_id ) ) ) {
				status_header( 400 );
				echo json_encode(
					array(
						'message' => __(
							'We were unable to retrieve your backup folder details. ' .
							'Box servers are overloaded at the moment. ' .
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

		echo json_encode( array( 'id' => $folder_id, 'name' => $folder_name ) );
		exit;
	}

	public static function init_cron() {
		$model = new Ai1wmke_Box_Settings();
		return $model->init_cron();
	}

	public static function notify_ok_toggle() {
		$model = new Ai1wmke_Box_Settings();
		return $model->get_notify_ok_toggle();
	}

	public static function notify_error_toggle() {
		$model = new Ai1wmke_Box_Settings();
		return $model->get_notify_error_toggle();
	}

	public static function notify_error_subject() {
		$model = new Ai1wmke_Box_Settings();
		return $model->get_notify_error_subject();
	}

	public static function notify_email() {
		$model = new Ai1wmke_Box_Settings();
		return $model->get_notify_email();
	}
}
