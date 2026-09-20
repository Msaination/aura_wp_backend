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

class Ai1wmke_Direct_Push_Confirm_Import {

	public static function execute( $params, $direct_client = null, $token_client = null ) {

		// Validate upload URL
		if ( ! isset( $params['upload_url'] ) ) {
			throw new Ai1wmke_Push_Exception( __( 'Direct Upload URL is not specified.', AI1WMKE_PLUGIN_NAME ) );
		}

		// Set Token client
		if ( is_null( $token_client ) ) {
			$token_client = new Ai1wmke_Direct_Token_Client();
		}

		if ( ! isset( $params['import_retries'] ) ) {
			$params['import_retries'] = 0;
		}

		// Set Direct client
		if ( is_null( $direct_client ) ) {
			if ( isset( $params['push']['params'] ) ) {
				$upload_params = $params['push']['params'];
			} else {
				// Overwrite params so we set it for export
				$upload_params = array_merge(
					$params,
					array(
						'storage'             => strrev( $params['storage'] ),
						'secret_key'          => $token_client->get_key_from_token( $params['site_url'] ),
						'action'              => 'ai1wm_import',
						'ai1wm_manual_import' => true,
						'priority'            => 150,
					)
				);

				unset( $upload_params['completed'] );
				unset( $upload_params['direct_push'] );
			}

			$direct_client = new Ai1wmke_Direct_Client( $upload_params, get_option( 'ai1wmke_direct_ssl', false ) );
		}

		$direct_client->load_upload_url( $params['upload_url'] );

		try {

			$params['import_retries'] += 1;

			// Send params
			$response = $direct_client->send_params();

			// Only update params when response includes params and not on file upload
			if ( isset( $response['priority'] ) ) {
				$params['push']      = array( 'params' => $response );
				$params['completed'] = false;
				if ( $response['priority'] > 350 ) {
					$params['completed'] = true;
				}
			}

			// Confirm or error return null
			if ( empty( $response ) ) {
				$params['completed'] = true;
				unset( $params['push'] );
			}
		} catch ( Ai1wmke_Error_Exception $e ) {
			if ( $params['import_retries'] <= 3 ) {
				return $params;
			}

			throw $e;
		}

		$status = $token_client->get_status( $token_client->get_key_from_token( $params['site_url'] ), $token_client->get_url_from_token( $params['site_url'] ), false, true );
		if ( $status === false ) {
			return $params;
		}

		// Set progress
		if ( defined( 'WP_CLI' ) ) {
			WP_CLI::log(
				sprintf(
					__( 'Remote import has started. Remote status: %s', AI1WMKE_PLUGIN_NAME ),
					$status['body']
				)
			);
		} else {
			Ai1wm_Status::info(
				sprintf(
					__( 'Remote import has started <br/>', AI1WMKE_PLUGIN_NAME ) .
					__( 'Remote status: <i>%s</i>', AI1WMKE_PLUGIN_NAME ),
					$status
				)
			);
		}

		return $params;
	}
}
