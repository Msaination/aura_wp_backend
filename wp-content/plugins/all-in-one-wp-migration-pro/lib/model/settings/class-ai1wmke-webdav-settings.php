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

class Ai1wmke_WebDAV_Settings {

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
			$future_backup_timestamps[] = wp_next_scheduled( "ai1wmke_webdav_{$schedule}_export", array( $this->get_cron_args() ) );
		}

		sort( $future_backup_timestamps );

		if ( isset( $future_backup_timestamps[0] ) ) {
			$next_backup_date = get_date_from_gmt( date( 'Y-m-d H:i:s', $future_backup_timestamps[0] ), 'F j, Y g:i a' );
		} else {
			$next_backup_date = __( 'None', AI1WMKE_PLUGIN_NAME );
		}

		return $next_backup_date;
	}

	public function set_cron_timestamp( $timestamp ) {
		return update_option( 'ai1wmke_webdav_cron_timestamp', $timestamp );
	}

	public function get_cron_timestamp() {
		return get_option( 'ai1wmke_webdav_cron_timestamp', time() );
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
		Ai1wm_Cron::clear( 'ai1wmke_webdav_hourly_export' );
		Ai1wm_Cron::clear( 'ai1wmke_webdav_daily_export' );
		Ai1wm_Cron::clear( 'ai1wmke_webdav_weekly_export' );
		Ai1wm_Cron::clear( 'ai1wmke_webdav_monthly_export' );

		// Update cron schedules
		foreach ( $schedules as $schedule ) {
			Ai1wm_Cron::add( "ai1wmke_webdav_{$schedule}_export", $schedule, $this->get_cron_timestamp(), array( $this->get_cron_args() ) );
		}

		return update_option( 'ai1wmke_webdav_cron', $schedules );
	}

	public function get_cron() {
		return get_option( 'ai1wmke_webdav_cron', array() );
	}

	public function init_cron() {
		foreach ( $this->get_cron() as $schedule ) {
			if ( ! Ai1wm_Cron::exists( "ai1wmke_webdav_{$schedule}_export", array( $this->get_cron_args() ) ) ) {
				Ai1wm_Cron::clear( "ai1wmke_webdav_{$schedule}_export" );
				Ai1wm_Cron::add( "ai1wmke_webdav_{$schedule}_export", $schedule, $this->get_cron_timestamp(), array( $this->get_cron_args() ) );
			}
		}
	}

	public function get_cron_args() {
		return array( 'secret_key' => get_option( AI1WM_SECRET_KEY ), 'webdav' => 1 );
	}

	public function set_type( $type ) {
		return update_option( 'ai1wmke_webdav_type', $type );
	}

	public function get_type() {
		return get_option( 'ai1wmke_webdav_type', AI1WMKE_WEBDAV_TYPE );
	}

	public function set_hostname( $hostname ) {
		return update_option( 'ai1wmke_webdav_hostname', $hostname );
	}

	public function get_hostname() {
		return get_option( 'ai1wmke_webdav_hostname', false );
	}

	public function set_username( $username ) {
		return update_option( 'ai1wmke_webdav_username', $username );
	}

	public function get_username() {
		return get_option( 'ai1wmke_webdav_username', false );
	}

	public function set_password( $password ) {
		return update_option( 'ai1wmke_webdav_password', $password );
	}

	public function get_password() {
		return get_option( 'ai1wmke_webdav_password', false );
	}

	public function set_authentication( $authentication ) {
		return update_option( 'ai1wmke_webdav_authentication', $authentication );
	}

	public function get_authentication() {
		return get_option( 'ai1wmke_webdav_authentication', AI1WMKE_WEBDAV_AUTHENTICATION );
	}

	public function set_directory( $directory ) {
		return update_option( 'ai1wmke_webdav_directory', $directory );
	}

	public function get_directory() {
		return get_option( 'ai1wmke_webdav_directory', false );
	}

	public function set_file_chunk_size( $file_chunk_size ) {
		return update_option( 'ai1wmke_webdav_file_chunk_size', $file_chunk_size );
	}

	public function get_file_chunk_size() {
		return get_option( 'ai1wmke_webdav_file_chunk_size', false );
	}

	public function set_port( $port ) {
		return update_option( 'ai1wmke_webdav_port', $port );
	}

	public function get_port() {
		return get_option( 'ai1wmke_webdav_port', AI1WMKE_WEBDAV_PORT );
	}

	public function set_connection( $connection ) {
		return update_option( 'ai1wmke_webdav_connection', $connection );
	}

	public function get_connection() {
		return get_option( 'ai1wmke_webdav_connection', false );
	}

	public function set_backups( $number ) {
		return update_option( 'ai1wmke_webdav_backups', $number );
	}

	public function get_backups() {
		return get_option( 'ai1wmke_webdav_backups', false );
	}

	public function set_total( $size ) {
		return update_option( 'ai1wmke_webdav_total', $size );
	}

	public function get_total() {
		return get_option( 'ai1wmke_webdav_total', false );
	}

	public function set_days( $days ) {
		return update_option( 'ai1wmke_webdav_days', $days );
	}

	public function get_days() {
		return get_option( 'ai1wmke_webdav_days', false );
	}

	public function set_notify_ok_toggle( $toggle ) {
		return update_option( 'ai1wmke_webdav_notify_toggle', $toggle );
	}

	public function get_notify_ok_toggle() {
		return get_option( 'ai1wmke_webdav_notify_toggle', false );
	}

	public function set_notify_error_toggle( $toggle ) {
		return update_option( 'ai1wmke_webdav_notify_error_toggle', $toggle );
	}

	public function get_notify_error_toggle() {
		return get_option( 'ai1wmke_webdav_notify_error_toggle', false );
	}

	public function set_notify_error_subject( $subject ) {
		return update_option( 'ai1wmke_webdav_notify_error_subject', $subject );
	}

	public function get_notify_error_subject() {
		return get_option( 'ai1wmke_webdav_notify_error_subject', sprintf( __( 'Backup to WebDAV has failed (%s)', AI1WMKE_PLUGIN_NAME ), parse_url( site_url(), PHP_URL_HOST ) . parse_url( site_url(), PHP_URL_PATH ) ) );
	}

	public function set_notify_email( $email ) {
		return update_option( 'ai1wmke_webdav_notify_email', $email );
	}

	public function get_notify_email() {
		return get_option( 'ai1wmke_webdav_notify_email', false );
	}
}
