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

class Ai1wmke_Azure_Storage_Settings_Controller {

	public static function index() {
		$model = new Ai1wmke_Azure_Storage_Settings();

		$azure_backup_schedules = get_option( 'ai1wmke_azure_storage_cron', array() );
		$azure_cron_timestamp   = get_option( 'ai1wmke_azure_storage_cron_timestamp', time() );
		$last_backup_timestamp  = get_option( 'ai1wmke_azure_storage_timestamp', false );

		$last_backup_date = $model->get_last_backup_date( $last_backup_timestamp );
		$next_backup_date = $model->get_next_backup_date( $azure_backup_schedules );

		try {
			if ( ( $shares = $model->get_shares() ) ) {
				if ( ! in_array( $model->get_share_name(), $shares ) ) {
					$shares[] = $model->get_share_name();
				}
			}
		} catch ( Ai1wmke_Error_Exception $e ) {
			$shares = false;
		}

		Ai1wm_Template::render(
			'settings/index/index-azure-storage',
			array(
				'azure_backup_schedules' => $azure_backup_schedules,
				'azure_cron_timestamp'   => $azure_cron_timestamp,
				'notify_ok_toggle'       => get_option( 'ai1wmke_azure_storage_notify_toggle', false ),
				'notify_error_toggle'    => get_option( 'ai1wmke_azure_storage_notify_error_toggle', false ),
				'notify_email'           => get_option( 'ai1wmke_azure_storage_notify_email', get_option( 'admin_email', false ) ),
				'last_backup_date'       => $last_backup_date,
				'next_backup_date'       => $next_backup_date,
				'ssl'                    => get_option( 'ai1wmke_azure_storage_ssl', true ),
				'account_name'           => get_option( 'ai1wmke_azure_storage_account_name', false ),
				'account_key'            => get_option( 'ai1wmke_azure_storage_account_key', false ),
				'share_name'             => get_option( 'ai1wmke_azure_storage_share_name', ai1wm_archive_share() ),
				'folder_name'            => get_option( 'ai1wmke_azure_storage_folder_name', '' ),
				'backups'                => get_option( 'ai1wmke_azure_storage_backups', false ),
				'total'                  => get_option( 'ai1wmke_azure_storage_total', false ),
				'days'                   => get_option( 'ai1wmke_azure_storage_days', false ),
				'shares'                 => $shares,
			),
			AI1WMKE_TEMPLATES_PATH
		);
	}

	public static function picker() {
		Ai1wm_Template::render(
			'settings/picker/picker-azure-storage',
			array(),
			AI1WMKE_TEMPLATES_PATH
		);
	}

	public static function selector( $params = array() ) {
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
				if ( $item['type'] === 'folder' ) {
					$response['items'][] = array(
						'name'       => isset( $item['name'] ) ? $item['name'] : null,
						'label'      => isset( $item['name'] ) ? $item['name'] : null,
						'path'       => isset( $item['path'] ) ? $item['path'] : null,
						'unix'       => isset( $item['date'] ) ? $item['date'] : null,
						'date'       => isset( $item['date'] ) ? human_time_diff( $item['date'] ) : null,
						'type'       => 'folder',
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

	public static function folder() {
		ai1wm_setup_environment();

		// Set Azure Storage client
		$azure = new Ai1wmke_Azure_Storage_Client(
			get_option( 'ai1wmke_azure_storage_account_name', false ),
			get_option( 'ai1wmke_azure_storage_account_key', false )
		);

		// Get share name
		$share_name = get_option( 'ai1wmke_azure_storage_share_name', ai1wm_archive_share() );

		// Get shares
		try {
			$shares = $azure->get_shares();
		} catch ( Ai1wmke_Error_Exception $e ) {
			status_header( 400 );
			echo json_encode(
				array(
					'message' => sprintf( __( 'Failed to get all shares. Error: %s.', AI1WMKE_PLUGIN_NAME ), $e->getMessage() ),
				)
			);
			exit;
		}

		// If share does not exist - create a new share
		if ( ! in_array( $share_name, $shares ) && ! $azure->is_share_available( $share_name ) ) {
			try {
				$azure->create_share( $share_name );
			} catch ( Ai1wmke_Error_Exception $e ) {
				status_header( 400 );
				echo json_encode(
					array(
						'message' => sprintf( __( 'Failed to create share: %s. Error: %s.', AI1WMKE_PLUGIN_NAME ), $share_name, $e->getMessage() ),
					)
				);
				exit;
			}
		}

		// Get folder name
		$folder_name = get_option( 'ai1wmke_azure_storage_folder_name', '' );

		// If folder path doesn't exist - reset the folder name
		if ( ! $azure->folder_exists( $folder_name, $share_name ) ) {
			try {
				$azure->create_folder( $folder_name, $share_name );
			} catch ( Ai1wmke_Error_Exception $e ) {
				status_header( 400 );
				echo json_encode(
					array(
						'message' => sprintf( __( 'Failed to create folder: %s. Error: %s.', AI1WMKE_PLUGIN_NAME ), $folder_name, $e->getMessage() ),
					)
				);
				exit;
			}
		}

		// Get folder name
		if ( empty( $share_name ) ) {
			status_header( 400 );
			echo json_encode(
				array(
					'message' => __(
						'We were unable to retrieve your backup share details. ' .
						'Microsoft servers are overloaded at the moment. ' .
						'Please wait for a few minutes and try again by refreshing the page.',
						AI1WMKE_PLUGIN_NAME
					),
				)
			);
			exit;
		}

		echo json_encode( array( 'share_name' => $share_name, 'folder_name' => $folder_name ) );
		exit;
	}

	public static function connection( $params = array() ) {
		check_admin_referer( 'ai1wmke_azure_storage_connection' );
		if ( ! current_user_can( 'export' ) ) {
			wp_die( __( 'You are not allowed to perform this action.', AI1WMKE_PLUGIN_NAME ) );
		}

		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_POST );
		}

		// Microsoft Azure Storage update
		if ( isset( $params['ai1wmke_azure_storage_update'] ) ) {
			$model = new Ai1wmke_Azure_Storage_Settings();

			// Account name
			if ( isset( $params['ai1wmke_azure_storage_account_name'] ) ) {
				$model->set_account_name( trim( $params['ai1wmke_azure_storage_account_name'] ) );
			}

			// Account key
			if ( ! empty( $params['ai1wmke_azure_storage_account_key'] ) ) {
				$model->set_account_key( trim( $params['ai1wmke_azure_storage_account_key'] ) );
			}

			try {
				// Get shares
				$model->get_shares();

				// Set message
				Ai1wm_Message::flash( 'success', __( 'Microsoft Azure Storage connection is successfully established.', AI1WMKE_PLUGIN_NAME ) );
			} catch ( Ai1wmke_Error_Exception $e ) {
				Ai1wm_Message::flash( 'error', $e->getMessage() );
			}
		}

		// Redirect to settings page
		wp_redirect( network_admin_url( 'admin.php?page=ai1wmke_azure_storage_settings' ) );
		exit;
	}

	public static function settings() {
		check_admin_referer( 'ai1wmke_azure_storage_settings' );
		if ( ! current_user_can( 'export' ) ) {
			wp_die( __( 'You are not allowed to perform this action.', AI1WMKE_PLUGIN_NAME ) );
		}

		$params = stripslashes_deep( $_POST );

		// Microsoft Azure Storage update
		if ( isset( $params['ai1wmke_azure_storage_update'] ) ) {
			$model = new Ai1wmke_Azure_Storage_Settings();

			// Cron timestamp update
			if ( ! empty( $params['ai1wmke_azure_storage_cron_timestamp'] ) && ( $cron_timestamp = strtotime( $params['ai1wmke_azure_storage_cron_timestamp'], current_time( 'timestamp' ) ) ) ) {
				$model->set_cron_timestamp( strtotime( get_gmt_from_date( date( 'Y-m-d H:i:s', $cron_timestamp ) ) ) );
			} else {
				$model->set_cron_timestamp( time() );
			}

			// Share name
			if ( isset( $params['ai1wmke_azure_storage_share_name'] ) ) {
				try {
					// Create share
					$model->create_share( trim( $params['ai1wmke_azure_storage_share_name'] ) );

					// Set share name
					$model->set_share_name( $params['ai1wmke_azure_storage_share_name'] );

					// Set message
					Ai1wm_Message::flash( 'settings', __( 'Your changes have been saved.', AI1WMKE_PLUGIN_NAME ) );
				} catch ( Ai1wmke_Error_Exception $e ) {
					Ai1wm_Message::flash( 'share', $e->getMessage() );
				}
			}

			// Folder name
			if ( isset( $params['ai1wmke_azure_storage_folder_name'] ) ) {
				$model->set_folder_name( trim( $params['ai1wmke_azure_storage_folder_name'] ) );
			}

			// Cron update
			if ( ! empty( $params['ai1wmke_azure_storage_cron'] ) ) {
				$model->set_cron( (array) $params['ai1wmke_azure_storage_cron'] );
			} else {
				$model->set_cron( array() );
			}

			// Set SSL mode
			if ( ! empty( $params['ai1wmke_azure_storage_ssl'] ) ) {
				$model->set_ssl( 0 );
			} else {
				$model->set_ssl( 1 );
			}

			// Set number of backups
			if ( ! empty( $params['ai1wmke_azure_storage_backups'] ) ) {
				$model->set_backups( (int) $params['ai1wmke_azure_storage_backups'] );
			} else {
				$model->set_backups( 0 );
			}

			// Set size of backups
			if ( ! empty( $params['ai1wmke_azure_storage_total'] ) && ! empty( $params['ai1wmke_azure_storage_total_unit'] ) ) {
				$model->set_total( (int) $params['ai1wmke_azure_storage_total'] . trim( $params['ai1wmke_azure_storage_total_unit'] ) );
			} else {
				$model->set_total( 0 );
			}

			// Set number of days
			if ( ! empty( $params['ai1wmke_azure_storage_days'] ) ) {
				$model->set_days( (int) $params['ai1wmke_azure_storage_days'] );
			} else {
				$model->set_days( 0 );
			}

			// Set notify ok toggle
			$model->set_notify_ok_toggle( isset( $params['ai1wmke_azure_storage_notify_toggle'] ) );

			// Set notify error toggle
			$model->set_notify_error_toggle( isset( $params['ai1wmke_azure_storage_notify_error_toggle'] ) );

			// Set notify email
			$model->set_notify_email( trim( $params['ai1wmke_azure_storage_notify_email'] ) );
		}

		// Redirect to settings page
		wp_redirect( network_admin_url( 'admin.php?page=ai1wmke_azure_storage_settings' ) );
		exit;
	}

	public static function init_cron() {
		$model = new Ai1wmke_Azure_Storage_Settings();
		return $model->init_cron();
	}

	public static function notify_ok_toggle() {
		$model = new Ai1wmke_Azure_Storage_Settings();
		return $model->get_notify_ok_toggle();
	}

	public static function notify_error_toggle() {
		$model = new Ai1wmke_Azure_Storage_Settings();
		return $model->get_notify_error_toggle();
	}

	public static function notify_error_subject() {
		$model = new Ai1wmke_Azure_Storage_Settings();
		return $model->get_notify_error_subject();
	}

	public static function notify_email() {
		$model = new Ai1wmke_Azure_Storage_Settings();
		return $model->get_notify_email();
	}
}
