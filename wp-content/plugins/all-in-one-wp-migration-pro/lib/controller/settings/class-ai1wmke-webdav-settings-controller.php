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

class Ai1wmke_WebDAV_Settings_Controller {

	public static function index() {
		$model = new Ai1wmke_WebDAV_Settings();

		$webdav_backup_schedules = get_option( 'ai1wmke_webdav_cron', array() );
		$webdav_cron_timestamp   = get_option( 'ai1wmke_webdav_cron_timestamp', time() );
		$last_backup_timestamp   = get_option( 'ai1wmke_webdav_timestamp', false );

		$last_backup_date = $model->get_last_backup_date( $last_backup_timestamp );
		$next_backup_date = $model->get_next_backup_date( $webdav_backup_schedules );

		Ai1wm_Template::render(
			'settings/index/index-webdav',
			array(
				'connection'              => get_option( 'ai1wmke_webdav_connection', false ),
				'webdav_backup_schedules' => $webdav_backup_schedules,
				'webdav_cron_timestamp'   => $webdav_cron_timestamp,
				'notify_ok_toggle'        => get_option( 'ai1wmke_webdav_notify_toggle', false ),
				'notify_error_toggle'     => get_option( 'ai1wmke_webdav_notify_error_toggle', false ),
				'notify_email'            => get_option( 'ai1wmke_webdav_notify_email', get_option( 'admin_email', false ) ),
				'last_backup_date'        => $last_backup_date,
				'next_backup_date'        => $next_backup_date,
				'type'                    => get_option( 'ai1wmke_webdav_type', AI1WMKE_WEBDAV_TYPE ),
				'hostname'                => get_option( 'ai1wmke_webdav_hostname', false ),
				'username'                => get_option( 'ai1wmke_webdav_username', false ),
				'password'                => get_option( 'ai1wmke_webdav_password', false ),
				'authentication'          => get_option( 'ai1wmke_webdav_authentication', AI1WMKE_WEBDAV_AUTHENTICATION ),
				'directory'               => get_option( 'ai1wmke_webdav_directory', false ),
				'file_chunk_size'         => get_option( 'ai1wmke_webdav_file_chunk_size', AI1WMKE_WEBDAV_FILE_CHUNK_SIZE ),
				'port'                    => get_option( 'ai1wmke_webdav_port', AI1WMKE_WEBDAV_PORT ),
				'timestamp'               => get_option( 'ai1wmke_webdav_timestamp', false ),
				'backups'                 => get_option( 'ai1wmke_webdav_backups', false ),
				'total'                   => get_option( 'ai1wmke_webdav_total', false ),
				'days'                    => get_option( 'ai1wmke_webdav_days', false ),
			),
			AI1WMKE_TEMPLATES_PATH
		);
	}

	public static function connection( $params = array() ) {
		check_admin_referer( 'ai1wmke_webdav_connection' );
		if ( ! current_user_can( 'export' ) ) {
			wp_die( __( 'You are not allowed to perform this action.', AI1WMKE_PLUGIN_NAME ) );
		}

		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_POST );
		}

		// Test WebDAV connection
		if ( isset( $params['ai1wmke_webdav_link'] ) ) {
			$model = new Ai1wmke_WebDAV_Settings();

			// Type
			if ( isset( $params['ai1wmke_webdav_type'] ) ) {
				$model->set_type( trim( $params['ai1wmke_webdav_type'] ) );
			}

			// Hostname
			if ( isset( $params['ai1wmke_webdav_hostname'] ) ) {
				$model->set_hostname( trim( $params['ai1wmke_webdav_hostname'] ) );
			}

			// Username
			if ( isset( $params['ai1wmke_webdav_username'] ) ) {
				$model->set_username( trim( $params['ai1wmke_webdav_username'] ) );
			}

			// Password
			if ( ! empty( $params['ai1wmke_webdav_password'] ) ) {
				$model->set_password( trim( $params['ai1wmke_webdav_password'] ) );
			}

			// Authentication
			if ( isset( $params['ai1wmke_webdav_authentication'] ) ) {
				$model->set_authentication( trim( $params['ai1wmke_webdav_authentication'] ) );
			}

			// Directory
			if ( isset( $params['ai1wmke_webdav_directory'] ) ) {
				$model->set_directory( trim( $params['ai1wmke_webdav_directory'] ) );
			}

			// Port
			if ( isset( $params['ai1wmke_webdav_port'] ) ) {
				$model->set_port( intval( $params['ai1wmke_webdav_port'] ) );
			}

			try {

				// Set WebDAV client
				$webdav = new Ai1wmke_WebDAV_Client(
					get_option( 'ai1wmke_webdav_type', AI1WMKE_WEBDAV_TYPE ),
					get_option( 'ai1wmke_webdav_hostname', false ),
					get_option( 'ai1wmke_webdav_username', false ),
					get_option( 'ai1wmke_webdav_password', false ),
					get_option( 'ai1wmke_webdav_authentication', AI1WMKE_WEBDAV_AUTHENTICATION ),
					get_option( 'ai1wmke_webdav_directory', false ),
					get_option( 'ai1wmke_webdav_port', AI1WMKE_WEBDAV_PORT )
				);

				// Test WebDAV connection
				$model->set_connection( (int) $webdav->test_connection() );

				// Set message
				Ai1wm_Message::flash( 'success', sprintf( __( 'WebDAV connection is successfully established.', AI1WMKE_PLUGIN_NAME ) ) );
			} catch ( Ai1wmke_Error_Exception $e ) {
				Ai1wm_Message::flash( 'error', $e->getMessage() );
			}
		}

		// Redirect to settings page
		wp_redirect( network_admin_url( 'admin.php?page=ai1wmke_webdav_settings' ) );
		exit;
	}

	public static function settings( $params = array() ) {
		check_admin_referer( 'ai1wmke_webdav_settings' );
		if ( ! current_user_can( 'export' ) ) {
			wp_die( __( 'You are not allowed to perform this action.', AI1WMKE_PLUGIN_NAME ) );
		}

		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_POST );
		}

		// WebDAV update
		if ( isset( $params['ai1wmke_webdav_update'] ) ) {
			$model = new Ai1wmke_WebDAV_Settings();

			// Cron timestamp update
			if ( ! empty( $params['ai1wmke_webdav_cron_timestamp'] ) && ( $cron_timestamp = strtotime( $params['ai1wmke_webdav_cron_timestamp'], current_time( 'timestamp' ) ) ) ) {
				$model->set_cron_timestamp( strtotime( get_gmt_from_date( date( 'Y-m-d H:i:s', $cron_timestamp ) ) ) );
			} else {
				$model->set_cron_timestamp( time() );
			}

			// Cron update
			if ( ! empty( $params['ai1wmke_webdav_cron'] ) ) {
				$model->set_cron( (array) $params['ai1wmke_webdav_cron'] );
			} else {
				$model->set_cron( array() );
			}

			// Set number of backups
			if ( ! empty( $params['ai1wmke_webdav_backups'] ) ) {
				$model->set_backups( (int) $params['ai1wmke_webdav_backups'] );
			} else {
				$model->set_backups( 0 );
			}

			// Set size of backups
			if ( ! empty( $params['ai1wmke_webdav_total'] ) && ! empty( $params['ai1wmke_webdav_total_unit'] ) ) {
				$model->set_total( (int) $params['ai1wmke_webdav_total'] . trim( $params['ai1wmke_webdav_total_unit'] ) );
			} else {
				$model->set_total( 0 );
			}

			// Set age of backups
			if ( ! empty( $params['ai1wmke_webdav_days'] ) ) {
				$model->set_days( (int) $params['ai1wmke_webdav_days'] );
			} else {
				$model->set_days( 0 );
			}

			// Set file chunk size
			if ( ! empty( $params['ai1wmke_webdav_file_chunk_size'] ) ) {
				$model->set_file_chunk_size( $params['ai1wmke_webdav_file_chunk_size'] );
			} else {
				$model->set_file_chunk_size( AI1WMKE_WEBDAV_FILE_CHUNK_SIZE );
			}

			// Set notify ok toggle
			$model->set_notify_ok_toggle( isset( $params['ai1wmke_webdav_notify_toggle'] ) );

			// Set notify error toggle
			$model->set_notify_error_toggle( isset( $params['ai1wmke_webdav_notify_error_toggle'] ) );

			// Set notify email
			$model->set_notify_email( trim( $params['ai1wmke_webdav_notify_email'] ) );

			// Set message
			Ai1wm_Message::flash( 'settings', __( 'Your changes have been saved.', AI1WMKE_PLUGIN_NAME ) );
		}

		// Redirect to settings page
		wp_redirect( network_admin_url( 'admin.php?page=ai1wmke_webdav_settings' ) );
		exit;
	}

	public static function init_cron() {
		$model = new Ai1wmke_WebDAV_Settings();
		return $model->init_cron();
	}

	public static function notify_ok_toggle() {
		$model = new Ai1wmke_WebDAV_Settings();
		return $model->get_notify_ok_toggle();
	}

	public static function notify_error_toggle() {
		$model = new Ai1wmke_WebDAV_Settings();
		return $model->get_notify_error_toggle();
	}

	public static function notify_error_subject() {
		$model = new Ai1wmke_WebDAV_Settings();
		return $model->get_notify_error_subject();
	}

	public static function notify_email() {
		$model = new Ai1wmke_WebDAV_Settings();
		return $model->get_notify_email();
	}
}
