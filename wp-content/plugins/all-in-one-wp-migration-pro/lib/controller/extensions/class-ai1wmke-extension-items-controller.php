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

class Ai1wmke_Extension_Items_Controller {

	public static function index() {
		Ai1wm_Template::render(
			'extensions/index',
			array(
				'extensions' => Ai1wmke_Extensions::get(),
			),
			AI1WMKE_TEMPLATES_PATH
		);
	}

	public static function update( $params = array() ) {
		check_admin_referer( 'ai1wmke_extensions' );
		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_die( __( 'You are not allowed to perform this action.', AI1WMKE_PLUGIN_NAME ) );
		}

		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( array_merge( $_GET, $_POST ) );
		}

		$extension_short = null;
		if ( isset( $params['ai1wmke_extension_short'] ) ) {
			$extension_short = trim( $params['ai1wmke_extension_short'] );
		}

		$extensions = Ai1wmke_Extensions::get();

		// Enable or disable extension
		if ( ( $pro_extensions = get_option( AI1WMKE_PRO_EXTENSIONS, array() ) ) !== false ) {
			if ( isset( $pro_extensions[ $extension_short ] ) ) {
				unset( $pro_extensions[ $extension_short ] );

				// Set message
				Ai1wm_Message::flash( 'info', sprintf( __( '%s is now disabled. You can enable it back later.', AI1WMKE_PLUGIN_NAME ), $extensions[ $extension_short ]['title'] ) );
			} else {
				$pro_extensions[ $extension_short ] = 1;

				// Set message
				Ai1wm_Message::flash( 'extensions', sprintf( __( '%s is now enabled. You can configure it from the settings page.', AI1WMKE_PLUGIN_NAME ), $extensions[ $extension_short ]['title'] ) );
			}

			update_option( AI1WMKE_PRO_EXTENSIONS, $pro_extensions );
		}

		// Redirect to extensions page
		wp_redirect( network_admin_url( 'admin.php?page=ai1wmke_extensions' ) );
		exit;
	}
}
