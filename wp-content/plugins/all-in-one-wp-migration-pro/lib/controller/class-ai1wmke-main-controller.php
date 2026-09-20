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

class Ai1wmke_Main_Controller {

	/**
	 * Main Application Controller
	 *
	 * @return object
	 */
	public function __construct() {
		register_activation_hook( AI1WMKE_PLUGIN_BASENAME, 'Ai1wmke_Upgrader::sync_options' );
		register_activation_hook( AI1WMKE_PLUGIN_BASENAME, 'Ai1wmke_Upgrader::sync_extensions' );
		register_activation_hook( AI1WMKE_PLUGIN_BASENAME, 'Ai1wmke_Report_Controller::on_activate' );
		register_activation_hook( AI1WMKE_PLUGIN_BASENAME, 'Ai1wmke_Install_Controller::on_activate' );

		// Activate hooks
		$this->activate_actions();
		$this->activate_filters();

		// Load plugin defaults
		$this->load_oauth_token();
		$this->enqueue_scripts_and_styles();
	}

	/**
	 * Outputs menu icon between head tags (do not use template to render the code)
	 *
	 * @return string
	 */
	public function admin_head() {
		?>
		<style type="text/css" media="all">
			.ai1wm-label {
				border: 1px solid #5cb85c;
				background-color: transparent;
				color: #5cb85c;
				cursor: pointer;
				text-transform: uppercase;
				font-weight: 600;
				outline: none;
				transition: background-color 0.2s ease-out;
				padding: .2em .6em;
				font-size: 0.8em;
				border-radius: 5px;
				text-decoration: none !important;
			}

			.ai1wm-label:hover {
				background-color: #5cb85c;
				color: #fff;
			}
		</style>
		<?php
	}

	/**
	 * Display admin notices (do not use template to render the code)
	 *
	 * @return string
	 */
	public function admin_notices() {
		// Check if the base plugin is not installed or activated
		if ( is_wp_error( validate_plugin( 'all-in-one-wp-migration/all-in-one-wp-migration.php' ) ) ) {
			?>
			<div class="error">
				<p>
					<?php
					_e(
						sprintf(
							'All-in-One WP Migration Pro requires All-in-One WP Migration plugin to be installed. <a href="%s">Install Now</a> or <a href="%s" target="_blank">Download Manually</a>',
							wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=all-in-one-wp-migration' ), 'install-plugin_all-in-one-wp-migration' ),
							'https://wordpress.org/plugins/all-in-one-wp-migration/'
						),
						AI1WMKE_PLUGIN_NAME
					);
					?>
				</p>
			</div>
			<?php
		} elseif ( is_plugin_inactive( 'all-in-one-wp-migration/all-in-one-wp-migration.php' ) ) {
			?>
			<div class="error">
				<p>
					<?php
					_e(
						sprintf(
							'All-in-One WP Migration Pro requires All-in-One WP Migration plugin to be activated. <a href="%s">Activate Now</a>',
							wp_nonce_url( 'plugins.php?action=activate&plugin=all-in-one-wp-migration/all-in-one-wp-migration.php', 'activate-plugin_all-in-one-wp-migration/all-in-one-wp-migration.php' )
						),
						AI1WMKE_PLUGIN_NAME
					);
					?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Enqueue admin scripts and styles
	 *
	 * @return void
	 */
	public function enqueue_scripts_and_styles() {
		add_action( 'admin_enqueue_scripts', 'Ai1wmke_Backups_Controller::enqueue_scripts_and_styles', 100 );
		add_action( 'admin_enqueue_scripts', 'Ai1wmke_Direct_Controller::enqueue_scripts_and_styles', 100 );
		add_action( 'admin_enqueue_scripts', 'Ai1wmke_Export_Controller::enqueue_scripts_and_styles', 100 );
		add_action( 'admin_enqueue_scripts', 'Ai1wmke_Extensions_Controller::enqueue_scripts_and_styles', 100 );
		add_action( 'admin_enqueue_scripts', 'Ai1wmke_Import_Controller::enqueue_scripts_and_styles', 100 );
		add_action( 'admin_enqueue_scripts', 'Ai1wmke_Reset_Controller::enqueue_scripts_and_styles', 100 );
		add_action( 'admin_enqueue_scripts', 'Ai1wmke_Schedules_Controller::enqueue_scripts_and_styles', 100 );
		add_action( 'admin_enqueue_scripts', 'Ai1wmke_Settings_Controller::enqueue_scripts_and_styles', 100 );
	}

	/**
	 * Load oAuth token requests
	 *
	 * @return void
	 */
	public function load_oauth_token() {
		add_action( 'admin_init', 'Ai1wmke_Box_Settings_Controller::token', 100 );
		add_action( 'admin_init', 'Ai1wmke_Dropbox_Settings_Controller::token', 100 );
		add_action( 'admin_init', 'Ai1wmke_GCloud_Storage_Settings_Controller::token', 100 );
		add_action( 'admin_init', 'Ai1wmke_GDrive_Settings_Controller::token', 100 );
		add_action( 'admin_init', 'Ai1wmke_OneDrive_Settings_Controller::token', 100 );
		add_action( 'admin_init', 'Ai1wmke_PCloud_Settings_Controller::token', 100 );
	}

	/**
	 * Load WP ajax for settings and import controllers
	 *
	 * @return void
	 */
	public function load_wp_ajax() {
		if ( current_user_can( 'export' ) ) {
			if ( ai1wmke_is_enabled( 'azure-storage' ) ) {
				if ( ai1wmke_is_admin( 'azure-storage' ) ) {
					add_action( 'wp_ajax_ai1wmke_azure_storage_folder', 'Ai1wmke_Azure_Storage_Settings_Controller::folder' );
					add_action( 'wp_ajax_ai1wmke_azure_storage_selector', 'Ai1wmke_Azure_Storage_Settings_Controller::selector' );
				}
			}

			if ( ai1wmke_is_enabled( 'box' ) ) {
				if ( ai1wmke_is_admin( 'box' ) ) {
					add_action( 'wp_ajax_ai1wmke_box_folder', 'Ai1wmke_Box_Settings_Controller::folder' );
					add_action( 'wp_ajax_ai1wmke_box_account', 'Ai1wmke_Box_Settings_Controller::account' );
					add_action( 'wp_ajax_ai1wmke_box_selector', 'Ai1wmke_Box_Settings_Controller::selector' );
				}
			}

			if ( ai1wmke_is_enabled( 'dropbox' ) ) {
				if ( ai1wmke_is_admin( 'dropbox' ) ) {
					add_action( 'wp_ajax_ai1wmke_dropbox_folder', 'Ai1wmke_Dropbox_Settings_Controller::folder' );
					add_action( 'wp_ajax_ai1wmke_dropbox_account', 'Ai1wmke_Dropbox_Settings_Controller::account' );
					add_action( 'wp_ajax_ai1wmke_dropbox_selector', 'Ai1wmke_Dropbox_Settings_Controller::selector' );
				}
			}

			if ( ai1wmke_is_enabled( 'gcloud-storage' ) ) {
				if ( ai1wmke_is_admin( 'gcloud-storage' ) ) {
					add_action( 'wp_ajax_ai1wmke_gcloud_storage_folder', 'Ai1wmke_GCloud_Storage_Settings_Controller::folder' );
					add_action( 'wp_ajax_ai1wmke_gcloud_storage_selector', 'Ai1wmke_GCloud_Storage_Settings_Controller::selector' );
				}
			}

			if ( ai1wmke_is_enabled( 'gdrive' ) ) {
				if ( ai1wmke_is_admin( 'gdrive' ) ) {
					add_action( 'wp_ajax_ai1wmke_gdrive_folder', 'Ai1wmke_GDrive_Settings_Controller::folder' );
					add_action( 'wp_ajax_ai1wmke_gdrive_account', 'Ai1wmke_GDrive_Settings_Controller::account' );
					add_action( 'wp_ajax_ai1wmke_gdrive_selector', 'Ai1wmke_GDrive_Settings_Controller::selector' );
				}
			}

			if ( ai1wmke_is_enabled( 'mega' ) ) {
				if ( ai1wmke_is_admin( 'mega' ) ) {
					add_action( 'wp_ajax_ai1wmke_mega_folder', 'Ai1wmke_Mega_Settings_Controller::folder' );
					add_action( 'wp_ajax_ai1wmke_mega_account', 'Ai1wmke_Mega_Settings_Controller::account' );
					add_action( 'wp_ajax_ai1wmke_mega_selector', 'Ai1wmke_Mega_Settings_Controller::selector' );
				}
			}

			if ( ai1wmke_is_enabled( 'onedrive' ) ) {
				if ( ai1wmke_is_admin( 'onedrive' ) ) {
					add_action( 'wp_ajax_ai1wmke_onedrive_folder', 'Ai1wmke_OneDrive_Settings_Controller::folder' );
					add_action( 'wp_ajax_ai1wmke_onedrive_account', 'Ai1wmke_OneDrive_Settings_Controller::account' );
					add_action( 'wp_ajax_ai1wmke_onedrive_selector', 'Ai1wmke_OneDrive_Settings_Controller::selector' );
				}
			}

			if ( ai1wmke_is_enabled( 'pcloud' ) ) {
				if ( ai1wmke_is_admin( 'pcloud' ) ) {
					add_action( 'wp_ajax_ai1wmke_pcloud_folder', 'Ai1wmke_PCloud_Settings_Controller::folder' );
					add_action( 'wp_ajax_ai1wmke_pcloud_account', 'Ai1wmke_PCloud_Settings_Controller::account' );
					add_action( 'wp_ajax_ai1wmke_pcloud_selector', 'Ai1wmke_PCloud_Settings_Controller::selector' );
				}
			}

			if ( ai1wmke_is_enabled( 'webdav' ) ) {
				if ( ai1wmke_is_admin( 'webdav' ) ) {
					add_action( 'wp_ajax_ai1wmke_webdav_folder', 'Ai1wmke_WebDAV_Settings_Controller::folder' );
					add_action( 'wp_ajax_ai1wmke_webdav_account', 'Ai1wmke_WebDAV_Settings_Controller::account' );
					add_action( 'wp_ajax_ai1wmke_webdav_selector', 'Ai1wmke_WebDAV_Settings_Controller::selector' );
				}
			}

			add_action( 'wp_ajax_ai1wmke_list_files', 'Ai1wmke_Advanced_Options_Controller::list_files' );
			add_action( 'wp_ajax_ai1wmke_list_folders', 'Ai1wmke_Pro_Settings_Controller::list_folders' );
			add_action( 'wp_ajax_ai1wmke_reset_tools', 'Ai1wmke_Reset_Tools_Controller::reset_tools' );

			add_action( 'wp_ajax_ai1wmke_schedule_event_delete', 'Ai1wmke_Schedule_Events_Controller::delete' );
			add_action( 'wp_ajax_ai1wmke_schedule_event_log', 'Ai1wmke_Schedule_Events_Controller::event_log' );
			add_action( 'wp_ajax_ai1wmke_schedule_event_clean', 'Ai1wmke_Schedule_Events_Controller::event_clean' );
			add_action( 'wp_ajax_ai1wmke_schedule_event_manual_run', 'Ai1wmke_Schedule_Events_Controller::manual_run' );
			add_action( 'wp_ajax_ai1wmke_schedule_event_status', 'Ai1wmke_Schedule_Events_Controller::event_status' );
		}

		if ( current_user_can( 'import' ) ) {
			add_action( 'wp_ajax_ai1wmke_azure_storage_browser', 'Ai1wmke_Azure_Storage_Import_Controller::browser' );
			add_action( 'wp_ajax_ai1wmke_b2_browser', 'Ai1wmke_B2_Import_Controller::browser' );
			add_action( 'wp_ajax_ai1wmke_box_browser', 'Ai1wmke_Box_Import_Controller::browser' );
			add_action( 'wp_ajax_ai1wmke_digitalocean_browser', 'Ai1wmke_DigitalOcean_Import_Controller::browser' );
			add_action( 'wp_ajax_ai1wmke_dropbox_browser', 'Ai1wmke_Dropbox_Import_Controller::browser' );
			add_action( 'wp_ajax_ai1wmke_ftp_browser', 'Ai1wmke_FTP_Import_Controller::browser' );
			add_action( 'wp_ajax_ai1wmke_gcloud_storage_browser', 'Ai1wmke_GCloud_Storage_Import_Controller::browser' );
			add_action( 'wp_ajax_ai1wmke_gdrive_browser', 'Ai1wmke_GDrive_Import_Controller::browser' );
			add_action( 'wp_ajax_ai1wmke_glacier_browser', 'Ai1wmke_Glacier_Import_Controller::browser' );
			add_action( 'wp_ajax_ai1wmke_mega_browser', 'Ai1wmke_Mega_Import_Controller::browser' );
			add_action( 'wp_ajax_ai1wmke_onedrive_browser', 'Ai1wmke_OneDrive_Import_Controller::browser' );
			add_action( 'wp_ajax_ai1wmke_pcloud_browser', 'Ai1wmke_PCloud_Import_Controller::browser' );
			add_action( 'wp_ajax_ai1wmke_s3_client_browser', 'Ai1wmke_S3_Client_Import_Controller::browser' );
			add_action( 'wp_ajax_ai1wmke_s3_browser', 'Ai1wmke_S3_Import_Controller::browser' );
			add_action( 'wp_ajax_ai1wmke_webdav_browser', 'Ai1wmke_WebDAV_Import_Controller::browser' );

			add_action( 'wp_ajax_ai1wmke_b2_incremental', 'Ai1wmke_B2_Import_Controller::incremental' );
			add_action( 'wp_ajax_ai1wmke_digitalocean_incremental', 'Ai1wmke_DigitalOcean_Import_Controller::incremental' );
			add_action( 'wp_ajax_ai1wmke_dropbox_incremental', 'Ai1wmke_Dropbox_Import_Controller::incremental' );
			add_action( 'wp_ajax_ai1wmke_ftp_incremental', 'Ai1wmke_FTP_Import_Controller::incremental' );
			add_action( 'wp_ajax_ai1wmke_gcloud_storage_incremental', 'Ai1wmke_GCloud_Storage_Import_Controller::incremental' );
			add_action( 'wp_ajax_ai1wmke_gdrive_incremental', 'Ai1wmke_GDrive_Import_Controller::incremental' );
			add_action( 'wp_ajax_ai1wmke_mega_incremental', 'Ai1wmke_Mega_Import_Controller::incremental' );
			add_action( 'wp_ajax_ai1wmke_onedrive_incremental', 'Ai1wmke_OneDrive_Import_Controller::incremental' );
			add_action( 'wp_ajax_ai1wmke_pcloud_incremental', 'Ai1wmke_PCloud_Import_Controller::incremental' );
			add_action( 'wp_ajax_ai1wmke_s3_client_incremental', 'Ai1wmke_S3_Client_Import_Controller::incremental' );
			add_action( 'wp_ajax_ai1wmke_s3_incremental', 'Ai1wmke_S3_Import_Controller::incremental' );
		}

		if ( current_user_can( 'upload_files' ) ) {
			if ( ai1wmke_is_enabled( 'direct', false ) ) {
				if ( ai1wmke_is_admin( 'direct' ) ) {
					add_action( 'wp_ajax_ai1wmke_direct_add_site', 'Ai1wmke_Direct_Sites_Controller::add_site' );
					add_action( 'wp_ajax_ai1wmke_direct_unlink_site', 'Ai1wmke_Direct_Sites_Controller::unlink_site' );
				}
			}
		}

		add_action( 'wp_ajax_nopriv_ai1wmke_direct_info', 'Ai1wmke_Direct_Sites_Controller::info' );
	}

	/**
	 * Initializes language domain for the plugin
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( AI1WMKE_PLUGIN_NAME, false, false );
	}

	/**
	 * WP CLI commands
	 *
	 * @return void
	 */
	public function cli_init() {
		if ( defined( 'WP_CLI' ) ) {
			if ( ai1wmke_is_enabled( 'azure-storage' ) ) {
				WP_CLI::add_command( 'ai1wm azure-storage', 'Ai1wmke_Azure_Storage_CLI_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for Microsoft Azure Storage', AI1WMKE_PLUGIN_NAME ) ) );
			}

			if ( ai1wmke_is_enabled( 'b2' ) ) {
				WP_CLI::add_command( 'ai1wm b2', 'Ai1wmke_B2_CLI_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for Backblaze B2', AI1WMKE_PLUGIN_NAME ) ) );
				WP_CLI::add_command( 'ai1wm b2 incremental', 'Ai1wmke_B2_CLI_Incremental_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for Backblaze B2 incremental backups', AI1WMKE_PLUGIN_NAME ) ) );
			}

			if ( ai1wmke_is_enabled( 'box' ) ) {
				WP_CLI::add_command( 'ai1wm box', 'Ai1wmke_Box_CLI_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for Box', AI1WMKE_PLUGIN_NAME ) ) );
			}

			if ( ai1wmke_is_enabled( 'digitalocean' ) ) {
				WP_CLI::add_command( 'ai1wm digitalocean', 'Ai1wmke_DigitalOcean_CLI_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for DigitalOcean Spaces', AI1WMKE_PLUGIN_NAME ) ) );
				WP_CLI::add_command( 'ai1wm digitalocean incremental', 'Ai1wmke_DigitalOcean_CLI_Incremental_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for DigitalOcean Spaces incremental backups', AI1WMKE_PLUGIN_NAME ) ) );
			}

			if ( ai1wmke_is_enabled( 'dropbox' ) ) {
				WP_CLI::add_command( 'ai1wm dropbox', 'Ai1wmke_Dropbox_CLI_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for Dropbox', AI1WMKE_PLUGIN_NAME ) ) );
				WP_CLI::add_command( 'ai1wm dropbox incremental', 'Ai1wmke_Dropbox_CLI_Incremental_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for Dropbox incremental backups', AI1WMKE_PLUGIN_NAME ) ) );
			}

			if ( ai1wmke_is_enabled( 'ftp' ) ) {
				WP_CLI::add_command( 'ai1wm ftp', 'Ai1wmke_FTP_CLI_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for FTP', AI1WMKE_PLUGIN_NAME ) ) );
				WP_CLI::add_command( 'ai1wm ftp incremental', 'Ai1wmke_FTP_CLI_Incremental_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for FTP incremental backups', AI1WMKE_PLUGIN_NAME ) ) );
			}

			if ( ai1wmke_is_enabled( 'gcloud-storage' ) ) {
				WP_CLI::add_command( 'ai1wm gcloud-storage', 'Ai1wmke_GCloud_Storage_CLI_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for Google Cloud Storage', AI1WMKE_PLUGIN_NAME ) ) );
				WP_CLI::add_command( 'ai1wm gcloud-storage incremental', 'Ai1wmke_GCloud_Storage_CLI_Incremental_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for Google Cloud Storage incremental backups', AI1WMKE_PLUGIN_NAME ) ) );
			}

			if ( ai1wmke_is_enabled( 'gdrive' ) ) {
				WP_CLI::add_command( 'ai1wm gdrive', 'Ai1wmke_GDrive_CLI_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for Google Drive', AI1WMKE_PLUGIN_NAME ) ) );
				WP_CLI::add_command( 'ai1wm gdrive incremental', 'Ai1wmke_GDrive_CLI_Incremental_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for Google Drive incremental backups', AI1WMKE_PLUGIN_NAME ) ) );
			}

			if ( ai1wmke_is_enabled( 'glacier' ) ) {
				WP_CLI::add_command( 'ai1wm glacier', 'Ai1wmke_Glacier_CLI_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for Amazon Glacier', AI1WMKE_PLUGIN_NAME ) ) );
			}

			if ( ai1wmke_is_enabled( 'mega' ) ) {
				WP_CLI::add_command( 'ai1wm mega', 'Ai1wmke_Mega_CLI_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for Mega', AI1WMKE_PLUGIN_NAME ) ) );
				WP_CLI::add_command( 'ai1wm mega incremental', 'Ai1wmke_Mega_CLI_Incremental_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for Mega incremental backups', AI1WMKE_PLUGIN_NAME ) ) );
			}

			if ( ai1wmke_is_enabled( 'onedrive' ) ) {
				WP_CLI::add_command( 'ai1wm onedrive', 'Ai1wmke_OneDrive_CLI_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for OneDrive', AI1WMKE_PLUGIN_NAME ) ) );
				WP_CLI::add_command( 'ai1wm onedrive incremental', 'Ai1wmke_OneDrive_CLI_Incremental_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for OneDrive incremental backups', AI1WMKE_PLUGIN_NAME ) ) );
			}

			if ( ai1wmke_is_enabled( 'pcloud' ) ) {
				WP_CLI::add_command( 'ai1wm pcloud', 'Ai1wmke_PCloud_CLI_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for pCloud', AI1WMKE_PLUGIN_NAME ) ) );
				WP_CLI::add_command( 'ai1wm pcloud incremental', 'Ai1wmke_PCloud_CLI_Incremental_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for pCloud incremental backups', AI1WMKE_PLUGIN_NAME ) ) );
			}

			if ( ai1wmke_is_enabled( 's3' ) ) {
				WP_CLI::add_command( 'ai1wm s3', 'Ai1wmke_S3_CLI_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for Amazon S3', AI1WMKE_PLUGIN_NAME ) ) );
				WP_CLI::add_command( 'ai1wm s3 incremental', 'Ai1wmke_S3_CLI_Incremental_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for Amazon S3 incremental backups', AI1WMKE_PLUGIN_NAME ) ) );
			}

			if ( ai1wmke_is_enabled( 's3-client' ) ) {
				WP_CLI::add_command( 'ai1wm s3-client', 'Ai1wmke_S3_Client_CLI_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for S3 Client', AI1WMKE_PLUGIN_NAME ) ) );
				WP_CLI::add_command( 'ai1wm s3-client incremental', 'Ai1wmke_S3_Client_CLI_Incremental_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for S3 Client incremental backups', AI1WMKE_PLUGIN_NAME ) ) );
			}

			if ( ai1wmke_is_enabled( 'url' ) ) {
				WP_CLI::add_command( 'ai1wm url', 'Ai1wmke_URL_CLI_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for URL', AI1WMKE_PLUGIN_NAME ) ) );
			}

			if ( ai1wmke_is_enabled( 'webdav' ) ) {
				WP_CLI::add_command( 'ai1wm webdav', 'Ai1wmke_WebDAV_CLI_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command for WebDAV', AI1WMKE_PLUGIN_NAME ) ) );
			}

			WP_CLI::add_command( 'ai1wm', 'Ai1wmke_Backup_CLI_Command', array( 'shortdesc' => __( 'All-in-One WP Migration Command', AI1WMKE_PLUGIN_NAME ) ) );
		}
	}

	/**
	 * Load WP cron hooks
	 *
	 * @return void
	 */
	public function load_cron_hooks() {
		add_action( 'ai1wmke_azure_storage_hourly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_azure_storage_daily_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_azure_storage_weekly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_azure_storage_monthly_export', 'Ai1wm_Export_Controller::export' );

		add_action( 'ai1wmke_b2_hourly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_b2_daily_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_b2_weekly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_b2_monthly_export', 'Ai1wm_Export_Controller::export' );

		add_action( 'ai1wmke_box_hourly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_box_daily_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_box_weekly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_box_monthly_export', 'Ai1wm_Export_Controller::export' );

		add_action( 'ai1wmke_digitalocean_hourly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_digitalocean_daily_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_digitalocean_weekly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_digitalocean_monthly_export', 'Ai1wm_Export_Controller::export' );

		add_action( 'ai1wmke_dropbox_hourly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_dropbox_daily_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_dropbox_weekly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_dropbox_monthly_export', 'Ai1wm_Export_Controller::export' );

		add_action( 'ai1wmke_ftp_hourly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_ftp_daily_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_ftp_weekly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_ftp_monthly_export', 'Ai1wm_Export_Controller::export' );

		add_action( 'ai1wmke_gcloud_storage_hourly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_gcloud_storage_daily_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_gcloud_storage_weekly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_gcloud_storage_monthly_export', 'Ai1wm_Export_Controller::export' );

		add_action( 'ai1wmke_gdrive_hourly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_gdrive_daily_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_gdrive_weekly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_gdrive_monthly_export', 'Ai1wm_Export_Controller::export' );

		add_action( 'ai1wmke_glacier_hourly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_glacier_daily_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_glacier_weekly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_glacier_monthly_export', 'Ai1wm_Export_Controller::export' );

		add_action( 'ai1wmke_mega_hourly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_mega_daily_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_mega_weekly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_mega_monthly_export', 'Ai1wm_Export_Controller::export' );

		add_action( 'ai1wmke_onedrive_hourly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_onedrive_daily_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_onedrive_weekly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_onedrive_monthly_export', 'Ai1wm_Export_Controller::export' );

		add_action( 'ai1wmke_pcloud_hourly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_pcloud_daily_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_pcloud_weekly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_pcloud_monthly_export', 'Ai1wm_Export_Controller::export' );

		add_action( 'ai1wmke_s3_client_hourly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_s3_client_daily_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_s3_client_weekly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_s3_client_monthly_export', 'Ai1wm_Export_Controller::export' );

		add_action( 'ai1wmke_s3_hourly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_s3_daily_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_s3_weekly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_s3_monthly_export', 'Ai1wm_Export_Controller::export' );

		add_action( 'ai1wmke_webdav_hourly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_webdav_daily_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_webdav_weekly_export', 'Ai1wm_Export_Controller::export' );
		add_action( 'ai1wmke_webdav_monthly_export', 'Ai1wm_Export_Controller::export' );

		add_action( Ai1wmke_Schedule_Event::CRON_HOOK, 'Ai1wmke_Schedule_Events_Controller::run' );
	}

	/**
	 * Load admin menu and notices
	 *
	 * @return void
	 */
	public function load_admin_menu() {
		if ( defined( 'AI1WM_PLUGIN_NAME' ) ) {
			if ( is_multisite() ) {
				add_action( 'network_admin_menu', array( $this, 'admin_menu' ), 100 );
			} else {
				add_action( 'admin_menu', array( $this, 'admin_menu' ), 100 );
			}
		} else {
			if ( is_multisite() ) {
				add_action( 'network_admin_notices', array( $this, 'admin_notices' ) );
			} else {
				add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			}
		}
	}

	/**
	 * Load export and import buttons
	 *
	 * @return void
	 */
	public function load_plugin_buttons() {
		add_filter( 'ai1wm_export_azure_storage', 'Ai1wmke_Azure_Storage_Export_Controller::button' );
		add_filter( 'ai1wm_export_b2', 'Ai1wmke_B2_Export_Controller::button' );
		add_filter( 'ai1wm_export_box', 'Ai1wmke_Box_Export_Controller::button' );
		add_filter( 'ai1wm_export_digitalocean', 'Ai1wmke_DigitalOcean_Export_Controller::button' );
		add_filter( 'ai1wm_export_dropbox', 'Ai1wmke_Dropbox_Export_Controller::button' );
		add_filter( 'ai1wm_export_ftp', 'Ai1wmke_FTP_Export_Controller::button' );
		add_filter( 'ai1wm_export_gcloud_storage', 'Ai1wmke_GCloud_Storage_Export_Controller::button' );
		add_filter( 'ai1wm_export_gdrive', 'Ai1wmke_GDrive_Export_Controller::button' );
		add_filter( 'ai1wm_export_glacier', 'Ai1wmke_Glacier_Export_Controller::button' );
		add_filter( 'ai1wm_export_mega', 'Ai1wmke_Mega_Export_Controller::button' );
		add_filter( 'ai1wm_export_onedrive', 'Ai1wmke_OneDrive_Export_Controller::button' );
		add_filter( 'ai1wm_export_pcloud', 'Ai1wmke_PCloud_Export_Controller::button' );
		add_filter( 'ai1wm_export_s3_client', 'Ai1wmke_S3_Client_Export_Controller::button' );
		add_filter( 'ai1wm_export_s3', 'Ai1wmke_S3_Export_Controller::button' );
		add_filter( 'ai1wm_export_webdav', 'Ai1wmke_WebDAV_Export_Controller::button' );

		add_filter( 'ai1wm_import_azure_storage', 'Ai1wmke_Azure_Storage_Import_Controller::button' );
		add_filter( 'ai1wm_import_b2', 'Ai1wmke_B2_Import_Controller::button' );
		add_filter( 'ai1wm_import_box', 'Ai1wmke_Box_Import_Controller::button' );
		add_filter( 'ai1wm_import_digitalocean', 'Ai1wmke_DigitalOcean_Import_Controller::button' );
		add_filter( 'ai1wm_import_dropbox', 'Ai1wmke_Dropbox_Import_Controller::button' );
		add_filter( 'ai1wm_import_ftp', 'Ai1wmke_FTP_Import_Controller::button' );
		add_filter( 'ai1wm_import_gcloud_storage', 'Ai1wmke_GCloud_Storage_Import_Controller::button' );
		add_filter( 'ai1wm_import_gdrive', 'Ai1wmke_GDrive_Import_Controller::button' );
		add_filter( 'ai1wm_import_glacier', 'Ai1wmke_Glacier_Import_Controller::button' );
		add_filter( 'ai1wm_import_mega', 'Ai1wmke_Mega_Import_Controller::button' );
		add_filter( 'ai1wm_import_onedrive', 'Ai1wmke_OneDrive_Import_Controller::button' );
		add_filter( 'ai1wm_import_pcloud', 'Ai1wmke_PCloud_Import_Controller::button' );
		add_filter( 'ai1wm_import_s3_client', 'Ai1wmke_S3_Client_Import_Controller::button' );
		add_filter( 'ai1wm_import_s3', 'Ai1wmke_S3_Import_Controller::button' );
		add_filter( 'ai1wm_import_url', 'Ai1wmke_URL_Import_Controller::button' );
		add_filter( 'ai1wm_import_webdav', 'Ai1wmke_WebDAV_Import_Controller::button' );
	}

	/**
	 * Load schedule buttons
	 *
	 * @return void
	 */
	public function load_schedule_buttons() {
		add_filter( 'ai1wmke_schedule_buttons', 'Ai1wmke_Schedule_Events_Controller::buttons' );
		add_filter( 'ai1wmke_incremental_storages', 'Ai1wmke_Schedule_Events_Controller::incremental_storages' );
	}

	/**
	 * Load export and import commands
	 *
	 * @return void
	 */
	public function load_plugin_commands() {
		if ( ai1wmke_is_enabled( 'azure-storage' ) ) {
			if ( ai1wmke_is_running( 'azure-storage' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_Azure_Storage_Export_Connect::execute', 250 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Azure_Storage_Export_Upload::execute', 260 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Azure_Storage_Export_Retention::execute', 280 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Azure_Storage_Export_Done::execute', 290 );

				add_filter( 'ai1wm_import', 'Ai1wmke_Azure_Storage_Import_Create::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_Azure_Storage_Import_Download::execute', 30 );

				remove_filter( 'ai1wm_export', 'Ai1wm_Export_Download::execute', 250 );
				remove_filter( 'ai1wm_import', 'Ai1wm_Import_Upload::execute', 5 );
			}
		}

		if ( ai1wmke_is_enabled( 'b2' ) ) {
			if ( ai1wmke_is_running( 'b2' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_B2_Export_Connect::execute', 250 );
				add_filter( 'ai1wm_export', 'Ai1wmke_B2_Export_Upload::execute', 260 );
				add_filter( 'ai1wm_export', 'Ai1wmke_B2_Export_Retention::execute', 280 );
				add_filter( 'ai1wm_export', 'Ai1wmke_B2_Export_Done::execute', 290 );

				add_filter( 'ai1wm_import', 'Ai1wmke_B2_Import_Create::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_B2_Import_Download::execute', 30 );

				remove_filter( 'ai1wm_export', 'Ai1wm_Export_Download::execute', 250 );
				remove_filter( 'ai1wm_import', 'Ai1wm_Import_Upload::execute', 5 );
			}
		}

		if ( ai1wmke_is_enabled( 'box' ) ) {
			if ( ai1wmke_is_running( 'box' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_Box_Export_Connect::execute', 250 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Box_Export_Upload::execute', 260 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Box_Export_Retention::execute', 280 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Box_Export_Done::execute', 290 );

				add_filter( 'ai1wm_import', 'Ai1wmke_Box_Import_Create::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_Box_Import_Download::execute', 30 );

				remove_filter( 'ai1wm_export', 'Ai1wm_Export_Download::execute', 250 );
				remove_filter( 'ai1wm_import', 'Ai1wm_Import_Upload::execute', 5 );
			}
		}

		if ( ai1wmke_is_enabled( 'digitalocean' ) ) {
			if ( ai1wmke_is_running( 'digitalocean' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_DigitalOcean_Export_Connect::execute', 250 );
				add_filter( 'ai1wm_export', 'Ai1wmke_DigitalOcean_Export_Upload::execute', 260 );
				add_filter( 'ai1wm_export', 'Ai1wmke_DigitalOcean_Export_Retention::execute', 280 );
				add_filter( 'ai1wm_export', 'Ai1wmke_DigitalOcean_Export_Done::execute', 290 );

				add_filter( 'ai1wm_import', 'Ai1wmke_DigitalOcean_Import_Create::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_DigitalOcean_Import_Download::execute', 30 );

				remove_filter( 'ai1wm_export', 'Ai1wm_Export_Download::execute', 250 );
				remove_filter( 'ai1wm_import', 'Ai1wm_Import_Upload::execute', 5 );
			}
		}

		if ( ai1wmke_is_enabled( 'direct', false ) ) {
			if ( ai1wmke_is_running( 'direct_push' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_Direct_Push_Init::execute', 250 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Direct_Push_Upload::execute', 260 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Direct_Push_Start_Import::execute', 270 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Direct_Push_Confirm_Import::execute', 280 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Direct_Push_Done::execute', 290 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Direct_Push_Clean::execute', 300 );

				remove_filter( 'ai1wm_export', 'Ai1wm_Export_Download::execute', 250 );
				remove_filter( 'ai1wm_export', 'Ai1wm_Export_Clean::execute', 300 );
			}

			if ( ai1wmke_is_running( 'direct_pull' ) ) {
				add_filter( 'ai1wm_import', 'Ai1wmke_Direct_Pull_Init::execute', 5 );
				add_filter( 'ai1wm_import', 'Ai1wmke_Direct_Pull_Start_Export::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_Direct_Pull_Download::execute', 30 );
				add_filter( 'ai1wm_import', 'Ai1wmke_Direct_Pull_Clean::execute', 400 );

				remove_filter( 'ai1wm_import', 'Ai1wm_Import_Upload::execute', 5 );
				remove_filter( 'ai1wm_import', 'Ai1wm_Import_Clean::execute', 400 );
			}
		}

		if ( ai1wmke_is_enabled( 'dropbox' ) ) {
			if ( ai1wmke_is_running( 'dropbox' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_Dropbox_Export_Connect::execute', 250 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Dropbox_Export_Upload::execute', 260 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Dropbox_Export_Retention::execute', 280 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Dropbox_Export_Done::execute', 290 );

				add_filter( 'ai1wm_import', 'Ai1wmke_Dropbox_Import_Create::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_Dropbox_Import_Download::execute', 30 );

				remove_filter( 'ai1wm_export', 'Ai1wm_Export_Download::execute', 250 );
				remove_filter( 'ai1wm_import', 'Ai1wm_Import_Upload::execute', 5 );
			}
		}

		if ( ai1wmke_is_enabled( 'ftp' ) ) {
			if ( ai1wmke_is_running( 'ftp' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_FTP_Export_Connect::execute', 250 );
				add_filter( 'ai1wm_export', 'Ai1wmke_FTP_Export_Upload::execute', 260 );
				add_filter( 'ai1wm_export', 'Ai1wmke_FTP_Export_Retention::execute', 280 );
				add_filter( 'ai1wm_export', 'Ai1wmke_FTP_Export_Done::execute', 290 );

				add_filter( 'ai1wm_import', 'Ai1wmke_FTP_Import_Create::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_FTP_Import_Download::execute', 30 );

				remove_filter( 'ai1wm_export', 'Ai1wm_Export_Download::execute', 250 );
				remove_filter( 'ai1wm_import', 'Ai1wm_Import_Upload::execute', 5 );
			}
		}

		if ( ai1wmke_is_enabled( 'gcloud-storage' ) ) {
			if ( ai1wmke_is_running( 'gcloud-storage' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_GCloud_Storage_Export_Connect::execute', 250 );
				add_filter( 'ai1wm_export', 'Ai1wmke_GCloud_Storage_Export_Upload::execute', 260 );
				add_filter( 'ai1wm_export', 'Ai1wmke_GCloud_Storage_Export_Retention::execute', 280 );
				add_filter( 'ai1wm_export', 'Ai1wmke_GCloud_Storage_Export_Done::execute', 290 );

				add_filter( 'ai1wm_import', 'Ai1wmke_GCloud_Storage_Import_Create::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_GCloud_Storage_Import_Download::execute', 30 );

				remove_filter( 'ai1wm_export', 'Ai1wm_Export_Download::execute', 250 );
				remove_filter( 'ai1wm_import', 'Ai1wm_Import_Upload::execute', 5 );
			}
		}

		if ( ai1wmke_is_enabled( 'gdrive' ) ) {
			if ( ai1wmke_is_running( 'gdrive' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_GDrive_Export_Connect::execute', 250 );
				add_filter( 'ai1wm_export', 'Ai1wmke_GDrive_Export_Upload::execute', 260 );
				add_filter( 'ai1wm_export', 'Ai1wmke_GDrive_Export_Retention::execute', 280 );
				add_filter( 'ai1wm_export', 'Ai1wmke_GDrive_Export_Done::execute', 290 );

				add_filter( 'ai1wm_import', 'Ai1wmke_GDrive_Import_Create::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_GDrive_Import_Download::execute', 30 );

				remove_filter( 'ai1wm_export', 'Ai1wm_Export_Download::execute', 250 );
				remove_filter( 'ai1wm_import', 'Ai1wm_Import_Upload::execute', 5 );
			}
		}

		if ( ai1wmke_is_enabled( 'glacier' ) ) {
			if ( ai1wmke_is_running( 'glacier' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_Glacier_Export_Connect::execute', 250 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Glacier_Export_Upload::execute', 260 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Glacier_Export_Done::execute', 290 );

				add_filter( 'ai1wm_import', 'Ai1wmke_Glacier_Import_Create::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_Glacier_Import_Download::execute', 30 );

				remove_filter( 'ai1wm_export', 'Ai1wm_Export_Download::execute', 250 );
				remove_filter( 'ai1wm_import', 'Ai1wm_Import_Upload::execute', 5 );
			}
		}

		if ( ai1wmke_is_enabled( 'mega' ) ) {
			if ( ai1wmke_is_running( 'mega' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_Mega_Export_Connect::execute', 250 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Mega_Export_Upload::execute', 260 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Mega_Export_Retention::execute', 280 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Mega_Export_Done::execute', 290 );

				add_filter( 'ai1wm_import', 'Ai1wmke_Mega_Import_Create::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_Mega_Import_Download::execute', 30 );

				remove_filter( 'ai1wm_export', 'Ai1wm_Export_Download::execute', 250 );
				remove_filter( 'ai1wm_import', 'Ai1wm_Import_Upload::execute', 5 );
			}
		}

		if ( ai1wmke_is_enabled( 'onedrive' ) ) {
			if ( ai1wmke_is_running( 'onedrive' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_OneDrive_Export_Connect::execute', 250 );
				add_filter( 'ai1wm_export', 'Ai1wmke_OneDrive_Export_Upload::execute', 260 );
				add_filter( 'ai1wm_export', 'Ai1wmke_OneDrive_Export_Retention::execute', 280 );
				add_filter( 'ai1wm_export', 'Ai1wmke_OneDrive_Export_Done::execute', 290 );

				add_filter( 'ai1wm_import', 'Ai1wmke_OneDrive_Import_Create::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_OneDrive_Import_Download::execute', 30 );

				remove_filter( 'ai1wm_export', 'Ai1wm_Export_Download::execute', 250 );
				remove_filter( 'ai1wm_import', 'Ai1wm_Import_Upload::execute', 5 );
			}
		}

		if ( ai1wmke_is_enabled( 'pcloud' ) ) {
			if ( ai1wmke_is_running( 'pcloud' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_PCloud_Export_Connect::execute', 250 );
				add_filter( 'ai1wm_export', 'Ai1wmke_PCloud_Export_Upload::execute', 260 );
				add_filter( 'ai1wm_export', 'Ai1wmke_PCloud_Export_Retention::execute', 280 );
				add_filter( 'ai1wm_export', 'Ai1wmke_PCloud_Export_Done::execute', 290 );

				add_filter( 'ai1wm_import', 'Ai1wmke_PCloud_Import_Create::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_PCloud_Import_Download::execute', 30 );

				remove_filter( 'ai1wm_export', 'Ai1wm_Export_Download::execute', 250 );
				remove_filter( 'ai1wm_import', 'Ai1wm_Import_Upload::execute', 5 );
			}
		}

		if ( ai1wmke_is_running( 'file' ) ) {
			remove_all_filters( 'ai1wm_export', 280 );
			remove_all_filters( 'ai1wm_import', 5 );

			add_filter( 'ai1wm_export', 'Ai1wmke_Pro_Export_Retention::execute', 280 );
			add_filter( 'ai1wm_import', 'Ai1wmke_Pro_Import_Upload::execute', 5 );
		}

		if ( ai1wmke_is_enabled( 's3-client' ) ) {
			if ( ai1wmke_is_running( 's3-client' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_S3_Client_Export_Connect::execute', 250 );
				add_filter( 'ai1wm_export', 'Ai1wmke_S3_Client_Export_Upload::execute', 260 );
				add_filter( 'ai1wm_export', 'Ai1wmke_S3_Client_Export_Retention::execute', 280 );
				add_filter( 'ai1wm_export', 'Ai1wmke_S3_Client_Export_Done::execute', 290 );

				add_filter( 'ai1wm_import', 'Ai1wmke_S3_Client_Import_Create::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_S3_Client_Import_Download::execute', 30 );

				remove_filter( 'ai1wm_export', 'Ai1wm_Export_Download::execute', 250 );
				remove_filter( 'ai1wm_import', 'Ai1wm_Import_Upload::execute', 5 );
			}
		}

		if ( ai1wmke_is_enabled( 's3' ) ) {
			if ( ai1wmke_is_running( 's3' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_S3_Export_Connect::execute', 250 );
				add_filter( 'ai1wm_export', 'Ai1wmke_S3_Export_Upload::execute', 260 );
				add_filter( 'ai1wm_export', 'Ai1wmke_S3_Export_Retention::execute', 280 );
				add_filter( 'ai1wm_export', 'Ai1wmke_S3_Export_Done::execute', 290 );

				add_filter( 'ai1wm_import', 'Ai1wmke_S3_Import_Create::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_S3_Import_Download::execute', 30 );

				remove_filter( 'ai1wm_export', 'Ai1wm_Export_Download::execute', 250 );
				remove_filter( 'ai1wm_import', 'Ai1wm_Import_Upload::execute', 5 );
			}
		}

		if ( ai1wmke_is_enabled( 'url' ) ) {
			if ( ai1wmke_is_running( 'url' ) ) {
				add_filter( 'ai1wm_import', 'Ai1wmke_URL_Import_Create::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_URL_Import_Download::execute', 30 );

				remove_filter( 'ai1wm_import', 'Ai1wm_Import_Upload::execute', 5 );
			}
		}

		if ( ai1wmke_is_enabled( 'webdav' ) ) {
			if ( ai1wmke_is_running( 'webdav' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_WebDAV_Export_Connect::execute', 250 );
				add_filter( 'ai1wm_export', 'Ai1wmke_WebDAV_Export_Upload::execute', 260 );
				add_filter( 'ai1wm_export', 'Ai1wmke_WebDAV_Export_Retention::execute', 280 );
				add_filter( 'ai1wm_export', 'Ai1wmke_WebDAV_Export_Done::execute', 290 );

				add_filter( 'ai1wm_import', 'Ai1wmke_WebDAV_Import_Create::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_WebDAV_Import_Download::execute', 30 );

				remove_filter( 'ai1wm_export', 'Ai1wm_Export_Download::execute', 250 );
				remove_filter( 'ai1wm_import', 'Ai1wm_Import_Upload::execute', 5 );
			}
		}

		add_filter( 'ai1wm_import', 'Ai1wmke_Import_Select_Settings::execute', 290 );
		add_filter( 'ai1wm_import', 'Ai1wmke_Import_Update_Settings::execute', 315 );
	}

	/**
	 * Load incremental commands
	 *
	 * @return void
	 */
	public function load_incremental_commands() {
		if ( ai1wmke_is_enabled( 'b2' ) ) {
			if ( ai1wmke_is_incremental( 'b2' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_B2_Export_Incremental_Content::execute', 105 );
				add_filter( 'ai1wm_export', 'Ai1wmke_B2_Export_Incremental_Media::execute', 115 );
				add_filter( 'ai1wm_export', 'Ai1wmke_B2_Export_Incremental_Plugins::execute', 125 );
				add_filter( 'ai1wm_export', 'Ai1wmke_B2_Export_Incremental_Themes::execute', 135 );
				add_filter( 'ai1wm_export', 'Ai1wmke_B2_Export_Incremental_Backups::execute', 270 );

				add_filter( 'ai1wm_import', 'Ai1wmke_B2_Import_Incremental_Prepare::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_B2_Import_Incremental_Download::execute', 30 );

				remove_filter( 'ai1wm_export', 'Ai1wmke_B2_Export_Retention::execute', 280 );
				remove_filter( 'ai1wm_import', 'Ai1wmke_B2_Import_Create::execute', 20 );
				remove_filter( 'ai1wm_import', 'Ai1wmke_B2_Import_Download::execute', 30 );
			}
		}

		if ( ai1wmke_is_enabled( 'digitalocean' ) ) {
			if ( ai1wmke_is_incremental( 'digitalocean' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_DigitalOcean_Export_Incremental_Content::execute', 105 );
				add_filter( 'ai1wm_export', 'Ai1wmke_DigitalOcean_Export_Incremental_Media::execute', 115 );
				add_filter( 'ai1wm_export', 'Ai1wmke_DigitalOcean_Export_Incremental_Plugins::execute', 125 );
				add_filter( 'ai1wm_export', 'Ai1wmke_DigitalOcean_Export_Incremental_Themes::execute', 135 );
				add_filter( 'ai1wm_export', 'Ai1wmke_DigitalOcean_Export_Incremental_Backups::execute', 270 );

				add_filter( 'ai1wm_import', 'Ai1wmke_DigitalOcean_Import_Incremental_Prepare::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_DigitalOcean_Import_Incremental_Download::execute', 30 );

				remove_filter( 'ai1wm_export', 'Ai1wmke_DigitalOcean_Export_Retention::execute', 280 );
				remove_filter( 'ai1wm_import', 'Ai1wmke_DigitalOcean_Import_Create::execute', 20 );
				remove_filter( 'ai1wm_import', 'Ai1wmke_DigitalOcean_Import_Download::execute', 30 );
			}
		}

		if ( ai1wmke_is_enabled( 'dropbox' ) ) {
			if ( ai1wmke_is_incremental( 'dropbox' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_Dropbox_Export_Incremental_Content::execute', 105 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Dropbox_Export_Incremental_Media::execute', 115 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Dropbox_Export_Incremental_Plugins::execute', 125 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Dropbox_Export_Incremental_Themes::execute', 135 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Dropbox_Export_Incremental_Backups::execute', 270 );

				add_filter( 'ai1wm_import', 'Ai1wmke_Dropbox_Import_Incremental_Prepare::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_Dropbox_Import_Incremental_Download::execute', 30 );

				remove_filter( 'ai1wm_export', 'Ai1wmke_Dropbox_Export_Retention::execute', 280 );
				remove_filter( 'ai1wm_import', 'Ai1wmke_Dropbox_Import_Create::execute', 20 );
				remove_filter( 'ai1wm_import', 'Ai1wmke_Dropbox_Import_Download::execute', 30 );
			}
		}

		if ( ai1wmke_is_enabled( 'ftp' ) ) {
			if ( ai1wmke_is_incremental( 'ftp' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_FTP_Export_Incremental_Content::execute', 105 );
				add_filter( 'ai1wm_export', 'Ai1wmke_FTP_Export_Incremental_Media::execute', 115 );
				add_filter( 'ai1wm_export', 'Ai1wmke_FTP_Export_Incremental_Plugins::execute', 125 );
				add_filter( 'ai1wm_export', 'Ai1wmke_FTP_Export_Incremental_Themes::execute', 135 );
				add_filter( 'ai1wm_export', 'Ai1wmke_FTP_Export_Incremental_Backups::execute', 270 );

				add_filter( 'ai1wm_import', 'Ai1wmke_FTP_Import_Incremental_Prepare::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_FTP_Import_Incremental_Download::execute', 30 );

				remove_filter( 'ai1wm_export', 'Ai1wmke_FTP_Export_Retention::execute', 280 );
				remove_filter( 'ai1wm_import', 'Ai1wmke_FTP_Import_Create::execute', 20 );
				remove_filter( 'ai1wm_import', 'Ai1wmke_FTP_Import_Download::execute', 30 );
			}
		}

		if ( ai1wmke_is_enabled( 'gcloud-storage' ) ) {
			if ( ai1wmke_is_incremental( 'gcloud-storage' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_GCloud_Storage_Export_Incremental_Content::execute', 105 );
				add_filter( 'ai1wm_export', 'Ai1wmke_GCloud_Storage_Export_Incremental_Media::execute', 115 );
				add_filter( 'ai1wm_export', 'Ai1wmke_GCloud_Storage_Export_Incremental_Plugins::execute', 125 );
				add_filter( 'ai1wm_export', 'Ai1wmke_GCloud_Storage_Export_Incremental_Themes::execute', 135 );
				add_filter( 'ai1wm_export', 'Ai1wmke_GCloud_Storage_Export_Incremental_Backups::execute', 270 );

				add_filter( 'ai1wm_import', 'Ai1wmke_GCloud_Storage_Import_Incremental_Prepare::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_GCloud_Storage_Import_Incremental_Download::execute', 30 );

				remove_filter( 'ai1wm_export', 'Ai1wmke_GCloud_Storage_Export_Retention::execute', 280 );
				remove_filter( 'ai1wm_import', 'Ai1wmke_GCloud_Storage_Import_Create::execute', 20 );
				remove_filter( 'ai1wm_import', 'Ai1wmke_GCloud_Storage_Import_Download::execute', 30 );
			}
		}

		if ( ai1wmke_is_enabled( 'gdrive' ) ) {
			if ( ai1wmke_is_incremental( 'gdrive' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_GDrive_Export_Incremental_Content::execute', 105 );
				add_filter( 'ai1wm_export', 'Ai1wmke_GDrive_Export_Incremental_Media::execute', 115 );
				add_filter( 'ai1wm_export', 'Ai1wmke_GDrive_Export_Incremental_Plugins::execute', 125 );
				add_filter( 'ai1wm_export', 'Ai1wmke_GDrive_Export_Incremental_Themes::execute', 135 );
				add_filter( 'ai1wm_export', 'Ai1wmke_GDrive_Export_Incremental_Backups::execute', 270 );

				add_filter( 'ai1wm_import', 'Ai1wmke_GDrive_Import_Incremental_Prepare::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_GDrive_Import_Incremental_Download::execute', 30 );

				remove_filter( 'ai1wm_export', 'Ai1wmke_GDrive_Export_Retention::execute', 280 );
				remove_filter( 'ai1wm_import', 'Ai1wmke_GDrive_Import_Create::execute', 20 );
				remove_filter( 'ai1wm_import', 'Ai1wmke_GDrive_Import_Download::execute', 30 );
			}
		}

		if ( ai1wmke_is_enabled( 'mega' ) ) {
			if ( ai1wmke_is_incremental( 'mega' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_Mega_Export_Incremental_Content::execute', 105 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Mega_Export_Incremental_Media::execute', 115 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Mega_Export_Incremental_Plugins::execute', 125 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Mega_Export_Incremental_Themes::execute', 135 );
				add_filter( 'ai1wm_export', 'Ai1wmke_Mega_Export_Incremental_Backups::execute', 270 );

				add_filter( 'ai1wm_import', 'Ai1wmke_Mega_Import_Incremental_Prepare::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_Mega_Import_Incremental_Download::execute', 30 );

				remove_filter( 'ai1wm_export', 'Ai1wmke_Mega_Export_Retention::execute', 280 );
				remove_filter( 'ai1wm_import', 'Ai1wmke_Mega_Import_Create::execute', 20 );
				remove_filter( 'ai1wm_import', 'Ai1wmke_Mega_Import_Download::execute', 30 );
			}
		}

		if ( ai1wmke_is_enabled( 'onedrive' ) ) {
			if ( ai1wmke_is_incremental( 'onedrive' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_OneDrive_Export_Incremental_Content::execute', 105 );
				add_filter( 'ai1wm_export', 'Ai1wmke_OneDrive_Export_Incremental_Media::execute', 115 );
				add_filter( 'ai1wm_export', 'Ai1wmke_OneDrive_Export_Incremental_Plugins::execute', 125 );
				add_filter( 'ai1wm_export', 'Ai1wmke_OneDrive_Export_Incremental_Themes::execute', 135 );
				add_filter( 'ai1wm_export', 'Ai1wmke_OneDrive_Export_Incremental_Backups::execute', 270 );

				add_filter( 'ai1wm_import', 'Ai1wmke_OneDrive_Import_Incremental_Prepare::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_OneDrive_Import_Incremental_Download::execute', 30 );

				remove_filter( 'ai1wm_export', 'Ai1wmke_OneDrive_Export_Retention::execute', 280 );
				remove_filter( 'ai1wm_import', 'Ai1wmke_OneDrive_Import_Create::execute', 20 );
				remove_filter( 'ai1wm_import', 'Ai1wmke_OneDrive_Import_Download::execute', 30 );
			}
		}

		if ( ai1wmke_is_enabled( 'pcloud' ) ) {
			if ( ai1wmke_is_incremental( 'pcloud' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_PCloud_Export_Incremental_Content::execute', 105 );
				add_filter( 'ai1wm_export', 'Ai1wmke_PCloud_Export_Incremental_Media::execute', 115 );
				add_filter( 'ai1wm_export', 'Ai1wmke_PCloud_Export_Incremental_Plugins::execute', 125 );
				add_filter( 'ai1wm_export', 'Ai1wmke_PCloud_Export_Incremental_Themes::execute', 135 );
				add_filter( 'ai1wm_export', 'Ai1wmke_PCloud_Export_Incremental_Backups::execute', 270 );

				add_filter( 'ai1wm_import', 'Ai1wmke_PCloud_Import_Incremental_Prepare::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_PCloud_Import_Incremental_Download::execute', 30 );

				remove_filter( 'ai1wm_export', 'Ai1wmke_PCloud_Export_Retention::execute', 280 );
				remove_filter( 'ai1wm_import', 'Ai1wmke_PCloud_Import_Create::execute', 20 );
				remove_filter( 'ai1wm_import', 'Ai1wmke_PCloud_Import_Download::execute', 30 );
			}
		}

		if ( ai1wmke_is_enabled( 's3-client' ) ) {
			if ( ai1wmke_is_incremental( 's3-client' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_S3_Client_Export_Incremental_Content::execute', 105 );
				add_filter( 'ai1wm_export', 'Ai1wmke_S3_Client_Export_Incremental_Media::execute', 115 );
				add_filter( 'ai1wm_export', 'Ai1wmke_S3_Client_Export_Incremental_Plugins::execute', 125 );
				add_filter( 'ai1wm_export', 'Ai1wmke_S3_Client_Export_Incremental_Themes::execute', 135 );
				add_filter( 'ai1wm_export', 'Ai1wmke_S3_Client_Export_Incremental_Backups::execute', 270 );

				add_filter( 'ai1wm_import', 'Ai1wmke_S3_Client_Import_Incremental_Prepare::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_S3_Client_Import_Incremental_Download::execute', 30 );

				remove_filter( 'ai1wm_export', 'Ai1wmke_S3_Client_Export_Retention::execute', 280 );
				remove_filter( 'ai1wm_import', 'Ai1wmke_S3_Client_Import_Create::execute', 20 );
				remove_filter( 'ai1wm_import', 'Ai1wmke_S3_Client_Import_Download::execute', 30 );
			}
		}

		if ( ai1wmke_is_enabled( 's3' ) ) {
			if ( ai1wmke_is_incremental( 's3' ) ) {
				add_filter( 'ai1wm_export', 'Ai1wmke_S3_Export_Incremental_Content::execute', 105 );
				add_filter( 'ai1wm_export', 'Ai1wmke_S3_Export_Incremental_Media::execute', 115 );
				add_filter( 'ai1wm_export', 'Ai1wmke_S3_Export_Incremental_Plugins::execute', 125 );
				add_filter( 'ai1wm_export', 'Ai1wmke_S3_Export_Incremental_Themes::execute', 135 );
				add_filter( 'ai1wm_export', 'Ai1wmke_S3_Export_Incremental_Backups::execute', 270 );

				add_filter( 'ai1wm_import', 'Ai1wmke_S3_Import_Incremental_Prepare::execute', 20 );
				add_filter( 'ai1wm_import', 'Ai1wmke_S3_Import_Incremental_Download::execute', 30 );

				remove_filter( 'ai1wm_export', 'Ai1wmke_S3_Export_Retention::execute', 280 );
				remove_filter( 'ai1wm_import', 'Ai1wmke_S3_Import_Create::execute', 20 );
				remove_filter( 'ai1wm_import', 'Ai1wmke_S3_Import_Download::execute', 30 );
			}
		}
	}

	/**
	 * Load reset commands
	 *
	 * @return void
	 */
	public function load_reset_commands() {
		add_filter( 'ai1wm_reset', 'Ai1wmke_Reset_Init::execute', 10 );
		add_filter( 'ai1wm_reset', 'Ai1wmke_Reset_Plugins::execute', 50 );
		add_filter( 'ai1wm_reset', 'Ai1wmke_Reset_Themes::execute', 100 );
		add_filter( 'ai1wm_reset', 'Ai1wmke_Reset_Media::execute', 150 );
		add_filter( 'ai1wm_reset', 'Ai1wmke_Reset_Database::execute', 200 );
	}

	/**
	 * Load status events
	 *
	 * @return void
	 */
	public function load_status_events() {
		if ( ai1wmke_is_running( 'file' ) ) {
			add_action( 'ai1wm_status_export_done', 'Ai1wmke_Reset_Tools_Controller::reset_labels' );
		}

		add_action( 'ai1wm_status_export_done', 'Ai1wmke_Report_Controller::on_export' );
		add_action( 'ai1wm_status_import_done', 'Ai1wmke_Report_Controller::on_import' );
		add_action( 'ai1wm_status_import_done', 'Ai1wmke_Report_Controller::on_restore' );

		add_action( 'ai1wm_status_export_done', 'Ai1wmke_Schedule_Events_Controller::log_success' );
		add_action( 'ai1wm_status_export_init', 'Ai1wmke_Schedule_Events_Controller::log_running' );
		add_action( 'ai1wm_status_export_error', 'Ai1wmke_Schedule_Events_Controller::log_failed', 10, 2 );
	}

	/**
	 * Enable notifications
	 *
	 * @return void
	 */
	public function load_plugin_notifications() {
		if ( ai1wmke_is_enabled( 'azure-storage' ) ) {
			if ( ai1wmke_is_running( 'azure-storage' ) ) {
				add_filter( 'ai1wm_notification_ok_toggle', 'Ai1wmke_Azure_Storage_Settings_Controller::notify_ok_toggle' );
				add_filter( 'ai1wm_notification_ok_email', 'Ai1wmke_Azure_Storage_Settings_Controller::notify_email' );
				add_filter( 'ai1wm_notification_error_toggle', 'Ai1wmke_Azure_Storage_Settings_Controller::notify_error_toggle' );
				add_filter( 'ai1wm_notification_error_subject', 'Ai1wmke_Azure_Storage_Settings_Controller::notify_error_subject' );
				add_filter( 'ai1wm_notification_error_email', 'Ai1wmke_Azure_Storage_Settings_Controller::notify_email' );
			}
		}

		if ( ai1wmke_is_enabled( 'b2' ) ) {
			if ( ai1wmke_is_running( 'b2' ) ) {
				add_filter( 'ai1wm_notification_ok_toggle', 'Ai1wmke_B2_Settings_Controller::notify_ok_toggle' );
				add_filter( 'ai1wm_notification_ok_email', 'Ai1wmke_B2_Settings_Controller::notify_email' );
				add_filter( 'ai1wm_notification_error_toggle', 'Ai1wmke_B2_Settings_Controller::notify_error_toggle' );
				add_filter( 'ai1wm_notification_error_subject', 'Ai1wmke_B2_Settings_Controller::notify_error_subject' );
				add_filter( 'ai1wm_notification_error_email', 'Ai1wmke_B2_Settings_Controller::notify_email' );
			}
		}

		if ( ai1wmke_is_enabled( 'box' ) ) {
			if ( ai1wmke_is_running( 'box' ) ) {
				add_filter( 'ai1wm_notification_ok_toggle', 'Ai1wmke_Box_Settings_Controller::notify_ok_toggle' );
				add_filter( 'ai1wm_notification_ok_email', 'Ai1wmke_Box_Settings_Controller::notify_email' );
				add_filter( 'ai1wm_notification_error_toggle', 'Ai1wmke_Box_Settings_Controller::notify_error_toggle' );
				add_filter( 'ai1wm_notification_error_subject', 'Ai1wmke_Box_Settings_Controller::notify_error_subject' );
				add_filter( 'ai1wm_notification_error_email', 'Ai1wmke_Box_Settings_Controller::notify_email' );
			}
		}

		if ( ai1wmke_is_enabled( 'digitalocean' ) ) {
			if ( ai1wmke_is_running( 'digitalocean' ) ) {
				add_filter( 'ai1wm_notification_ok_toggle', 'Ai1wmke_DigitalOcean_Settings_Controller::notify_ok_toggle' );
				add_filter( 'ai1wm_notification_ok_email', 'Ai1wmke_DigitalOcean_Settings_Controller::notify_email' );
				add_filter( 'ai1wm_notification_error_toggle', 'Ai1wmke_DigitalOcean_Settings_Controller::notify_error_toggle' );
				add_filter( 'ai1wm_notification_error_subject', 'Ai1wmke_DigitalOcean_Settings_Controller::notify_error_subject' );
				add_filter( 'ai1wm_notification_error_email', 'Ai1wmke_DigitalOcean_Settings_Controller::notify_email' );
			}
		}

		if ( ai1wmke_is_enabled( 'dropbox' ) ) {
			if ( ai1wmke_is_running( 'dropbox' ) ) {
				add_filter( 'ai1wm_notification_ok_toggle', 'Ai1wmke_Dropbox_Settings_Controller::notify_ok_toggle' );
				add_filter( 'ai1wm_notification_ok_email', 'Ai1wmke_Dropbox_Settings_Controller::notify_email' );
				add_filter( 'ai1wm_notification_error_toggle', 'Ai1wmke_Dropbox_Settings_Controller::notify_error_toggle' );
				add_filter( 'ai1wm_notification_error_subject', 'Ai1wmke_Dropbox_Settings_Controller::notify_error_subject' );
				add_filter( 'ai1wm_notification_error_email', 'Ai1wmke_Dropbox_Settings_Controller::notify_email' );
			}
		}

		if ( ai1wmke_is_enabled( 'ftp' ) ) {
			if ( ai1wmke_is_running( 'ftp' ) ) {
				add_filter( 'ai1wm_notification_ok_toggle', 'Ai1wmke_FTP_Settings_Controller::notify_ok_toggle' );
				add_filter( 'ai1wm_notification_ok_email', 'Ai1wmke_FTP_Settings_Controller::notify_email' );
				add_filter( 'ai1wm_notification_error_toggle', 'Ai1wmke_FTP_Settings_Controller::notify_error_toggle' );
				add_filter( 'ai1wm_notification_error_subject', 'Ai1wmke_FTP_Settings_Controller::notify_error_subject' );
				add_filter( 'ai1wm_notification_error_email', 'Ai1wmke_FTP_Settings_Controller::notify_email' );
			}
		}

		if ( ai1wmke_is_enabled( 'gcloud-storage' ) ) {
			if ( ai1wmke_is_running( 'gcloud-storage' ) ) {
				add_filter( 'ai1wm_notification_ok_toggle', 'Ai1wmke_GCloud_Storage_Settings_Controller::notify_ok_toggle' );
				add_filter( 'ai1wm_notification_ok_email', 'Ai1wmke_GCloud_Storage_Settings_Controller::notify_email' );
				add_filter( 'ai1wm_notification_error_toggle', 'Ai1wmke_GCloud_Storage_Settings_Controller::notify_error_toggle' );
				add_filter( 'ai1wm_notification_error_subject', 'Ai1wmke_GCloud_Storage_Settings_Controller::notify_error_subject' );
				add_filter( 'ai1wm_notification_error_email', 'Ai1wmke_GCloud_Storage_Settings_Controller::notify_email' );
			}
		}

		if ( ai1wmke_is_enabled( 'gdrive' ) ) {
			if ( ai1wmke_is_running( 'gdrive' ) ) {
				add_filter( 'ai1wm_notification_ok_toggle', 'Ai1wmke_GDrive_Settings_Controller::notify_ok_toggle' );
				add_filter( 'ai1wm_notification_ok_email', 'Ai1wmke_GDrive_Settings_Controller::notify_email' );
				add_filter( 'ai1wm_notification_error_toggle', 'Ai1wmke_GDrive_Settings_Controller::notify_error_toggle' );
				add_filter( 'ai1wm_notification_error_subject', 'Ai1wmke_GDrive_Settings_Controller::notify_error_subject' );
				add_filter( 'ai1wm_notification_error_email', 'Ai1wmke_GDrive_Settings_Controller::notify_email' );
			}
		}

		if ( ai1wmke_is_enabled( 'glacier' ) ) {
			if ( ai1wmke_is_running( 'glacier' ) ) {
				add_filter( 'ai1wm_notification_ok_toggle', 'Ai1wmke_Glacier_Settings_Controller::notify_ok_toggle' );
				add_filter( 'ai1wm_notification_ok_email', 'Ai1wmke_Glacier_Settings_Controller::notify_email' );
				add_filter( 'ai1wm_notification_error_toggle', 'Ai1wmke_Glacier_Settings_Controller::notify_error_toggle' );
				add_filter( 'ai1wm_notification_error_subject', 'Ai1wmke_Glacier_Settings_Controller::notify_error_subject' );
				add_filter( 'ai1wm_notification_error_email', 'Ai1wmke_Glacier_Settings_Controller::notify_email' );
			}
		}

		if ( ai1wmke_is_enabled( 'mega' ) ) {
			if ( ai1wmke_is_running( 'mega' ) ) {
				add_filter( 'ai1wm_notification_ok_toggle', 'Ai1wmke_Mega_Settings_Controller::notify_ok_toggle' );
				add_filter( 'ai1wm_notification_ok_email', 'Ai1wmke_Mega_Settings_Controller::notify_email' );
				add_filter( 'ai1wm_notification_error_toggle', 'Ai1wmke_Mega_Settings_Controller::notify_error_toggle' );
				add_filter( 'ai1wm_notification_error_subject', 'Ai1wmke_Mega_Settings_Controller::notify_error_subject' );
				add_filter( 'ai1wm_notification_error_email', 'Ai1wmke_Mega_Settings_Controller::notify_email' );
			}
		}

		if ( ai1wmke_is_enabled( 'onedrive' ) ) {
			if ( ai1wmke_is_running( 'onedrive' ) ) {
				add_filter( 'ai1wm_notification_ok_toggle', 'Ai1wmke_OneDrive_Settings_Controller::notify_ok_toggle' );
				add_filter( 'ai1wm_notification_ok_email', 'Ai1wmke_OneDrive_Settings_Controller::notify_email' );
				add_filter( 'ai1wm_notification_error_toggle', 'Ai1wmke_OneDrive_Settings_Controller::notify_error_toggle' );
				add_filter( 'ai1wm_notification_error_subject', 'Ai1wmke_OneDrive_Settings_Controller::notify_error_subject' );
				add_filter( 'ai1wm_notification_error_email', 'Ai1wmke_OneDrive_Settings_Controller::notify_email' );
			}
		}

		if ( ai1wmke_is_enabled( 'pcloud' ) ) {
			if ( ai1wmke_is_running( 'pcloud' ) ) {
				add_filter( 'ai1wm_notification_ok_toggle', 'Ai1wmke_PCloud_Settings_Controller::notify_ok_toggle' );
				add_filter( 'ai1wm_notification_ok_email', 'Ai1wmke_PCloud_Settings_Controller::notify_email' );
				add_filter( 'ai1wm_notification_error_toggle', 'Ai1wmke_PCloud_Settings_Controller::notify_error_toggle' );
				add_filter( 'ai1wm_notification_error_subject', 'Ai1wmke_PCloud_Settings_Controller::notify_error_subject' );
				add_filter( 'ai1wm_notification_error_email', 'Ai1wmke_PCloud_Settings_Controller::notify_email' );
			}
		}

		if ( ai1wmke_is_enabled( 's3-client' ) ) {
			if ( ai1wmke_is_running( 's3-client' ) ) {
				add_filter( 'ai1wm_notification_ok_toggle', 'Ai1wmke_S3_Client_Settings_Controller::notify_ok_toggle' );
				add_filter( 'ai1wm_notification_ok_email', 'Ai1wmke_S3_Client_Settings_Controller::notify_email' );
				add_filter( 'ai1wm_notification_error_toggle', 'Ai1wmke_S3_Client_Settings_Controller::notify_error_toggle' );
				add_filter( 'ai1wm_notification_error_subject', 'Ai1wmke_S3_Client_Settings_Controller::notify_error_subject' );
				add_filter( 'ai1wm_notification_error_email', 'Ai1wmke_S3_Client_Settings_Controller::notify_email' );
			}
		}

		if ( ai1wmke_is_enabled( 's3' ) ) {
			if ( ai1wmke_is_running( 's3' ) ) {
				add_filter( 'ai1wm_notification_ok_toggle', 'Ai1wmke_S3_Settings_Controller::notify_ok_toggle' );
				add_filter( 'ai1wm_notification_ok_email', 'Ai1wmke_S3_Settings_Controller::notify_email' );
				add_filter( 'ai1wm_notification_error_toggle', 'Ai1wmke_S3_Settings_Controller::notify_error_toggle' );
				add_filter( 'ai1wm_notification_error_subject', 'Ai1wmke_S3_Settings_Controller::notify_error_subject' );
				add_filter( 'ai1wm_notification_error_email', 'Ai1wmke_S3_Settings_Controller::notify_email' );
			}
		}

		if ( ai1wmke_is_enabled( 'webdav' ) ) {
			if ( ai1wmke_is_running( 'webdav' ) ) {
				add_filter( 'ai1wm_notification_ok_toggle', 'Ai1wmke_WebDAV_Settings_Controller::notify_ok_toggle' );
				add_filter( 'ai1wm_notification_ok_email', 'Ai1wmke_WebDAV_Settings_Controller::notify_email' );
				add_filter( 'ai1wm_notification_error_toggle', 'Ai1wmke_WebDAV_Settings_Controller::notify_error_toggle' );
				add_filter( 'ai1wm_notification_error_subject', 'Ai1wmke_WebDAV_Settings_Controller::notify_error_subject' );
				add_filter( 'ai1wm_notification_error_email', 'Ai1wmke_WebDAV_Settings_Controller::notify_email' );
			}
		}
	}

	/**
	 * Update schedule details
	 *
	 * @return void
	 */
	public function admin_post_schedules() {
		add_action( 'admin_post_ai1wmke_schedule_event_save', 'Ai1wmke_Schedule_Events_Controller::save' );
	}

	/**
	 * Enable or disable extension items
	 *
	 * @return void
	 */
	public function admin_post_extensions() {
		add_action( 'admin_post_ai1wmke_extensions', 'Ai1wmke_Extension_Items_Controller::update' );
	}

	/**
	 * Update connection details
	 *
	 * @return void
	 */
	public function admin_post_connections() {
		if ( ai1wmke_is_enabled( 'azure-storage' ) ) {
			if ( ai1wmke_is_admin( 'azure-storage' ) ) {
				add_action( 'admin_post_ai1wmke_azure_storage_connection', 'Ai1wmke_Azure_Storage_Settings_Controller::connection' );
			}
		}

		if ( ai1wmke_is_enabled( 'b2' ) ) {
			if ( ai1wmke_is_admin( 'b2' ) ) {
				add_action( 'admin_post_ai1wmke_b2_connection', 'Ai1wmke_B2_Settings_Controller::connection' );
			}
		}

		if ( ai1wmke_is_enabled( 'digitalocean' ) ) {
			if ( ai1wmke_is_admin( 'digitalocean' ) ) {
				add_action( 'admin_post_ai1wmke_digitalocean_connection', 'Ai1wmke_DigitalOcean_Settings_Controller::connection' );
			}
		}

		if ( ai1wmke_is_enabled( 'ftp' ) ) {
			if ( ai1wmke_is_admin( 'ftp' ) ) {
				add_action( 'admin_post_ai1wmke_ftp_connection', 'Ai1wmke_FTP_Settings_Controller::connection' );
			}
		}

		if ( ai1wmke_is_enabled( 'glacier' ) ) {
			if ( ai1wmke_is_admin( 'glacier' ) ) {
				add_action( 'admin_post_ai1wmke_glacier_connection', 'Ai1wmke_Glacier_Settings_Controller::connection' );
			}
		}

		if ( ai1wmke_is_enabled( 'mega' ) ) {
			if ( ai1wmke_is_admin( 'mega' ) ) {
				add_action( 'admin_post_ai1wmke_mega_connection', 'Ai1wmke_Mega_Settings_Controller::connection' );
			}
		}

		if ( ai1wmke_is_enabled( 's3-client' ) ) {
			if ( ai1wmke_is_admin( 's3-client' ) ) {
				add_action( 'admin_post_ai1wmke_s3_client_connection', 'Ai1wmke_S3_Client_Settings_Controller::connection' );
			}
		}

		if ( ai1wmke_is_enabled( 's3' ) ) {
			if ( ai1wmke_is_admin( 's3' ) ) {
				add_action( 'admin_post_ai1wmke_s3_connection', 'Ai1wmke_S3_Settings_Controller::connection' );
			}
		}

		if ( ai1wmke_is_enabled( 'webdav' ) ) {
			if ( ai1wmke_is_admin( 'webdav' ) ) {
				add_action( 'admin_post_ai1wmke_webdav_connection', 'Ai1wmke_WebDAV_Settings_Controller::connection' );
			}
		}
	}

	/**
	 * Update settings details
	 *
	 * @return void
	 */
	public function admin_post_settings() {
		if ( ai1wmke_is_enabled( 'azure-storage' ) ) {
			if ( ai1wmke_is_admin( 'azure-storage' ) ) {
				add_action( 'admin_post_ai1wmke_azure_storage_settings', 'Ai1wmke_Azure_Storage_Settings_Controller::settings' );
			}
		}

		if ( ai1wmke_is_enabled( 'b2' ) ) {
			if ( ai1wmke_is_admin( 'b2' ) ) {
				add_action( 'admin_post_ai1wmke_b2_settings', 'Ai1wmke_B2_Settings_Controller::settings' );
			}
		}

		if ( ai1wmke_is_enabled( 'box' ) ) {
			if ( ai1wmke_is_admin( 'box' ) ) {
				add_action( 'admin_post_ai1wmke_box_settings', 'Ai1wmke_Box_Settings_Controller::settings' );
			}
		}

		if ( ai1wmke_is_enabled( 'digitalocean' ) ) {
			if ( ai1wmke_is_admin( 'digitalocean' ) ) {
				add_action( 'admin_post_ai1wmke_digitalocean_settings', 'Ai1wmke_DigitalOcean_Settings_Controller::settings' );
			}
		}

		if ( ai1wmke_is_enabled( 'dropbox' ) ) {
			if ( ai1wmke_is_admin( 'dropbox' ) ) {
				add_action( 'admin_post_ai1wmke_dropbox_settings', 'Ai1wmke_Dropbox_Settings_Controller::settings' );
			}
		}

		if ( ai1wmke_is_enabled( 'ftp' ) ) {
			if ( ai1wmke_is_admin( 'ftp' ) ) {
				add_action( 'admin_post_ai1wmke_ftp_settings', 'Ai1wmke_FTP_Settings_Controller::settings' );
			}
		}

		if ( ai1wmke_is_enabled( 'gcloud-storage' ) ) {
			if ( ai1wmke_is_admin( 'gcloud-storage' ) ) {
				add_action( 'admin_post_ai1wmke_gcloud_storage_settings', 'Ai1wmke_GCloud_Storage_Settings_Controller::settings' );
			}
		}

		if ( ai1wmke_is_enabled( 'gdrive' ) ) {
			if ( ai1wmke_is_admin( 'gdrive' ) ) {
				add_action( 'admin_post_ai1wmke_gdrive_settings', 'Ai1wmke_GDrive_Settings_Controller::settings' );
			}
		}

		if ( ai1wmke_is_enabled( 'glacier' ) ) {
			if ( ai1wmke_is_admin( 'glacier' ) ) {
				add_action( 'admin_post_ai1wmke_glacier_settings', 'Ai1wmke_Glacier_Settings_Controller::settings' );
			}
		}

		if ( ai1wmke_is_enabled( 'mega' ) ) {
			if ( ai1wmke_is_admin( 'mega' ) ) {
				add_action( 'admin_post_ai1wmke_mega_settings', 'Ai1wmke_Mega_Settings_Controller::settings' );
			}
		}

		if ( ai1wmke_is_enabled( 'onedrive' ) ) {
			if ( ai1wmke_is_admin( 'onedrive' ) ) {
				add_action( 'admin_post_ai1wmke_onedrive_settings', 'Ai1wmke_OneDrive_Settings_Controller::settings' );
			}
		}

		if ( ai1wmke_is_enabled( 'pcloud' ) ) {
			if ( ai1wmke_is_admin( 'pcloud' ) ) {
				add_action( 'admin_post_ai1wmke_pcloud_settings', 'Ai1wmke_PCloud_Settings_Controller::settings' );
			}
		}

		add_action( 'admin_post_ai1wmke_pro_settings', 'Ai1wmke_Pro_Settings_Controller::settings' );
		add_action( 'admin_post_ai1wmke_s3_client_settings', 'Ai1wmke_S3_Client_Settings_Controller::settings' );
		add_action( 'admin_post_ai1wmke_s3_settings', 'Ai1wmke_S3_Settings_Controller::settings' );
		add_action( 'admin_post_ai1wmke_url_settings', 'Ai1wmke_URL_Settings_Controller::settings' );
		add_action( 'admin_post_ai1wmke_webdav_settings', 'Ai1wmke_WebDAV_Settings_Controller::settings' );
	}

	/**
	 * Revoke user credentials
	 *
	 * @return void
	 */
	public function admin_post_revokes() {
		if ( ai1wmke_is_enabled( 'box' ) ) {
			if ( ai1wmke_is_admin( 'box' ) ) {
				add_action( 'admin_post_ai1wmke_box_revoke', 'Ai1wmke_Box_Settings_Controller::revoke' );
			}
		}

		if ( ai1wmke_is_enabled( 'dropbox' ) ) {
			if ( ai1wmke_is_admin( 'dropbox' ) ) {
				add_action( 'admin_post_ai1wmke_dropbox_revoke', 'Ai1wmke_Dropbox_Settings_Controller::revoke' );
			}
		}

		if ( ai1wmke_is_enabled( 'gcloud-storage' ) ) {
			if ( ai1wmke_is_admin( 'gcloud-storage' ) ) {
				add_action( 'admin_post_ai1wmke_gcloud_storage_revoke', 'Ai1wmke_GCloud_Storage_Settings_Controller::revoke' );
			}
		}

		if ( ai1wmke_is_enabled( 'gdrive' ) ) {
			if ( ai1wmke_is_admin( 'gdrive' ) ) {
				add_action( 'admin_post_ai1wmke_gdrive_revoke', 'Ai1wmke_GDrive_Settings_Controller::revoke' );
			}
		}

		if ( ai1wmke_is_enabled( 'mega' ) ) {
			if ( ai1wmke_is_admin( 'mega' ) ) {
				add_action( 'admin_post_ai1wmke_mega_revoke', 'Ai1wmke_Mega_Settings_Controller::revoke' );
			}
		}

		if ( ai1wmke_is_enabled( 'onedrive' ) ) {
			if ( ai1wmke_is_admin( 'onedrive' ) ) {
				add_action( 'admin_post_ai1wmke_onedrive_revoke', 'Ai1wmke_OneDrive_Settings_Controller::revoke' );
			}
		}

		if ( ai1wmke_is_enabled( 'pcloud' ) ) {
			if ( ai1wmke_is_admin( 'pcloud' ) ) {
				add_action( 'admin_post_ai1wmke_pcloud_revoke', 'Ai1wmke_PCloud_Settings_Controller::revoke' );
			}
		}
	}

	/**
	 * Picker templates for ajax actions
	 *
	 * @return void
	 */
	public function admin_post_pickers() {
		if ( ai1wmke_is_enabled( 'azure-storage' ) ) {
			if ( ai1wmke_is_admin( 'azure-storage' ) ) {
				add_action( 'ai1wmke_azure_storage_settings_left_end', 'Ai1wmke_Azure_Storage_Settings_Controller::picker' );
			}
		}

		if ( ai1wmke_is_enabled( 'box' ) ) {
			if ( ai1wmke_is_admin( 'box' ) ) {
				add_action( 'ai1wmke_box_settings_left_end', 'Ai1wmke_Box_Settings_Controller::picker' );
			}
		}

		if ( ai1wmke_is_enabled( 'dropbox' ) ) {
			if ( ai1wmke_is_admin( 'dropbox' ) ) {
				add_action( 'ai1wmke_dropbox_settings_left_end', 'Ai1wmke_Dropbox_Settings_Controller::picker' );
			}
		}

		if ( ai1wmke_is_enabled( 'gcloud-storage' ) ) {
			if ( ai1wmke_is_admin( 'gcloud-storage' ) ) {
				add_action( 'ai1wmke_gcloud_storage_settings_left_end', 'Ai1wmke_GCloud_Storage_Settings_Controller::picker' );
			}
		}

		if ( ai1wmke_is_enabled( 'gdrive' ) ) {
			if ( ai1wmke_is_admin( 'gdrive' ) ) {
				add_action( 'ai1wmke_gdrive_settings_left_end', 'Ai1wmke_GDrive_Settings_Controller::picker' );
			}
		}

		if ( ai1wmke_is_enabled( 'mega' ) ) {
			if ( ai1wmke_is_admin( 'mega' ) ) {
				add_action( 'ai1wmke_mega_settings_left_end', 'Ai1wmke_Mega_Settings_Controller::picker' );
			}
		}

		if ( ai1wmke_is_enabled( 'onedrive' ) ) {
			if ( ai1wmke_is_admin( 'onedrive' ) ) {
				add_action( 'ai1wmke_onedrive_settings_left_end', 'Ai1wmke_OneDrive_Settings_Controller::picker' );
			}
		}

		if ( ai1wmke_is_enabled( 'pcloud' ) ) {
			if ( ai1wmke_is_admin( 'pcloud' ) ) {
				add_action( 'ai1wmke_pcloud_settings_left_end', 'Ai1wmke_PCloud_Settings_Controller::picker' );
			}
		}

		add_action( 'ai1wm_import_left_end', 'Ai1wmke_Azure_Storage_Import_Controller::picker' );
		add_action( 'ai1wm_import_left_end', 'Ai1wmke_B2_Import_Controller::picker' );
		add_action( 'ai1wm_import_left_end', 'Ai1wmke_Box_Import_Controller::picker' );
		add_action( 'ai1wm_import_left_end', 'Ai1wmke_DigitalOcean_Import_Controller::picker' );
		add_action( 'ai1wm_import_left_end', 'Ai1wmke_Dropbox_Import_Controller::picker' );
		add_action( 'ai1wm_import_left_end', 'Ai1wmke_FTP_Import_Controller::picker' );
		add_action( 'ai1wm_import_left_end', 'Ai1wmke_GCloud_Storage_Import_Controller::picker' );
		add_action( 'ai1wm_import_left_end', 'Ai1wmke_GDrive_Import_Controller::picker' );
		add_action( 'ai1wm_import_left_end', 'Ai1wmke_Glacier_Import_Controller::picker' );
		add_action( 'ai1wm_import_left_end', 'Ai1wmke_Mega_Import_Controller::picker' );
		add_action( 'ai1wm_import_left_end', 'Ai1wmke_OneDrive_Import_Controller::picker' );
		add_action( 'ai1wm_import_left_end', 'Ai1wmke_PCloud_Import_Controller::picker' );
		add_action( 'ai1wm_import_left_end', 'Ai1wmke_S3_Client_Import_Controller::picker' );
		add_action( 'ai1wm_import_left_end', 'Ai1wmke_S3_Import_Controller::picker' );
		add_action( 'ai1wm_import_left_end', 'Ai1wmke_URL_Import_Controller::picker' );
		add_action( 'ai1wm_import_left_end', 'Ai1wmke_WebDAV_Import_Controller::picker' );
	}

	/**
	 * Enable advanced options
	 *
	 * @return void
	 */
	public function load_advanced_options() {
		// Add export compression types
		if ( ! has_action( 'ai1wm_export_compression_types' ) ) {
			add_action( 'ai1wm_export_compression_types', 'Ai1wmke_Advanced_Options_Controller::compression_types' );
		}

		// Add export inactive themes
		if ( ! has_action( 'ai1wm_export_inactive_themes' ) ) {
			add_action( 'ai1wm_export_inactive_themes', 'Ai1wmke_Advanced_Options_Controller::inactive_themes' );
		}

		// Add export inactive plugins
		if ( ! has_action( 'ai1wm_export_inactive_plugins' ) ) {
			add_action( 'ai1wm_export_inactive_plugins', 'Ai1wmke_Advanced_Options_Controller::inactive_plugins' );
		}

		// Add export cache files
		if ( ! has_action( 'ai1wm_export_cache_files' ) ) {
			add_action( 'ai1wm_export_cache_files', 'Ai1wmke_Advanced_Options_Controller::cache_files' );
		}

		// Add export exclude files
		if ( ! has_action( 'ai1wm_export_advanced_settings' ) ) {
			add_action( 'ai1wm_export_advanced_settings', 'Ai1wmke_Advanced_Options_Controller::exclude_files' );
		}

		// Add export exclude db tables
		if ( ! has_action( 'ai1wm_export_exclude_db_tables' ) ) {
			add_action( 'ai1wm_export_exclude_db_tables', 'Ai1wmke_Advanced_Options_Controller::exclude_db_tables' );
		}

		// Add export include db tables
		if ( ! has_action( 'ai1wm_export_include_db_tables' ) ) {
			add_action( 'ai1wm_export_include_db_tables', 'Ai1wmke_Advanced_Options_Controller::include_db_tables' );
		}
	}

	/**
	 * Minimum version compatibility check
	 *
	 * @return array
	 */
	public function load_compatibility_check( $params ) {
		if ( AI1WM_VERSION !== 'develop' ) {
			if ( version_compare( AI1WM_VERSION, AI1WMKE_MIN_AI1WM_VERSION, '<' ) ) {
				if ( defined( 'WP_CLI' ) ) {
					$message = __( 'All-in-One WP Migration is not the latest version. You must update the plugin before you can use it. ', AI1WMKE_PLUGIN_NAME );
				} else {
					$message = sprintf( __( '<strong>All-in-One WP Migration</strong> is not the latest version. <br />You must <a href="%s">update the plugin</a> before you can use it. <br />', AI1WMKE_PLUGIN_NAME ), network_admin_url( 'plugins.php' ) );
				}

				throw new Ai1wm_Compatibility_Exception( $message );
			}
		}

		return $params;
	}

	/**
	 * Add links to plugin list page
	 *
	 * @return array
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( $file === AI1WMKE_PLUGIN_BASENAME ) {
			$links[] = __( '<a href="https://help.servmask.com/knowledgebase/all-in-one-wp-migration-pro-extension-user-guide/" target="_blank">User Guide</a>', AI1WMKE_PLUGIN_NAME );
			$links[] = __( '<a href="https://servmask.com/contact-support" target="_blank">Contact Support</a>', AI1WMKE_PLUGIN_NAME );
		}

		return $links;
	}

	/**
	 * Add pro text to plugin page
	 *
	 * @return string
	 */
	public function plugin_pro_text() {
		return Ai1wm_Template::get_content( 'main/pro', array(), AI1WMKE_TEMPLATES_PATH );
	}

	/**
	 * Register listeners for actions
	 *
	 * @return void
	 */
	protected function activate_actions() {
		add_action( 'admin_head', array( $this, 'admin_head' ), 100 );

		add_action( 'cli_init', array( $this, 'cli_init' ), 100 );
		add_action( 'admin_init', array( $this, 'load_wp_ajax' ), 100 );
		add_action( 'admin_init', array( $this, 'load_plugin_buttons' ), 100 );
		add_action( 'admin_init', array( $this, 'load_plugin_textdomain' ), 100 );
		add_action( 'admin_init', array( $this, 'load_schedule_buttons' ), 100 );
		add_action( 'admin_init', array( $this, 'load_advanced_options' ), 100 );

		add_action( 'admin_init', array( $this, 'admin_post_schedules' ), 100 );
		add_action( 'admin_init', array( $this, 'admin_post_extensions' ), 100 );
		add_action( 'admin_init', array( $this, 'admin_post_connections' ), 100 );
		add_action( 'admin_init', array( $this, 'admin_post_settings' ), 100 );
		add_action( 'admin_init', array( $this, 'admin_post_revokes' ), 100 );
		add_action( 'admin_init', array( $this, 'admin_post_pickers' ), 100 );

		add_action( 'plugins_loaded', array( $this, 'load_admin_menu' ), 100 );
		add_action( 'plugins_loaded', array( $this, 'load_plugin_notifications' ), 100 );
		add_action( 'plugins_loaded', array( $this, 'load_plugin_commands' ), 100 );
		add_action( 'plugins_loaded', array( $this, 'load_incremental_commands' ), 100 );
		add_action( 'plugins_loaded', array( $this, 'load_reset_commands' ), 100 );
		add_action( 'plugins_loaded', array( $this, 'load_status_events' ), 100 );
		add_action( 'plugins_loaded', array( $this, 'load_cron_hooks' ), 100 );

		if ( defined( 'WP_CLI' ) ) {
			WP_CLI::add_hook( 'ai1wm_cli_notifications', array( $this, 'load_plugin_notifications' ) );
			WP_CLI::add_hook( 'ai1wm_cli_commands', array( $this, 'load_plugin_commands' ) );
			WP_CLI::add_hook( 'ai1wm_cli_incrementals', array( $this, 'load_incremental_commands' ) );
		}
	}

	/**
	 * Register listeners for filters
	 *
	 * @return void
	 */
	protected function activate_filters() {
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 5, 2 );
		add_filter( 'ai1wm_pro', array( $this, 'plugin_pro_text' ), 100 );

		add_filter( 'ai1wm_export', array( $this, 'load_compatibility_check' ), 10 );
		add_filter( 'ai1wm_import', array( $this, 'load_compatibility_check' ), 10 );
		add_filter( 'ai1wm_reset', array( $this, 'load_compatibility_check' ), 10 );
	}

	/**
	 * Register plugin menus
	 *
	 * @return void
	 */
	public function admin_menu() {
		add_submenu_page(
			'ai1wm_export',
			__( 'Reset Hub', AI1WMKE_PLUGIN_NAME ),
			__( 'Reset Hub', AI1WMKE_PLUGIN_NAME ),
			'import',
			'ai1wmke_reset',
			'Ai1wmke_Reset_Tools_Controller::index'
		);

		add_submenu_page(
			'ai1wm_export',
			__( 'Schedules', AI1WMKE_PLUGIN_NAME ),
			__( 'Schedules', AI1WMKE_PLUGIN_NAME ),
			'export',
			'ai1wmke_schedules',
			'Ai1wmke_Schedule_Events_Controller::index'
		);

		add_submenu_page(
			'ai1wm_export',
			__( 'Extensions', AI1WMKE_PLUGIN_NAME ),
			__( 'Extensions', AI1WMKE_PLUGIN_NAME ),
			'export',
			'ai1wmke_extensions',
			'Ai1wmke_Extension_Items_Controller::index'
		);

		add_submenu_page(
			'ai1wm_export',
			__( 'Settings', AI1WMKE_PLUGIN_NAME ),
			__( 'Settings', AI1WMKE_PLUGIN_NAME ),
			'export',
			'ai1wmke_pro_settings',
			'Ai1wmke_Pro_Settings_Controller::index'
		);

		if ( ai1wmke_is_enabled( 'direct', false ) ) {
			if ( ai1wmke_is_admin( 'direct' ) ) {
				add_submenu_page(
					'ai1wm_export',
					__( 'Linked Sites', AI1WMKE_PLUGIN_NAME ),
					__( 'Linked Sites', AI1WMKE_PLUGIN_NAME ),
					'export',
					'ai1wmke_direct',
					'Ai1wmke_Direct_Sites_Controller::index'
				);
			}
		}

		if ( ai1wmke_is_enabled( 'azure-storage' ) ) {
			if ( ai1wmke_is_admin( 'azure-storage' ) ) {
				add_submenu_page(
					'ai1wm_export',
					__( 'Microsoft Azure Storage Settings', AI1WMKE_PLUGIN_NAME ),
					__( 'Microsoft Azure Storage Settings', AI1WMKE_PLUGIN_NAME ),
					'export',
					'ai1wmke_azure_storage_settings',
					'Ai1wmke_Azure_Storage_Settings_Controller::index'
				);
			}
		}

		if ( ai1wmke_is_enabled( 'b2' ) ) {
			if ( ai1wmke_is_admin( 'b2' ) ) {
				add_submenu_page(
					'ai1wm_export',
					__( 'Backblaze B2 Settings', AI1WMKE_PLUGIN_NAME ),
					__( 'Backblaze B2 Settings', AI1WMKE_PLUGIN_NAME ),
					'export',
					'ai1wmke_b2_settings',
					'Ai1wmke_B2_Settings_Controller::index'
				);
			}
		}

		if ( ai1wmke_is_enabled( 'box' ) ) {
			if ( ai1wmke_is_admin( 'box' ) ) {
				add_submenu_page(
					'ai1wm_export',
					__( 'Box Settings', AI1WMKE_PLUGIN_NAME ),
					__( 'Box Settings', AI1WMKE_PLUGIN_NAME ),
					'export',
					'ai1wmke_box_settings',
					'Ai1wmke_Box_Settings_Controller::index'
				);
			}
		}

		if ( ai1wmke_is_enabled( 'digitalocean' ) ) {
			if ( ai1wmke_is_admin( 'digitalocean' ) ) {
				add_submenu_page(
					'ai1wm_export',
					__( 'DigitalOcean Spaces Settings', AI1WMKE_PLUGIN_NAME ),
					__( 'DigitalOcean Spaces Settings', AI1WMKE_PLUGIN_NAME ),
					'export',
					'ai1wmke_digitalocean_settings',
					'Ai1wmke_DigitalOcean_Settings_Controller::index'
				);
			}
		}

		if ( ai1wmke_is_enabled( 'dropbox' ) ) {
			if ( ai1wmke_is_admin( 'dropbox' ) ) {
				add_submenu_page(
					'ai1wm_export',
					__( 'Dropbox Settings', AI1WMKE_PLUGIN_NAME ),
					__( 'Dropbox Settings', AI1WMKE_PLUGIN_NAME ),
					'export',
					'ai1wmke_dropbox_settings',
					'Ai1wmke_Dropbox_Settings_Controller::index'
				);
			}
		}

		if ( ai1wmke_is_enabled( 'ftp' ) ) {
			if ( ai1wmke_is_admin( 'ftp' ) ) {
				add_submenu_page(
					'ai1wm_export',
					__( 'FTP Settings', AI1WMKE_PLUGIN_NAME ),
					__( 'FTP Settings', AI1WMKE_PLUGIN_NAME ),
					'export',
					'ai1wmke_ftp_settings',
					'Ai1wmke_FTP_Settings_Controller::index'
				);
			}
		}

		if ( ai1wmke_is_enabled( 'gcloud-storage' ) ) {
			if ( ai1wmke_is_admin( 'gcloud-storage' ) ) {
				add_submenu_page(
					'ai1wm_export',
					__( 'Google Cloud Storage Settings', AI1WMKE_PLUGIN_NAME ),
					__( 'Google Cloud Storage Settings', AI1WMKE_PLUGIN_NAME ),
					'export',
					'ai1wmke_gcloud_storage_settings',
					'Ai1wmke_GCloud_Storage_Settings_Controller::index'
				);
			}
		}

		if ( ai1wmke_is_enabled( 'gdrive' ) ) {
			if ( ai1wmke_is_admin( 'gdrive' ) ) {
				add_submenu_page(
					'ai1wm_export',
					__( 'Google Drive Settings', AI1WMKE_PLUGIN_NAME ),
					__( 'Google Drive Settings', AI1WMKE_PLUGIN_NAME ),
					'export',
					'ai1wmke_gdrive_settings',
					'Ai1wmke_GDrive_Settings_Controller::index'
				);
			}
		}

		if ( ai1wmke_is_enabled( 'glacier' ) ) {
			if ( ai1wmke_is_admin( 'glacier' ) ) {
				add_submenu_page(
					'ai1wm_export',
					__( 'Amazon Glacier Settings', AI1WMKE_PLUGIN_NAME ),
					__( 'Amazon Glacier Settings', AI1WMKE_PLUGIN_NAME ),
					'export',
					'ai1wmke_glacier_settings',
					'Ai1wmke_Glacier_Settings_Controller::index'
				);
			}
		}

		if ( ai1wmke_is_enabled( 'mega' ) ) {
			if ( ai1wmke_is_admin( 'mega' ) ) {
				add_submenu_page(
					'ai1wm_export',
					__( 'Mega Settings', AI1WMKE_PLUGIN_NAME ),
					__( 'Mega Settings', AI1WMKE_PLUGIN_NAME ),
					'export',
					'ai1wmke_mega_settings',
					'Ai1wmke_Mega_Settings_Controller::index'
				);
			}
		}

		if ( ai1wmke_is_enabled( 'onedrive' ) ) {
			if ( ai1wmke_is_admin( 'onedrive' ) ) {
				add_submenu_page(
					'ai1wm_export',
					__( 'OneDrive Settings', AI1WMKE_PLUGIN_NAME ),
					__( 'OneDrive Settings', AI1WMKE_PLUGIN_NAME ),
					'export',
					'ai1wmke_onedrive_settings',
					'Ai1wmke_OneDrive_Settings_Controller::index'
				);
			}
		}

		if ( ai1wmke_is_enabled( 'pcloud' ) ) {
			if ( ai1wmke_is_admin( 'pcloud' ) ) {
				add_submenu_page(
					'ai1wm_export',
					__( 'pCloud Settings', AI1WMKE_PLUGIN_NAME ),
					__( 'pCloud Settings', AI1WMKE_PLUGIN_NAME ),
					'export',
					'ai1wmke_pcloud_settings',
					'Ai1wmke_PCloud_Settings_Controller::index'
				);
			}
		}

		if ( ai1wmke_is_enabled( 's3-client' ) ) {
			if ( ai1wmke_is_admin( 's3-client' ) ) {
				add_submenu_page(
					'ai1wm_export',
					__( 'S3 Client Settings', AI1WMKE_PLUGIN_NAME ),
					__( 'S3 Client Settings', AI1WMKE_PLUGIN_NAME ),
					'export',
					'ai1wmke_s3_client_settings',
					'Ai1wmke_S3_Client_Settings_Controller::index'
				);
			}
		}

		if ( ai1wmke_is_enabled( 's3' ) ) {
			if ( ai1wmke_is_admin( 's3' ) ) {
				add_submenu_page(
					'ai1wm_export',
					__( 'Amazon S3 Settings', AI1WMKE_PLUGIN_NAME ),
					__( 'Amazon S3 Settings', AI1WMKE_PLUGIN_NAME ),
					'export',
					'ai1wmke_s3_settings',
					'Ai1wmke_S3_Settings_Controller::index'
				);
			}
		}

		if ( ai1wmke_is_enabled( 'url' ) ) {
			if ( ai1wmke_is_admin( 'url' ) ) {
				add_submenu_page(
					'ai1wm_export',
					__( 'URL Settings', AI1WMKE_PLUGIN_NAME ),
					__( 'URL Settings', AI1WMKE_PLUGIN_NAME ),
					'import',
					'ai1wmke_url_settings',
					'Ai1wmke_URL_Settings_Controller::index'
				);
			}
		}

		if ( ai1wmke_is_enabled( 'webdav' ) ) {
			if ( ai1wmke_is_admin( 'webdav' ) ) {
				add_submenu_page(
					'ai1wm_export',
					__( 'WebDAV Settings', AI1WMKE_PLUGIN_NAME ),
					__( 'WebDAV Settings', AI1WMKE_PLUGIN_NAME ),
					'export',
					'ai1wmke_webdav_settings',
					'Ai1wmke_WebDAV_Settings_Controller::index'
				);
			}
		}

		remove_submenu_page( 'ai1wm_export', 'ai1wmve_reset' );
		remove_submenu_page( 'ai1wm_export', 'ai1wmve_schedules' );
		remove_submenu_page( 'ai1wm_export', 'ai1wmze_settings' );
		remove_submenu_page( 'ai1wm_export', 'ai1wmae_settings' );
		remove_submenu_page( 'ai1wm_export', 'ai1wmbe_settings' );
		remove_submenu_page( 'ai1wm_export', 'ai1wmie_settings' );
		remove_submenu_page( 'ai1wm_export', 'ai1wmxe_sites' );
		remove_submenu_page( 'ai1wm_export', 'ai1wmde_settings' );
		remove_submenu_page( 'ai1wm_export', 'ai1wmfe_settings' );
		remove_submenu_page( 'ai1wm_export', 'ai1wmce_settings' );
		remove_submenu_page( 'ai1wm_export', 'ai1wmge_settings' );
		remove_submenu_page( 'ai1wm_export', 'ai1wmre_settings' );
		remove_submenu_page( 'ai1wm_export', 'ai1wmee_settings' );
		remove_submenu_page( 'ai1wm_export', 'ai1wmme_settings' );
		remove_submenu_page( 'ai1wm_export', 'ai1wmoe_settings' );
		remove_submenu_page( 'ai1wm_export', 'ai1wmpe_settings' );
		remove_submenu_page( 'ai1wm_export', 'ai1wmne_settings' );
		remove_submenu_page( 'ai1wm_export', 'ai1wmse_settings' );
		remove_submenu_page( 'ai1wm_export', 'ai1wmue_settings' );
		remove_submenu_page( 'ai1wm_export', 'ai1wmle_settings' );
		remove_submenu_page( 'ai1wm_export', 'ai1wmwe_settings' );
	}
}
