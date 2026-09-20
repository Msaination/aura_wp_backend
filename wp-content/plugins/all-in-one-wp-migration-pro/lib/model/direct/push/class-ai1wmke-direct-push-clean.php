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

class Ai1wmke_Direct_Push_Clean {

	public static function execute( $params, $direct_client = null, $token_client = null ) {
		$params['completed'] = false;

		// Set Token client
		if ( is_null( $token_client ) ) {
			$token_client = new Ai1wmke_Direct_Token_Client();
		}

		// Overwrite params so we set it for export
		$upload_params = array_merge(
			$params,
			array(
				'storage'             => strrev( $params['storage'] ),
				'secret_key'          => $token_client->get_key_from_token( $params['site_url'] ),
				'action'              => 'ai1wm_import',
				'ai1wm_manual_import' => true,
				'priority'            => 400,
			)
		);

		unset( $upload_params['direct_push'] );
		unset( $upload_params['push'] );

		// Set Direct client
		if ( is_null( $direct_client ) ) {
			$direct_client = new Ai1wmke_Direct_Client( $upload_params, get_option( 'ai1wmke_direct_ssl', false ) );
		}

		$direct_client->load_upload_url( $token_client->get_url_from_token( $params['site_url'] ) );
		$direct_client->send_params( true );

		Ai1wm_Export_Clean::execute( $params );
		exit;
	}
}
