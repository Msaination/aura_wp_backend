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

class Ai1wmke_Direct_Token_Client {

	public function __construct() {
	}

	public function get_domain_from_token( $token ) {
		$token = urldecode( $token );

		return parse_url( $token, PHP_URL_HOST );
	}

	public function get_url_from_token( $token ) {
		$token = urldecode( $token );
		$parts = explode( '?', $token );

		return $parts[0];
	}

	public function get_key_from_token( $token ) {
		$token = urldecode( $token );
		$parts = explode( '?', $token );
		parse_str( $parts[1], $args );

		return $args['secret_key'];
	}

	public function get_status( $key, $url, $connection_check = false, $return = false ) {
		$status = wp_remote_get( add_query_arg( array( 'action' => 'ai1wm_status', 'secret_key' => $key ), $url ), array( 'sslverify' => false, 'timeout' => 30 ) );
		if ( is_wp_error( $status ) ) {
			return false;
		}

		$body = json_decode( $status['body'], true );
		if ( ! is_array( $body ) ) {
			// Secret key issue or something else, this should have output
			return false;
		}

		if ( ! $connection_check && isset( $body['type'] ) && $body['type'] === 'error' ) {
			throw new Ai1wmke_Error_Exception( sprintf( __( 'Remote site encountered an error: %s', AI1WMKE_PLUGIN_NAME ), $body['message'] ) );
		}

		if ( $return ) {
			if ( isset( $body['message'] ) ) {
				return $body['message'];
			} else {
				return false;
			}
		}

		return true;
	}
}
