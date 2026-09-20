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

class Ai1wmke_Backups_Controller {

	/**
	 * Enqueue scripts and styles for Backups Controller
	 *
	 * @param  string $hook Hook suffix
	 * @return void
	 */
	public static function enqueue_scripts_and_styles( $hook ) {
		if ( stripos( 'all-in-one-wp-migration_page_ai1wm_backups', $hook ) === false ) {
			return;
		}

		wp_enqueue_script(
			'ai1wmke_backups',
			Ai1wm_Template::asset_link( 'javascript/backups.min.js', 'AI1WMKE' ),
			array( 'ai1wm_backups' )
		);

		wp_enqueue_script(
			'ai1wmke_wasm_exec',
			Ai1wm_Template::asset_link( 'javascript/vendor/wasm_exec.js', 'AI1WMKE' ),
			array( 'ai1wmke_backups' )
		);

		// Base service
		wp_localize_script(
			'ai1wmke_backups',
			'ai1wmke_base_service',
			array(
				'url' => AI1WMKE_SERVICE_URL,
				'key' => AI1WMKE_PURCHASE_ID,
			)
		);

		// Site details
		wp_localize_script(
			'ai1wmke_backups',
			'ai1wmke_site_details',
			array(
				'site_url'    => site_url(),
				'admin_email' => get_option( 'admin_email' ),
			)
		);

		// Add Google Tag Manager
		add_action( 'admin_print_scripts', 'Ai1wmke_GTM_Controller::print_scripts', 100 );
	}
}
