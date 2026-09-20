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

class Ai1wmke_Glacier_Settings_Controller {

	public static function index() {
		$model = new Ai1wmke_Glacier_Settings();

		$glacier_backup_schedules = get_option( 'ai1wmke_glacier_cron', array() );
		$glacier_cron_timestamp   = get_option( 'ai1wmke_glacier_cron_timestamp', time() );
		$last_backup_timestamp    = get_option( 'ai1wmke_glacier_timestamp', false );

		$last_backup_date = $model->get_last_backup_date( $last_backup_timestamp );
		$next_backup_date = $model->get_next_backup_date( $glacier_backup_schedules );

		$user = wp_get_current_user();

		try {
			if ( ( $vaults = $model->get_vaults() ) ) {
				if ( ! in_array( $model->get_vault_name(), $vaults ) ) {
					$vaults[] = $model->get_vault_name();
				}
			}
		} catch ( Ai1wmke_Error_Exception $e ) {
			$vaults = false;
		}

		$regions = $model->get_regions();

		Ai1wm_Template::render(
			'settings/index/index-glacier',
			array(
				'backups'                  => get_option( 'ai1wmke_glacier_backups', false ),
				'glacier_backup_schedules' => $glacier_backup_schedules,
				'glacier_cron_timestamp'   => $glacier_cron_timestamp,
				'notify_ok_toggle'         => get_option( 'ai1wmke_glacier_notify_toggle', false ),
				'notify_error_toggle'      => get_option( 'ai1wmke_glacier_notify_error_toggle', false ),
				'notify_email'             => get_option( 'ai1wmke_glacier_notify_email', get_option( 'admin_email', false ) ),
				'last_backup_date'         => $last_backup_date,
				'next_backup_date'         => $next_backup_date,
				'account_id'               => get_option( 'ai1wmke_glacier_account_id', false ),
				'access_key'               => get_option( 'ai1wmke_glacier_access_key', ai1wmke_aws_access_key() ),
				'secret_key'               => get_option( 'ai1wmke_glacier_secret_key', ai1wmke_aws_secret_key() ),
				'vault_name'               => get_option( 'ai1wmke_glacier_vault_name', ai1wm_archive_vault() ),
				'region_name'              => get_option( 'ai1wmke_glacier_region_name', ai1wmke_aws_region_name( AI1WMKE_GLACIER_REGION_NAME ) ),
				'file_chunk_size'          => log( get_option( 'ai1wmke_glacier_file_chunk_size', AI1WMKE_GLACIER_FILE_CHUNK_SIZE ) / 1024 / 1024, 2 ),
				'total'                    => get_option( 'ai1wmke_glacier_total', false ),
				'lock_mode'                => get_option( 'ai1wmke_glacier_lock_mode', false ),
				'user_display_name'        => $user->display_name,
				'regions'                  => $regions,
				'vaults'                   => $vaults,
			),
			AI1WMKE_TEMPLATES_PATH
		);
	}

	public static function connection( $params = array() ) {
		check_admin_referer( 'ai1wmke_glacier_connection' );
		if ( ! current_user_can( 'export' ) ) {
			wp_die( __( 'You are not allowed to perform this action.', AI1WMKE_PLUGIN_NAME ) );
		}

		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_POST );
		}

		// Amazon Glacier update
		if ( isset( $params['ai1wmke_glacier_update'] ) ) {
			$model = new Ai1wmke_Glacier_Settings();

			// Account ID
			if ( isset( $params['ai1wmke_glacier_account_id'] ) ) {
				$model->set_account_id( trim( $params['ai1wmke_glacier_account_id'] ) );
			}

			// Access Key
			if ( isset( $params['ai1wmke_glacier_access_key'] ) ) {
				$model->set_access_key( trim( $params['ai1wmke_glacier_access_key'] ) );
			}

			// Secret Key
			if ( ! empty( $params['ai1wmke_glacier_secret_key'] ) ) {
				$model->set_secret_key( trim( $params['ai1wmke_glacier_secret_key'] ) );
			}

			try {
				// Get vaults
				$model->get_vaults();

				// Set message
				Ai1wm_Message::flash( 'success', __( 'Amazon Glacier connection is successfully established.', AI1WMKE_PLUGIN_NAME ) );
			} catch ( Ai1wmke_Error_Exception $e ) {
				Ai1wm_Message::flash( 'error', $e->getMessage() );
			}
		}

		// Redirect to settings page
		wp_redirect( network_admin_url( 'admin.php?page=ai1wmke_glacier_settings' ) );
		exit;
	}

	public static function settings( $params = array() ) {
		check_admin_referer( 'ai1wmke_glacier_settings' );
		if ( ! current_user_can( 'export' ) ) {
			wp_die( __( 'You are not allowed to perform this action.', AI1WMKE_PLUGIN_NAME ) );
		}

		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_POST );
		}

		// Amazon Glacier update
		if ( isset( $params['ai1wmke_glacier_update'] ) ) {
			$model = new Ai1wmke_Glacier_Settings();

			// Cron timestamp update
			if ( ! empty( $params['ai1wmke_glacier_cron_timestamp'] ) && ( $cron_timestamp = strtotime( $params['ai1wmke_glacier_cron_timestamp'], current_time( 'timestamp' ) ) ) ) {
				$model->set_cron_timestamp( strtotime( get_gmt_from_date( date( 'Y-m-d H:i:s', $cron_timestamp ) ) ) );
			} else {
				$model->set_cron_timestamp( time() );
			}

			// Region name
			if ( isset( $params['ai1wmke_glacier_region_name'] ) ) {
				$model->set_region_name( trim( $params['ai1wmke_glacier_region_name'] ) );
			}

			// Vault name
			if ( ! empty( $params['ai1wmke_glacier_vault_name'] ) ) {
				try {
					// Create vault
					$model->create_vault( trim( $params['ai1wmke_glacier_vault_name'] ) );

					// Set vault name
					$model->set_vault_name( trim( $params['ai1wmke_glacier_vault_name'] ) );

					// Set message
					Ai1wm_Message::flash( 'settings', __( 'Your changes have been saved.', AI1WMKE_PLUGIN_NAME ) );
				} catch ( Ai1wmke_Error_Exception $e ) {
					Ai1wm_Message::flash( 'vault', $e->getMessage() );
				}
			}

			// Cron update
			if ( ! empty( $params['ai1wmke_glacier_cron'] ) ) {
				$model->set_cron( (array) $params['ai1wmke_glacier_cron'] );
			} else {
				$model->set_cron( array() );
			}

			// Set number of backups
			if ( ! empty( $params['ai1wmke_glacier_backups'] ) ) {
				$model->set_backups( (int) $params['ai1wmke_glacier_backups'] );
			} else {
				$model->set_backups( 0 );
			}

			// Set size of backups
			if ( ! empty( $params['ai1wmke_glacier_total'] ) && ! empty( $params['ai1wmke_glacier_total_unit'] ) ) {
				$model->set_total( (int) $params['ai1wmke_glacier_total'] . trim( $params['ai1wmke_glacier_total_unit'] ) );
			} else {
				$model->set_total( 0 );
			}

			// Set file chunk size
			if ( ! empty( $params['ai1wmke_glacier_file_chunk_size'] ) ) {
				$model->set_file_chunk_size( pow( 2, $params['ai1wmke_glacier_file_chunk_size'] ) * 1024 * 1024 );
			} else {
				$model->set_file_chunk_size( AI1WMKE_GLACIER_FILE_CHUNK_SIZE );
			}

			// Set lock mode
			if ( ! empty( $params['ai1wmke_glacier_lock_mode'] ) ) {
				$model->set_lock_mode( 1 );
			} else {
				$model->set_lock_mode( 0 );
			}

			// Set notify ok toggle
			$model->set_notify_ok_toggle( isset( $params['ai1wmke_glacier_notify_toggle'] ) );

			// Set notify error toggle
			$model->set_notify_error_toggle( isset( $params['ai1wmke_glacier_notify_error_toggle'] ) );

			// Set notify email
			$model->set_notify_email( trim( $params['ai1wmke_glacier_notify_email'] ) );
		}

		// Redirect to settings page
		wp_redirect( network_admin_url( 'admin.php?page=ai1wmke_glacier_settings' ) );
		exit;
	}

	public static function init_cron() {
		$model = new Ai1wmke_Glacier_Settings();
		return $model->init_cron();
	}

	public static function notify_ok_toggle() {
		$model = new Ai1wmke_Glacier_Settings();
		return $model->get_notify_ok_toggle();
	}

	public static function notify_error_toggle() {
		$model = new Ai1wmke_Glacier_Settings();
		return $model->get_notify_error_toggle();
	}

	public static function notify_error_subject() {
		$model = new Ai1wmke_Glacier_Settings();
		return $model->get_notify_error_subject();
	}

	public static function notify_email() {
		$model = new Ai1wmke_Glacier_Settings();
		return $model->get_notify_email();
	}
}
