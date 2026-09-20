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

class Ai1wmke_WebDAV_Export_Retention extends Ai1wmke_Export_Retention_Base {

	/**
	 * WebDAV client
	 *
	 * @var Ai1wmke_WebDAV_Client
	 */
	protected $webdav = null;

	protected function get_files() {
		$items = $this->webdav->list_folder( ai1wm_archive_folder() );

		$backups = array();
		foreach ( $items as $item ) {
			if ( $item['type'] === 'file' && $item['ext'] === 'wpress' ) {
				$backups[] = $item;
			}
		}

		return Ai1wmke_File_Sorter::sort( $backups, Ai1wmke_File_Sorter::by_date_desc( $this->file_date_key() ) );
	}

	protected function delete_file( $backup ) {
		return $this->webdav->remove_folder( $backup['path'] );
	}

	protected function setup_client( $client ) {
		// Set WebDAV client
		if ( is_null( $client ) ) {
			$client = new Ai1wmke_WebDAV_Client(
				get_option( 'ai1wmke_webdav_type', AI1WMKE_WEBDAV_TYPE ),
				get_option( 'ai1wmke_webdav_hostname', false ),
				get_option( 'ai1wmke_webdav_username', false ),
				get_option( 'ai1wmke_webdav_password', false ),
				get_option( 'ai1wmke_webdav_authentication', AI1WMKE_WEBDAV_AUTHENTICATION ),
				get_option( 'ai1wmke_webdav_directory', false ),
				get_option( 'ai1wmke_webdav_port', AI1WMKE_WEBDAV_PORT )
			);
		}

		$this->webdav = $client;
	}

	protected function get_options_prefix() {
		return 'ai1wmke_webdav';
	}
}
