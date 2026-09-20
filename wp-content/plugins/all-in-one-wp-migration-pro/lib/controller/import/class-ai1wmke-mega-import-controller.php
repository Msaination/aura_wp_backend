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

class Ai1wmke_Mega_Import_Controller {

	public static function button() {
		return Ai1wm_Template::get_content(
			'import/button/button-mega',
			array( 'user_session' => get_option( 'ai1wmke_mega_user_session', false ) ),
			AI1WMKE_TEMPLATES_PATH
		);
	}

	public static function picker() {
		Ai1wm_Template::render(
			'import/picker/picker-mega',
			array(),
			AI1WMKE_TEMPLATES_PATH
		);
	}

	public static function browser( $params = array() ) {
		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_GET );
		}

		// Set node ID
		$node_id = null;
		if ( isset( $params['node_id'] ) ) {
			$node_id = trim( $params['node_id'] );
		}

		// Set Mega client
		$mega = new Ai1wmke_Mega_Client(
			get_option( 'ai1wmke_mega_user_email', false ),
			get_option( 'ai1wmke_mega_user_password', false )
		);

		$mega->load_user_session( get_option( 'ai1wmke_mega_user_session', false ) );

		// Get node list
		$nodes = $mega->list_nodes( $node_id );

		// Set folder structure
		$response = array( 'items' => array(), 'num_hidden_files' => 0 );

		// Loop over node list
		foreach ( $nodes as $node ) {
			if ( $node->is_dir() || pathinfo( $node->get_file_name(), PATHINFO_EXTENSION ) === 'wpress' ) {
				$response['items'][] = array(
					'index'   => null,
					'id'      => $node->get_node_id(),
					'name'    => $node->get_file_name(),
					'path'    => $node->get_file_name(),
					'date'    => human_time_diff( $node->get_last_modified_date() ),
					'size'    => $node->get_type() === 'file' ? ai1wm_size_format( $node->get_size() ) : null,
					'bytes'   => $node->get_size(),
					'type'    => $node->get_type(),
					'node_id' => $node_id,
				);
			} else {
				$response['num_hidden_files']++;
			}
		}

		// Sort nodes by type desc and date desc
		Ai1wmke_File_Sorter::sort( $response['items'], Ai1wmke_File_Sorter::by_type_desc_date_desc( 'unix' ) );

		echo json_encode( $response );
		exit;
	}

	public static function incremental( $params = array() ) {
		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_GET );
		}

		// Set node ID
		$node_id = null;
		if ( isset( $params['node_id'] ) ) {
			$node_id = trim( $params['node_id'] );
		}

		$mega = new Ai1wmke_Mega_Client(
			get_option( 'ai1wmke_mega_user_email', false ),
			get_option( 'ai1wmke_mega_user_password', false )
		);

		$mega->load_user_session( get_option( 'ai1wmke_mega_user_session', false ) );

		try {
			if ( ( $node_item = $mega->get_node_item_by_name( 'incremental.backups.list', $node_id ) ) ) {
				$mega->load_download_url( $mega->get_download_url( $node_item->get_node_id() ) );
				$file_content = $mega->download_file_content( $node_item->get_key() );
			}
		} catch ( Ai1wmke_Error_Exception $e ) {
		}

		$items = array();
		if ( isset( $file_content ) ) {
			foreach ( str_getcsv( $file_content, "\n" ) as $row ) {
				if ( list( $file_index, $file_id, $file_path, $file_size, $file_mtime ) = str_getcsv( $row ) ) {
					$items[] = array(
						'index'   => $file_index,
						'id'      => $file_id,
						'name'    => sprintf( __( 'Restore point %d', AI1WMKE_PLUGIN_NAME ), $file_index ),
						'path'    => $file_path,
						'date'    => get_date_from_gmt( date( 'Y-m-d H:i:s', $file_mtime ), 'M j, Y g:i a' ),
						'size'    => ai1wm_size_format( $file_size ),
						'bytes'   => $file_size,
						'type'    => 'file',
						'node_id' => $node_id,
					);
				}
			}
		}

		echo json_encode( array( 'items' => array_reverse( $items ), 'cursor' => null ) );
		exit;
	}
}
