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

class Ai1wmke_GCloud_Storage_Export_Retention extends Ai1wmke_Export_Retention_Base {

	/**
	 * GCloud Storage client
	 *
	 * @var Ai1wmke_GCloud_Storage_Client
	 */
	protected $gcloud = null;

	/**
	 * Bucket name
	 *
	 * @var string
	 */
	protected $bucket_name = null;

	/**
	 * Prefix
	 *
	 * @var string
	 */
	protected $prefix = null;

	protected function run() {
		// No bucket, no need to apply backup retention
		if ( ! $this->gcloud->is_bucket_available( $this->bucket_name ) ) {
			return $this->params;
		}

		return parent::run();
	}

	protected function get_files() {
		$items = $this->gcloud->get_objects_by_bucket( $this->bucket_name, array( 'delimiter' => '/', 'prefix' => $this->prefix ) );

		$backups = array();
		foreach ( $items as $item ) {
			if ( $item['type'] === 'file' && pathinfo( $item['name'], PATHINFO_EXTENSION ) === 'wpress' ) {
				$backups[] = $item;
			}
		}

		return Ai1wmke_File_Sorter::sort( $backups, Ai1wmke_File_Sorter::by_date_desc( $this->file_date_key() ) );
	}

	protected function delete_file( $backup ) {
		return $this->gcloud->delete( $backup['path'], $this->bucket_name );
	}

	protected function setup_client( $client ) {
		// Set GCloud Storage client
		if ( is_null( $client ) ) {
			$client = new Ai1wmke_GCloud_Storage_Client(
				get_option( 'ai1wmke_gcloud_storage_token' ),
				get_option( 'ai1wmke_gcloud_storage_ssl', true )
			);
		}

		$this->gcloud      = $client;
		$this->bucket_name = $this->params['bucket_name'];

		if ( ! empty( $this->params['folder_name'] ) ) {
			$this->prefix = sprintf( '%s/', $this->params['folder_name'] );
		}
	}

	protected function get_options_prefix() {
		return 'ai1wmke_gcloud_storage';
	}
}
