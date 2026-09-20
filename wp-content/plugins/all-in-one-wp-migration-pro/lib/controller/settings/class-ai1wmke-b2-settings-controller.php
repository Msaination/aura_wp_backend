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

class Ai1wmke_B2_Settings_Controller {

	public static function index() {
		$model = new Ai1wmke_B2_Settings();

		$b2_backup_schedules   = get_option( 'ai1wmke_b2_cron', array() );
		$b2_cron_timestamp     = get_option( 'ai1wmke_b2_cron_timestamp', time() );
		$last_backup_timestamp = get_option( 'ai1wmke_b2_timestamp', false );

		$last_backup_date = $model->get_last_backup_date( $last_backup_timestamp );
		$next_backup_date = $model->get_next_backup_date( $b2_backup_schedules );

		$user = wp_get_current_user();

		try {
			if ( ( $buckets = $model->get_buckets() ) ) {
				if ( ! in_array( $model->get_bucket_name(), $buckets ) ) {
					$buckets[] = $model->get_bucket_name();
				}
			}
		} catch ( Ai1wmke_Error_Exception $e ) {
			$buckets = false;
		}

		Ai1wm_Template::render(
			'settings/index/index-b2',
			array(
				'b2_backup_schedules' => $b2_backup_schedules,
				'b2_cron_timestamp'   => $b2_cron_timestamp,
				'notify_ok_toggle'    => get_option( 'ai1wmke_b2_notify_toggle', false ),
				'notify_error_toggle' => get_option( 'ai1wmke_b2_notify_error_toggle', false ),
				'notify_email'        => get_option( 'ai1wmke_b2_notify_email', get_option( 'admin_email', false ) ),
				'last_backup_date'    => $last_backup_date,
				'next_backup_date'    => $next_backup_date,
				'account_id'          => get_option( 'ai1wmke_b2_account_id', false ),
				'application_key'     => get_option( 'ai1wmke_b2_application_key', false ),
				'bucket_name'         => get_option( 'ai1wmke_b2_bucket_name', ai1wm_archive_bucket() ),
				'folder_name'         => get_option( 'ai1wmke_b2_folder_name', '' ),
				'file_chunk_size'     => get_option( 'ai1wmke_b2_file_chunk_size', AI1WMKE_B2_FILE_CHUNK_SIZE ),
				'backups'             => get_option( 'ai1wmke_b2_backups', false ),
				'total'               => get_option( 'ai1wmke_b2_total', false ),
				'days'                => get_option( 'ai1wmke_b2_days', false ),
				'incremental'         => get_option( 'ai1wmke_b2_incremental', false ),
				'lock_mode'           => get_option( 'ai1wmke_b2_lock_mode', false ),
				'user_display_name'   => $user->display_name,
				'buckets'             => $buckets,
			),
			AI1WMKE_TEMPLATES_PATH
		);
	}

	public static function connection( $params = array() ) {
		check_admin_referer( 'ai1wmke_b2_connection' );
		if ( ! current_user_can( 'export' ) ) {
			wp_die( __( 'You are not allowed to perform this action.', AI1WMKE_PLUGIN_NAME ) );
		}

		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_POST );
		}

		// Backblaze B2 update
		if ( isset( $params['ai1wmke_b2_update'] ) ) {
			$model = new Ai1wmke_B2_Settings();

			// Set account ID
			if ( isset( $params['ai1wmke_b2_account_id'] ) ) {
				$model->set_account_id( trim( $params['ai1wmke_b2_account_id'] ) );
			}

			// Set application key
			if ( ! empty( $params['ai1wmke_b2_application_key'] ) ) {
				$model->set_application_key( trim( $params['ai1wmke_b2_application_key'] ) );
			}

			try {
				// Get buckets
				$model->get_buckets();

				// Set message
				Ai1wm_Message::flash( 'success', __( 'Backblaze B2 connection is successfully established.', AI1WMKE_PLUGIN_NAME ) );
			} catch ( Ai1wmke_Error_Exception $e ) {
				Ai1wm_Message::flash( 'error', $e->getMessage() );
			}
		}

		// Redirect to settings page
		wp_redirect( network_admin_url( 'admin.php?page=ai1wmke_b2_settings' ) );
		exit;
	}

	public static function settings( $params = array() ) {
		check_admin_referer( 'ai1wmke_b2_settings' );
		if ( ! current_user_can( 'export' ) ) {
			wp_die( __( 'You are not allowed to perform this action.', AI1WMKE_PLUGIN_NAME ) );
		}

		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_POST );
		}

		// Backblaze B2 update
		if ( isset( $params['ai1wmke_b2_update'] ) ) {
			try {

				$model = new Ai1wmke_B2_Settings();

				// Set incremental
				if ( ! empty( $params['ai1wmke_b2_incremental'] ) ) {
					$model->set_incremental( 1 );
				} else {
					$model->set_incremental( 0 );
				}

				// Cron timestamp update
				if ( ! empty( $params['ai1wmke_b2_cron_timestamp'] ) && ( $cron_timestamp = strtotime( $params['ai1wmke_b2_cron_timestamp'], current_time( 'timestamp' ) ) ) ) {
					$model->set_cron_timestamp( strtotime( get_gmt_from_date( date( 'Y-m-d H:i:s', $cron_timestamp ) ) ) );
				} else {
					$model->set_cron_timestamp( time() );
				}

				// Bucket name
				if ( isset( $params['ai1wmke_b2_bucket_name'] ) ) {
					$model->set_bucket_id( $model->create_bucket( trim( $params['ai1wmke_b2_bucket_name'] ) ) );
					$model->set_bucket_name( trim( $params['ai1wmke_b2_bucket_name'] ) );
				}

				// Cron update
				if ( ! empty( $params['ai1wmke_b2_cron'] ) ) {
					$model->set_cron( (array) $params['ai1wmke_b2_cron'] );
				} else {
					$model->set_cron( array() );
				}

				// Set number of backups
				if ( ! empty( $params['ai1wmke_b2_backups'] ) ) {
					$model->set_backups( (int) $params['ai1wmke_b2_backups'] );
				} else {
					$model->set_backups( 0 );
				}

				// Folder name
				if ( isset( $params['ai1wmke_b2_folder_name'] ) ) {
					$model->set_folder_name( trim( $params['ai1wmke_b2_folder_name'] ) );
				}

				// Set size of backups
				if ( ! empty( $params['ai1wmke_b2_total'] ) && ! empty( $params['ai1wmke_b2_total_unit'] ) ) {
					$model->set_total( (int) $params['ai1wmke_b2_total'] . trim( $params['ai1wmke_b2_total_unit'] ) );
				} else {
					$model->set_total( 0 );
				}

				// Set age of backups
				if ( ! empty( $params['ai1wmke_b2_days'] ) ) {
					$model->set_days( (int) $params['ai1wmke_b2_days'] );
				} else {
					$model->set_days( 0 );
				}

				// Set file chunk size
				if ( ! empty( $params['ai1wmke_b2_file_chunk_size'] ) ) {
					$model->set_file_chunk_size( $params['ai1wmke_b2_file_chunk_size'] );
				} else {
					$model->set_file_chunk_size( AI1WMKE_B2_FILE_CHUNK_SIZE );
				}

				// Set lock mode
				if ( ! empty( $params['ai1wmke_b2_lock_mode'] ) ) {
					$model->set_lock_mode( 1 );
				} else {
					$model->set_lock_mode( 0 );
				}

				// Set notify ok toggle
				$model->set_notify_ok_toggle( isset( $params['ai1wmke_b2_notify_toggle'] ) );

				// Set notify error toggle
				$model->set_notify_error_toggle( isset( $params['ai1wmke_b2_notify_error_toggle'] ) );

				// Set notify email
				$model->set_notify_email( trim( $params['ai1wmke_b2_notify_email'] ) );

				// Set settings capability
				if ( ( $user = wp_get_current_user() ) ) {
					$user->add_cap( 'ai1wmke_b2_admin', $model->get_lock_mode() );
				}

				// Set message
				Ai1wm_Message::flash( 'settings', __( 'Your changes have been saved.', AI1WMKE_PLUGIN_NAME ) );

			} catch ( Ai1wmke_Error_Exception $e ) {
				Ai1wm_Message::flash( 'bucket', $e->getMessage() );
			}
		}

		// Redirect to settings page
		wp_redirect( network_admin_url( 'admin.php?page=ai1wmke_b2_settings' ) );
		exit;
	}

	public static function init_cron() {
		$model = new Ai1wmke_B2_Settings();
		return $model->init_cron();
	}

	public static function notify_ok_toggle() {
		$model = new Ai1wmke_B2_Settings();
		return $model->get_notify_ok_toggle();
	}

	public static function notify_error_toggle() {
		$model = new Ai1wmke_B2_Settings();
		return $model->get_notify_error_toggle();
	}

	public static function notify_error_subject() {
		$model = new Ai1wmke_B2_Settings();
		return $model->get_notify_error_subject();
	}

	public static function notify_email() {
		$model = new Ai1wmke_B2_Settings();
		return $model->get_notify_email();
	}
}
