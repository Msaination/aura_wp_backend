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

class Ai1wmke_B2_Export_Retention extends Ai1wmke_Export_Retention_Base {

	/**
	 * Backblaze B2 client
	 *
	 * @var Ai1wmke_B2_Client
	 */
	protected $b2 = null;

	/**
	 * Bucket ID
	 *
	 * @var string
	 */
	protected $bucket_id = null;

	/**
	 * Folder path
	 *
	 * @var string
	 */
	protected $folder_path = null;

	protected function get_files() {
		$items = $this->b2->list_file_names( $this->bucket_id, $this->folder_path );

		$backups = array();
		foreach ( $items as $item ) {
			if ( $item['type'] === 'upload' && pathinfo( $item['name'], PATHINFO_EXTENSION ) === 'wpress' ) {
				$backups[] = $item;
			}
		}

		return Ai1wmke_File_Sorter::sort( $backups, Ai1wmke_File_Sorter::by_date_desc( $this->file_date_key() ) );
	}

	protected function delete_file( $backup ) {
		return $this->b2->delete_file( $backup['id'], $backup['path'] );
	}

	protected function setup_client( $client ) {
		// Set Backblaze B2 client
		if ( is_null( $client ) ) {
			$client = new Ai1wmke_B2_Client(
				get_option( 'ai1wmke_b2_account_id', false ),
				get_option( 'ai1wmke_b2_application_key', false )
			);
		}

		$client->authorize_account();

		$this->b2        = $client;
		$this->bucket_id = $this->params['bucket_id'];

		if ( ! empty( $this->params['folder_name'] ) ) {
			$this->folder_path = sprintf( '%s/', $this->params['folder_name'] );
		}
	}

	protected function get_options_prefix() {
		return 'ai1wmke_b2';
	}
}
