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

class Ai1wmke_Settings_Controller {

	/**
	 * Enqueue scripts and styles for Settings Controller
	 *
	 * @param  string $hook Hook suffix
	 * @return void
	 */
	public static function enqueue_scripts_and_styles( $hook ) {
		switch ( true ) {
			case ( $load_azure_storage_settings = stripos( 'all-in-one-wp-migration_page_ai1wmke_azure_storage_settings', $hook ) !== false ):
			case ( $load_b2_settings = stripos( 'all-in-one-wp-migration_page_ai1wmke_b2_settings', $hook ) !== false ):
			case ( $load_box_settings = stripos( 'all-in-one-wp-migration_page_ai1wmke_box_settings', $hook ) !== false ):
			case ( $load_digitalocean_settings = stripos( 'all-in-one-wp-migration_page_ai1wmke_digitalocean_settings', $hook ) !== false ):
			case ( $load_dropbox_settings = stripos( 'all-in-one-wp-migration_page_ai1wmke_dropbox_settings', $hook ) !== false ):
			case ( $load_ftp_settings = stripos( 'all-in-one-wp-migration_page_ai1wmke_ftp_settings', $hook ) !== false ):
			case ( $load_gcloud_storage_settings = stripos( 'all-in-one-wp-migration_page_ai1wmke_gcloud_storage_settings', $hook ) !== false ):
			case ( $load_gdrive_settings = stripos( 'all-in-one-wp-migration_page_ai1wmke_gdrive_settings', $hook ) !== false ):
			case ( $load_glacier_settings = stripos( 'all-in-one-wp-migration_page_ai1wmke_glacier_settings', $hook ) !== false ):
			case ( $load_mega_settings = stripos( 'all-in-one-wp-migration_page_ai1wmke_mega_settings', $hook ) !== false ):
			case ( $load_onedrive_settings = stripos( 'all-in-one-wp-migration_page_ai1wmke_onedrive_settings', $hook ) !== false ):
			case ( $load_pcloud_settings = stripos( 'all-in-one-wp-migration_page_ai1wmke_pcloud_settings', $hook ) !== false ):
			case ( $load_pro_settings = stripos( 'all-in-one-wp-migration_page_ai1wmke_pro_settings', $hook ) !== false ):
			case ( $load_s3_client_settings = stripos( 'all-in-one-wp-migration_page_ai1wmke_s3_client_settings', $hook ) !== false ):
			case ( $load_s3_settings = stripos( 'all-in-one-wp-migration_page_ai1wmke_s3_settings', $hook ) !== false ):
			case ( $load_url_settings = stripos( 'all-in-one-wp-migration_page_ai1wmke_url_settings', $hook ) !== false ):
			case ( $load_webdav_settings = stripos( 'all-in-one-wp-migration_page_ai1wmke_webdav_settings', $hook ) !== false ):
				break;

			default:
				return;
		}

		if ( is_rtl() ) {
			wp_enqueue_style(
				'ai1wmke_settings',
				Ai1wm_Template::asset_link( 'css/settings.min.rtl.css', 'AI1WMKE' ),
				array( 'ai1wm_servmask' )
			);
		} else {
			wp_enqueue_style(
				'ai1wmke_settings',
				Ai1wm_Template::asset_link( 'css/settings.min.css', 'AI1WMKE' ),
				array( 'ai1wm_servmask' )
			);
		}

		wp_enqueue_script(
			'ai1wmke_settings',
			Ai1wm_Template::asset_link( 'javascript/settings.min.js', 'AI1WMKE' ),
			array( 'ai1wm_settings' )
		);

		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_settings',
			array(
				'requires' => array(
					'load_azure_storage_settings'  => ! empty( $load_azure_storage_settings ),
					'load_b2_settings'             => ! empty( $load_b2_settings ),
					'load_box_settings'            => ! empty( $load_box_settings ),
					'load_digitalocean_settings'   => ! empty( $load_digitalocean_settings ),
					'load_dropbox_settings'        => ! empty( $load_dropbox_settings ),
					'load_ftp_settings'            => ! empty( $load_ftp_settings ),
					'load_gcloud_storage_settings' => ! empty( $load_gcloud_storage_settings ),
					'load_gdrive_settings'         => ! empty( $load_gdrive_settings ),
					'load_glacier_settings'        => ! empty( $load_glacier_settings ),
					'load_mega_settings'           => ! empty( $load_mega_settings ),
					'load_onedrive_settings'       => ! empty( $load_onedrive_settings ),
					'load_pcloud_settings'         => ! empty( $load_pcloud_settings ),
					'load_pro_settings'            => ! empty( $load_pro_settings ),
					'load_s3_client_settings'      => ! empty( $load_s3_client_settings ),
					'load_s3_settings'             => ! empty( $load_s3_settings ),
					'load_url_settings'            => ! empty( $load_url_settings ),
					'load_webdav_settings'         => ! empty( $load_webdav_settings ),
				),
			)
		);

		// Microsoft Azure Storage
		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_azure_storage_settings',
			array(
				'ajax'        => array(
					'folder_url'   => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_azure_storage_folder' ) ) ),
					'selector_url' => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_azure_storage_selector' ) ) ),
				),
				'account_key' => get_option( 'ai1wmke_azure_storage_account_key' ),
			)
		);

		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_azure_storage_locale',
			array(
				'unable_to_retrieve_data' => __( 'Unable to retrieve Microsoft Azure Storage data. Refresh the page and try again.', AI1WMKE_PLUGIN_NAME ),
			)
		);

		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_azure_storage_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl', 'libxml', 'simplexml' ) ) )
		);

		// Backblaze B2
		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_b2_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl' ) ) )
		);

		// Box
		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_box_settings',
			array(
				'ajax'  => array(
					'folder_url'   => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_box_folder' ) ) ),
					'account_url'  => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_box_account' ) ) ),
					'selector_url' => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_box_selector' ) ) ),
				),
				'token' => get_option( 'ai1wmke_box_token' ),
			)
		);

		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_box_locale',
			array(
				'unable_to_retrieve_data' => __( 'Unable to retrieve Box data. Refresh the page and try again.', AI1WMKE_PLUGIN_NAME ),
			)
		);

		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_box_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl' ) ) )
		);

		// DigitalOcean Spaces
		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_digitalocean_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl', 'libxml', 'simplexml' ) ) )
		);

		// Dropbox
		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_dropbox_settings',
			array(
				'ajax'  => array(
					'folder_url'   => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_dropbox_folder' ) ) ),
					'account_url'  => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_dropbox_account' ) ) ),
					'selector_url' => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_dropbox_selector' ) ) ),
				),
				'token' => get_option( 'ai1wmke_dropbox_token' ),
			)
		);

		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_dropbox_locale',
			array(
				'unable_to_retrieve_data' => __( 'Unable to retrieve Dropbox data. Refresh the page and try again.', AI1WMKE_PLUGIN_NAME ),
			)
		);

		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_dropbox_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl' ) ) )
		);

		// Pro
		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_pro_settings',
			array(
				'ajax'       => array(
					'url' => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_list_folders' ) ) ),
				),
				'secret_key' => get_option( AI1WM_SECRET_KEY ),
			)
		);

		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_pro_locale',
			array(
				'folder_browser_change' => __( 'Change', AI1WMKE_PLUGIN_NAME ),
				'title_name'            => __( 'Name', AI1WMKE_PLUGIN_NAME ),
				'title_date'            => __( 'Date', AI1WMKE_PLUGIN_NAME ),
				'empty_list_message'    => __( 'No folders to list. Click on the navbar to go back.', AI1WMKE_PLUGIN_NAME ),
				'legend_select_info'    => __( 'Select with a click', AI1WMKE_PLUGIN_NAME ),
				'legend_open_info'      => __( 'Open with two clicks', AI1WMKE_PLUGIN_NAME ),
				'button_close'          => __( 'Close', AI1WMKE_PLUGIN_NAME ),
				'button_select'         => __( 'Select folder &gt;', AI1WMKE_PLUGIN_NAME ),
				'show_more'             => __( 'more', AI1WMKE_PLUGIN_NAME ),
				'show_less'             => __( 'less', AI1WMKE_PLUGIN_NAME ),
			)
		);

		// FTP
		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_ftp_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl' ) ) )
		);

		// Google Cloud Storage
		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_gcloud_storage_settings',
			array(
				'ajax'  => array(
					'folder_url'   => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_gcloud_storage_folder' ) ) ),
					'selector_url' => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_gcloud_storage_selector' ) ) ),
				),
				'token' => get_option( 'ai1wmke_gcloud_storage_token' ),
			)
		);

		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_gcloud_storage_locale',
			array(
				'unable_to_retrieve_data' => __( 'Unable to retrieve Google Cloud Storage data. Refresh the page and try again.', AI1WMKE_PLUGIN_NAME ),
			)
		);

		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_gcloud_storage_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl' ) ) )
		);

		// Google Drive
		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_gdrive_settings',
			array(
				'ajax'       => array(
					'folder_url'   => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_gdrive_folder' ) ) ),
					'account_url'  => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_gdrive_account' ) ) ),
					'selector_url' => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_gdrive_selector' ) ) ),
				),
				'token'      => get_option( 'ai1wmke_gdrive_token' ),
				'app_folder' => get_option( 'ai1wmke_gdrive_app_folder' ),
			)
		);

		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_gdrive_locale',
			array(
				'unable_to_retrieve_data' => __( 'Unable to retrieve Google Drive data. Refresh the page and try again.', AI1WMKE_PLUGIN_NAME ),
			)
		);

		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_gdrive_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl' ) ) )
		);

		// Amazon Glacier
		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_glacier_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl' ) ) )
		);

		// Mega
		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_mega_settings',
			array(
				'ajax'    => array(
					'folder_url'   => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_mega_folder' ) ) ),
					'account_url'  => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_mega_account' ) ) ),
					'selector_url' => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_mega_selector' ) ) ),
				),
				'session' => get_option( 'ai1wmke_mega_user_session' ),
			)
		);

		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_mega_locale',
			array(
				'unable_to_retrieve_data' => __( 'Unable to retrieve Mega data. Refresh the page and try again.', AI1WMKE_PLUGIN_NAME ),
			)
		);

		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_mega_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl', 'bcmath', array( 'openssl', 'mcrypt' ) ) ) )
		);

		// OneDrive
		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_onedrive_settings',
			array(
				'ajax'  => array(
					'folder_url'   => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_onedrive_folder' ) ) ),
					'account_url'  => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_onedrive_account' ) ) ),
					'selector_url' => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_onedrive_selector' ) ) ),
				),
				'token' => get_option( 'ai1wmke_onedrive_token' ),
			)
		);

		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_onedrive_locale',
			array(
				'unable_to_retrieve_data' => __( 'Unable to retrieve OneDrive data. Refresh the page and try again.', AI1WMKE_PLUGIN_NAME ),
			)
		);

		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_onedrive_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl' ) ) )
		);

		// pCloud
		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_pcloud_settings',
			array(
				'ajax'  => array(
					'folder_url'   => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_pcloud_folder' ) ) ),
					'account_url'  => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_pcloud_account' ) ) ),
					'selector_url' => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_pcloud_selector' ) ) ),
				),
				'token' => get_option( 'ai1wmke_pcloud_token' ),
			)
		);

		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_pcloud_locale',
			array(
				'unable_to_retrieve_data' => __( 'Unable to retrieve pCloud data. Refresh the page and try again.', AI1WMKE_PLUGIN_NAME ),
			)
		);

		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_pcloud_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl' ) ) )
		);

		// S3 Client
		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_s3_client_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl', 'libxml', 'simplexml' ) ) )
		);

		// Amazon S3
		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_s3_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl', 'libxml', 'simplexml' ) ) )
		);

		// URL
		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_url_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl' ) ) )
		);

		// WebDAV
		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_webdav_settings',
			array(
				'ajax' => array(
					'folder_url'   => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_webdav_folder' ) ) ),
					'account_url'  => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_webdav_account' ) ) ),
					'selector_url' => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_webdav_selector' ) ) ),
				),
			)
		);

		wp_localize_script(
			'ai1wmke_settings',
			'ai1wmke_webdav_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl', 'libxml', 'simplexml' ) ) )
		);

		// Add Google Tag Manager
		add_action( 'admin_print_scripts', 'Ai1wmke_GTM_Controller::print_scripts', 100 );
	}
}
