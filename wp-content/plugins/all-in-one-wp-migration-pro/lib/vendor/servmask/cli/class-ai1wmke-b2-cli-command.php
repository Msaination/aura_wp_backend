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
 * Backblaze B2
 *
 * ## EXAMPLES
 *
 *     # Backup site to Backblaze B2.
 *     $ wp ai1wm b2 backup
 *     Success: Backup completed.
 *
 *     # List existing Backblaze B2 backups.
 *     $ wp ai1wm b2 list-backups
 *     +--------------------------------------+--------------+----------+
 *     | Backup name                          | Date created | Size     |
 *     +--------------------------------------+--------------+----------+
 *     | wordpress-20181123-071143-621.wpress | 1 minute ago | 16,19 MB |
 *     | wordpress-20181122-130356-301.wpress | 18 hours ago | 16,19 MB |
 *     +--------------------------------------+--------------+----------+
 *
 *     # Restore website from Backblaze B2 backups.
 *     $ wp ai1wm b2 restore wordpress-20181123-071143-621.wpress --yes
 *     Success: Restore completed.
 */
class Ai1wmke_B2_CLI_Command extends Ai1wmke_Backup_CLI_Base {

	public function __construct( $params = array() ) {
		parent::__construct( array_merge( $params, array( 'b2' => 1 ) ) );

		// Check connection details
		if ( ! get_option( 'ai1wmke_b2_account_id', false ) || ! get_option( 'ai1wmke_b2_application_key', false ) ) {
			WP_CLI::error_multi_line(
				array(
					__( 'In order to use Backblaze B2 you need to configure it first.', AI1WMKE_PLUGIN_NAME ),
					__( 'Please navigate to WP Admin > All-in-One WP Migration > Backblaze B2 Settings and configure your Backblaze B2 connection details.', AI1WMKE_PLUGIN_NAME ),
				)
			);
			exit;
		}
	}

	/**
	 * Creates a new backup and uploads to Backblaze B2.
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
	 * $ wp ai1wm b2 backup --replace "wp" "WordPress"
	 * Backup in progress...
	 * Uploading wordpress-20190509-124938-405.wpress (33 MB) [60% complete]
	 * Uploading wordpress-20190509-124938-405.wpress (33 MB) [100% complete]
	 * Success: Backup completed.
	 * Backup file: wordpress-20190509-124938-405.wpress
	 * Backup location: [bucket: servmask] [path: backups/premium/servmask/daily/wordpress-20190509-124938-405.wpress]
	 *
	 * @subcommand backup
	 */
	public function backup( $args = array(), $assoc_args = array() ) {
		$params = $this->run_backup(
			$this->build_export_params( $args, $assoc_args )
		);

		WP_CLI::log( sprintf( __( 'Backup location: %s', AI1WMKE_PLUGIN_NAME ), $this->get_backup_location( $params ) ) );
	}

	/**
	 * Get a list of Backblaze B2 backup files.
	 *
	 * ## OPTIONS
	 *
	 * [--folder-path=<path>]
	 * : List backups in a specific b2 subfolder
	 *
	 * ## EXAMPLES
	 *
	 * $ wp ai1wm b2 list-backups
	 * +------------------------------------------------+--------------+-----------+
	 * | Backup name                                    | Date created | Size      |
	 * +------------------------------------------------+--------------+-----------+
	 * | migration-wp-20170908-152313-435.wpress        | 4 days ago   | 536.77 MB |
	 * | migration-wp-20170908-152103-603.wpress        | 4 days ago   | 536.77 MB |
	 * | migration-wp-20170908-152036-162.wpress        | 4 days ago   | 536.77 MB |
	 * +------------------------------------------------+--------------+-----------+
	 *
	 * $ wp ai1wm b2 list-backups --folder-path=/backups/daily
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

		$folder_path = $this->get_folder_path( $assoc_args );
		$items       = $this->list_items( $folder_path );

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
	 * Restores a backup from Backblaze B2.
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : Name of the backup file
	 *
	 * [--folder-path=<path>]
	 * : Download a backup from a specific b2 subfolder
	 *
	 * [--yes]
	 * : Automatically confirm the restore operation
	 *
	 * ## EXAMPLES
	 *
	 * $ wp ai1wm b2 restore migration-wp-20170913-095743-931.wpress
	 * Restore in progress...
	 * Restore completed.
	 *
	 * $ wp ai1wm b2 restore migration-wp-20170913-095743-931.wpress --folder-path=/backups/daily
	 * @subcommand restore
	 */
	public function restore( $args = array(), $assoc_args = array() ) {
		if ( ! isset( $args[0] ) ) {
			WP_CLI::error_multi_line(
				array(
					__( 'A backup name must be provided in order to proceed with the restore process.', AI1WMKE_PLUGIN_NAME ),
					__( 'Example: wp ai1wm b2 restore migration-wp-20170913-095743-931.wpress', AI1WMKE_PLUGIN_NAME ),
				)
			);
			exit;
		}

		$folder_path = $this->get_folder_path( $assoc_args );
		$items       = $this->list_items( $folder_path );

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
					__( "The backup file could not be located in $folder_path folder.", AI1WMKE_PLUGIN_NAME ),
					__( 'To list available backups use: wp ai1wm b2 list-backups', AI1WMKE_PLUGIN_NAME ),
				)
			);
			exit;
		}

		$params = array(
			'archive'    => $args[0],
			'storage'    => ai1wm_storage_folder(),
			'file_id'    => $file['id'],
			'file_size'  => $file['bytes'],
			'cli_args'   => $assoc_args,
			'secret_key' => get_option( AI1WM_SECRET_KEY, false ),
		);

		$this->run_restore( $params );
	}

	/**
	 * Get backup items list
	 *
	 * @param  string $folder_path Folder path where backups located
	 * @return array  Backup items
	 */
	protected function list_items( $folder_path ) {
		$items = array();

		// Set Backblaze B2 client
		$b2 = new Ai1wmke_B2_Client(
			get_option( 'ai1wmke_b2_account_id', false ),
			get_option( 'ai1wmke_b2_application_key', false )
		);

		$b2->authorize_account();
		$bucket_id = get_option( 'ai1wmke_b2_bucket_id', false );

		try {
			$items = $b2->list_file_names( $bucket_id, $folder_path );
		} catch ( Exception $e ) {
			WP_CLI::error( $e->getMessage() );
			exit;
		}

		Ai1wmke_File_Sorter::sort( $items, Ai1wmke_File_Sorter::by_date_desc() );

		return $items;
	}

	/**
	 * Get folder path from command-line or WP settings
	 *
	 * @param  array  $assoc_args CLI params
	 * @return string Folder path
	 */
	protected function get_folder_path( $assoc_args ) {
		$path = get_option( 'ai1wmke_b2_folder_name', '' );
		if ( isset( $assoc_args['folder-path'] ) ) {
			$path = trim( $assoc_args['folder-path'], '/' );
		}
		return empty( $path ) ? '' : sprintf( '%s/', $path );
	}

	/**
	 * Get backup location string
	 *
	 * @param  array  $params Params
	 * @return string Human-readable backup file location
	 */
	protected function get_backup_location( $params ) {
		// Set file path
		$file_path = sprintf( '%s/%s', $params['folder_name'], $params['archive'] );
		$file_path = trim( $file_path, '/' );

		return sprintf( '[bucket: %s] [path: %s]', $params['bucket_name'], $file_path );
	}
}
