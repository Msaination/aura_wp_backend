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

class Ai1wmke_Dropbox_Export_Retention extends Ai1wmke_Export_Retention_Base {

	/**
	 * Dropbox client
	 *
	 * @var Ai1wmke_Dropbox_Client
	 */
	protected $dropbox = null;

	/**
	 * Folder path
	 *
	 * @var string
	 */
	protected $folder_path = null;

	protected function setup_client( $client ) {
		// Set Dropbox client
		if ( is_null( $client ) ) {
			$client = new Ai1wmke_Dropbox_Client(
				get_option( 'ai1wmke_dropbox_token', false ),
				get_option( 'ai1wmke_dropbox_ssl', true )
			);
		}

		$this->dropbox     = $client;
		$this->folder_path = $this->params['folder_path'];
	}

	protected function get_files() {
		$data = $this->dropbox->list_folder( $this->folder_path, array( 'file' => '/\.wpress$/' ) );

		$items = array();
		if ( isset( $data['items'] ) ) {
			foreach ( $data['items'] as $item ) {
				$items[] = $item;
			}
		}

		return $items;
	}

	protected function delete_file( $backup ) {
		return $this->dropbox->delete( $backup['path'] );
	}

	protected function get_options_prefix() {
		return 'ai1wmke_dropbox';
	}
}
