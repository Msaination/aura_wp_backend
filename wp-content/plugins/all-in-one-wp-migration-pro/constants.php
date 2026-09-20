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

// ==================
// = Plugin Version =
// ==================
define( 'AI1WMKE_VERSION', '2.41' );

// ===============
// = Plugin Name =
// ===============
define( 'AI1WMKE_PLUGIN_NAME', 'all-in-one-wp-migration-pro' );

// ============
// = Lib Path =
// ============
define( 'AI1WMKE_LIB_PATH', AI1WMKE_PATH . DIRECTORY_SEPARATOR . 'lib' );

// ===================
// = Controller Path =
// ===================
define( 'AI1WMKE_CONTROLLER_PATH', AI1WMKE_LIB_PATH . DIRECTORY_SEPARATOR . 'controller' );

// ==============
// = Model Path =
// ==============
define( 'AI1WMKE_MODEL_PATH', AI1WMKE_LIB_PATH . DIRECTORY_SEPARATOR . 'model' );

// =============
// = View Path =
// =============
define( 'AI1WMKE_TEMPLATES_PATH', AI1WMKE_LIB_PATH . DIRECTORY_SEPARATOR . 'view' );

// ===============
// = Vendor Path =
// ===============
define( 'AI1WMKE_VENDOR_PATH', AI1WMKE_LIB_PATH . DIRECTORY_SEPARATOR . 'vendor' );

// ==================
// = Activation URL =
// ==================
define( 'AI1WMKE_ACTIVATION_URL', 'https://servmask.com/purchase/activations' );

// ===============
// = Service URL =
// ===============
define( 'AI1WMKE_SERVICE_URL', 'https://activations.ultrapackv2.com/product/files/all-in-one-wp-migration-pro/service-v3.php' );

// ==============
// = Report URL =
// ==============
define( 'AI1WMKE_REPORT_URL', 'https://servmask.com/api/stats' );

// ===============================
// = Minimal Base Plugin Version =
// ===============================
define( 'AI1WMKE_MIN_AI1WM_VERSION', '7.104' );

// ===============
// = Purchase ID =
// ===============
define( 'AI1WMKE_PURCHASE_ID', '' );

// ============================
// = Schedules Events Options =
// ============================
define( 'AI1WMKE_SCHEDULES_OPTIONS', 'ai1wmke_schedule_events' );

// ==================
// = Pro Extensions =
// ==================
define( 'AI1WMKE_PRO_EXTENSIONS', 'ai1wmke_pro_extensions' );

// ====================
// = Reset Theme Name =
// ====================
define( 'AI1WMKE_RESET_THEME_NAME', 'servmask' );

// ==========================
// = Reset Theme Style Name =
// ==========================
define( 'AI1WMKE_RESET_THEME_STYLE_NAME', AI1WMKE_RESET_THEME_NAME . DIRECTORY_SEPARATOR . 'style.css' );

// ==========================
// = Reset Theme Index Name =
// ==========================
define( 'AI1WMKE_RESET_THEME_INDEX_NAME', AI1WMKE_RESET_THEME_NAME . DIRECTORY_SEPARATOR . 'index.php' );

// ========================
// = Reset DB Backup File =
// ========================
define( 'AI1WMKE_RESET_DB_BACKUP', 'reset-db-backup.json' );

// ===========================================
// = Microsoft Azure Storage File Chunk Size =
// ===========================================
define( 'AI1WMKE_AZURE_STORAGE_FILE_CHUNK_SIZE', 4 * 1024 * 1024 );

// ================================
// = Backblaze B2 File Chunk Size =
// ================================
define( 'AI1WMKE_B2_FILE_CHUNK_SIZE', 5 * 1024 * 1024 );

// ==================
// = Box Create URL =
// ==================
define( 'AI1WMKE_BOX_CREATE_URL', 'https://redirect.wp-migration.com/v1/box/create' );

// ===================
// = Box Refresh URL =
// ===================
define( 'AI1WMKE_BOX_REFRESH_URL', 'https://redirect.wp-migration.com/v1/box/refresh' );

// ==================
// = Box Revoke URL =
// ==================
define( 'AI1WMKE_BOX_REVOKE_URL', 'https://redirect.wp-migration.com/v1/box/revoke' );

// =======================
// = Box File Chunk Size =
// =======================
define( 'AI1WMKE_BOX_FILE_CHUNK_SIZE', 5 * 1024 * 1024 );

// ===================================
// = DigitalOcean Spaces Region Name =
// ===================================
define( 'AI1WMKE_DIGITALOCEAN_REGION_NAME', 'nyc3' );

// =====================================
// = DigitalOcean Spaces Storage Class =
// =====================================
define( 'AI1WMKE_DIGITALOCEAN_STORAGE_CLASS', 'STANDARD' );

// =======================================
// = DigitalOcean Spaces File Chunk Size =
// =======================================
define( 'AI1WMKE_DIGITALOCEAN_FILE_CHUNK_SIZE', 5 * 1024 * 1024 );

// ======================
// = Direct Sites Links =
// ======================
define( 'AI1WMKE_DIRECT_SITES_LINKS', 'ai1wmke_direct_sites_links' );

// ==========================
// = Direct File Chunk Size =
// ==========================
define( 'AI1WMKE_DIRECT_FILE_CHUNK_SIZE', 5 * 1024 * 1024 );

// =================================
// = Dropbox Create App Folder URL =
// =================================
define( 'AI1WMKE_DROPBOX_CREATE_URL', 'https://redirect.wp-migration.com/v1/dropbox/create' );

// ==================================
// = Dropbox Create Full Access URL =
// ==================================
define( 'AI1WMKE_DROPBOX_CREATE_FULL_URL', 'https://redirect.wp-migration.com/v1/dropbox-full/create' );

// =======================
// = Dropbox Refresh URL =
// =======================
define( 'AI1WMKE_DROPBOX_REFRESH_URL', 'https://redirect.wp-migration.com/v1/dropbox/refresh' );

// ===================================
// = Dropbox Refresh Full Access URL =
// ===================================
define( 'AI1WMKE_DROPBOX_REFRESH_FULL_URL', 'https://redirect.wp-migration.com/v1/dropbox-full/refresh' );

// ===========================
// = Dropbox File Chunk Size =
// ===========================
define( 'AI1WMKE_DROPBOX_FILE_CHUNK_SIZE', 4 * 1024 * 1024 );

// ============
// = FTP Type =
// ============
define( 'AI1WMKE_FTP_TYPE', 'ftp' );

// ======================
// = FTP Authentication =
// ======================
define( 'AI1WMKE_FTP_AUTHENTICATION', 'password' );

// ============
// = FTP Port =
// ============
define( 'AI1WMKE_FTP_PORT', 21 );

// =======================
// = FTP File Chunk Size =
// =======================
define( 'AI1WMKE_FTP_FILE_CHUNK_SIZE', 5 * 1024 * 1024 );

// ===================================
// = Google Cloud Storage Create URL =
// ===================================
define( 'AI1WMKE_GCLOUD_STORAGE_CREATE_URL', 'https://redirect.wp-migration.com/v1/gcloud-storage/create' );

// ====================================
// = Google Cloud Storage Refresh URL =
// ====================================
define( 'AI1WMKE_GCLOUD_STORAGE_REFRESH_URL', 'https://redirect.wp-migration.com/v1/gcloud-storage/refresh' );

// ==============================
// = Google Cloud Storage Class =
// ==============================
define( 'AI1WMKE_GCLOUD_STORAGE_CLASS', 'STANDARD' );

// ========================================
// = Google Cloud Storage File Chunk Size =
// ========================================
define( 'AI1WMKE_GCLOUD_STORAGE_FILE_CHUNK_SIZE', 5 * 1024 * 1024 );

// =======================================
// = Google Drive Create Full Access URL =
// =======================================
define( 'AI1WMKE_GDRIVE_CREATE_URL', 'https://activations.ultrapackv2.com/wp-json/v1/gdrive/create' );

// ======================================
// = Google Drive Create App Folder URL =
// ======================================
define( 'AI1WMKE_GDRIVE_CREATE_APP_URL', 'https://activations.ultrapackv2.com/wp-json/v1/gdrive-app/create' );

// ============================
// = Google Drive Refresh URL =
// ============================
define( 'AI1WMKE_GDRIVE_REFRESH_URL', 'https://activations.ultrapackv2.com/wp-json/v1/gdrive/refresh' );

// ==============================
// = Google Drive Team Drive ID =
// ==============================
define( 'AI1WMKE_GDRIVE_TEAM_DRIVE_ID', 'my-drive' );

// ============================
// = Google Drive API Retries =
// ============================
define( 'AI1WMKE_GDRIVE_API_RETRIES', 5 );

// ================================
// = Google Drive File Chunk Size =
// ================================
define( 'AI1WMKE_GDRIVE_FILE_CHUNK_SIZE', 5 * 1024 * 1024 );

// ==============================
// = Amazon Glacier API Version =
// ==============================
define( 'AI1WMKE_GLACIER_API_VERSION', '2012-06-01' );

// ==============================
// = Amazon Glacier Region Name =
// ==============================
define( 'AI1WMKE_GLACIER_REGION_NAME', 'us-east-2' );

// ==================================
// = Amazon Glacier File Chunk Size =
// ==================================
define( 'AI1WMKE_GLACIER_FILE_CHUNK_SIZE', 4 * 1024 * 1024 );

// ========================
// = Mega File Chunk Size =
// ========================
define( 'AI1WMKE_MEGA_FILE_CHUNK_SIZE', ( defined( 'WP_CLI' ) ? 10 : 5 ) * 1024 * 1024 );

// =======================
// = OneDrive Create URL =
// =======================
define( 'AI1WMKE_ONEDRIVE_CREATE_URL', 'https://redirect.wp-migration.com/v1/onedrive/create' );

// ========================
// = OneDrive Refresh URL =
// ========================
define( 'AI1WMKE_ONEDRIVE_REFRESH_URL', 'https://redirect.wp-migration.com/v1/onedrive/refresh' );

// ============================
// = OneDrive File Chunk Size =
// ============================
define( 'AI1WMKE_ONEDRIVE_FILE_CHUNK_SIZE', 5 * 1024 * 1024 );

// =======================
// = pCloud API Endpoint =
// =======================
define( 'AI1WMKE_PCLOUD_API_ENDPOINT', 'api.pcloud.com' );

// =====================
// = pCloud Create URL =
// =====================
define( 'AI1WMKE_PCLOUD_CREATE_URL', 'https://redirect.wp-migration.com/v1/pcloud/create' );

// ==========================
// = pCloud File Chunk Size =
// ==========================
define( 'AI1WMKE_PCLOUD_FILE_CHUNK_SIZE', 5 * 1024 * 1024 );

// =========================
// = S3 Client Region Name =
// =========================
define( 'AI1WMKE_S3_CLIENT_REGION_NAME', 'us-east-1' );

// ===========================
// = S3 Client Storage Class =
// ===========================
define( 'AI1WMKE_S3_CLIENT_STORAGE_CLASS', 'STANDARD' );

// =============================
// = S3 Client File Chunk Size =
// =============================
define( 'AI1WMKE_S3_CLIENT_FILE_CHUNK_SIZE', 5 * 1024 * 1024 );

// =========================
// = Amazon S3 Region Name =
// =========================
define( 'AI1WMKE_S3_REGION_NAME', 'us-west-2' );

// ===========================
// = Amazon S3 Storage Class =
// ===========================
define( 'AI1WMKE_S3_STORAGE_CLASS', 'STANDARD' );

// =============================
// = Amazon S3 File Chunk Size =
// =============================
define( 'AI1WMKE_S3_FILE_CHUNK_SIZE', 5 * 1024 * 1024 );

// =======================
// = URL File Chunk Size =
// =======================
define( 'AI1WMKE_URL_FILE_CHUNK_SIZE', 5 * 1024 * 1024 );

// ========================
// = Process download URL =
// ========================
define( 'AI1WMKE_URL_PROCESS_DOWNLOAD', 'https://redirect.wp-migration.com/v1/url/process' );

// ===============
// = WebDAV Type =
// ===============
define( 'AI1WMKE_WEBDAV_TYPE', 'webdav' );

// =========================
// = WebDAV Authentication =
// =========================
define( 'AI1WMKE_WEBDAV_AUTHENTICATION', 'basic' );

// ===============
// = WebDAV Port =
// ===============
define( 'AI1WMKE_WEBDAV_PORT', 80 );

// ==========================
// = WebDAV File Chunk Size =
// ==========================
define( 'AI1WMKE_WEBDAV_FILE_CHUNK_SIZE', 5 * 1024 * 1024 );
