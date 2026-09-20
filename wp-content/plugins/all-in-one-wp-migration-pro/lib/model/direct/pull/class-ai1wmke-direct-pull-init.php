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

class Ai1wmke_Direct_Pull_Init {

	public static function execute( $params, $token_client = null ) {

		// Set Token client
		if ( is_null( $token_client ) ) {
			$token_client = new Ai1wmke_Direct_Token_Client();
		}

		Ai1wm_Status::info( __( 'Starting push...', AI1WMKE_PLUGIN_NAME ) );

		$domain = $token_client->get_domain_from_token( $params['site_url'] );

		// Set progress
		Ai1wm_Status::info( sprintf( __( 'Connecting to %s server at %s...', AI1WMKE_PLUGIN_NAME ), $params['site_name'], $domain ) );

		$status_ok = $token_client->get_status( $token_client->get_key_from_token( $params['site_url'] ), $token_client->get_url_from_token( $params['site_url'] ), true );
		if ( empty( $status_ok ) ) {
			throw new Ai1wmke_Connect_Exception( __( 'Remote site has a problem with connection, or it already has a backup in progress. Check the URL and the remote website', AI1WMKE_PLUGIN_NAME ) );
		}

		// Set progress
		Ai1wm_Status::info( sprintf( __( 'Successfully connected to %s server at %s.', AI1WMKE_PLUGIN_NAME ), $params['site_name'], $domain ) );

		// Set download URL
		$params['download_url'] = $token_client->get_url_from_token( $params['site_url'] ) . '?action=ai1wm_export';

		return $params;
	}
}
