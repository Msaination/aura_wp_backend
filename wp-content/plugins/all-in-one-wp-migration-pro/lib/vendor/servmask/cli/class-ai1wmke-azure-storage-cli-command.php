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
 * Microsoft Azure Storage
 *
 * ## EXAMPLES
 *
 *     # Backup site to Microsoft Azure Storage.
 *     $ wp ai1wm azure-storage backup
 *     Success: Backup completed.
 *
 *     # List existing Microsoft Azure Storage backups.
 *     $ wp ai1wm azure-storage list-backups
 *     +--------------------------------------+--------------+----------+
 *     | Backup name                          | Date created | Size     |
 *     +--------------------------------------+--------------+----------+
 *     | wordpress-20181123-071143-621.wpress | 1 minute ago | 16,19 MB |
 *     | wordpress-20181122-130356-301.wpress | 18 hours ago | 16,19 MB |
 *     +--------------------------------------+--------------+----------+
 *
 *     # Restore website from Microsoft Azure Storage backups.
 *     $ wp ai1wm azure-storage restore wordpress-20181123-071143-621.wpress --yes
 *     Success: Restore completed.
 */
class Ai1wmke_Azure_Storage_CLI_Command extends Ai1wmke_Backup_CLI_Base {

	public function __construct( $params = array() ) {
		parent::__construct( array_merge( $params, array( 'azure-storage' => 1 ) ) );

		// Check connection details
		if ( ! get_option( 'ai1wmke_azure_storage_account_key', false ) ) {
			WP_CLI::error_multi_line(
				array(
					__( 'In order to use Microsoft Azure Storage you need to configure it first.', AI1WMKE_PLUGIN_NAME ),
					__( 'Please navigate to WP Admin > All-in-One WP Migration > Microsoft Azure Storage Settings and configure your Microsoft Azure Storage connection details.', AI1WMKE_PLUGIN_NAME ),
				)
			);
			exit;
		}
	}

	/**
	 * Creates a new backup and uploads to Azure Storage.
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
	 * $ wp ai1wm azure-storage backup
	 * Backup in progress...
	 * Uploading wordpress-20190509-115817-344.wpress (33 MB) [12% complete]
	 * Uploading wordpress-20190509-115817-344.wpress (33 MB) [48% complete]
	 * Uploading wordpress-20190509-115817-344.wpress (33 MB) [100% complete]
	 * Success: Backup completed.
	 * Backup file: wordpress-20190509-115817-344.wpress
	 * Backup location: [account: servmask] [share: backups] [path: wordpress-20190509-115817-344.wpress]
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
	 * Get a list of Azure Storage backup files.
	 *
	 * ## OPTIONS
	 *
	 * [--folder-path=<path>]
	 * : List backups in a specific azure storage folder
	 *
	 * ## EXAMPLES
	 *
	 * $ wp ai1wm azure-storage list-backups
	 * +------------------------------------------------+--------------+-----------+
	 * | Backup name                                    | Date created | Size      |
	 * +------------------------------------------------+--------------+-----------+
	 * | migration-wp-20170908-152313-435.wpress        | 4 days ago   | 536.77 MB |
	 * | migration-wp-20170908-152103-603.wpress        | 4 days ago   | 536.77 MB |
	 * | migration-wp-20170908-152036-162.wpress        | 4 days ago   | 536.77 MB |
	 * +------------------------------------------------+--------------+-----------+
	 *
	 * $ wp ai1wm azure-storage list-backups --folder-path=/backups/daily
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
	 * Restores a backup from Azure Storage.
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : Name of the backup file
	 *
	 * [--folder-path=<path>]
	 * : Download a backup from a specific azure storage subfolder
	 *
	 * [--yes]
	 * : Automatically confirm the restore operation
	 *
	 * ## EXAMPLES
	 *
	 * $ wp ai1wm azure-storage restore migration-wp-20170913-095743-931.wpress
	 * Restore in progress...
	 * Restore completed.
	 *
	 * $ wp ai1wm azure-storage restore migration-wp-20170913-095743-931.wpress --folder-path=/backups/daily
	 * @subcommand restore
	 */
	public function restore( $args = array(), $assoc_args = array() ) {
		if ( ! isset( $args[0] ) ) {
			WP_CLI::error_multi_line(
				array(
					__( 'A backup name must be provided in order to proceed with the restore process.', AI1WMKE_PLUGIN_NAME ),
					__( 'Example: wp ai1wm azure-storage restore migration-wp-20170913-095743-931.wpress', AI1WMKE_PLUGIN_NAME ),
				)
			);
			exit;
		}

		$folder_path = $this->get_folder_path( $assoc_args );
		$items       = $this->list_items( $folder_path );
		$share_name  = get_option( 'ai1wmke_azure_storage_share_name', ai1wm_archive_share() );

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
					__( 'To list available backups use: wp ai1wm azure-storage list-backups', AI1WMKE_PLUGIN_NAME ),
				)
			);
			exit;
		}

		$params = array(
			'archive'    => $args[0],
			'storage'    => ai1wm_storage_folder(),
			'file_path'  => $file['path'],
			'file_size'  => $file['bytes'],
			'share_name' => $share_name,
			'cli_args'   => $assoc_args,
			'secret_key' => get_option( AI1WM_SECRET_KEY, false ),
		);

		$this->run_restore( $params );
	}

	/**
	 * Get backup items list
	 *
	 * @param  string $folder_path Folder path where backups located
	 * @return array
	 */
	protected function list_items( $folder_path ) {
		$items = array();

		// Set Azure Storage client
		$azure = new Ai1wmke_Azure_Storage_Client(
			get_option( 'ai1wmke_azure_storage_account_name', false ),
			get_option( 'ai1wmke_azure_storage_account_key', false )
		);

		$share_name = get_option( 'ai1wmke_azure_storage_share_name', ai1wm_archive_share() );

		try {
			$items = $azure->get_objects_by_share( $share_name, $folder_path );
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
	 * @return string
	 */
	protected function get_folder_path( $assoc_args ) {
		// Default folder path is read from the settings page
		$folder_path = get_option( 'ai1wmke_azure_storage_folder_name', '' );

		// If user specified a custom folder it has priority
		if ( isset( $assoc_args['folder-path'] ) ) {
			$folder_path = $assoc_args['folder-path'];
		}

		// Normalizing the folder path
		$folder_path = trim( $folder_path, '/' );
		if ( ! empty( $folder_path ) ) {
			$folder_path = sprintf( '%s/', $folder_path );
		}

		return $folder_path;
	}

	/**
	 * Get backup location string
	 *
	 * @param  array  $params Params
	 * @return string Human-readable backup file location
	 */
	protected function get_backup_location( $params ) {
		// Set Azure Storage client
		$azure = new Ai1wmke_Azure_Storage_Client(
			get_option( 'ai1wmke_azure_storage_account_name', false ),
			get_option( 'ai1wmke_azure_storage_account_key', false )
		);

		// Set host name
		$account_name = get_option( 'ai1wmke_azure_storage_account_name', false );

		// Set file path
		$file_path = sprintf( '%s/%s', $params['folder_name'], $params['archive'] );
		$file_path = trim( $file_path, '/' );
		$file_path = $azure->rawurlencode_path( $file_path );

		return sprintf( '[account: %s] [share: %s] [path: %s]', $account_name, $params['share_name'], $file_path );
	}
}
