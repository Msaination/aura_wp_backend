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

class Ai1wmke_Mega_Settings_Controller {

	public static function index() {
		$model = new Ai1wmke_Mega_Settings();

		$mega_backup_schedules = get_option( 'ai1wmke_mega_cron', array() );
		$mega_cron_timestamp   = get_option( 'ai1wmke_mega_cron_timestamp', time() );
		$last_backup_timestamp = get_option( 'ai1wmke_mega_timestamp', false );

		$last_backup_date = $model->get_last_backup_date( $last_backup_timestamp );
		$next_backup_date = $model->get_next_backup_date( $mega_backup_schedules );

		$user = wp_get_current_user();

		Ai1wm_Template::render(
			'settings/index/index-mega',
			array(
				'mega_backup_schedules' => $mega_backup_schedules,
				'mega_cron_timestamp'   => $mega_cron_timestamp,
				'notify_ok_toggle'      => get_option( 'ai1wmke_mega_notify_toggle', false ),
				'notify_error_toggle'   => get_option( 'ai1wmke_mega_notify_error_toggle', false ),
				'notify_email'          => get_option( 'ai1wmke_mega_notify_email', get_option( 'admin_email', false ) ),
				'last_backup_date'      => $last_backup_date,
				'next_backup_date'      => $next_backup_date,
				'node_id'               => get_option( 'ai1wmke_mega_node_id', false ),
				'timestamp'             => get_option( 'ai1wmke_mega_timestamp', false ),
				'user_email'            => get_option( 'ai1wmke_mega_user_email', false ),
				'user_password'         => get_option( 'ai1wmke_mega_user_password', false ),
				'user_session'          => get_option( 'ai1wmke_mega_user_session', false ),
				'backups'               => get_option( 'ai1wmke_mega_backups', false ),
				'total'                 => get_option( 'ai1wmke_mega_total', false ),
				'days'                  => get_option( 'ai1wmke_mega_days', false ),
				'incremental'           => get_option( 'ai1wmke_mega_incremental', false ),
				'lock_mode'             => get_option( 'ai1wmke_mega_lock_mode', false ),
				'user_display_name'     => $user->display_name,
			),
			AI1WMKE_TEMPLATES_PATH
		);
	}

	public static function picker() {
		Ai1wm_Template::render(
			'settings/picker/picker-mega',
			array(),
			AI1WMKE_TEMPLATES_PATH
		);
	}

	public static function connection( $params = array() ) {
		check_admin_referer( 'ai1wmke_mega_connection' );
		if ( ! current_user_can( 'export' ) ) {
			wp_die( __( 'You are not allowed to perform this action.', AI1WMKE_PLUGIN_NAME ) );
		}

		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_POST );
		}

		// Mega update
		if ( isset( $params['ai1wmke_mega_link'] ) ) {
			$model = new Ai1wmke_Mega_Settings();

			// Set user email
			if ( isset( $params['ai1wmke_mega_user_email'] ) ) {
				$model->set_user_email( trim( $params['ai1wmke_mega_user_email'] ) );
			}

			// Set user password
			if ( isset( $params['ai1wmke_mega_user_password'] ) ) {
				$model->set_user_password( trim( $params['ai1wmke_mega_user_password'] ) );
			}

			try {
				// Set user session
				$model->set_user_session( $model->do_login() );

				// Set message
				Ai1wm_Message::flash( 'success', __( 'Mega connection is successfully established.', AI1WMKE_PLUGIN_NAME ) );
			} catch ( Ai1wmke_Error_Exception $e ) {
				Ai1wm_Message::flash( 'error', $e->getMessage() );
			}
		}

		// Redirect to settings page
		wp_redirect( network_admin_url( 'admin.php?page=ai1wmke_mega_settings' ) );
		exit;
	}

	public static function settings( $params = array() ) {
		check_admin_referer( 'ai1wmke_mega_settings' );
		if ( ! current_user_can( 'export' ) ) {
			wp_die( __( 'You are not allowed to perform this action.', AI1WMKE_PLUGIN_NAME ) );
		}

		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_POST );
		}

		// Mega update
		if ( isset( $params['ai1wmke_mega_update'] ) ) {
			try {

				$model = new Ai1wmke_Mega_Settings();

				// Set incremental
				if ( ! empty( $params['ai1wmke_mega_incremental'] ) ) {
					$model->set_incremental( 1 );
				} else {
					$model->set_incremental( 0 );
				}

				// Cron timestamp update
				if ( ! empty( $params['ai1wmke_mega_cron_timestamp'] ) && ( $cron_timestamp = strtotime( $params['ai1wmke_mega_cron_timestamp'], current_time( 'timestamp' ) ) ) ) {
					$model->set_cron_timestamp( strtotime( get_gmt_from_date( date( 'Y-m-d H:i:s', $cron_timestamp ) ) ) );
				} else {
					$model->set_cron_timestamp( time() );
				}

				// Cron update
				if ( ! empty( $params['ai1wmke_mega_cron'] ) ) {
					$model->set_cron( (array) $params['ai1wmke_mega_cron'] );
				} else {
					$model->set_cron( array() );
				}

				// Set number of backups
				if ( ! empty( $params['ai1wmke_mega_backups'] ) ) {
					$model->set_backups( (int) $params['ai1wmke_mega_backups'] );
				} else {
					$model->set_backups( 0 );
				}

				// Set size of backups
				if ( ! empty( $params['ai1wmke_mega_total'] ) && ! empty( $params['ai1wmke_mega_total_unit'] ) ) {
					$model->set_total( (int) $params['ai1wmke_mega_total'] . trim( $params['ai1wmke_mega_total_unit'] ) );
				} else {
					$model->set_total( 0 );
				}

				// Set age of backups
				if ( ! empty( $params['ai1wmke_mega_days'] ) ) {
					$model->set_days( (int) $params['ai1wmke_mega_days'] );
				} else {
					$model->set_days( 0 );
				}

				// Set Node ID
				$model->set_node_id( trim( $params['ai1wmke_mega_node_id'] ) );

				// Set notify ok toggle
				$model->set_notify_ok_toggle( isset( $params['ai1wmke_mega_notify_toggle'] ) );

				// Set notify error toggle
				$model->set_notify_error_toggle( isset( $params['ai1wmke_mega_notify_error_toggle'] ) );

				// Set notify email
				$model->set_notify_email( trim( $params['ai1wmke_mega_notify_email'] ) );

				// Set lock mode
				if ( ! empty( $params['ai1wmke_mega_lock_mode'] ) ) {
					$model->set_lock_mode( 1 );
				} else {
					$model->set_lock_mode( 0 );
				}

				// Set settings capability
				if ( ( $user = wp_get_current_user() ) ) {
					$user->add_cap( 'ai1wmke_mega_admin', $model->get_lock_mode() );
				}

				// Set message
				Ai1wm_Message::flash( 'settings', __( 'Your changes have been saved.', AI1WMKE_PLUGIN_NAME ) );

			} catch ( Ai1wmke_Error_Exception $e ) {
				Ai1wm_Message::flash( 'error', $e->getMessage() );
			}
		}

		// Redirect to settings page
		wp_redirect( network_admin_url( 'admin.php?page=ai1wmke_mega_settings' ) );
		exit;
	}

	public static function revoke( $params = array() ) {
		check_admin_referer( 'ai1wmke_mega_revoke' );
		if ( ! current_user_can( 'export' ) ) {
			wp_die( __( 'You are not allowed to perform this action.', AI1WMKE_PLUGIN_NAME ) );
		}

		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_POST );
		}

		// Mega logout
		if ( isset( $params['ai1wmke_mega_logout'] ) ) {
			$model = new Ai1wmke_Mega_Settings();
			$model->revoke();
		}

		// Redirect to settings page
		wp_redirect( network_admin_url( 'admin.php?page=ai1wmke_mega_settings' ) );
		exit;
	}

	public static function account() {
		ai1wm_setup_environment();

		try {
			$model = new Ai1wmke_Mega_Settings();
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

		// Set Node ID
		$node_id = null;
		if ( isset( $params['node_id'] ) ) {
			$node_id = $params['node_id'];
		}

		// Set Mega client
		$mega = new Ai1wmke_Mega_Client(
			get_option( 'ai1wmke_mega_user_email', false ),
			get_option( 'ai1wmke_mega_user_password', false )
		);

		$mega->load_user_session( get_option( 'ai1wmke_mega_user_session', false ) );

		// Get node list
		$items = $mega->list_nodes( $node_id );

		// Set folder structure
		$response = array( 'items' => array(), 'num_hidden_files' => 0 );

		// Set folder items
		foreach ( $items as $item ) {
			if ( $item->is_dir() ) {
				$response['items'][] = array(
					'id'    => strval( $item->get_node_id() ),
					'key'   => $item->get_key(),
					'name'  => $item->get_file_name(),
					'date'  => human_time_diff( $item->get_last_modified_date() ),
					'size'  => ai1wm_size_format( $item->get_size() ),
					'bytes' => $item->get_size(),
					'type'  => $item->get_type(),
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

		// Set Mega client
		$mega = new Ai1wmke_Mega_Client(
			get_option( 'ai1wmke_mega_user_email', false ),
			get_option( 'ai1wmke_mega_user_password', false )
		);

		$mega->load_user_session( get_option( 'ai1wmke_mega_user_session', false ) );

		try {

			// Create folder
			if ( ! ( $node_item = $mega->get_node_item_by_id( get_option( 'ai1wmke_mega_node_id', false ) ) ) ) {
				if ( ! ( $node_item = $mega->get_node_item_by_name( ai1wm_archive_folder() ) ) ) {
					$node_item = $mega->create( ai1wm_archive_folder() );
				}
			}

			// Set folder ID
			update_option( 'ai1wmke_mega_node_id', $node_item->get_node_id() );

			// Set incremental folder ID
			if ( get_option( 'ai1wmke_mega_incremental', false ) ) {
				if ( ! ( $incremental_node_item = $mega->get_node_item_by_name( 'incremental-backups', $node_item->get_node_id() ) ) ) {
					$incremental_node_item = $mega->create( 'incremental-backups', $node_item->get_node_id() );
				}

				update_option( 'ai1wmke_mega_incremental_node_id', $incremental_node_item->get_node_id() );
			}

			// Get folder name
			if ( ! ( $node_item = $mega->get_node_item_by_id( $node_item->get_node_id() ) ) ) {
				status_header( 400 );
				echo json_encode(
					array(
						'message' => __(
							'We were unable to retrieve your backup folder details. ' .
							'Mega servers are overloaded at the moment. ' .
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

		echo json_encode( array( 'id' => $node_item->get_node_id(), 'name' => $node_item->get_file_name() ) );
		exit;
	}

	public static function init_cron() {
		$model = new Ai1wmke_Mega_Settings();
		return $model->init_cron();
	}

	public static function notify_ok_toggle() {
		$model = new Ai1wmke_Mega_Settings();
		return $model->get_notify_ok_toggle();
	}

	public static function notify_error_toggle() {
		$model = new Ai1wmke_Mega_Settings();
		return $model->get_notify_error_toggle();
	}

	public static function notify_error_subject() {
		$model = new Ai1wmke_Mega_Settings();
		return $model->get_notify_error_subject();
	}

	public static function notify_email() {
		$model = new Ai1wmke_Mega_Settings();
		return $model->get_notify_email();
	}
}
