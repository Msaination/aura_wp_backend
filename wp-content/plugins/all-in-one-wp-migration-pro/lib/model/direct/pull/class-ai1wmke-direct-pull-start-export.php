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

class Ai1wmke_Direct_Pull_Start_Export {

	public static function execute( $params, $direct_client = null, $token_client = null, $url_client = null ) {

		// Validate upload URL
		if ( ! isset( $params['download_url'] ) ) {
			throw new Ai1wmke_Pull_Exception( __( 'Direct Download URL is not specified.', AI1WMKE_PLUGIN_NAME ) );
		}

		// Set Token client
		if ( is_null( $token_client ) ) {
			$token_client = new Ai1wmke_Direct_Token_Client();
		}

		if ( ! isset( $params['export_retries'] ) ) {
			$params['export_retries'] = 0;
		}

		// Set Direct client
		if ( is_null( $direct_client ) ) {
			if ( isset( $params['pull']['params'] ) ) {
				$download_params = $params['pull']['params'];
			} else {
				// Overwrite params so we set it for export
				$download_params = array_merge(
					$params,
					array(
						'storage'             => strrev( $params['storage'] ),
						'secret_key'          => $token_client->get_key_from_token( $params['site_url'] ),
						'action'              => 'ai1wm_export',
						'ai1wm_manual_export' => true,
						'priority'            => 5,
					)
				);

				unset( $download_params['completed'] );
				unset( $download_params['direct_pull'] );
			}

			$direct_client = new Ai1wmke_Direct_Client( $download_params, get_option( 'ai1wmke_direct_ssl', false ) );
		}

		$direct_client->load_upload_url( $params['download_url'] );

		try {

			$params['export_retries'] += 1;

			// Send params
			$response = $direct_client->send_params();

			// Only update params when response includes params and not on file upload
			if ( isset( $response['priority'] ) ) {
				$params['pull']      = array( 'params' => $response );
				$params['completed'] = false;
				if ( $response['priority'] > 250 ) {
					$params['completed'] = true;
					$params['archive']   = $params['pull']['params']['archive'];
					unset( $params['pull'] );
				}
			}

			// Confirm or error return null
			if ( empty( $response ) ) {
				$params['completed'] = true;
			}
		} catch ( Ai1wmke_Error_Exception $e ) {
			if ( $params['export_retries'] <= 3 ) {
				return $params;
			}

			throw $e;
		}

		$status = $token_client->get_status( $token_client->get_key_from_token( $params['site_url'] ), $token_client->get_url_from_token( $params['site_url'] ), false, true );

		if ( $params['completed'] ) {

			// Set URL client
			if ( is_null( $url_client ) ) {
				$url_client = new Ai1wmke_URL_Client( self::parse_link( $status ) );
			}

			// Set file URL
			$params['file_url'] = $url_client->get_file_url();

			// Set file size
			$params['file_size'] = $url_client->get_file_size();

			// Set file ranges
			$params['file_ranges'] = $url_client->get_file_ranges();

			$status = __( 'Backup file has been created', AI1WMKE_PLUGIN_NAME );
		}

		if ( $status === false ) {
			return $params;
		}

		// Set progress
		if ( defined( 'WP_CLI' ) ) {
			WP_CLI::log(
				sprintf(
					__( 'Remote export has started. Remote status: %s', AI1WMKE_PLUGIN_NAME ),
					$status
				)
			);
		} else {
			Ai1wm_Status::info(
				sprintf(
					__( 'Remote export has started <br/>', AI1WMKE_PLUGIN_NAME ) .
					__( 'Remote status: <i>%s</i>', AI1WMKE_PLUGIN_NAME ),
					$status
				)
			);
		}

		return $params;
	}

	public static function parse_link( $link ) {
		if ( preg_match( '/href="(.+?)"/', $link, $href ) ) {
			return $href[1];
		}
	}
}
