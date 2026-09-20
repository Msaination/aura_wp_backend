<?php
/**
 * Plugin Name: All-in-One WP Migration Pro
 * Plugin URI: https://servmask.com/
 * Description: Extension for All-in-One WP Migration that enables using Microsoft Azure Storage, Backblaze B2, Box, DigitalOcean Spaces, Direct, Dropbox, FTP/SFTP, Google Cloud Storage, Google Drive, Amazon Glacier, Mega, OneDrive, pCloud, S3 Client, Amazon S3, URL and WebDAV
 * Author: ServMask
 * Author URI: https://servmask.com/
 * Version: 1.41
 * Text Domain: all-in-one-wp-migration-pro
 * Domain Path: /languages
 * Network: True
 * License: GPLv3
 *
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

// Check SSL mode
if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && ( $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) ) {
	$_SERVER['HTTPS'] = 'on';
}

// Plugin basename
define( 'AI1WMKE_PLUGIN_BASENAME', basename( __DIR__ ) . '/' . basename( __FILE__ ) );

// Plugin path
define( 'AI1WMKE_PATH', __DIR__ );

// Plugin URL
define( 'AI1WMKE_URL', plugins_url( '', AI1WMKE_PLUGIN_BASENAME ) );

// Include constants
require_once __DIR__ . DIRECTORY_SEPARATOR . 'constants.php';

// Include functions
require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';

// Include exceptions
require_once __DIR__ . DIRECTORY_SEPARATOR . 'exceptions.php';

// Include loader
require_once __DIR__ . DIRECTORY_SEPARATOR . 'loader.php';

// Include autoload
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

/* Ultrapack Unlock */
add_action( 'plugins_loaded', 'ai1wmke_mask_purchase_id', 999 );
function ai1wmke_mask_purchase_id() {
	$key = get_option( 'ai1wmke_masked_purchase_id' );
	if ( empty( $key ) ) {
		if ( function_exists( 'wp_generate_uuid4' ) ) {
			$key = wp_generate_uuid4();
		} else {
			$key = sprintf(
				'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
				random_int( 0, 0xffff ),
				random_int( 0, 0xffff ),
				random_int( 0, 0xffff ),
				random_int( 0, 0x0fff ) | 0x4000,
				random_int( 0, 0x3fff ) | 0x8000,
				random_int( 0, 0xffff ),
				random_int( 0, 0xffff ),
				random_int( 0, 0xffff )
			);
		}
		update_option( 'ai1wmke_masked_purchase_id', $key, true );
	}

	if ( defined( 'AI1WMKE_PLUGIN_KEY' ) ) {
		update_option( AI1WMKE_PLUGIN_KEY, $key, true );
	}
}

delete_option('ai1wm_updater');

add_filter( 'pre_http_request', 'ai1wmke_block_servmask_calls', 10, 3 );
function ai1wmke_block_servmask_calls( $preempt, $args, $url ) {
	if ( strpos( $url, 'servmask.com/purchase/activations' ) !== false ||
		strpos( $url, 'servmask.com/api/stats' ) !== false ) {
		return array(
			'headers'       => array(),
			'body'          => '{"success": true}',
			'response'      => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'       => array(),
			'http_response' => null,
		);
	}
	return $preempt;
}

add_filter( 'pre_http_request', 'ai1wmke_block_redirect_calls', 1, 3 );
function ai1wmke_block_redirect_calls( $preempt, $args, $url ) {
	// Block ALL verification calls from free plugin
	if ( strpos( $url, 'redirect.wp-migration.com/v1/check/all-in-one-wp-migration-pro/' ) !== false ) {
		return array(
			'headers'       => array(),
			'body'          => '{"success": true, "outdated": false, "version": "'. AI1WMKE_VERSION . '"}',
			'response'      => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'       => array(),
			'http_response' => null,
		);
	}
	return $preempt;
}

add_action( 'admin_footer', 'ai1wmke_add_javascript_filter' );
function ai1wmke_add_javascript_filter() {
	if ( isset( $_GET['page'] ) && strpos( $_GET['page'], 'ai1wm' ) !== false ) {
		?>
		<script type="text/javascript">
		(function() {
			'use strict';
			const originalFetch = window.fetch;
			window.fetch = function(url, options = {}) {
				if (typeof url === 'string' && url.includes('redirect.wp-migration.com/v1/check/all-in-one-wp-migration-pro/')) {
					return Promise.resolve(new Response(
						JSON.stringify({ success: true, outdated: false, version: <?php echo AI1WMKE_VERSION ?> }),
						{
							status: 200,
							statusText: 'OK',
							headers: {
								'Content-Type': 'application/json'
							}
						}
					));
				}
				return originalFetch.apply(this, arguments);
			};
			
			const originalXHROpen = XMLHttpRequest.prototype.open;
			XMLHttpRequest.prototype.open = function(method, url, async, user, password) {
				this._url = url;
				return originalXHROpen.apply(this, arguments);
			};
			
			const originalXHRSend = XMLHttpRequest.prototype.send;
			XMLHttpRequest.prototype.send = function(data) {
				if (this._url && this._url.includes('redirect.wp-migration.com/v1/check/all-in-one-wp-migration-pro/')) {
					const self = this;
					setTimeout(function() {
						Object.defineProperty(self, 'status', { value: 200, writable: false });
						Object.defineProperty(self, 'statusText', { value: 'OK', writable: false });
						Object.defineProperty(self, 'responseText', { value: '{"success": true, "outdated": false, "version": <?php echo AI1WMKE_VERSION ?>}', writable: false });
						Object.defineProperty(self, 'response', { value: '{"success": true, "outdated": false, "version": <?php echo AI1WMKE_VERSION ?>}', writable: false });
						Object.defineProperty(self, 'readyState', { value: 4, writable: false });
						
						if (self.onreadystatechange) {
							self.onreadystatechange();
						}
						if (self.onload) {
							self.onload();
						}
					}, 0);
					return;
				}
				
				return originalXHRSend.apply(this, arguments);
			};
		})();
		</script>
		<?php
	}
}
/* End Ultrapack Unlock */

// Plugin initialization
$main_controller = new Ai1wmke_Main_Controller();
