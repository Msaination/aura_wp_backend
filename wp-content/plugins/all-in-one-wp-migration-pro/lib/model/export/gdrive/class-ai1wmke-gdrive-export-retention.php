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

class Ai1wmke_GDrive_Export_Retention extends Ai1wmke_Export_Retention_Base {

	/**
	 * GDrive client
	 *
	 * @var Ai1wmke_GDrive_Client
	 */
	protected $gdrive = null;

	/**
	 * Team Drive ID
	 *
	 * @var string
	 */
	protected $team_drive_id = null;

	/**
	 * Folder ID
	 *
	 * @var string
	 */
	protected $folder_id = null;

	protected function get_files() {
		$backups         = array();
		$next_page_token = null;

		do {
			$data = $this->gdrive->list_folder_by_id( $this->folder_id, $this->team_drive_id, $next_page_token, "fileExtension = 'wpress'", array( 'orderBy' => 'createdDate desc' ) );
			if ( isset( $data['items'] ) ) {
				foreach ( $data['items'] as $item ) {
					$backups[] = $item;
				}
			}
		} while ( isset( $data['token'] ) && ( $next_page_token = $data['token'] ) );

		return Ai1wmke_File_Sorter::sort( $backups, Ai1wmke_File_Sorter::by_date_desc( $this->file_date_key() ) );
	}

	protected function delete_file( $backup ) {
		return $this->gdrive->delete( $backup['id'] );
	}

	protected function setup_client( $client ) {
		// Set GDrive client
		if ( is_null( $client ) ) {
			$client = new Ai1wmke_GDrive_Client(
				get_option( 'ai1wmke_gdrive_token', false ),
				get_option( 'ai1wmke_gdrive_ssl', true )
			);
		}

		$this->gdrive        = $client;
		$this->team_drive_id = $this->params['team_drive_id'];
		$this->folder_id     = $this->params['folder_id'];
	}

	protected function get_options_prefix() {
		return 'ai1wmke_gdrive';
	}
}
