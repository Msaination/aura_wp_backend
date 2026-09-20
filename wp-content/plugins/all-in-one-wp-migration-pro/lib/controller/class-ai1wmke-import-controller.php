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

class Ai1wmke_Import_Controller {

	/**
	 * Enqueue scripts and styles for Import Controller
	 *
	 * @param  string $hook Hook suffix
	 * @return void
	 */
	public static function enqueue_scripts_and_styles( $hook ) {
		if ( stripos( 'all-in-one-wp-migration_page_ai1wm_import', $hook ) === false ) {
			return;
		}

		if ( is_rtl() ) {
			wp_enqueue_style(
				'ai1wmke_import',
				Ai1wm_Template::asset_link( 'css/import.min.rtl.css', 'AI1WMKE' ),
				array( 'ai1wm_import' )
			);
		} else {
			wp_enqueue_style(
				'ai1wmke_import',
				Ai1wm_Template::asset_link( 'css/import.min.css', 'AI1WMKE' ),
				array( 'ai1wm_import' )
			);
		}

		wp_enqueue_script(
			'ai1wmke_import',
			Ai1wm_Template::asset_link( 'javascript/import.min.js', 'AI1WMKE' ),
			array( 'ai1wm_import' )
		);

		wp_enqueue_script(
			'ai1wmke_wasm_exec',
			Ai1wm_Template::asset_link( 'javascript/vendor/wasm_exec.js', 'AI1WMKE' ),
			array( 'ai1wmke_import' )
		);

		// Base service
		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_base_service',
			array(
				'url' => AI1WMKE_SERVICE_URL,
				'key' => AI1WMKE_PURCHASE_ID,
			)
		);

		// Site details
		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_site_details',
			array(
				'site_url'    => site_url(),
				'admin_email' => get_option( 'admin_email' ),
			)
		);

		// Microsoft Azure Storage
		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_azure_storage_import',
			array(
				'ajax' => array(
					'browser_url' => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_azure_storage_browser' ) ) ),
				),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_azure_storage_locale',
			array(
				'unable_to_retrieve_data' => __( 'Unable to retrieve Microsoft Azure Storage data. Refresh the page and try again.', AI1WMKE_PLUGIN_NAME ),
				'unable_to_import_file'   => __( 'We are sorry but %s cannot be imported.', AI1WMKE_PLUGIN_NAME ),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_azure_storage_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl', 'libxml', 'simplexml' ) ) )
		);

		// Backblaze B2
		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_b2_import',
			array(
				'ajax' => array(
					'browser_url'     => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_b2_browser' ) ) ),
					'incremental_url' => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_b2_incremental' ) ) ),
					'root'            => ai1wmke_get_root_folder( 'b2' ),
				),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_b2_locale',
			array(
				'unable_to_retrieve_data' => __( 'Unable to retrieve Backblaze B2 data. Refresh the page and try again.', AI1WMKE_PLUGIN_NAME ),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_b2_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl' ) ) )
		);

		// Box
		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_box_import',
			array(
				'ajax' => array(
					'browser_url' => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_box_browser' ) ) ),
				),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_box_locale',
			array(
				'unable_to_retrieve_data' => __( 'Unable to retrieve Box data. Refresh the page and try again.', AI1WMKE_PLUGIN_NAME ),
				'unable_to_import_file'   => __( 'We are sorry but %s cannot be imported.', AI1WMKE_PLUGIN_NAME ),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_box_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl' ) ) )
		);

		// DigitalOcean Spaces
		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_digitalocean_import',
			array(
				'ajax' => array(
					'browser_url'     => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_digitalocean_browser' ) ) ),
					'incremental_url' => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_digitalocean_incremental' ) ) ),
				),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_digitalocean_locale',
			array(
				'unable_to_retrieve_data' => __( 'Unable to retrieve DigitalOcean Spaces data. Refresh the page and try again.', AI1WMKE_PLUGIN_NAME ),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_digitalocean_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl', 'libxml', 'simplexml' ) ) )
		);

		// Dropbox
		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_dropbox_import',
			array(
				'ajax' => array(
					'browser_url'     => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_dropbox_browser' ) ) ),
					'incremental_url' => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_dropbox_incremental' ) ) ),
				),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_dropbox_locale',
			array(
				'unable_to_retrieve_data' => __( 'Unable to retrieve Dropbox data. Refresh the page and try again.', AI1WMKE_PLUGIN_NAME ),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_dropbox_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl' ) ) )
		);

		// FTP
		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_ftp_import',
			array(
				'ajax' => array(
					'browser_url'     => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_ftp_browser' ) ) ),
					'incremental_url' => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_ftp_incremental' ) ) ),
				),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_ftp_locale',
			array(
				'unable_to_retrieve_data' => __( 'Unable to retrieve FTP/SFTP data. Refresh the page and try again.', AI1WMKE_PLUGIN_NAME ),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_ftp_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl' ) ) )
		);

		// Google Cloud Storage
		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_gcloud_storage_import',
			array(
				'ajax' => array(
					'browser_url'     => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_gcloud_storage_browser' ) ) ),
					'incremental_url' => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_gcloud_storage_incremental' ) ) ),
				),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_gcloud_storage_locale',
			array(
				'unable_to_retrieve_data' => __( 'Unable to retrieve Google Cloud Storage data. Refresh the page and try again.', AI1WMKE_PLUGIN_NAME ),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_gcloud_storage_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl' ) ) )
		);

		// Google Drive
		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_gdrive_import',
			array(
				'ajax'       => array(
					'browser_url'     => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_gdrive_browser' ) ) ),
					'incremental_url' => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_gdrive_incremental' ) ) ),
					'root'            => ai1wmke_get_root_folder( 'gdrive' ),
				),
				'app_folder' => get_option( 'ai1wmke_gdrive_app_folder' ),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_gdrive_locale',
			array(
				'unable_to_retrieve_data' => __( 'Unable to retrieve Google Drive data. Refresh the page and try again.', AI1WMKE_PLUGIN_NAME ),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_gdrive_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl' ) ) )
		);

		// Amazon Glacier
		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_glacier_import',
			array(
				'ajax' => array(
					'browser_url' => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_glacier_browser' ) ) ),
					'root'        => ai1wmke_get_root_folder( 'glacier' ),
				),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_glacier_locale',
			array(
				'unable_to_retrieve_data' => __( 'Unable to retrieve Amazon Glacier data. Refresh the page and try again.', AI1WMKE_PLUGIN_NAME ),
				'unable_to_import_file'   => __( 'We are sorry but %s cannot be imported.', AI1WMKE_PLUGIN_NAME ),
				'unable_to_list_vaults'   => __( 'Unable to list additional vaults because "%s". You can try again.', AI1WMKE_PLUGIN_NAME ),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_glacier_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl' ) ) )
		);

		// Mega
		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_mega_import',
			array(
				'ajax' => array(
					'browser_url'     => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_mega_browser' ) ) ),
					'incremental_url' => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_mega_incremental' ) ) ),
					'root'            => ai1wmke_get_root_folder( 'mega' ),
				),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_mega_locale',
			array(
				'unable_to_retrieve_data' => __( 'Unable to retrieve Mega data. Refresh the page and try again.', AI1WMKE_PLUGIN_NAME ),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_mega_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl', 'bcmath', array( 'openssl', 'mcrypt' ) ) ) )
		);

		// OneDrive
		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_onedrive_import',
			array(
				'ajax' => array(
					'browser_url'     => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_onedrive_browser' ) ) ),
					'incremental_url' => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_onedrive_incremental' ) ) ),
					'root'            => ai1wmke_get_root_folder( 'onedrive' ),
				),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_onedrive_locale',
			array(
				'unable_to_retrieve_data' => __( 'Unable to retrieve OneDrive data. Refresh the page and try again.', AI1WMKE_PLUGIN_NAME ),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_onedrive_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl' ) ) )
		);

		// pCloud
		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_pcloud_import',
			array(
				'ajax' => array(
					'browser_url'     => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_pcloud_browser' ) ) ),
					'incremental_url' => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_pcloud_incremental' ) ) ),
					'root'            => ai1wmke_get_root_folder( 'pcloud' ),
				),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_pcloud_locale',
			array(
				'unable_to_retrieve_data' => __( 'Unable to retrieve pCloud data. Refresh the page and try again.', AI1WMKE_PLUGIN_NAME ),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_pcloud_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl' ) ) )
		);

		// S3 Client
		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_s3_client_import',
			array(
				'ajax' => array(
					'browser_url'     => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_s3_client_browser' ) ) ),
					'incremental_url' => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_s3_client_incremental' ) ) ),
				),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_s3_client_locale',
			array(
				'unable_to_retrieve_data' => __( 'Unable to retrieve S3 Client data. Refresh the page and try again.', AI1WMKE_PLUGIN_NAME ),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_s3_client_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl', 'libxml', 'simplexml' ) ) )
		);

		// Amazon S3
		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_s3_import',
			array(
				'ajax' => array(
					'browser_url'     => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_s3_browser' ) ) ),
					'incremental_url' => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_s3_incremental' ) ) ),
				),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_s3_locale',
			array(
				'unable_to_retrieve_data' => __( 'Unable to retrieve Amazon S3 data. Refresh the page and try again.', AI1WMKE_PLUGIN_NAME ),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_s3_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl', 'libxml', 'simplexml' ) ) )
		);

		// URL
		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_url_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl' ) ) )
		);

		// WebDAV
		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_webdav_import',
			array(
				'ajax' => array(
					'browser_url' => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_webdav_browser' ) ) ),
				),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_webdav_locale',
			array(
				'unable_to_retrieve_data' => __( 'Unable to retrieve WebDAV data. Refresh the page and try again.', AI1WMKE_PLUGIN_NAME ),
				'unable_to_import_file'   => __( 'We are sorry but %s cannot be imported.', AI1WMKE_PLUGIN_NAME ),
			)
		);

		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_webdav_dependencies',
			array( 'messages' => Ai1wmke_Dependencies::check( array( 'curl', 'libxml', 'simplexml' ) ) )
		);

		// File uploader
		wp_localize_script(
			'ai1wmke_import',
			'ai1wmke_file_uploader',
			array(
				'config'  => array(
					'chunk_size'  => (int) apply_filters( 'ai1wm_max_chunk_size', AI1WM_MAX_CHUNK_SIZE ),
					'max_retries' => (int) apply_filters( 'ai1wm_max_chunk_retries', AI1WM_MAX_CHUNK_RETRIES ),
				),
				'url'     => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wm_import' ) ) ),
				'params'  => array(
					'priority'   => 5,
					'secret_key' => get_option( AI1WM_SECRET_KEY ),
				),
				'filters' => array(
					'ai1wm_archive_extension' => array( 'wpress' ),
				),
			)
		);

		// Add Google Tag Manager
		add_action( 'admin_print_scripts', 'Ai1wmke_GTM_Controller::print_scripts', 100 );
	}
}
