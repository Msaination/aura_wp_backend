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

/**
 * Box
 *
 * ## EXAMPLES
 *
 *     # Backup site to Box.
 *     $ wp ai1wm box backup
 *     Success: Backup completed.
 *
 *     # List existing Box backups.
 *     $ wp ai1wm box list-backups
 *     +--------------------------------------+--------------+----------+
 *     | Backup name                          | Date created | Size     |
 *     +--------------------------------------+--------------+----------+
 *     | wordpress-20181123-071143-621.wpress | 1 minute ago | 16,19 MB |
 *     | wordpress-20181122-130356-301.wpress | 18 hours ago | 16,19 MB |
 *     +--------------------------------------+--------------+----------+
 *
 *     # Restore website from Box backups.
 *     $ wp ai1wm box restore wordpress-20181123-071143-621.wpress --yes
 *     Success: Restore completed.
 */
class Ai1wmke_Box_CLI_Command extends Ai1wmke_Backup_CLI_Base {

	public function __construct( $params = array() ) {
		parent::__construct( array_merge( $params, array( 'box' => 1 ) ) );

		// Check connection details
		if ( ! get_option( 'ai1wmke_box_token', false ) ) {
			WP_CLI::error_multi_line(
				array(
					__( 'In order to use Box you need to configure it first.', AI1WMKE_PLUGIN_NAME ),
					__( 'Please navigate to WP Admin > All-in-One WP Migration > Box Settings and Link your Box account.', AI1WMKE_PLUGIN_NAME ),
				)
			);
			exit;
		}
	}

	/**
	 * Creates a new backup and uploads to Box.
	 *
	 * ## OPTIONS
	 *
	 * [--sites[=<comma_separated_ids>]]
	 * : Export sites by id (Multisite only). To list sites use: wp site list --fields=blog_id,url
	 *
	 * [--password[=<password>]]
	 * : Encrypt backup with password
	 *
	 * [--compression[=<type>]]
	 * : Compress backup with the specified type: gzip or bzip2
	 *
	 * [--exclude-spam-comments]
	 * : Do not export spam comments
	 *
	 * [--exclude-post-revisions]
	 * : Do not export post revisions
	 *
	 * [--exclude-media]
	 * : Do not export media library (files)
	 *
	 * [--exclude-themes]
	 * : Do not export themes (files)
	 *
	 * [--exclude-inactive-themes]
	 * : Do not export inactive themes (files)
	 *
	 * [--exclude-muplugins]
	 * : Do not export must-use plugins (files)
	 *
	 * [--exclude-plugins]
	 * : Do not export plugins (files)
	 *
	 * [--exclude-inactive-plugins]
	 * : Do not export inactive plugins (files)
	 *
	 * [--exclude-cache]
	 * : Do not export cache (files)
	 *
	 * [--exclude-files[=<comma_separated_paths>]]
	 * : Do not export the selected files or folders (paths relative to wp-content, e.g. uploads/large-folder,plugins/some-plugin)
	 *
	 * [--exclude-database]
	 * : Do not export database (sql)
	 *
	 * [--exclude-tables[=<comma_separated_names>]]
	 * : Do not export selected database tables (sql)
	 *
	 * [--include-tables[=<comma_separated_names>]]
	 * : Include the selected non‑WP tables (sql)
	 *
	 * [--exclude-email-replace]
	 * : Do not replace email domain (sql)
	 *
	 * [--replace]
	 * : Find and replace text in the database
	 *
	 * [<find>...]
	 * : A string to search for within the database
	 *
	 * [<replace>...]
	 * : Replace instances of the first string with this new string
	 *
	 * ## EXAMPLES
	 *
	 * wp ai1wm box backup --replace "wp" "WordPress"
	 * Backup in progress...
	 * Uploading wordpress-20190509-134945-568.wpress (33 MB) [60% complete]
	 * Uploading wordpress-20190509-134945-568.wpress (33 MB) [100% complete]
	 * Success: Backup completed.
	 * Backup file: wordpress-20190509-134945-568.wpress
	 * Backup location: https://app.box.com/folder/76087541737
	 *
	 * @subcommand backup
	 */
	public function backup( $args = array(), $assoc_args = array() ) {
		$params = $this->run_backup(
			$this->build_export_params( $args, $assoc_args )
		);

		WP_CLI::log( sprintf( __( 'Backup location: %s', AI1WMKE_PLUGIN_NAME ), $this->get_backup_url( $params ) ) );
	}

	/**
	 * Get a list of Box backup files.
	 *
	 * ## OPTIONS
	 *
	 * [--folder-path=<path>]
	 * : List backups in a specific Box subfolder
	 *
	 * ## EXAMPLES
	 *
	 * $ wp ai1wm box list-backups
	 * +------------------------------------------------+--------------+-----------+
	 * | Backup name                                    | Date created | Size      |
	 * +------------------------------------------------+--------------+-----------+
	 * | migration-wp-20170908-152313-435.wpress        | 4 days ago   | 536.77 MB |
	 * | migration-wp-20170908-152103-603.wpress        | 4 days ago   | 536.77 MB |
	 * | migration-wp-20170908-152036-162.wpress        | 4 days ago   | 536.77 MB |
	 * +------------------------------------------------+--------------+-----------+
	 *
	 * $ wp ai1wm box list-backups --folder-path=/backups/daily
	 * +------------------------------------------------+--------------+-----------+
	 * | Backup name                                    | Date created | Size      |
	 * +------------------------------------------------+--------------+-----------+
	 * | migration-wp-20170908-152313-435.wpress        | 4 days ago   | 536.77 MB |
	 * | migration-wp-20170908-152103-603.wpress        | 4 days ago   | 536.77 MB |
	 * +------------------------------------------------+--------------+-----------+
	 *
	 * @subcommand list-backups
	 */
	public function list_backups( $args = array(), $assoc_args = array() ) {
		$backups = new cli\Table();

		$backups->setHeaders(
			array(
				'name' => __( 'Backup name', AI1WMKE_PLUGIN_NAME ),
				'date' => __( 'Date created', AI1WMKE_PLUGIN_NAME ),
				'size' => __( 'Size', AI1WMKE_PLUGIN_NAME ),
			)
		);

		$folder_id = $this->get_folder_id( $assoc_args );
		$items     = $this->list_items( $folder_id );

		// Set folder structure
		$response = array( 'items' => array(), 'num_hidden_files' => 0 );

		foreach ( $items as $item ) {
			if ( pathinfo( $item['name'], PATHINFO_EXTENSION ) === 'wpress' ) {
				$backups->addRow(
					array(
						'name' => $item['name'],
						'date' => sprintf( __( '%s ago', AI1WMKE_PLUGIN_NAME ), human_time_diff( $item['date'] ) ),
						'size' => ai1wm_size_format( $item['bytes'], 2 ),
					)
				);
			}
		}

		$backups->display();
	}

	/**
	 * Restores a backup from Box.
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : Name of the backup file
	 *
	 * [--folder-path=<path>]
	 * : Download a backup from a specific Box folder
	 *
	 * [--yes]
	 * : Automatically confirm the restore operation
	 *
	 * ## EXAMPLES
	 *
	 * $ wp ai1wm box restore migration-wp-20170913-095743-931.wpress
	 * Restore in progress...
	 * Restore completed.
	 *
	 * $ wp ai1wm box restore migration-wp-20170913-095743-931.wpress --folder-path=/backups/daily
	 * @subcommand restore
	 */
	public function restore( $args = array(), $assoc_args = array() ) {
		if ( ! isset( $args[0] ) ) {
			WP_CLI::error_multi_line(
				array(
					__( 'A backup name must be provided in order to proceed with the restore process.', AI1WMKE_PLUGIN_NAME ),
					__( 'Example: wp ai1wm box restore migration-wp-20170913-095743-931.wpress', AI1WMKE_PLUGIN_NAME ),
				)
			);
			exit;
		}

		$folder_id = $this->get_folder_id( $assoc_args );
		$items     = $this->list_items( $folder_id );

		$file = null;
		foreach ( $items as $item ) {
			if ( $item['name'] === $args[0] ) {
				$file = $item;
				break;
			}
		}

		if ( is_null( $file ) ) {
			WP_CLI::error_multi_line(
				array(
					__( 'The backup file could not be located.', AI1WMKE_PLUGIN_NAME ),
					__( 'To list available backups use: wp ai1wm box list-backups', AI1WMKE_PLUGIN_NAME ),
				)
			);
			exit;
		}

		$params = array(
			'archive'    => $args[0],
			'storage'    => ai1wm_storage_folder(),
			'folder_id'  => $file['id'],
			'cli_args'   => $assoc_args,
			'secret_key' => get_option( AI1WM_SECRET_KEY, false ),
		);

		$this->run_restore( $params );
	}

	/**
	 * Get backup items list
	 *
	 * @param  string $folder_id Folder ID where backups located
	 * @return array  Backup items
	 */
	protected function list_items( $folder_id ) {
		$items = array();

		// Set Box client
		$box = new Ai1wmke_Box_Client(
			get_option( 'ai1wmke_box_token', false ),
			get_option( 'ai1wmke_box_ssl', true )
		);

		try {
			$items = $box->list_folder_by_id( $folder_id );
		} catch ( Exception $e ) {
			WP_CLI::error( $e->getMessage() );
			exit;
		}

		Ai1wmke_File_Sorter::sort( $items, Ai1wmke_File_Sorter::by_date_desc() );

		return $items;
	}

	/**
	 * Get folder ID from command-line or WP settings
	 *
	 * @param  array  $assoc_args CLI params
	 * @return string Folder ID
	 */
	protected function get_folder_id( $assoc_args ) {
		if ( isset( $assoc_args['folder-path'] ) ) {
			// Set Box client
			$box = new Ai1wmke_Box_Client(
				get_option( 'ai1wmke_box_token', false ),
				get_option( 'ai1wmke_box_ssl', true )
			);

			$path      = explode( '/', trim( $assoc_args['folder-path'], '/' ) );
			$parent_id = 0;
			foreach ( $path as $folder_name ) {
				try {
					$folder_id = $box->get_folder_id_by_name( $folder_name, $parent_id );
				} catch ( Ai1wmke_Error_Exception $e ) {
					WP_CLI::error( sprintf( __( 'Folder \'%s\' not found', AI1WMKE_PLUGIN_NAME ), $folder_name ) );
					exit;
				}
				$parent_id = $folder_id;
			}
			return $folder_id;
		}
		return get_option( 'ai1wmke_box_folder_id', null );
	}

	/**
	 * Get backup file location URL
	 *
	 * @param  array  $params Params
	 * @return string Box.com URL to the resulting backup file
	 */
	protected function get_backup_url( $params ) {
		// Set Box client
		$box = new Ai1wmke_Box_Client(
			get_option( 'ai1wmke_box_token', false ),
			get_option( 'ai1wmke_box_ssl', true )
		);

		// Getting backup folder ID
		$folder_id = $box->get_folder_id_by_name( $params['archive'], $params['parent_id'] );

		// Composing folder browsing URL
		return sprintf( 'https://app.box.com/folder/%s', $folder_id );
	}
}
