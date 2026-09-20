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
 * Amazon Glacier
 *
 * ## EXAMPLES
 *
 *     # Backup site to Amazon Glacier.
 *     $ wp ai1wm glacier backup
 *     Success: Backup completed.
 *
 *     # List existing Amazon Glacier backups.
 *     $ wp ai1wm glacier list-backups
 *     +--------------------------------------+--------------+----------+
 *     | Backup name                          | Date created | Size     |
 *     +--------------------------------------+--------------+----------+
 *     | wordpress-20181123-071143-621.wpress | 1 minute ago | 16,19 MB |
 *     | wordpress-20181122-130356-301.wpress | 18 hours ago | 16,19 MB |
 *     +--------------------------------------+--------------+----------+
 *
 *     # Restore website from Amazon Glacier backups.
 *     $ wp ai1wm glacier restore wordpress-20181123-071143-621.wpress --yes
 *     Success: Restore completed.
 */
class Ai1wmke_Glacier_CLI_Command extends Ai1wmke_Backup_CLI_Base {

	public function __construct( $params = array() ) {
		parent::__construct( array_merge( $params, array( 'glacier' => 1 ) ) );

		// Check connection details
		if ( ! get_option( 'ai1wmke_glacier_account_id', false ) || ! get_option( 'ai1wmke_glacier_access_key', ai1wmke_aws_access_key() ) || ! get_option( 'ai1wmke_glacier_secret_key', ai1wmke_aws_secret_key() ) ) {
			WP_CLI::error_multi_line(
				array(
					__( 'In order to use Amazon Glacier you need to configure it first.', AI1WMKE_PLUGIN_NAME ),
					__( 'Please navigate to WP Admin > All-in-One WP Migration > Amazon Glacier Settings and configure your Amazon Glacier connection details.', AI1WMKE_PLUGIN_NAME ),
				)
			);
			exit;
		}
	}

	/**
	 * Creates a new backup and uploads to Amazon Glacier.
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
	 * $ wp ai1wm glacier backup --replace "wp" "WordPress"
	 * Backup in progress...
	 * Uploading wordpress-20190509-142628-179.wpress (33 MB) [100% complete]
	 * Success: Backup completed.
	 * Backup file: wordpress-20190509-142628-179.wpress
	 * Backup location: [region: us-east-1] [vault: servmask] [archive: wordpress-20190509-142628-179.wpress]
	 *
	 * @subcommand backup
	 */
	public function backup( $args = array(), $assoc_args = array() ) {
		$params = $this->run_backup(
			$this->build_export_params( $args, $assoc_args )
		);

		WP_CLI::log( sprintf( __( 'Backup location: %s', AI1WMKE_PLUGIN_NAME ), $this->get_backup_uri( $params ) ) );
	}

	/**
	 * Get a list of Amazon Glacier backup files.
	 *
	 * ## OPTIONS
	 *
	 * ## EXAMPLES
	 *
	 * $ wp ai1wm glacier list-backups
	 * +------------------------------------------------+--------------+-----------+
	 * | Backup name                                    | Date created | Size      |
	 * +------------------------------------------------+--------------+-----------+
	 * | migration-wp-20170908-152313-435.wpress        | 4 days ago   | 536.77 MB |
	 * | migration-wp-20170908-152103-603.wpress        | 4 days ago   | 536.77 MB |
	 * | migration-wp-20170908-152036-162.wpress        | 4 days ago   | 536.77 MB |
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

		$items = $this->list_items();

		// Set folder structure
		$response = array( 'items' => array(), 'num_hidden_files' => 0 );

		foreach ( $items as $item ) {
			if ( pathinfo( $item['name'], PATHINFO_EXTENSION ) === 'wpress' ) {
				$backups->addRow(
					array(
						'name' => $item['name'],
						'date' => sprintf( __( '%s ago', AI1WMKE_PLUGIN_NAME ), $item['date'] ),
						'size' => $item['size'],
					)
				);
			}
		}

		$backups->display();
	}

	/**
	 * Restores a backup from Amazon Glacier.
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : Name of the backup file
	 *
	 * [--yes]
	 * : Automatically confirm the restore operation
	 *
	 * ## EXAMPLES
	 *
	 * $ wp ai1wm glacier restore migration-wp-20170913-095743-931.wpress
	 * Restore in progress...
	 * Restore completed.
	 *
	 * @subcommand restore
	 */
	public function restore( $args = array(), $assoc_args = array() ) {
		if ( ! isset( $args[0] ) ) {
			WP_CLI::error_multi_line(
				array(
					__( 'A backup name must be provided in order to proceed with the restore process.', AI1WMKE_PLUGIN_NAME ),
					__( 'Example: wp ai1wm glacier restore migration-wp-20170913-095743-931.wpress', AI1WMKE_PLUGIN_NAME ),
				)
			);
			exit;
		}

		$items = $this->list_items();

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
					__( 'To list available backups use: wp ai1wm glacier list-backups', AI1WMKE_PLUGIN_NAME ),
				)
			);
			exit;
		}

		$params = array(
			'archive'     => $args[0],
			'storage'     => ai1wm_storage_folder(),
			'file_size'   => $file['bytes'],
			'vault_name'  => $file['vault_name'],
			'region_name' => $file['region_name'],
			'job_id'      => (string) $file['job_id'],
			'cli_args'    => $assoc_args,
			'secret_key'  => get_option( AI1WM_SECRET_KEY, false ),
		);

		$this->run_restore( $params );
	}

	/**
	 * Get backup items list
	 *
	 * @return array  Backup items
	 */
	protected function list_items() {
		$inventory_retrieval_archives = array();

		// Set Amazon Glacier client
		$glacier = new Ai1wmke_Glacier_Client(
			get_option( 'ai1wmke_glacier_account_id', false ),
			get_option( 'ai1wmke_glacier_access_key', ai1wmke_aws_access_key() ),
			get_option( 'ai1wmke_glacier_secret_key', ai1wmke_aws_secret_key() )
		);

		$vault_name  = get_option( 'ai1wmke_glacier_vault_name', ai1wm_archive_vault() );
		$region_name = get_option( 'ai1wmke_glacier_region_name', ai1wmke_aws_region_name( AI1WMKE_GLACIER_REGION_NAME ) );

		try {

			// Get inventory retrieval jobs
			$inventory_retrieval_jobs = $glacier->get_inventory_retrieval_jobs( $vault_name, $region_name );

			// Loop over inventory retrieval jobs
			if ( count( $inventory_retrieval_jobs ) > 0 ) {
				if ( isset( $inventory_retrieval_jobs[0] ) && ( $job = $inventory_retrieval_jobs[0] ) ) {
					if ( empty( $job['completed'] ) ) {
						WP_CLI::error( __( 'Amazon Glacier is still retrieving this vault. Please come back later.', AI1WMKE_PLUGIN_NAME ) );
						exit;
					}

					// Get job output
					$inventory_retrieval_archives = $glacier->get_job_output( $job['id'], $vault_name, $region_name );
				}
			} else {

				// Initiate inventory retrieval job
				if ( $glacier->initiate_inventory_retrieval( $vault_name, $region_name ) ) {
					WP_CLI::error( __( 'We have initiated retrieval of the vault. This process takes 4-5 hours. Please come back later to restore your backup.', AI1WMKE_PLUGIN_NAME ) );
					exit;
				}
			}
		} catch ( Ai1wmke_Error_Exception $e ) {
			try {

				// Initiate inventory retrieval job
				if ( $glacier->initiate_inventory_retrieval( $vault_name, $region_name ) ) {
					WP_CLI::error( __( 'We have initiated retrieval of the vault. This process takes 4-5 hours. Please come back later to restore your backup.', AI1WMKE_PLUGIN_NAME ) );
					exit;
				}
			} catch ( Ai1wmke_Error_Exception $e ) {
				WP_CLI::error( __( 'Unable to initiate retrieval of the vault. Please check your data retrieval policy in Amazon Glacier Console.', AI1WMKE_PLUGIN_NAME ) );
				exit;
			}
		} catch ( Exception $e ) {
			WP_CLI::error( $e->getMessage() );
			exit;
		}

		return $inventory_retrieval_archives;
	}

	/**
	 * Get backup location URL
	 *
	 * @param  array  $params Params
	 * @return string Backup file location URL
	 */
	protected function get_backup_uri( $params ) {
		$region = $params['region_name'];
		$vault  = $params['vault_name'];
		$file   = ai1wm_archive_name( $params );

		return sprintf( '[region: %s] [vault: %s] [archive: %s]', $region, $vault, $file );
	}
}
