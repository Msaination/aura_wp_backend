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

class Ai1wmke_FTP_Export_Retention extends Ai1wmke_Export_Retention_Base {

	/**
	 * FTP client
	 *
	 * @var Ai1wmke_FTP_Factory
	 */
	protected $ftp = null;

	protected function get_files() {
		$items = $this->ftp->list_folder( ai1wm_archive_folder() );

		$backups = array();
		foreach ( $items as $item ) {
			if ( $item['type'] === 'file' && pathinfo( $item['name'], PATHINFO_EXTENSION ) === 'wpress' ) {
				$backups[] = $item;
			}
		}

		return Ai1wmke_File_Sorter::sort( $backups, Ai1wmke_File_Sorter::by_date_desc( $this->file_date_key() ) );
	}

	protected function delete_file( $backup ) {
		return $this->ftp->remove_file( $backup['path'] );
	}

	protected function setup_client( $client ) {
		// Set FTP client
		if ( is_null( $client ) ) {
			$client = Ai1wmke_FTP_Factory::create(
				get_option( 'ai1wmke_ftp_type', AI1WMKE_FTP_TYPE ),
				get_option( 'ai1wmke_ftp_hostname', false ),
				get_option( 'ai1wmke_ftp_username', false ),
				get_option( 'ai1wmke_ftp_password', false ),
				get_option( 'ai1wmke_ftp_authentication', AI1WMKE_FTP_AUTHENTICATION ),
				get_option( 'ai1wmke_ftp_key', false ),
				get_option( 'ai1wmke_ftp_passphrase', false ),
				get_option( 'ai1wmke_ftp_directory', false ),
				get_option( 'ai1wmke_ftp_port', AI1WMKE_FTP_PORT ),
				get_option( 'ai1wmke_ftp_active', false )
			);
		}

		$this->ftp = $client;
	}

	protected function get_options_prefix() {
		return 'ai1wmke_ftp';
	}
}
