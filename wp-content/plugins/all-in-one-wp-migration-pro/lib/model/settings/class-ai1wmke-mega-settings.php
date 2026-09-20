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

class Ai1wmke_Mega_Settings {

	public function revoke() {
		$mega = new Ai1wmke_Mega_Client(
			get_option( 'ai1wmke_mega_user_email', false ),
			get_option( 'ai1wmke_mega_user_password', false )
		);

		$mega->load_user_session( get_option( 'ai1wmke_mega_user_session', false ) );

		// Logout
		$mega->logout();

		// Remove user session option
		delete_option( 'ai1wmke_mega_user_session' );

		// Remove cron option
		delete_option( 'ai1wmke_mega_cron' );

		// Reset cron schedules
		Ai1wm_Cron::clear( 'ai1wmke_mega_hourly_export' );
		Ai1wm_Cron::clear( 'ai1wmke_mega_daily_export' );
		Ai1wm_Cron::clear( 'ai1wmke_mega_weekly_export' );
		Ai1wm_Cron::clear( 'ai1wmke_mega_monthly_export' );
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
			$future_backup_timestamps[] = wp_next_scheduled( "ai1wmke_mega_{$schedule}_export", array( $this->get_cron_args() ) );
		}

		sort( $future_backup_timestamps );

		if ( isset( $future_backup_timestamps[0] ) ) {
			$next_backup_date = get_date_from_gmt( date( 'Y-m-d H:i:s', $future_backup_timestamps[0] ), 'F j, Y g:i a' );
		} else {
			$next_backup_date = __( 'None', AI1WMKE_PLUGIN_NAME );
		}

		return $next_backup_date;
	}

	public function get_account() {
		$mega = new Ai1wmke_Mega_Client(
			get_option( 'ai1wmke_mega_user_email', false ),
			get_option( 'ai1wmke_mega_user_password', false )
		);

		$mega->load_user_session( get_option( 'ai1wmke_mega_user_session', false ) );

		// Get account info
		$account = $mega->get_account_info();

		// Get storage info
		$storage = $mega->get_storage_info();

		// Set account name
		$name = null;
		if ( isset( $account['name'] ) ) {
			$name = $account['name'];
		}

		// Set email
		$email = null;
		if ( isset( $account['email'] ) ) {
			$email = $account['email'];
		}

		// Set used quota
		$used = 1;
		if ( isset( $storage['cstrg'] ) ) {
			$used = $storage['cstrg'];
		}

		// Set total quota
		$total = 1;
		if ( isset( $storage['mstrg'] ) ) {
			$total = $storage['mstrg'];
		}

		return array(
			'name'     => $name,
			'email'    => $email,
			'used'     => ai1wm_size_format( $used ),
			'total'    => ai1wm_size_format( $total ),
			'progress' => ceil( ( $used / $total ) * 100 ),
		);
	}

	public function do_login() {
		$mega = new Ai1wmke_Mega_Client(
			get_option( 'ai1wmke_mega_user_email', false ),
			get_option( 'ai1wmke_mega_user_password', false )
		);

		return $mega->login();
	}

	public function set_cron_timestamp( $timestamp ) {
		return update_option( 'ai1wmke_mega_cron_timestamp', $timestamp );
	}

	public function get_cron_timestamp() {
		return get_option( 'ai1wmke_mega_cron_timestamp', time() );
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
		Ai1wm_Cron::clear( 'ai1wmke_mega_hourly_export' );
		Ai1wm_Cron::clear( 'ai1wmke_mega_daily_export' );
		Ai1wm_Cron::clear( 'ai1wmke_mega_weekly_export' );
		Ai1wm_Cron::clear( 'ai1wmke_mega_monthly_export' );

		// Update cron schedules
		foreach ( $schedules as $schedule ) {
			Ai1wm_Cron::add( "ai1wmke_mega_{$schedule}_export", $schedule, $this->get_cron_timestamp(), array( $this->get_cron_args() ) );
		}

		return update_option( 'ai1wmke_mega_cron', $schedules );
	}

	public function get_cron() {
		return get_option( 'ai1wmke_mega_cron', array() );
	}

	public function init_cron() {
		foreach ( $this->get_cron() as $schedule ) {
			if ( ! Ai1wm_Cron::exists( "ai1wmke_mega_{$schedule}_export", array( $this->get_cron_args() ) ) ) {
				Ai1wm_Cron::clear( "ai1wmke_mega_{$schedule}_export" );
				Ai1wm_Cron::add( "ai1wmke_mega_{$schedule}_export", $schedule, $this->get_cron_timestamp(), array( $this->get_cron_args() ) );
			}
		}
	}

	public function get_cron_args() {
		if ( $this->get_incremental() ) {
			return array( 'secret_key' => get_option( AI1WM_SECRET_KEY ), 'incremental' => 1, 'mega' => 1 );
		}

		return array( 'secret_key' => get_option( AI1WM_SECRET_KEY ), 'mega' => 1 );
	}

	public function set_user_email( $user_email ) {
		return update_option( 'ai1wmke_mega_user_email', $user_email );
	}

	public function get_user_email() {
		return get_option( 'ai1wmke_mega_user_email', false );
	}

	public function set_user_password( $user_password ) {
		return update_option( 'ai1wmke_mega_user_password', $user_password );
	}

	public function get_user_password() {
		return get_option( 'ai1wmke_mega_user_password', false );
	}

	public function set_user_session( $user_session ) {
		return update_option( 'ai1wmke_mega_user_session', $user_session );
	}

	public function get_user_session() {
		return get_option( 'ai1wmke_mega_user_session', false );
	}

	public function set_backups( $number ) {
		return update_option( 'ai1wmke_mega_backups', $number );
	}

	public function get_backups() {
		return get_option( 'ai1wmke_mega_backups', false );
	}

	public function set_total( $size ) {
		return update_option( 'ai1wmke_mega_total', $size );
	}

	public function get_total() {
		return get_option( 'ai1wmke_mega_total', false );
	}

	public function set_days( $days ) {
		return update_option( 'ai1wmke_mega_days', $days );
	}

	public function get_days() {
		return get_option( 'ai1wmke_mega_days', false );
	}

	public function set_node_id( $node_id ) {
		return update_option( 'ai1wmke_mega_node_id', $node_id );
	}

	public function get_node_id() {
		return get_option( 'ai1wmke_mega_node_id', false );
	}

	public function set_notify_ok_toggle( $toggle ) {
		return update_option( 'ai1wmke_mega_notify_toggle', $toggle );
	}

	public function get_notify_ok_toggle() {
		return get_option( 'ai1wmke_mega_notify_toggle', false );
	}

	public function set_notify_error_toggle( $toggle ) {
		return update_option( 'ai1wmke_mega_notify_error_toggle', $toggle );
	}

	public function get_notify_error_toggle() {
		return get_option( 'ai1wmke_mega_notify_error_toggle', false );
	}

	public function set_notify_error_subject( $subject ) {
		return update_option( 'ai1wmke_mega_notify_error_subject', $subject );
	}

	public function get_notify_error_subject() {
		return get_option( 'ai1wmke_mega_notify_error_subject', sprintf( __( 'Backup to Mega has failed (%s)', AI1WMKE_PLUGIN_NAME ), parse_url( site_url(), PHP_URL_HOST ) . parse_url( site_url(), PHP_URL_PATH ) ) );
	}

	public function set_notify_email( $email ) {
		return update_option( 'ai1wmke_mega_notify_email', $email );
	}

	public function get_notify_email() {
		return get_option( 'ai1wmke_mega_notify_email', false );
	}

	public function set_lock_mode( $mode ) {
		return update_option( 'ai1wmke_mega_lock_mode', $mode );
	}

	public function get_lock_mode() {
		return get_option( 'ai1wmke_mega_lock_mode', false );
	}

	public function set_incremental( $incremental ) {
		return update_option( 'ai1wmke_mega_incremental', $incremental );
	}

	public function get_incremental() {
		return get_option( 'ai1wmke_mega_incremental', false );
	}
}
