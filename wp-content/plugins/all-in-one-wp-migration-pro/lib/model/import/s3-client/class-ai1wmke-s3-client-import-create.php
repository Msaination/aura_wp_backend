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

class Ai1wmke_S3_Client_Import_Create {

	public static function execute( $params, $s3 = null ) {

		// Set progress
		Ai1wm_Status::info( __( 'Creating an empty archive...', AI1WMKE_PLUGIN_NAME ) );

		// Create empty archive file
		$archive = new Ai1wm_Compressor( ai1wm_archive_path( $params ) );
		$archive->close();

		if ( ! isset( $params['region_name'] ) ) {
			if ( is_null( $s3 ) ) {
				$s3 = new Ai1wmke_S3_Storage_Client(
					get_option( 'ai1wmke_s3_client_access_key', ai1wmke_aws_access_key() ),
					get_option( 'ai1wmke_s3_client_secret_key', false ),
					get_option( 'ai1wmke_s3_client_https_protocol', true )
				);
			}

			$s3->set_api_endpoint( get_option( 'ai1wmke_s3_client_api_endpoint', ai1wmke_aws_api_endpoint() ) );
			$s3->set_bucket_template( get_option( 'ai1wmke_s3_client_bucket_template', ai1wmke_aws_bucket_template() ) );

			// Get region name
			$params['region_name'] = $s3->get_bucket_location( $params['bucket_name'], get_option( 'ai1wmke_s3_client_region_name', ai1wmke_aws_region_name( AI1WMKE_S3_CLIENT_REGION_NAME ) ) );
		}

		// Set progress
		Ai1wm_Status::info( __( 'Done creating an empty archive.', AI1WMKE_PLUGIN_NAME ) );

		return $params;
	}
}
