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

class Ai1wmke_DigitalOcean_Export_Retention extends Ai1wmke_Export_Retention_Base {

	/**
	 * DigitalOcean client
	 *
	 * @var Ai1wmke_DigitalOcean_Client
	 */
	protected $digitalocean = null;

	/**
	 * Bucket name
	 *
	 * @var string
	 */
	protected $bucket_name = null;

	/**
	 * Region name
	 *
	 * @var string
	 */
	protected $region_name = null;

	/**
	 * Prefix
	 *
	 * @var string
	 */
	protected $prefix = null;

	protected function run() {
		// No bucket, no need to apply backup retention
		if ( ! $this->digitalocean->is_bucket_available( $this->bucket_name, $this->region_name ) ) {
			return $this->params;
		}

		return parent::run();
	}

	protected function get_files() {
		$items = $this->digitalocean->get_objects_by_bucket( $this->bucket_name, $this->region_name, array( 'delimiter' => '/', 'prefix' => $this->prefix ) );

		$backups = array();
		foreach ( $items as $item ) {
			if ( $item['type'] === 'file' && pathinfo( $item['name'], PATHINFO_EXTENSION ) === 'wpress' ) {
				$backups[] = $item;
			}
		}

		return Ai1wmke_File_Sorter::sort( $backups, Ai1wmke_File_Sorter::by_date_desc( $this->file_date_key() ) );
	}

	protected function delete_file( $backup ) {
		return $this->digitalocean->remove_file( $backup['path'], $this->bucket_name, $this->region_name );
	}

	protected function setup_client( $client ) {
		// Set DigitalOcean Spaces client
		if ( is_null( $client ) ) {
			$client = new Ai1wmke_DigitalOcean_Client(
				get_option( 'ai1wmke_digitalocean_access_key', ai1wmke_aws_access_key() ),
				get_option( 'ai1wmke_digitalocean_secret_key', ai1wmke_aws_secret_key() )
			);
		}

		$this->digitalocean = $client;
		$this->bucket_name  = $this->params['bucket_name'];
		$this->region_name  = $this->params['region_name'];

		if ( ! empty( $this->params['folder_name'] ) ) {
			$this->prefix = sprintf( '%s/', $this->params['folder_name'] );
		}
	}

	protected function get_options_prefix() {
		return 'ai1wmke_digitalocean';
	}
}
