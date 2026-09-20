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

class Ai1wmke_Upgrader {

	public static function sync_options() {
		global $wpdb;

		// Microsoft Azure Storage
		if ( defined( 'AI1WMZE_PLUGIN_NAME' ) ) {
			update_option( 'ai1wmke_azure_storage_cron_timestamp', get_option( 'ai1wmze_azure_storage_cron_timestamp', time() ) );
			update_option( 'ai1wmke_azure_storage_cron', get_option( 'ai1wmze_azure_storage_cron', array() ) );
			update_option( 'ai1wmke_azure_storage_account_name', get_option( 'ai1wmze_azure_storage_account_name', false ) );
			update_option( 'ai1wmke_azure_storage_account_key', get_option( 'ai1wmze_azure_storage_account_key', false ) );
			update_option( 'ai1wmke_azure_storage_share_name', get_option( 'ai1wmze_azure_storage_share_name', ai1wm_archive_share() ) );
			update_option( 'ai1wmke_azure_storage_ssl', get_option( 'ai1wmze_azure_storage_ssl', false ) );
			update_option( 'ai1wmke_azure_storage_backups', get_option( 'ai1wmze_azure_storage_backups', false ) );
			update_option( 'ai1wmke_azure_storage_folder_name', get_option( 'ai1wmze_azure_storage_folder_name', '' ) );
			update_option( 'ai1wmke_azure_storage_total', get_option( 'ai1wmze_azure_storage_total', false ) );
			update_option( 'ai1wmke_azure_storage_days', get_option( 'ai1wmze_azure_storage_days', false ) );
			update_option( 'ai1wmke_azure_storage_notify_toggle', get_option( 'ai1wmze_azure_storage_notify_toggle', false ) );
			update_option( 'ai1wmke_azure_storage_notify_error_toggle', get_option( 'ai1wmze_azure_storage_notify_error_toggle', false ) );
			update_option( 'ai1wmke_azure_storage_notify_error_subject', get_option( 'ai1wmze_azure_storage_notify_error_subject', false ) );
			update_option( 'ai1wmke_azure_storage_notify_email', get_option( 'ai1wmze_azure_storage_notify_email', false ) );
		}

		// Backblaze B2
		if ( defined( 'AI1WMAE_PLUGIN_NAME' ) ) {
			update_option( 'ai1wmke_b2_cron_timestamp', get_option( 'ai1wmae_b2_cron_timestamp', time() ) );
			update_option( 'ai1wmke_b2_cron', get_option( 'ai1wmae_b2_cron', array() ) );
			update_option( 'ai1wmke_b2_account_id', get_option( 'ai1wmae_b2_account_id', false ) );
			update_option( 'ai1wmke_b2_application_key', get_option( 'ai1wmae_b2_application_key', false ) );
			update_option( 'ai1wmke_b2_bucket_id', get_option( 'ai1wmae_b2_bucket_id', false ) );
			update_option( 'ai1wmke_b2_bucket_name', get_option( 'ai1wmae_b2_bucket_name', ai1wm_archive_bucket() ) );
			update_option( 'ai1wmke_b2_backups', get_option( 'ai1wmae_b2_backups', false ) );
			update_option( 'ai1wmke_b2_total', get_option( 'ai1wmae_b2_total', false ) );
			update_option( 'ai1wmke_b2_days', get_option( 'ai1wmae_b2_days', false ) );
			update_option( 'ai1wmke_b2_file_chunk_size', get_option( 'ai1wmae_b2_file_chunk_size', AI1WMKE_B2_FILE_CHUNK_SIZE ) );
			update_option( 'ai1wmke_b2_notify_toggle', get_option( 'ai1wmae_b2_notify_toggle', false ) );
			update_option( 'ai1wmke_b2_notify_error_toggle', get_option( 'ai1wmae_b2_notify_error_toggle', false ) );
			update_option( 'ai1wmke_b2_notify_error_subject', get_option( 'ai1wmae_b2_notify_error_subject', false ) );
			update_option( 'ai1wmke_b2_notify_email', get_option( 'ai1wmae_b2_notify_email', false ) );
			update_option( 'ai1wmke_b2_lock_mode', get_option( 'ai1wmae_b2_lock_mode', false ) );
			update_option( 'ai1wmke_b2_incremental', get_option( 'ai1wmae_b2_incremental', false ) );
		}

		// Box
		if ( defined( 'AI1WMBE_PLUGIN_NAME' ) ) {
			update_option( 'ai1wmke_box_cron_timestamp', get_option( 'ai1wmbe_box_cron_timestamp', time() ) );
			update_option( 'ai1wmke_box_cron', get_option( 'ai1wmbe_box_cron', array() ) );
			update_option( 'ai1wmke_box_token', get_option( 'ai1wmbe_box_token', false ) );
			update_option( 'ai1wmke_box_ssl', get_option( 'ai1wmbe_box_ssl', false ) );
			update_option( 'ai1wmke_box_backups', get_option( 'ai1wmbe_box_backups', false ) );
			update_option( 'ai1wmke_box_folder_id', get_option( 'ai1wmbe_box_folder_id', false ) );
			update_option( 'ai1wmke_box_total', get_option( 'ai1wmbe_box_total', false ) );
			update_option( 'ai1wmke_box_days', get_option( 'ai1wmbe_box_days', false ) );
			update_option( 'ai1wmke_box_file_chunk_size', get_option( 'ai1wmbe_box_file_chunk_size', AI1WMKE_BOX_FILE_CHUNK_SIZE ) );
			update_option( 'ai1wmke_box_notify_toggle', get_option( 'ai1wmbe_box_notify_toggle', false ) );
			update_option( 'ai1wmke_box_notify_error_toggle', get_option( 'ai1wmbe_box_notify_error_toggle', false ) );
			update_option( 'ai1wmke_box_notify_error_subject', get_option( 'ai1wmbe_box_notify_error_subject', false ) );
			update_option( 'ai1wmke_box_notify_email', get_option( 'ai1wmbe_box_notify_email', false ) );
			update_option( 'ai1wmke_box_access_token', get_option( 'ai1wmbe_box_access_token', false ) );
			update_option( 'ai1wmke_box_access_token_expires_in', get_option( 'ai1wmbe_box_access_token_expires_in', false ) );
		}

		// DigitalOcean Spaces
		if ( defined( 'AI1WMIE_PLUGIN_NAME' ) ) {
			update_option( 'ai1wmke_digitalocean_cron_timestamp', get_option( 'ai1wmie_digitalocean_cron_timestamp', time() ) );
			update_option( 'ai1wmke_digitalocean_cron', get_option( 'ai1wmie_digitalocean_cron', array() ) );
			update_option( 'ai1wmke_digitalocean_access_key', get_option( 'ai1wmie_digitalocean_access_key', ai1wmke_aws_access_key() ) );
			update_option( 'ai1wmke_digitalocean_secret_key', get_option( 'ai1wmie_digitalocean_secret_key', ai1wmke_aws_secret_key() ) );
			update_option( 'ai1wmke_digitalocean_bucket_name', get_option( 'ai1wmie_digitalocean_bucket_name', ai1wm_archive_bucket() ) );
			update_option( 'ai1wmke_digitalocean_region_name', get_option( 'ai1wmie_digitalocean_region_name', ai1wmke_aws_region_name( AI1WMKE_DIGITALOCEAN_REGION_NAME ) ) );
			update_option( 'ai1wmke_digitalocean_storage_class', get_option( 'ai1wmie_digitalocean_storage_class', AI1WMKE_DIGITALOCEAN_STORAGE_CLASS ) );
			update_option( 'ai1wmke_digitalocean_encryption', get_option( 'ai1wmie_digitalocean_encryption', false ) );
			update_option( 'ai1wmke_digitalocean_folder_name', get_option( 'ai1wmie_digitalocean_folder_name', '' ) );
			update_option( 'ai1wmke_digitalocean_backups', get_option( 'ai1wmie_digitalocean_backups', false ) );
			update_option( 'ai1wmke_digitalocean_total', get_option( 'ai1wmie_digitalocean_total', false ) );
			update_option( 'ai1wmke_digitalocean_days', get_option( 'ai1wmie_digitalocean_days', false ) );
			update_option( 'ai1wmke_digitalocean_file_chunk_size', get_option( 'ai1wmie_digitalocean_file_chunk_size', AI1WMKE_DIGITALOCEAN_FILE_CHUNK_SIZE ) );
			update_option( 'ai1wmke_digitalocean_notify_toggle', get_option( 'ai1wmie_digitalocean_notify_toggle', false ) );
			update_option( 'ai1wmke_digitalocean_notify_error_toggle', get_option( 'ai1wmie_digitalocean_notify_error_toggle', false ) );
			update_option( 'ai1wmke_digitalocean_notify_error_subject', get_option( 'ai1wmie_digitalocean_notify_error_subject', false ) );
			update_option( 'ai1wmke_digitalocean_notify_email', get_option( 'ai1wmie_digitalocean_notify_email', false ) );
			update_option( 'ai1wmke_digitalocean_incremental', get_option( 'ai1wmie_digitalocean_incremental', false ) );
		}

		// Direct
		if ( defined( 'AI1WMXE_PLUGIN_NAME' ) ) {
			update_option( 'ai1wmke_direct_sites_links', get_option( 'ai1wm_sites_links', array() ) );
		}

		// Dropbox
		if ( defined( 'AI1WMDE_PLUGIN_NAME' ) ) {
			update_option( 'ai1wmke_dropbox_cron_timestamp', get_option( 'ai1wmde_dropbox_cron_timestamp', time() ) );
			update_option( 'ai1wmke_dropbox_cron', get_option( 'ai1wmde_dropbox_cron', array() ) );
			update_option( 'ai1wmke_dropbox_token', get_option( 'ai1wmde_dropbox_token', false ) );
			update_option( 'ai1wmke_dropbox_ssl', get_option( 'ai1wmde_dropbox_ssl', false ) );
			update_option( 'ai1wmke_dropbox_folder_path', get_option( 'ai1wmde_dropbox_folder_path', false ) );
			update_option( 'ai1wmke_dropbox_folder_shared_link', get_option( 'ai1wmde_dropbox_folder_shared_link', false ) );
			update_option( 'ai1wmke_dropbox_backups', get_option( 'ai1wmde_dropbox_backups', false ) );
			update_option( 'ai1wmke_dropbox_total', get_option( 'ai1wmde_dropbox_total', false ) );
			update_option( 'ai1wmke_dropbox_days', get_option( 'ai1wmde_dropbox_days', false ) );
			update_option( 'ai1wmke_dropbox_file_chunk_size', get_option( 'ai1wmde_dropbox_file_chunk_size', AI1WMKE_DROPBOX_FILE_CHUNK_SIZE ) );
			update_option( 'ai1wmke_dropbox_notify_toggle', get_option( 'ai1wmde_dropbox_notify_toggle', false ) );
			update_option( 'ai1wmke_dropbox_notify_error_toggle', get_option( 'ai1wmde_dropbox_notify_error_toggle', false ) );
			update_option( 'ai1wmke_dropbox_notify_error_subject', get_option( 'ai1wmde_dropbox_notify_error_subject', false ) );
			update_option( 'ai1wmke_dropbox_notify_email', get_option( 'ai1wmde_dropbox_notify_email', false ) );
			update_option( 'ai1wmke_dropbox_offline', get_option( 'ai1wmde_offline', false ) );
			update_option( 'ai1wmke_dropbox_full_access', get_option( 'ai1wmde_dropbox_full_access', false ) );
			update_option( 'ai1wmke_dropbox_access_token', get_option( 'ai1wmde_dropbox_access_token', false ) );
			update_option( 'ai1wmke_dropbox_access_token_expires_in', get_option( 'ai1wmde_dropbox_access_token_expires_in', false ) );
			update_option( 'ai1wmke_dropbox_incremental', get_option( 'ai1wmde_dropbox_incremental', false ) );
		}

		// FTP
		if ( defined( 'AI1WMFE_PLUGIN_NAME' ) ) {
			update_option( 'ai1wmke_ftp_cron_timestamp', get_option( 'ai1wmfe_ftp_cron_timestamp', time() ) );
			update_option( 'ai1wmke_ftp_cron', get_option( 'ai1wmfe_ftp_cron', array() ) );
			update_option( 'ai1wmke_ftp_type', get_option( 'ai1wmfe_ftp_type', AI1WMKE_FTP_TYPE ) );
			update_option( 'ai1wmke_ftp_hostname', get_option( 'ai1wmfe_ftp_hostname', false ) );
			update_option( 'ai1wmke_ftp_username', get_option( 'ai1wmfe_ftp_username', false ) );
			update_option( 'ai1wmke_ftp_password', get_option( 'ai1wmfe_ftp_password', false ) );
			update_option( 'ai1wmke_ftp_authentication', get_option( 'ai1wmfe_ftp_authentication', AI1WMKE_FTP_AUTHENTICATION ) );
			update_option( 'ai1wmke_ftp_key', get_option( 'ai1wmfe_ftp_key', false ) );
			update_option( 'ai1wmke_ftp_passphrase', get_option( 'ai1wmfe_ftp_passphrase', false ) );
			update_option( 'ai1wmke_ftp_directory', get_option( 'ai1wmfe_ftp_directory', false ) );
			update_option( 'ai1wmke_ftp_port', get_option( 'ai1wmfe_ftp_port', AI1WMKE_FTP_PORT ) );
			update_option( 'ai1wmke_ftp_active', get_option( 'ai1wmfe_ftp_active', false ) );
			update_option( 'ai1wmke_ftp_connection', get_option( 'ai1wmfe_ftp_connection', false ) );
			update_option( 'ai1wmke_ftp_append', get_option( 'ai1wmfe_ftp_append', false ) );
			update_option( 'ai1wmke_ftp_backups', get_option( 'ai1wmfe_ftp_backups', false ) );
			update_option( 'ai1wmke_ftp_total', get_option( 'ai1wmfe_ftp_total', false ) );
			update_option( 'ai1wmke_ftp_days', get_option( 'ai1wmfe_ftp_days', false ) );
			update_option( 'ai1wmke_ftp_file_chunk_size', get_option( 'ai1wmfe_ftp_file_chunk_size', AI1WMKE_FTP_FILE_CHUNK_SIZE ) );
			update_option( 'ai1wmke_ftp_notify_toggle', get_option( 'ai1wmfe_ftp_notify_toggle', false ) );
			update_option( 'ai1wmke_ftp_notify_error_toggle', get_option( 'ai1wmfe_ftp_notify_error_toggle', false ) );
			update_option( 'ai1wmke_ftp_notify_error_subject', get_option( 'ai1wmfe_ftp_notify_error_subject', false ) );
			update_option( 'ai1wmke_ftp_notify_email', get_option( 'ai1wmfe_ftp_notify_email', false ) );
			update_option( 'ai1wmke_ftp_incremental', get_option( 'ai1wmfe_ftp_incremental', false ) );
		}

		// Google Cloud Storage
		if ( defined( 'AI1WMCE_PLUGIN_NAME' ) ) {
			update_option( 'ai1wmke_gcloud_storage_cron_timestamp', get_option( 'ai1wmce_gcloud_storage_cron_timestamp', time() ) );
			update_option( 'ai1wmke_gcloud_storage_cron', get_option( 'ai1wmce_gcloud_storage_cron', array() ) );
			update_option( 'ai1wmke_gcloud_storage_token', get_option( 'ai1wmce_gcloud_storage_token', false ) );
			update_option( 'ai1wmke_gcloud_storage_ssl', get_option( 'ai1wmce_gcloud_storage_ssl', false ) );
			update_option( 'ai1wmke_gcloud_storage_project_id', get_option( 'ai1wmce_gcloud_storage_project_id', ai1wm_archive_project() ) );
			update_option( 'ai1wmke_gcloud_storage_bucket_name', get_option( 'ai1wmce_gcloud_storage_bucket_name', ai1wm_archive_bucket() ) );
			update_option( 'ai1wmke_gcloud_storage_class', get_option( 'ai1wmce_gcloud_storage_class', AI1WMKE_GCLOUD_STORAGE_CLASS ) );
			update_option( 'ai1wmke_gcloud_storage_folder_name', get_option( 'ai1wmce_gcloud_storage_folder_name', '' ) );
			update_option( 'ai1wmke_gcloud_storage_backups', get_option( 'ai1wmce_gcloud_storage_backups', false ) );
			update_option( 'ai1wmke_gcloud_storage_total', get_option( 'ai1wmce_gcloud_storage_total', false ) );
			update_option( 'ai1wmke_gcloud_storage_days', get_option( 'ai1wmce_gcloud_storage_days', false ) );
			update_option( 'ai1wmke_gcloud_storage_file_chunk_size', get_option( 'ai1wmce_gcloud_storage_file_chunk_size', AI1WMKE_GCLOUD_STORAGE_FILE_CHUNK_SIZE ) );
			update_option( 'ai1wmke_gcloud_storage_notify_toggle', get_option( 'ai1wmce_gcloud_storage_notify_toggle', false ) );
			update_option( 'ai1wmke_gcloud_storage_notify_error_toggle', get_option( 'ai1wmce_gcloud_storage_notify_error_toggle', false ) );
			update_option( 'ai1wmke_gcloud_storage_notify_error_subject', get_option( 'ai1wmce_gcloud_storage_notify_error_subject', false ) );
			update_option( 'ai1wmke_gcloud_storage_notify_email', get_option( 'ai1wmce_gcloud_storage_notify_email', false ) );
			update_option( 'ai1wmke_gcloud_storage_access_token', get_option( 'ai1wmce_gcloud_storage_access_token', false ) );
			update_option( 'ai1wmke_gcloud_storage_access_token_expires_in', get_option( 'ai1wmce_gcloud_storage_access_token_expires_in', false ) );
			update_option( 'ai1wmke_gcloud_storage_incremental', get_option( 'ai1wmce_gcloud_storage_incremental', false ) );
		}

		// Google Drive
		if ( defined( 'AI1WMGE_PLUGIN_NAME' ) ) {
			update_option( 'ai1wmke_gdrive_cron_timestamp', get_option( 'ai1wmge_gdrive_cron_timestamp', time() ) );
			update_option( 'ai1wmke_gdrive_cron', get_option( 'ai1wmge_gdrive_cron', array() ) );
			update_option( 'ai1wmke_gdrive_token', get_option( 'ai1wmge_gdrive_token', false ) );
			update_option( 'ai1wmke_gdrive_ssl', get_option( 'ai1wmge_gdrive_ssl', false ) );
			update_option( 'ai1wmke_gdrive_folder_id', get_option( 'ai1wmge_gdrive_folder_id', false ) );
			update_option( 'ai1wmke_gdrive_team_drive_id', get_option( 'ai1wmge_gdrive_team_drive_id', null ) ?: AI1WMKE_GDRIVE_TEAM_DRIVE_ID );
			update_option( 'ai1wmke_gdrive_backups', get_option( 'ai1wmge_gdrive_backups', false ) );
			update_option( 'ai1wmke_gdrive_total', get_option( 'ai1wmge_gdrive_total', false ) );
			update_option( 'ai1wmke_gdrive_days', get_option( 'ai1wmge_gdrive_days', false ) );
			update_option( 'ai1wmke_gdrive_file_chunk_size', get_option( 'ai1wmge_gdrive_file_chunk_size', AI1WMKE_GDRIVE_FILE_CHUNK_SIZE ) );
			update_option( 'ai1wmke_gdrive_notify_toggle', get_option( 'ai1wmge_gdrive_notify_toggle', false ) );
			update_option( 'ai1wmke_gdrive_notify_error_toggle', get_option( 'ai1wmge_gdrive_notify_error_toggle', false ) );
			update_option( 'ai1wmke_gdrive_notify_error_subject', get_option( 'ai1wmge_gdrive_notify_error_subject', false ) );
			update_option( 'ai1wmke_gdrive_notify_email', get_option( 'ai1wmge_gdrive_notify_email', false ) );
			update_option( 'ai1wmke_gdrive_access_token', get_option( 'ai1wmge_gdrive_access_token', false ) );
			update_option( 'ai1wmke_gdrive_access_token_expires_in', get_option( 'ai1wmge_gdrive_access_token_expires_in', false ) );
			update_option( 'ai1wmke_gdrive_lock_mode', get_option( 'ai1wmge_gdrive_lock_mode', false ) );
			update_option( 'ai1wmke_gdrive_app_folder', get_option( 'ai1wmge_gdrive_app_folder', false ) );
			update_option( 'ai1wmke_gdrive_incremental_folder_id', get_option( 'ai1wmge_gdrive_incremental_folder_id', null ) );
			update_option( 'ai1wmke_gdrive_incremental', get_option( 'ai1wmge_gdrive_incremental', false ) );
		}

		// Amazon Glacier
		if ( defined( 'AI1WMRE_PLUGIN_NAME' ) ) {
			update_option( 'ai1wmke_glacier_cron_timestamp', get_option( 'ai1wmre_glacier_cron_timestamp', time() ) );
			update_option( 'ai1wmke_glacier_cron', get_option( 'ai1wmre_glacier_cron', array() ) );
			update_option( 'ai1wmke_glacier_account_id', get_option( 'ai1wmre_glacier_account_id', false ) );
			update_option( 'ai1wmke_glacier_access_key', get_option( 'ai1wmre_glacier_access_key', ai1wmke_aws_access_key() ) );
			update_option( 'ai1wmke_glacier_secret_key', get_option( 'ai1wmre_glacier_secret_key', ai1wmke_aws_secret_key() ) );
			update_option( 'ai1wmke_glacier_vault_name', get_option( 'ai1wmre_glacier_vault_name', ai1wm_archive_vault() ) );
			update_option( 'ai1wmke_glacier_region_name', get_option( 'ai1wmre_glacier_region_name', ai1wmke_aws_region_name( AI1WMKE_GLACIER_REGION_NAME ) ) );
			update_option( 'ai1wmke_glacier_backups', get_option( 'ai1wmre_glacier_backups', false ) );
			update_option( 'ai1wmke_glacier_total', get_option( 'ai1wmre_glacier_total', false ) );
			update_option( 'ai1wmke_glacier_file_chunk_size', get_option( 'ai1wmre_glacier_file_chunk_size', AI1WMKE_GLACIER_FILE_CHUNK_SIZE ) );
			update_option( 'ai1wmke_glacier_notify_toggle', get_option( 'ai1wmre_glacier_notify_toggle', false ) );
			update_option( 'ai1wmke_glacier_notify_error_toggle', get_option( 'ai1wmre_glacier_notify_error_toggle', false ) );
			update_option( 'ai1wmke_glacier_notify_error_subject', get_option( 'ai1wmre_glacier_notify_error_subject', false ) );
			update_option( 'ai1wmke_glacier_notify_email', get_option( 'ai1wmre_glacier_notify_email', false ) );
		}

		// Mega
		if ( defined( 'AI1WMEE_PLUGIN_NAME' ) ) {
			update_option( 'ai1wmke_mega_cron_timestamp', get_option( 'ai1wmee_mega_cron_timestamp', time() ) );
			update_option( 'ai1wmke_mega_cron', get_option( 'ai1wmee_mega_cron', array() ) );
			update_option( 'ai1wmke_mega_user_email', get_option( 'ai1wmee_mega_user_email', false ) );
			update_option( 'ai1wmke_mega_user_password', get_option( 'ai1wmee_mega_user_password', false ) );
			update_option( 'ai1wmke_mega_user_session', get_option( 'ai1wmee_mega_user_session', false ) );
			update_option( 'ai1wmke_mega_node_id', get_option( 'ai1wmee_mega_node_id', false ) );
			update_option( 'ai1wmke_mega_backups', get_option( 'ai1wmee_mega_backups', false ) );
			update_option( 'ai1wmke_mega_total', get_option( 'ai1wmee_mega_total', false ) );
			update_option( 'ai1wmke_mega_days', get_option( 'ai1wmee_mega_days', false ) );
			update_option( 'ai1wmke_mega_notify_toggle', get_option( 'ai1wmee_mega_notify_toggle', false ) );
			update_option( 'ai1wmke_mega_notify_error_toggle', get_option( 'ai1wmee_mega_notify_error_toggle', false ) );
			update_option( 'ai1wmke_mega_notify_error_subject', get_option( 'ai1wmee_mega_notify_error_subject', false ) );
			update_option( 'ai1wmke_mega_notify_email', get_option( 'ai1wmee_mega_notify_email', false ) );
			update_option( 'ai1wmke_mega_incremental', get_option( 'ai1wmee_mega_incremental', false ) );
		}

		// OneDrive
		if ( defined( 'AI1WMOE_PLUGIN_NAME' ) ) {
			update_option( 'ai1wmke_onedrive_cron_timestamp', get_option( 'ai1wmoe_onedrive_cron_timestamp', time() ) );
			update_option( 'ai1wmke_onedrive_cron', get_option( 'ai1wmoe_onedrive_cron', array() ) );
			update_option( 'ai1wmke_onedrive_token', get_option( 'ai1wmoe_onedrive_token', false ) );
			update_option( 'ai1wmke_onedrive_ssl', get_option( 'ai1wmoe_onedrive_ssl', false ) );
			update_option( 'ai1wmke_onedrive_folder_id', get_option( 'ai1wmoe_onedrive_folder_id', false ) );
			update_option( 'ai1wmke_onedrive_backups', get_option( 'ai1wmoe_onedrive_backups', false ) );
			update_option( 'ai1wmke_onedrive_total', get_option( 'ai1wmoe_onedrive_total', false ) );
			update_option( 'ai1wmke_onedrive_days', get_option( 'ai1wmoe_onedrive_days', false ) );
			update_option( 'ai1wmke_onedrive_file_chunk_size', get_option( 'ai1wmoe_onedrive_file_chunk_size', AI1WMKE_ONEDRIVE_FILE_CHUNK_SIZE ) );
			update_option( 'ai1wmke_onedrive_notify_toggle', get_option( 'ai1wmoe_onedrive_notify_toggle', false ) );
			update_option( 'ai1wmke_onedrive_notify_error_toggle', get_option( 'ai1wmoe_onedrive_notify_error_toggle', false ) );
			update_option( 'ai1wmke_onedrive_notify_error_subject', get_option( 'ai1wmoe_onedrive_notify_error_subject', false ) );
			update_option( 'ai1wmke_onedrive_notify_email', get_option( 'ai1wmoe_onedrive_notify_email', false ) );
			update_option( 'ai1wmke_onedrive_access_token', get_option( 'ai1wmoe_onedrive_access_token', false ) );
			update_option( 'ai1wmke_onedrive_access_token_expires_in', get_option( 'ai1wmoe_onedrive_access_token_expires_in', false ) );
			update_option( 'ai1wmke_onedrive_lock_mode', get_option( 'ai1wmoe_onedrive_lock_mode', false ) );
			update_option( 'ai1wmke_onedrive_incremental_folder_id', get_option( 'ai1wmoe_onedrive_incremental_folder_id', null ) );
			update_option( 'ai1wmke_onedrive_incremental', get_option( 'ai1wmoe_onedrive_incremental', false ) );
		}

		// pCloud
		if ( defined( 'AI1WMPE_PLUGIN_NAME' ) ) {
			update_option( 'ai1wmke_pcloud_cron_timestamp', get_option( 'ai1wmpe_pcloud_cron_timestamp', time() ) );
			update_option( 'ai1wmke_pcloud_cron', get_option( 'ai1wmpe_pcloud_cron', array() ) );
			update_option( 'ai1wmke_pcloud_hostname', get_option( 'ai1wmpe_pcloud_hostname', AI1WMKE_PCLOUD_API_ENDPOINT ) );
			update_option( 'ai1wmke_pcloud_token', get_option( 'ai1wmpe_pcloud_token', false ) );
			update_option( 'ai1wmke_pcloud_ssl', get_option( 'ai1wmpe_pcloud_ssl', false ) );
			update_option( 'ai1wmke_pcloud_folder_id', get_option( 'ai1wmpe_pcloud_folder_id', false ) );
			update_option( 'ai1wmke_pcloud_backups', get_option( 'ai1wmpe_pcloud_backups', false ) );
			update_option( 'ai1wmke_pcloud_total', get_option( 'ai1wmpe_pcloud_total', false ) );
			update_option( 'ai1wmke_pcloud_days', get_option( 'ai1wmpe_pcloud_days', false ) );
			update_option( 'ai1wmke_pcloud_file_chunk_size', get_option( 'ai1wmpe_pcloud_file_chunk_size', AI1WMKE_PCLOUD_FILE_CHUNK_SIZE ) );
			update_option( 'ai1wmke_pcloud_notify_toggle', get_option( 'ai1wmpe_pcloud_notify_toggle', false ) );
			update_option( 'ai1wmke_pcloud_notify_error_toggle', get_option( 'ai1wmpe_pcloud_notify_error_toggle', false ) );
			update_option( 'ai1wmke_pcloud_notify_error_subject', get_option( 'ai1wmpe_pcloud_notify_error_subject', false ) );
			update_option( 'ai1wmke_pcloud_notify_email', get_option( 'ai1wmpe_pcloud_notify_email', false ) );
			update_option( 'ai1wmke_pcloud_lock_mode', get_option( 'ai1wmpe_pcloud_lock_mode', false ) );
			update_option( 'ai1wmke_pcloud_incremental_folder_id', get_option( 'ai1wmpe_pcloud_incremental_folder_id', null ) );
			update_option( 'ai1wmke_pcloud_incremental', get_option( 'ai1wmpe_pcloud_incremental', false ) );
		}

		// S3 Client
		if ( defined( 'AI1WMNE_PLUGIN_NAME' ) ) {
			update_option( 'ai1wmke_s3_client_cron_timestamp', get_option( 'ai1wmne_s3_cron_timestamp', time() ) );
			update_option( 'ai1wmke_s3_client_cron', get_option( 'ai1wmne_s3_cron', array() ) );
			update_option( 'ai1wmke_s3_client_api_endpoint', get_option( 'ai1wmne_s3_api_endpoint', ai1wmke_aws_api_endpoint() ) );
			update_option( 'ai1wmke_s3_client_bucket_template', get_option( 'ai1wmne_s3_bucket_template', ai1wmke_aws_bucket_template() ) );
			update_option( 'ai1wmke_s3_client_access_key', get_option( 'ai1wmne_s3_access_key', ai1wmke_aws_access_key() ) );
			update_option( 'ai1wmke_s3_client_secret_key', get_option( 'ai1wmne_s3_secret_key', ai1wmke_aws_secret_key() ) );
			update_option( 'ai1wmke_s3_client_bucket_name', get_option( 'ai1wmne_s3_bucket_name', ai1wm_archive_bucket() ) );
			update_option( 'ai1wmke_s3_client_region_name', get_option( 'ai1wmne_s3_region_name', ai1wmke_aws_region_name( AI1WMKE_S3_CLIENT_REGION_NAME ) ) );
			update_option( 'ai1wmke_s3_client_https_protocol', get_option( 'ai1wmne_s3_https_protocol', true ) );
			update_option( 'ai1wmke_s3_client_storage_class', get_option( 'ai1wmne_s3_storage_class', AI1WMKE_S3_CLIENT_STORAGE_CLASS ) );
			update_option( 'ai1wmke_s3_client_encryption', get_option( 'ai1wmne_s3_encryption', false ) );
			update_option( 'ai1wmke_s3_client_folder_name', get_option( 'ai1wmne_s3_folder_name', '' ) );
			update_option( 'ai1wmke_s3_client_backups', get_option( 'ai1wmne_s3_backups', false ) );
			update_option( 'ai1wmke_s3_client_total', get_option( 'ai1wmne_s3_total', false ) );
			update_option( 'ai1wmke_s3_client_days', get_option( 'ai1wmne_s3_days', false ) );
			update_option( 'ai1wmke_s3_client_file_chunk_size', get_option( 'ai1wmne_s3_file_chunk_size', AI1WMKE_S3_CLIENT_FILE_CHUNK_SIZE ) );
			update_option( 'ai1wmke_s3_client_notify_toggle', get_option( 'ai1wmne_s3_notify_toggle', false ) );
			update_option( 'ai1wmke_s3_client_notify_error_toggle', get_option( 'ai1wmne_s3_notify_error_toggle', false ) );
			update_option( 'ai1wmke_s3_client_notify_error_subject', get_option( 'ai1wmne_s3_notify_error_subject', false ) );
			update_option( 'ai1wmke_s3_client_notify_email', get_option( 'ai1wmne_s3_notify_email', false ) );
			update_option( 'ai1wmke_s3_client_incremental', get_option( 'ai1wmne_s3_incremental', false ) );
		}

		// Amazon S3
		if ( defined( 'AI1WMSE_PLUGIN_NAME' ) ) {
			update_option( 'ai1wmke_s3_cron_timestamp', get_option( 'ai1wmse_s3_cron_timestamp', time() ) );
			update_option( 'ai1wmke_s3_cron', get_option( 'ai1wmse_s3_cron', array() ) );
			update_option( 'ai1wmke_s3_access_key', get_option( 'ai1wmse_s3_access_key', ai1wmke_aws_access_key() ) );
			update_option( 'ai1wmke_s3_secret_key', get_option( 'ai1wmse_s3_secret_key', ai1wmke_aws_secret_key() ) );
			update_option( 'ai1wmke_s3_bucket_name', get_option( 'ai1wmse_s3_bucket_name', ai1wm_archive_bucket() ) );
			update_option( 'ai1wmke_s3_region_name', get_option( 'ai1wmse_s3_region_name', ai1wmke_aws_region_name( AI1WMKE_S3_REGION_NAME ) ) );
			update_option( 'ai1wmke_s3_https_protocol', get_option( 'ai1wmse_s3_https_protocol', true ) );
			update_option( 'ai1wmke_s3_storage_class', get_option( 'ai1wmse_s3_storage_class', AI1WMKE_S3_STORAGE_CLASS ) );
			update_option( 'ai1wmke_s3_encryption', get_option( 'ai1wmse_s3_encryption', false ) );
			update_option( 'ai1wmke_s3_folder_name', get_option( 'ai1wmse_s3_folder_name', '' ) );
			update_option( 'ai1wmke_s3_backups', get_option( 'ai1wmse_s3_backups', false ) );
			update_option( 'ai1wmke_s3_total', get_option( 'ai1wmse_s3_total', false ) );
			update_option( 'ai1wmke_s3_days', get_option( 'ai1wmse_s3_days', false ) );
			update_option( 'ai1wmke_s3_file_chunk_size', get_option( 'ai1wmse_s3_file_chunk_size', AI1WMKE_S3_FILE_CHUNK_SIZE ) );
			update_option( 'ai1wmke_s3_notify_toggle', get_option( 'ai1wmse_s3_notify_toggle', false ) );
			update_option( 'ai1wmke_s3_notify_error_toggle', get_option( 'ai1wmse_s3_notify_error_toggle', false ) );
			update_option( 'ai1wmke_s3_notify_error_subject', get_option( 'ai1wmse_s3_notify_error_subject', false ) );
			update_option( 'ai1wmke_s3_notify_email', get_option( 'ai1wmse_s3_notify_email', false ) );
			update_option( 'ai1wmke_s3_incremental', get_option( 'ai1wmse_s3_incremental', false ) );
		}

		// Unlimited
		if ( defined( 'AI1WMUE_PLUGIN_NAME' ) ) {
			update_option( 'ai1wmke_pro_backups', get_option( 'ai1wmue_backups', false ) );
			update_option( 'ai1wmke_pro_total', get_option( 'ai1wmue_total', false ) );
			update_option( 'ai1wmke_pro_days', get_option( 'ai1wmue_days', false ) );
		}

		// Schedules
		if ( defined( 'AI1WMVE_PATH' ) ) {
			$wpdb->query( "INSERT INTO {$wpdb->options} (option_name, option_value) SELECT 'ai1wmke_schedule_events', REPLACE(o.option_value, 'Ai1wmve_Schedule_Event', 'Ai1wmke_Schedule_Event') FROM `{$wpdb->options}` AS o WHERE o.option_name = 'ai1wmve_schedule_events' AND NOT EXISTS (SELECT * FROM {$wpdb->options} WHERE `option_name` = 'ai1wmke_schedule_events')" );
		}

		// URL
		if ( defined( 'AI1WMLE_PLUGIN_NAME' ) ) {
			update_option( 'ai1wmke_url_file_chunk_size', get_option( 'ai1wmle_url_file_chunk_size', AI1WMKE_URL_FILE_CHUNK_SIZE ) );
		}

		// WebDAV
		if ( defined( 'AI1WMWE_PLUGIN_NAME' ) ) {
			update_option( 'ai1wmke_webdav_cron_timestamp', get_option( 'ai1wmwe_webdav_cron_timestamp', time() ) );
			update_option( 'ai1wmke_webdav_cron', get_option( 'ai1wmwe_webdav_cron', array() ) );
			update_option( 'ai1wmke_webdav_type', get_option( 'ai1wmwe_webdav_type', AI1WMKE_WEBDAV_TYPE ) );
			update_option( 'ai1wmke_webdav_hostname', get_option( 'ai1wmwe_webdav_hostname', false ) );
			update_option( 'ai1wmke_webdav_username', get_option( 'ai1wmwe_webdav_username', false ) );
			update_option( 'ai1wmke_webdav_password', get_option( 'ai1wmwe_webdav_password', false ) );
			update_option( 'ai1wmke_webdav_authentication', get_option( 'ai1wmwe_webdav_authentication', AI1WMKE_WEBDAV_AUTHENTICATION ) );
			update_option( 'ai1wmke_webdav_directory', get_option( 'ai1wmwe_webdav_directory', false ) );
			update_option( 'ai1wmke_webdav_port', get_option( 'ai1wmwe_webdav_port', AI1WMKE_WEBDAV_PORT ) );
			update_option( 'ai1wmke_webdav_connection', get_option( 'ai1wmwe_webdav_connection', false ) );
			update_option( 'ai1wmke_webdav_backups', get_option( 'ai1wmwe_webdav_backups', false ) );
			update_option( 'ai1wmke_webdav_total', get_option( 'ai1wmwe_webdav_total', false ) );
			update_option( 'ai1wmke_webdav_days', get_option( 'ai1wmwe_webdav_days', false ) );
			update_option( 'ai1wmke_webdav_file_chunk_size', get_option( 'ai1wmwe_webdav_file_chunk_size', AI1WMKE_WEBDAV_FILE_CHUNK_SIZE ) );
			update_option( 'ai1wmke_webdav_notify_toggle', get_option( 'ai1wmwe_webdav_notify_toggle', false ) );
			update_option( 'ai1wmke_webdav_notify_error_toggle', get_option( 'ai1wmwe_webdav_notify_error_toggle', false ) );
			update_option( 'ai1wmke_webdav_notify_error_subject', get_option( 'ai1wmwe_webdav_notify_error_subject', false ) );
			update_option( 'ai1wmke_webdav_notify_email', get_option( 'ai1wmwe_webdav_notify_email', false ) );
		}
	}

	public static function sync_extensions() {
		$pro_extensions = get_option( AI1WMKE_PRO_EXTENSIONS, array() );

		// Microsoft Azure Storage
		if ( defined( 'AI1WMZE_PLUGIN_NAME' ) ) {
			$pro_extensions[ AI1WMZE_PLUGIN_SHORT ] = 1;
		}

		// Backblaze B2
		if ( defined( 'AI1WMAE_PLUGIN_NAME' ) ) {
			$pro_extensions[ AI1WMAE_PLUGIN_SHORT ] = 1;
		}

		// Box
		if ( defined( 'AI1WMBE_PLUGIN_NAME' ) ) {
			$pro_extensions[ AI1WMBE_PLUGIN_SHORT ] = 1;
		}

		// DigitalOcean Spaces
		if ( defined( 'AI1WMIE_PLUGIN_NAME' ) ) {
			$pro_extensions[ AI1WMIE_PLUGIN_SHORT ] = 1;
		}

		// Direct
		if ( defined( 'AI1WMXE_PLUGIN_NAME' ) ) {
			$pro_extensions[ AI1WMXE_PLUGIN_SHORT ] = 1;
		}

		// Dropbox
		if ( defined( 'AI1WMDE_PLUGIN_NAME' ) ) {
			$pro_extensions[ AI1WMDE_PLUGIN_SHORT ] = 1;
		}

		// FTP
		if ( defined( 'AI1WMFE_PLUGIN_NAME' ) ) {
			$pro_extensions[ AI1WMFE_PLUGIN_SHORT ] = 1;
		}

		// Google Cloud Storage
		if ( defined( 'AI1WMCE_PLUGIN_NAME' ) ) {
			$pro_extensions[ AI1WMCE_PLUGIN_SHORT ] = 1;
		}

		// Google Drive
		if ( defined( 'AI1WMGE_PLUGIN_NAME' ) ) {
			$pro_extensions[ AI1WMGE_PLUGIN_SHORT ] = 1;
		}

		// Amazon Glacier
		if ( defined( 'AI1WMRE_PLUGIN_NAME' ) ) {
			$pro_extensions[ AI1WMRE_PLUGIN_SHORT ] = 1;
		}

		// Mega
		if ( defined( 'AI1WMEE_PLUGIN_NAME' ) ) {
			$pro_extensions[ AI1WMEE_PLUGIN_SHORT ] = 1;
		}

		// OneDrive
		if ( defined( 'AI1WMOE_PLUGIN_NAME' ) ) {
			$pro_extensions[ AI1WMOE_PLUGIN_SHORT ] = 1;
		}

		// pCloud
		if ( defined( 'AI1WMPE_PLUGIN_NAME' ) ) {
			$pro_extensions[ AI1WMPE_PLUGIN_SHORT ] = 1;
		}

		// S3 Client
		if ( defined( 'AI1WMNE_PLUGIN_NAME' ) ) {
			$pro_extensions[ AI1WMNE_PLUGIN_SHORT ] = 1;
		}

		// Amazon S3
		if ( defined( 'AI1WMSE_PLUGIN_NAME' ) ) {
			$pro_extensions[ AI1WMSE_PLUGIN_SHORT ] = 1;
		}

		// URL
		if ( defined( 'AI1WMLE_PLUGIN_NAME' ) ) {
			$pro_extensions[ AI1WMLE_PLUGIN_SHORT ] = 1;
		}

		// WebDAV
		if ( defined( 'AI1WMWE_PLUGIN_NAME' ) ) {
			$pro_extensions[ AI1WMWE_PLUGIN_SHORT ] = 1;
		}

		// Deactivate old extensions
		deactivate_plugins(
			array(
				'all-in-one-wp-migration-azure-storage-extension/all-in-one-wp-migration-azure-storage-extension.php',
				'all-in-one-wp-migration-b2-extension/all-in-one-wp-migration-b2-extension.php',
				'all-in-one-wp-migration-box-extension/all-in-one-wp-migration-box-extension.php',
				'all-in-one-wp-migration-digitalocean-extension/all-in-one-wp-migration-digitalocean-extension.php',
				'all-in-one-wp-migration-direct-extension/all-in-one-wp-migration-direct-extension.php',
				'all-in-one-wp-migration-dropbox-extension/all-in-one-wp-migration-dropbox-extension.php',
				'all-in-one-wp-migration-file-extension/all-in-one-wp-migration-file-extension.php',
				'all-in-one-wp-migration-ftp-extension/all-in-one-wp-migration-ftp-extension.php',
				'all-in-one-wp-migration-gcloud-storage-extension/all-in-one-wp-migration-gcloud-storage-extension.php',
				'all-in-one-wp-migration-gdrive-extension/all-in-one-wp-migration-gdrive-extension.php',
				'all-in-one-wp-migration-glacier-extension/all-in-one-wp-migration-glacier-extension.php',
				'all-in-one-wp-migration-mega-extension/all-in-one-wp-migration-mega-extension.php',
				'all-in-one-wp-migration-onedrive-extension/all-in-one-wp-migration-onedrive-extension.php',
				'all-in-one-wp-migration-pcloud-extension/all-in-one-wp-migration-pcloud-extension.php',
				'all-in-one-wp-migration-s3-client-extension/all-in-one-wp-migration-s3-client-extension.php',
				'all-in-one-wp-migration-s3-extension/all-in-one-wp-migration-s3-extension.php',
				'all-in-one-wp-migration-unlimited-extension/all-in-one-wp-migration-unlimited-extension.php',
				'all-in-one-wp-migration-url-extension/all-in-one-wp-migration-url-extension.php',
				'all-in-one-wp-migration-webdav-extension/all-in-one-wp-migration-webdav-extension.php',
			)
		);

		// Enable pro extensions
		update_option( AI1WMKE_PRO_EXTENSIONS, $pro_extensions );
	}
}
