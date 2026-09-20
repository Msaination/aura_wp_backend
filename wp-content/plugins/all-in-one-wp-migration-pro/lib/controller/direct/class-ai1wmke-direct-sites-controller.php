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

class Ai1wmke_Direct_Sites_Controller {

	public static function index() {
		$model = new Ai1wmke_Direct_Sites();
		Ai1wm_Template::render(
			'direct/index',
			array(
				'sites' => $model->get_all(),
			),
			AI1WMKE_TEMPLATES_PATH
		);
	}

	public static function add_site( $params = array() ) {
		check_ajax_referer( 'ai1wmke_direct_create', 'nonce' );
		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_POST );
		}

		self::validate( $params );

		$site = self::fetch_info( $params['site']['url'] );
		if ( ! $site ) {
			echo json_encode(
				array(
					'success' => false,
					'message' => __( "Remote website couldn't be reached", AI1WMKE_PLUGIN_NAME ),
				)
			);
			exit;
		}

		$sites = get_option( AI1WMKE_DIRECT_SITES_LINKS, array() );

		$success = update_option( AI1WMKE_DIRECT_SITES_LINKS, array_merge( $sites, array( $site ) ) );
		if ( ! $success ) {
			echo json_encode(
				array(
					'success' => $success,
					'message' => __( "Site couldn't be linked, please try again", AI1WMKE_PLUGIN_NAME ),
				)
			);
			exit;
		}

		echo json_encode(
			array(
				'success' => $success,
				'site'    => $site,
				'message' => __( 'Site has been linked successfully', AI1WMKE_PLUGIN_NAME ),
			)
		);
		exit;
	}

	public static function unlink_site( $params = array() ) {
		check_ajax_referer( 'ai1wmke_direct_unlink', 'nonce' );
		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_POST );
		}

		$sites = get_option( AI1WMKE_DIRECT_SITES_LINKS, array() );
		foreach ( $sites as $k => $site ) {
			if ( $site['url'] === $params['site']['url'] ) {
				unset( $sites[ $k ] );
			}
		}

		echo json_encode(
			array(
				'success' => update_option( AI1WMKE_DIRECT_SITES_LINKS, array_values( $sites ) ),
			)
		);
		exit;
	}

	public static function fetch_info( $url ) {
		$response = wp_remote_get( $url, array( 'sslverify' => false, 'timeout' => 30 ) );
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = json_decode( $response['body'], ARRAY_A );
		if ( ! isset( $body['site'] ) ) {
			// Secret key issue or something else, this should exist
			return false;
		}

		return array_merge( $body['site'], array( 'url' => $url ) );
	}

	public static function info( $params = array() ) {
		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( array_merge( $_GET, $_POST ) );
		}

		// Set secret key
		$secret_key = null;
		if ( isset( $params['secret_key'] ) ) {
			$secret_key = trim( $params['secret_key'] );
		}

		try {
			// Ensure that unauthorized people cannot access site info action
			ai1wm_verify_secret_key( $secret_key );
		} catch ( Ai1wm_Not_Valid_Secret_Key_Exception $e ) {
			exit;
		}

		echo json_encode(
			array(
				'site' => self::get_site_data(),
			)
		);
		exit;
	}

	public static function get_site_data() {
		$front_page_id = get_option( 'page_on_front' );
		if ( $front_page_id ) {
			$image = get_the_post_thumbnail_url( $front_page_id );
		}

		if ( empty( $image ) ) {
			$image = AI1WMKE_URL . '/lib/view/assets/img/servmask.svg';
		}

		$image = apply_filters( 'ai1wmke_og_image', $image );

		return array(
			'image' => $image,
			'name'  => get_bloginfo( 'name' ),
			'home'  => get_bloginfo( 'url' ),
		);
	}

	public static function validate( $params ) {
		if ( empty( $params['site']['url'] ) ) {
			echo json_encode(
				array(
					'success' => false,
					'message' => __( 'Please provide a site link', AI1WMKE_PLUGIN_NAME ),
				)
			);
			exit;
		}

		$remote_host = parse_url( $params['site']['url'], PHP_URL_HOST );
		$remote_path = parse_url( $params['site']['url'], PHP_URL_PATH );

		$local_host = parse_url( admin_url( 'admin-ajax.php' ), PHP_URL_HOST );
		$local_path = parse_url( admin_url( 'admin-ajax.php' ), PHP_URL_PATH );

		if ( $remote_host === $local_host && $remote_path === $local_path ) {
			echo json_encode(
				array(
					'success' => false,
					'message' => __( 'You cannot link the current website', AI1WMKE_PLUGIN_NAME ),
				)
			);
			exit;
		}

		$model = new Ai1wmke_Direct_Sites();

		$sites = $model->get_all();
		foreach ( $sites as $key => $site ) {
			if ( $site['url'] !== $params['site']['url'] ) {
				continue;
			}

			echo json_encode(
				array(
					'success' => false,
					'message' => __( 'Website has already been linked', AI1WMKE_PLUGIN_NAME ),
				)
			);
			exit;
		}
	}
}
