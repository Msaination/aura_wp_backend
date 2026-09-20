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

class Ai1wmke_Extensions {

	/**
	 * Get available extensions
	 *
	 * @return array
	 */
	public static function get() {
		$extensions = array();

		// Microsoft Azure
		if ( defined( 'AI1WMZE_PLUGIN_SHORT' ) ) {
			$extensions[ AI1WMZE_PLUGIN_SHORT ] = array(
				'title' => __( 'Microsoft Azure Storage', AI1WMKE_PLUGIN_NAME ),
				'about' => __( 'Backup and migrate WordPress to Microsoft Azure Storage', AI1WMKE_PLUGIN_NAME ),
			);
		}

		// Backblaze B2
		if ( defined( 'AI1WMAE_PLUGIN_SHORT' ) ) {
			$extensions[ AI1WMAE_PLUGIN_SHORT ] = array(
				'title' => __( 'Backblaze B2', AI1WMKE_PLUGIN_NAME ),
				'about' => __( 'Backup and migrate WordPress to Backblaze B2', AI1WMKE_PLUGIN_NAME ),
			);
		}

		// Box
		if ( defined( 'AI1WMBE_PLUGIN_SHORT' ) ) {
			$extensions[ AI1WMBE_PLUGIN_SHORT ] = array(
				'title' => __( 'Box', AI1WMKE_PLUGIN_NAME ),
				'about' => __( 'Backup and migrate WordPress to Box', AI1WMKE_PLUGIN_NAME ),
			);
		}

		// DigitalOcean Spaces
		if ( defined( 'AI1WMIE_PLUGIN_SHORT' ) ) {
			$extensions[ AI1WMIE_PLUGIN_SHORT ] = array(
				'title' => __( 'DigitalOcean Spaces', AI1WMKE_PLUGIN_NAME ),
				'about' => __( 'Backup and migrate WordPress to DigitalOcean Spaces', AI1WMKE_PLUGIN_NAME ),
			);
		}

		// Direct
		if ( defined( 'AI1WMXE_PLUGIN_SHORT' ) ) {
			$extensions[ AI1WMXE_PLUGIN_SHORT ] = array(
				'title' => __( 'Direct', AI1WMKE_PLUGIN_NAME ),
				'about' => __( 'Backup and migrate WordPress to remote websites (non-multisite)', AI1WMKE_PLUGIN_NAME ),
			);
		}

		// Dropbox
		if ( defined( 'AI1WMDE_PLUGIN_SHORT' ) ) {
			$extensions[ AI1WMDE_PLUGIN_SHORT ] = array(
				'title' => __( 'Dropbox', AI1WMKE_PLUGIN_NAME ),
				'about' => __( 'Backup and migrate WordPress to Dropbox', AI1WMKE_PLUGIN_NAME ),
			);
		}

		// FTP
		if ( defined( 'AI1WMFE_PLUGIN_SHORT' ) ) {
			$extensions[ AI1WMFE_PLUGIN_SHORT ] = array(
				'title' => __( 'FTP', AI1WMKE_PLUGIN_NAME ),
				'about' => __( 'Backup and migrate WordPress to FTP/FTPS server', AI1WMKE_PLUGIN_NAME ),
			);
		}

		// Google Cloud Storage
		if ( defined( 'AI1WMCE_PLUGIN_SHORT' ) ) {
			$extensions[ AI1WMCE_PLUGIN_SHORT ] = array(
				'title' => __( 'Google Cloud Storage', AI1WMKE_PLUGIN_NAME ),
				'about' => __( 'Backup and migrate WordPress to Google Cloud Storage', AI1WMKE_PLUGIN_NAME ),
			);
		}

		// Google Drive
		if ( defined( 'AI1WMGE_PLUGIN_SHORT' ) ) {
			$extensions[ AI1WMGE_PLUGIN_SHORT ] = array(
				'title' => __( 'Google Drive', AI1WMKE_PLUGIN_NAME ),
				'about' => __( 'Backup and migrate WordPress to Google Drive', AI1WMKE_PLUGIN_NAME ),
			);
		}

		// Amazon Glacier
		if ( defined( 'AI1WMRE_PLUGIN_SHORT' ) ) {
			$extensions[ AI1WMRE_PLUGIN_SHORT ] = array(
				'title' => __( 'Amazon Glacier', AI1WMKE_PLUGIN_NAME ),
				'about' => __( 'Backup and migrate WordPress to Amazon Glacier', AI1WMKE_PLUGIN_NAME ),
			);
		}

		// Mega
		if ( defined( 'AI1WMEE_PLUGIN_SHORT' ) ) {
			$extensions[ AI1WMEE_PLUGIN_SHORT ] = array(
				'title' => __( 'Mega', AI1WMKE_PLUGIN_NAME ),
				'about' => __( 'Backup and migrate WordPress to Mega.nz', AI1WMKE_PLUGIN_NAME ),
			);
		}

		// OneDrive
		if ( defined( 'AI1WMOE_PLUGIN_SHORT' ) ) {
			$extensions[ AI1WMOE_PLUGIN_SHORT ] = array(
				'title' => __( 'OneDrive', AI1WMKE_PLUGIN_NAME ),
				'about' => __( 'Backup and migrate WordPress to Microsoft OneDrive', AI1WMKE_PLUGIN_NAME ),
			);
		}

		// pCloud
		if ( defined( 'AI1WMPE_PLUGIN_SHORT' ) ) {
			$extensions[ AI1WMPE_PLUGIN_SHORT ] = array(
				'title' => __( 'pCloud', AI1WMKE_PLUGIN_NAME ),
				'about' => __( 'Backup and migrate WordPress to pCloud', AI1WMKE_PLUGIN_NAME ),
			);
		}

		// S3 Client
		if ( defined( 'AI1WMNE_PLUGIN_SHORT' ) ) {
			$extensions[ AI1WMNE_PLUGIN_SHORT ] = array(
				'title' => __( 'S3 Client', AI1WMKE_PLUGIN_NAME ),
				'about' => __( 'Backup and migrate WordPress to S3 compatible object storage service', AI1WMKE_PLUGIN_NAME ),
			);
		}

		// Amazon S3
		if ( defined( 'AI1WMSE_PLUGIN_SHORT' ) ) {
			$extensions[ AI1WMSE_PLUGIN_SHORT ] = array(
				'title' => __( 'Amazon S3', AI1WMKE_PLUGIN_NAME ),
				'about' => __( 'Backup and migrate WordPress to Amazon S3', AI1WMKE_PLUGIN_NAME ),
			);
		}

		// URL
		if ( defined( 'AI1WMLE_PLUGIN_SHORT' ) ) {
			$extensions[ AI1WMLE_PLUGIN_SHORT ] = array(
				'title' => __( 'URL', AI1WMKE_PLUGIN_NAME ),
				'about' => __( 'Backup and migrate WordPress via direct download', AI1WMKE_PLUGIN_NAME ),
			);
		}

		// WebDAV
		if ( defined( 'AI1WMWE_PLUGIN_SHORT' ) ) {
			$extensions[ AI1WMWE_PLUGIN_SHORT ] = array(
				'title' => __( 'WebDAV', AI1WMKE_PLUGIN_NAME ),
				'about' => __( 'Backup and migrate WordPress to WebDAV server', AI1WMKE_PLUGIN_NAME ),
			);
		}

		return $extensions;
	}
}
