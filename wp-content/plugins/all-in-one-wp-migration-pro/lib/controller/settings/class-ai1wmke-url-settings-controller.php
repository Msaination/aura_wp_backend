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

class Ai1wmke_URL_Settings_Controller {

	public static function index() {
		$chunk_size = get_option( 'ai1wmke_url_file_chunk_size', AI1WMKE_URL_FILE_CHUNK_SIZE );

		Ai1wm_Template::render(
			'settings/index/index-url',
			array(
				'file_chunk_size' => $chunk_size,
			),
			AI1WMKE_TEMPLATES_PATH
		);
	}

	public static function settings( $params = array() ) {
		check_admin_referer( 'ai1wmke_url_settings' );
		if ( ! current_user_can( 'export' ) ) {
			wp_die( __( 'You are not allowed to perform this action.', AI1WMKE_PLUGIN_NAME ) );
		}

		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_POST );
		}

		// Url settings update
		if ( isset( $params['ai1wmke_url_update'] ) ) {
			$model = new Ai1wmke_URL_Settings();

			// Set file chunk size
			if ( ! empty( $params['ai1wmke_url_file_chunk_size'] ) ) {
				$model->set_file_chunk_size( $params['ai1wmke_url_file_chunk_size'] );
			} else {
				$model->set_file_chunk_size( AI1WMKE_URL_FILE_CHUNK_SIZE );
			}

			// Set message
			Ai1wm_Message::flash( 'settings', __( 'Your changes have been saved.', AI1WMKE_PLUGIN_NAME ) );
		}

		// Redirect to settings page
		wp_redirect( network_admin_url( 'admin.php?page=ai1wmke_url_settings' ) );
		exit;
	}
}
