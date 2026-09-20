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

class Ai1wmke_Mega_Export_Retention extends Ai1wmke_Export_Retention_Base {

	/**
	 * Mega client
	 *
	 * @var Ai1wmke_Mega_Client
	 */
	protected $mega = null;

	/**
	 * Node ID
	 *
	 * @var string
	 */
	protected $node_id = null;

	protected function delete_backups_older_than() {
		$days = $this->get_days();
		if ( $days > 0 ) {
			$backups = $this->get_filtered_files();
			foreach ( $backups as $backup ) {
				if ( $backup->get_last_modified_date() <= time() - $days * 86400 ) {
					$this->delete_file( $backup );
				}
			}
		}
	}

	protected function delete_backups_when_total_size_over() {
		$retention_size = ai1wm_parse_size( $this->get_size() );
		if ( $retention_size > 0 ) {
			$backups = $this->get_filtered_files();

			// Get the size of the latest backup before we remove it
			$size_of_backups = $backups[0]->get_size();

			// Remove the latest backup, the user should have at least one backup
			array_shift( $backups );

			foreach ( $backups as $backup ) {
				if ( $size_of_backups + $backup->get_size() > $retention_size ) {
					$this->delete_file( $backup );
				} else {
					$size_of_backups += $backup->get_size();
				}
			}
		}
	}

	protected function delete_backups_when_total_count_over() {
		$limit = $this->get_limit();
		if ( $limit > 0 ) {
			$backups = $this->get_filtered_files();
			if ( count( $backups ) > $limit ) {
				for ( $i = $limit; $i < count( $backups ); $i++ ) {
					$this->delete_file( $backups[ $i ] );
				}
			}
		}
	}

	protected function is_not_event_file( $file ) {
		// Check if file name does not end with "-event_id.wpress"
		return preg_match( '/-[0-9]+.wpress$/', $file->get_file_name() ) !== 1;
	}

	protected function is_event_file( $file ) {
		// Check if file name ends with "-event_id.wpress" ($this->filter_events_files)
		return substr_compare(
			$file->get_file_name(),
			$this->filter_events_files,
			-strlen( $this->filter_events_files )
		) === 0;
	}

	protected function get_files() {
		$items = $this->mega->list_nodes( $this->node_id );

		$backups = array();
		foreach ( $items as $item ) {
			if ( $item->is_file() && pathinfo( $item->get_file_name(), PATHINFO_EXTENSION ) === 'wpress' ) {
				$backups[] = $item;
			}
		}

		usort( $backups, 'Ai1wmke_Mega_Export_Retention::sort_by_date_desc' );
		return $backups;
	}

	public static function sort_by_date_desc( $first_backup, $second_backup ) {
		return intval( $second_backup->get_last_modified_date() ) - intval( $first_backup->get_last_modified_date() );
	}

	protected function delete_file( $backup ) {
		return $this->mega->delete( $backup->get_node_id() );
	}

	protected function setup_client( $client ) {
		// Set Mega client
		if ( is_null( $client ) ) {
			$client = new Ai1wmke_Mega_Client(
				get_option( 'ai1wmke_mega_user_email', false ),
				get_option( 'ai1wmke_mega_user_password', false )
			);
		}

		$client->load_user_session( get_option( 'ai1wmke_mega_user_session', false ) );

		$this->mega    = $client;
		$this->node_id = $this->params['node_id'];
	}

	protected function get_options_prefix() {
		return 'ai1wmke_mega';
	}
}
