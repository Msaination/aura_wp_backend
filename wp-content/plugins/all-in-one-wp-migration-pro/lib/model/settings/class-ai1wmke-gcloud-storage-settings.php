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

class Ai1wmke_GCloud_Storage_Settings {

	public function revoke() {
		// Remove token option
		delete_option( 'ai1wmke_gcloud_storage_token' );
		delete_option( 'ai1wmke_gcloud_storage_access_token' );
		delete_option( 'ai1wmke_gcloud_storage_access_token_expires_in' );

		// Remove cron option
		delete_option( 'ai1wmke_gcloud_storage_cron' );

		// Reset cron schedules
		Ai1wm_Cron::clear( 'ai1wmke_gcloud_storage_hourly_export' );
		Ai1wm_Cron::clear( 'ai1wmke_gcloud_storage_daily_export' );
		Ai1wm_Cron::clear( 'ai1wmke_gcloud_storage_weekly_export' );
		Ai1wm_Cron::clear( 'ai1wmke_gcloud_storage_monthly_export' );
	}

	public function get_last_backup_date( $last_backup_timestamp ) {
		if ( $last_backup_timestamp ) {
			$last_backup_date = get_date_from_gmt( date( 'Y-m-d H:i:s', $last_backup_timestamp ), 'F j, Y g:i a' );
		} else {
			$last_backup_date = __( 'None', AI1WMKE_PLUGIN_NAME );
		}

		return $last_backup_date;
	}

	public function get_next_backup_date( $schedules ) {
		$future_backup_timestamps = array();

		// Get next scheduled event
		foreach ( $schedules as $schedule ) {
			$future_backup_timestamps[] = wp_next_scheduled( "ai1wmke_gcloud_storage_{$schedule}_export", array( $this->get_cron_args() ) );
		}

		sort( $future_backup_timestamps );

		if ( isset( $future_backup_timestamps[0] ) ) {
			$next_backup_date = get_date_from_gmt( date( 'Y-m-d H:i:s', $future_backup_timestamps[0] ), 'F j, Y g:i a' );
		} else {
			$next_backup_date = __( 'None', AI1WMKE_PLUGIN_NAME );
		}

		return $next_backup_date;
	}

	/**
	 * Get project list
	 *
	 * @return array
	 */
	public function get_projects() {
		$gcloud = new Ai1wmke_GCloud_Storage_Client(
			get_option( 'ai1wmke_gcloud_storage_token' ),
			get_option( 'ai1wmke_gcloud_storage_ssl', true )
		);

		return $gcloud->get_projects();
	}

	/**
	 * Create project
	 *
	 * @param  string  $project_id Project ID
	 * @return boolean
	 */
	public function create_project( $project_id ) {
		$gcloud = new Ai1wmke_GCloud_Storage_Client(
			get_option( 'ai1wmke_gcloud_storage_token' ),
			get_option( 'ai1wmke_gcloud_storage_ssl', true )
		);

		// Create project if does not exist
		if ( $gcloud->is_project_available( $project_id ) ) {
			return false;
		}

		return $gcloud->create_project( $project_id );
	}

	/**
	 * Get bucket list
	 *
	 * @return array
	 */
	public function get_buckets() {
		$gcloud = new Ai1wmke_GCloud_Storage_Client(
			get_option( 'ai1wmke_gcloud_storage_token' ),
			get_option( 'ai1wmke_gcloud_storage_ssl', true )
		);

		return $gcloud->get_buckets( $this->get_project_id() );
	}

	/**
	 * Create bucket
	 *
	 * @param  string  $bucket_name Bucket name
	 * @return boolean
	 */
	public function create_bucket( $bucket_name ) {
		$gcloud = new Ai1wmke_GCloud_Storage_Client(
			get_option( 'ai1wmke_gcloud_storage_token' ),
			get_option( 'ai1wmke_gcloud_storage_ssl', true )
		);

		// Create bucket if does not exist
		if ( $gcloud->is_bucket_available( $bucket_name ) ) {
			return false;
		}

		return $gcloud->create_bucket( $bucket_name, $this->get_project_id(), $this->get_storage_class() );
	}

	public function set_cron_timestamp( $timestamp ) {
		return update_option( 'ai1wmke_gcloud_storage_cron_timestamp', $timestamp );
	}

	public function get_cron_timestamp() {
		return get_option( 'ai1wmke_gcloud_storage_cron_timestamp', time() );
	}

	/**
	 * Set cron schedules
	 *
	 * @param  array   $schedules List of schedules
	 * @return boolean
	 */
	public function set_cron( $schedules ) {
		ai1wm_cache_flush();

		// Reset cron schedules
		Ai1wm_Cron::clear( 'ai1wmke_gcloud_storage_hourly_export' );
		Ai1wm_Cron::clear( 'ai1wmke_gcloud_storage_daily_export' );
		Ai1wm_Cron::clear( 'ai1wmke_gcloud_storage_weekly_export' );
		Ai1wm_Cron::clear( 'ai1wmke_gcloud_storage_monthly_export' );

		// Update cron schedules
		foreach ( $schedules as $schedule ) {
			Ai1wm_Cron::add( "ai1wmke_gcloud_storage_{$schedule}_export", $schedule, $this->get_cron_timestamp(), array( $this->get_cron_args() ) );
		}

		return update_option( 'ai1wmke_gcloud_storage_cron', $schedules );
	}

	public function get_cron() {
		return get_option( 'ai1wmke_gcloud_storage_cron', array() );
	}

	public function init_cron() {
		foreach ( $this->get_cron() as $schedule ) {
			if ( ! Ai1wm_Cron::exists( "ai1wmke_gcloud_storage_{$schedule}_export", array( $this->get_cron_args() ) ) ) {
				Ai1wm_Cron::clear( "ai1wmke_gcloud_storage_{$schedule}_export" );
				Ai1wm_Cron::add( "ai1wmke_gcloud_storage_{$schedule}_export", $schedule, $this->get_cron_timestamp(), array( $this->get_cron_args() ) );
			}
		}
	}

	public function get_cron_args() {
		if ( $this->get_incremental() ) {
			return array( 'secret_key' => get_option( AI1WM_SECRET_KEY ), 'incremental' => 1, 'gcloud-storage' => 1 );
		}

		return array( 'secret_key' => get_option( AI1WM_SECRET_KEY ), 'gcloud-storage' => 1 );
	}

	public function set_token( $token ) {
		return update_option( 'ai1wmke_gcloud_storage_token', $token );
	}

	public function get_token() {
		return get_option( 'ai1wmke_gcloud_storage_token', false );
	}

	public function set_ssl( $mode ) {
		return update_option( 'ai1wmke_gcloud_storage_ssl', $mode );
	}

	public function get_ssl() {
		return get_option( 'ai1wmke_gcloud_storage_ssl', false );
	}

	public function set_project_id( $project_id ) {
		return update_option( 'ai1wmke_gcloud_storage_project_id', $project_id );
	}

	public function get_project_id() {
		return get_option( 'ai1wmke_gcloud_storage_project_id', ai1wm_archive_project() );
	}

	public function set_bucket_name( $bucket_name ) {
		return update_option( 'ai1wmke_gcloud_storage_bucket_name', $bucket_name );
	}

	public function get_bucket_name() {
		return get_option( 'ai1wmke_gcloud_storage_bucket_name', ai1wm_archive_bucket() );
	}

	public function set_folder_name( $folder_name ) {
		return update_option( 'ai1wmke_gcloud_storage_folder_name', $folder_name );
	}

	public function get_folder_name() {
		return get_option( 'ai1wmke_gcloud_storage_folder_name', '' );
	}

	public function set_file_chunk_size( $file_chunk_size ) {
		return update_option( 'ai1wmke_gcloud_storage_file_chunk_size', $file_chunk_size );
	}

	public function get_file_chunk_size() {
		return get_option( 'ai1wmke_gcloud_storage_file_chunk_size', false );
	}

	public function set_storage_class( $storage_class ) {
		return update_option( 'ai1wmke_gcloud_storage_class', $storage_class );
	}

	public function get_storage_class() {
		return get_option( 'ai1wmke_gcloud_storage_class', AI1WMKE_GCLOUD_STORAGE_CLASS );
	}

	public function set_backups( $number ) {
		return update_option( 'ai1wmke_gcloud_storage_backups', $number );
	}

	public function get_backups() {
		return get_option( 'ai1wmke_gcloud_storage_backups', false );
	}

	public function set_total( $size ) {
		return update_option( 'ai1wmke_gcloud_storage_total', $size );
	}

	public function get_total() {
		return get_option( 'ai1wmke_gcloud_storage_total', false );
	}

	public function set_days( $days ) {
		return update_option( 'ai1wmke_gcloud_storage_days', $days );
	}

	public function get_days() {
		return get_option( 'ai1wmke_gcloud_storage_days', false );
	}

	public function set_notify_ok_toggle( $toggle ) {
		return update_option( 'ai1wmke_gcloud_storage_notify_toggle', $toggle );
	}

	public function get_notify_ok_toggle() {
		return get_option( 'ai1wmke_gcloud_storage_notify_toggle', false );
	}

	public function set_notify_error_toggle( $toggle ) {
		return update_option( 'ai1wmke_gcloud_storage_notify_error_toggle', $toggle );
	}

	public function get_notify_error_toggle() {
		return get_option( 'ai1wmke_gcloud_storage_notify_error_toggle', false );
	}

	public function set_notify_error_subject( $subject ) {
		return update_option( 'ai1wmke_gcloud_storage_notify_error_subject', $subject );
	}

	public function get_notify_error_subject() {
		return get_option( 'ai1wmke_gcloud_storage_notify_error_subject', sprintf( __( 'Backup to Google Cloud Storage has failed (%s)', AI1WMKE_PLUGIN_NAME ), parse_url( site_url(), PHP_URL_HOST ) . parse_url( site_url(), PHP_URL_PATH ) ) );
	}

	public function set_notify_email( $email ) {
		return update_option( 'ai1wmke_gcloud_storage_notify_email', $email );
	}

	public function get_notify_email() {
		return get_option( 'ai1wmke_gcloud_storage_notify_email', false );
	}

	public function set_access_token( $access_token ) {
		return update_option( 'ai1wmke_gcloud_storage_access_token', $access_token );
	}

	public function get_access_token() {
		return get_option( 'ai1wmke_gcloud_storage_access_token', false );
	}

	public function set_access_token_expires_in( $expires_in ) {
		return update_option( 'ai1wmke_gcloud_storage_access_token_expires_in', $expires_in );
	}

	public function get_access_token_expires_in() {
		return get_option( 'ai1wmke_gcloud_storage_access_token_expires_in', false );
	}

	public function set_incremental( $incremental ) {
		return update_option( 'ai1wmke_gcloud_storage_incremental', $incremental );
	}

	public function get_incremental() {
		return get_option( 'ai1wmke_gcloud_storage_incremental', false );
	}
}
