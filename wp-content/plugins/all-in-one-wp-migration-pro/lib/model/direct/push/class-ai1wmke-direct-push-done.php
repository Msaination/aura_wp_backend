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

class Ai1wmke_Direct_Push_Done {

	public static function execute( $params, $token_client = null ) {
		$params['completed'] = true;

		// Set Token client
		if ( is_null( $token_client ) ) {
			$token_client = new Ai1wmke_Direct_Token_Client();
		}

		// Set progress
		Ai1wm_Status::done(
			sprintf( __( 'Push to %s completed', AI1WMKE_PLUGIN_NAME ), $params['site_name'] ),
			sprintf( __( 'Your WordPress archive has been uploaded and imported to the %s server.', AI1WMKE_PLUGIN_NAME ), $token_client->get_domain_from_token( $params['site_url'] ) )
		);

		// Send notification
		Ai1wm_Notification::ok(
			sprintf( __( '✅ Push to %s has been completed', AI1WMKE_PLUGIN_NAME ), $token_client->get_domain_from_token( $params['site_url'] ) ),
			sprintf( __( '<p>Your site %s was successfully exported to %s.</p>', AI1WMKE_PLUGIN_NAME ), site_url(), $token_client->get_domain_from_token( $params['site_url'] ) ) .
			sprintf( __( '<p>Date: %s</p>', AI1WMKE_PLUGIN_NAME ), date_i18n( 'r' ) ) .
			sprintf( __( '<p>Backup file: %s</p>', AI1WMKE_PLUGIN_NAME ), ai1wm_archive_name( $params ) ) .
			sprintf( __( '<p>Size: %s</p>', AI1WMKE_PLUGIN_NAME ), ai1wm_archive_size( $params ) )
		);

		do_action( 'ai1wm_status_export_done', $params );

		return $params;
	}
}
