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

class Ai1wmke_Direct_Controller {

	/**
	 * Enqueue scripts and styles for Direct Controller
	 *
	 * @param  string $hook Hook suffix
	 * @return void
	 */
	public static function enqueue_scripts_and_styles( $hook ) {
		if ( stripos( 'all-in-one-wp-migration_page_ai1wmke_direct', $hook ) === false ) {
			return;
		}

		if ( is_rtl() ) {
			wp_enqueue_style(
				'ai1wmke_direct',
				Ai1wm_Template::asset_link( 'css/direct.min.rtl.css', 'AI1WMKE' ),
				array( 'ai1wm_servmask' )
			);
		} else {
			wp_enqueue_style(
				'ai1wmke_direct',
				Ai1wm_Template::asset_link( 'css/direct.min.css', 'AI1WMKE' ),
				array( 'ai1wm_servmask' )
			);
		}

		wp_enqueue_script(
			'ai1wmke_direct',
			Ai1wm_Template::asset_link( 'javascript/direct.min.js', 'AI1WMKE' ),
			array( 'ai1wm_export', 'ai1wm_import' )
		);

		wp_localize_script(
			'ai1wmke_direct',
			'ai1wmke_direct',
			array(
				'ajax' => array(
					'create'       => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_direct_add_site' ) ) ),
					'create_nonce' => wp_create_nonce( 'ai1wmke_direct_create' ),
					'unlink'       => wp_make_link_relative( add_query_arg( array( 'ai1wm_import' => 1 ), admin_url( 'admin-ajax.php?action=ai1wmke_direct_unlink_site' ) ) ),
					'unlink_nonce' => wp_create_nonce( 'ai1wmke_direct_unlink' ),
				),
			)
		);

		wp_localize_script(
			'ai1wmke_direct',
			'ai1wmke_locale',
			array(
				'create'                        => __( 'Link website', AI1WMKE_PLUGIN_NAME ),
				'create_error'                  => __( 'An error occurred when trying to add the website', AI1WMKE_PLUGIN_NAME ),
				'unlink_error'                  => __( 'An error occurred when trying to remove the website', AI1WMKE_PLUGIN_NAME ),
				'close_push'                    => __( 'Close', AI1WMKE_PLUGIN_NAME ),
				'stop_push'                     => __( 'Stop push', AI1WMKE_PLUGIN_NAME ),
				'stop_pushing_your_website'     => __( 'You are about to stop pushing your website, are you sure?', AI1WMKE_PLUGIN_NAME ),
				'unable_to_push'                => __( 'Unable to push', AI1WMKE_PLUGIN_NAME ),
				'unable_to_start_the_push'      => __( 'Unable to start the push. Refresh the page and try again', AI1WMKE_PLUGIN_NAME ),
				'unable_to_run_the_push'        => __( 'Unable to run the push. Refresh the page and try again', AI1WMKE_PLUGIN_NAME ),
				'unable_to_stop_the_push'       => __( 'Unable to stop the push. Refresh the page and try again', AI1WMKE_PLUGIN_NAME ),
				'please_wait_stopping_the_push' => __( 'Please wait, stopping the push...', AI1WMKE_PLUGIN_NAME ),
				'close_pull'                    => __( 'Close', AI1WMKE_PLUGIN_NAME ),
				'stop_pull'                     => __( 'Stop pull', AI1WMKE_PLUGIN_NAME ),
				'stop_pulling_your_website'     => __( 'You are about to stop pulling your website, are you sure?', AI1WMKE_PLUGIN_NAME ),
				'unable_to_pull'                => __( 'Unable to pull', AI1WMKE_PLUGIN_NAME ),
				'unable_to_start_the_pull'      => __( 'Unable to start the pull. Refresh the page and try again', AI1WMKE_PLUGIN_NAME ),
				'unable_to_run_the_pull'        => __( 'Unable to run the pull. Refresh the page and try again', AI1WMKE_PLUGIN_NAME ),
				'unable_to_stop_the_pull'       => __( 'Unable to stop the pull. Refresh the page and try again', AI1WMKE_PLUGIN_NAME ),
				'please_wait_stopping_the_pull' => __( 'Please wait, stopping the pull...', AI1WMKE_PLUGIN_NAME ),
				'close'                         => __( 'Close', AI1WMKE_PLUGIN_NAME ),
				'site_link'                     => __( 'Site Link URL', AI1WMKE_PLUGIN_NAME ),
				'push'                          => __( 'Push', AI1WMKE_PLUGIN_NAME ),
				'push_start'                    => __( 'Preparing to push file to remote website', AI1WMKE_PLUGIN_NAME ),
				'pull'                          => __( 'Pull', AI1WMKE_PLUGIN_NAME ),
				'pull_start'                    => __( 'Preparing to pull file to remote website', AI1WMKE_PLUGIN_NAME ),
				'unlink'                        => __( 'Unlink', AI1WMKE_PLUGIN_NAME ),
				'help'                          => __( 'You can get this from the remote website "Sites" page', AI1WMKE_PLUGIN_NAME ),
				'copied'                        => __( 'Copied!', AI1WMKE_PLUGIN_NAME ),
				'press'                         => __( 'Press', AI1WMKE_PLUGIN_NAME ),
			)
		);

		// Add Google Tag Manager
		add_action( 'admin_print_scripts', 'Ai1wmke_GTM_Controller::print_scripts', 100 );
	}
}
