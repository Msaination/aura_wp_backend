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

/**
 * Check whether extension is enabled/disabled
 *
 * @param  string  $short_name      Extension short name
 * @param  boolean $allow_multisite Allow multisite mode
 * @return boolean
 */
function ai1wmke_is_enabled( $short_name, $allow_multisite = true ) {
	if ( empty( $allow_multisite ) ) {
		if ( is_multisite() ) {
			return false;
		}
	}

	// All extensions enabled
	return true;
}

/**
 * Check whether export/import is running
 *
 * @param  string $short_name Extension short name
 * @return boolean
 */
function ai1wmke_is_running( $short_name ) {
	if ( isset( $_GET[ $short_name ] ) || isset( $_POST[ $short_name ] ) ) {
		return true;
	}

	return false;
}

/**
 * Check whether export/import is incremental
 *
 * @param  string $short_name Extension short name
 * @return boolean
 */
function ai1wmke_is_incremental( $short_name ) {
	if ( isset( $_GET[ $short_name ], $_GET['incremental'] ) || isset( $_POST[ $short_name ], $_POST['incremental'] ) ) {
		return true;
	}

	return false;
}

/**
 * Check whether current user is admin
 *
 * @param  string $short_name Extension short name
 * @return boolean
 */
function ai1wmke_is_admin( $short_name ) {
	switch ( $short_name ) {
		case 'b2':
			return current_user_can( 'ai1wmke_b2_admin' ) || ! get_option( 'ai1wmke_b2_lock_mode', false );

		case 'gdrive':
			return current_user_can( 'ai1wmke_gdrive_admin' ) || ! get_option( 'ai1wmke_gdrive_lock_mode', false );

		case 'glacier':
			return current_user_can( 'ai1wmke_glacier_admin' ) || ! get_option( 'ai1wmke_glacier_lock_mode', false );

		case 'mega':
			return current_user_can( 'ai1wmke_mega_admin' ) || ! get_option( 'ai1wmke_mega_lock_mode', false );

		case 'onedrive':
			return current_user_can( 'ai1wmke_onedrive_admin' ) || ! get_option( 'ai1wmke_onedrive_lock_mode', false );

		case 'pcloud':
			return current_user_can( 'ai1wmke_pcloud_admin' ) || ! get_option( 'ai1wmke_pcloud_lock_mode', false );

		default:
			return true;
	}
}

/**
 * Get root folder details
 *
 * @param  string $short_name Extension short name
 * @return mixed
 */
function ai1wmke_get_root_folder( $short_name ) {
	if ( ai1wmke_is_admin( $short_name ) ) {
		return null;
	}

	switch ( $short_name ) {
		case 'b2':
			return array(
				'bucket_id'   => get_option( 'ai1wmke_b2_bucket_id', null ),
				'bucket_name' => get_option( 'ai1wmke_b2_bucket_name', null ),
				'folder_name' => get_option( 'ai1wmke_b2_folder_name', null ),
			);

		case 'gdrive':
			return array(
				'folder_id'     => get_option( 'ai1wmke_gdrive_folder_id', null ),
				'team_drive_id' => get_option( 'ai1wmke_gdrive_team_drive_id', AI1WMKE_GDRIVE_TEAM_DRIVE_ID ),
			);

		case 'glacier':
			return array(
				'region_name' => get_option( 'ai1wmke_glacier_region_name', null ),
				'vault_name'  => get_option( 'ai1wmke_glacier_vault_name', null ),
			);

		case 'mega':
			return array(
				'node_id' => get_option( 'ai1wmke_mega_node_id', null ),
			);

		case 'onedrive':
			return array(
				'folder_id' => get_option( 'ai1wmke_onedrive_folder_id', null ),
			);

		case 'pcloud':
			return array(
				'folder_id' => get_option( 'ai1wmke_pcloud_folder_id', null ),
			);

	}
}

/**
 * Get reset-db-backup.json absolute path
 *
 * @return string
 */
function ai1wmke_reset_db_backup_path() {
	return AI1WM_STORAGE_PATH . DIRECTORY_SEPARATOR . AI1WMKE_RESET_DB_BACKUP;
}

/**
 * Get AWS API endpoint environment variable
 *
 * @return string
 */
function ai1wmke_aws_api_endpoint() {
	if ( defined( 'AWS_API_ENDPOINT' ) ) {
		return constant( 'AWS_API_ENDPOINT' );
	}

	return getenv( 'AWS_API_ENDPOINT' );
}

/**
 * Get AWS bucket template environment variable
 *
 * @return string
 */
function ai1wmke_aws_bucket_template() {
	if ( defined( 'AWS_BUCKET_TEMPLATE' ) ) {
		return constant( 'AWS_BUCKET_TEMPLATE' );
	}

	return getenv( 'AWS_BUCKET_TEMPLATE' );
}

/**
 * Get AWS access key environment variable
 *
 * @return string
 */
function ai1wmke_aws_access_key() {
	if ( defined( 'AWS_ACCESS_KEY_ID' ) ) {
		return constant( 'AWS_ACCESS_KEY_ID' );
	}

	return getenv( 'AWS_ACCESS_KEY_ID' );
}

/**
 * Get AWS secret key environment variable
 *
 * @return string
 */
function ai1wmke_aws_secret_key() {
	if ( defined( 'AWS_SECRET_ACCESS_KEY' ) ) {
		return constant( 'AWS_SECRET_ACCESS_KEY' );
	}

	return getenv( 'AWS_SECRET_ACCESS_KEY' );
}

/**
 * Get AWS region name environment variable
 *
 * @param  string $default Region name
 * @return string
 */
function ai1wmke_aws_region_name( $default = null ) {
	if ( defined( 'AWS_DEFAULT_REGION' ) ) {
		return constant( 'AWS_DEFAULT_REGION' );
	}

	if ( getenv( 'AWS_DEFAULT_REGION' ) ) {
		return getenv( 'AWS_DEFAULT_REGION' );
	}

	return $default;
}

/**
 * Raise number into power
 * Fix for PHP (8.4.1, 8.3.14 and 8.2.26) bug with gmp_pow
 *
 * @param $num
 * @param $exponent
 *
 * @return \GMP|mixed|resource
 */
function ai1wmke_gmp_pow( $num, $exponent ) {
	$buggy_versions = array( 80401, 80314, 80226 );

	if ( in_array( PHP_VERSION_ID, $buggy_versions, true ) ) {
		return pow( $num, $exponent );
	}

	return gmp_pow( $num, $exponent );
}
