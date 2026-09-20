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

class Ai1wmke_Glacier_Import_Controller {

	public static function button() {
		return Ai1wm_Template::get_content(
			'import/button/button-glacier',
			array( 'access_key' => get_option( 'ai1wmke_glacier_access_key', ai1wmke_aws_access_key() ) ),
			AI1WMKE_TEMPLATES_PATH
		);
	}

	public static function picker() {
		Ai1wm_Template::render(
			'import/picker/picker-glacier',
			array(),
			AI1WMKE_TEMPLATES_PATH
		);
	}

	public static function browser( $params = array() ) {
		ai1wm_setup_environment();

		// Set params
		if ( empty( $params ) ) {
			$params = stripslashes_deep( $_GET );
		}

		// Set region name
		$region_name = null;
		if ( isset( $params['region_name'] ) ) {
			$region_name = trim( $params['region_name'] );
		}

		// Set vault name
		$vault_name = null;
		if ( isset( $params['vault_name'] ) ) {
			$vault_name = trim( $params['vault_name'] );
		}

		// Set archive ID
		$archive_id = null;
		if ( isset( $params['archive_id'] ) ) {
			$archive_id = trim( $params['archive_id'] );
		}

		// Set marker
		$marker = null;
		if ( isset( $params['marker'] ) ) {
			$marker = trim( $params['marker'] );
		}

		// Set Amazon Glacier client
		$glacier = new Ai1wmke_Glacier_Client(
			get_option( 'ai1wmke_glacier_account_id', false ),
			get_option( 'ai1wmke_glacier_access_key', ai1wmke_aws_access_key() ),
			get_option( 'ai1wmke_glacier_secret_key', ai1wmke_aws_secret_key() )
		);

		// Set region structure
		$response = array( 'items' => array(), 'errors' => array() );

		// Loop over regions
		if ( $region_name ) {

			// Loop over vaults
			if ( $vault_name ) {

				// Loop over archives
				if ( $archive_id ) {

					try {

						// Get archive retrieval jobs
						$archive_retrieval_jobs = $glacier->get_archive_retrieval_jobs( $vault_name, $region_name );

						// Loop over archive retrieval jobs
						if ( count( $archive_retrieval_jobs ) > 0 ) {
							if ( isset( $archive_retrieval_jobs[ $archive_id ] ) && ( $job = $archive_retrieval_jobs[ $archive_id ] ) ) {
								$response['errors'][] = __( 'Amazon Glacier is still retrieving this vault. Please come back later.', AI1WMKE_PLUGIN_NAME );
							} else {

								// Initiate archive retrieval job
								if ( $glacier->initiate_archive_retrieval( $archive_id, $vault_name, $region_name ) ) {
									$response['errors'][] = __( 'We have initiated retrieval of the archive. This process takes 4-5 hours. Please come back later to restore your backup.', AI1WMKE_PLUGIN_NAME );
								}
							}
						} else {

							// Initiate archive retrieval job
							if ( $glacier->initiate_archive_retrieval( $archive_id, $vault_name, $region_name ) ) {
								$response['errors'][] = __( 'We have initiated retrieval of the archive. This process takes 4-5 hours. Please come back later to restore your backup.', AI1WMKE_PLUGIN_NAME );
							}
						}
					} catch ( Ai1wmke_Error_Exception $e ) {
						try {

							// Initiate archive retrieval job
							if ( $glacier->initiate_archive_retrieval( $archive_id, $vault_name, $region_name ) ) {
								$response['errors'][] = __( 'We have initiated retrieval of the archive. This process takes 4-5 hours. Please come back later to restore your backup.', AI1WMKE_PLUGIN_NAME );
							}
						} catch ( Ai1wmke_Error_Exception $e ) {
							$response['errors'][] = __( 'Unable to initiate retrieval of the archive. Please check your data retrieval policy in Amazon Glacier Console.', AI1WMKE_PLUGIN_NAME );
						}
					}
				} else {

					try {

						// Get archive retrieval jobs
						$archive_retrieval_jobs = $glacier->get_archive_retrieval_jobs( $vault_name, $region_name );

						// Get inventory retrieval jobs
						$inventory_retrieval_jobs = $glacier->get_inventory_retrieval_jobs( $vault_name, $region_name );

						// Loop over inventory retrieval jobs
						if ( count( $inventory_retrieval_jobs ) > 0 ) {
							if ( isset( $inventory_retrieval_jobs[0] ) && ( $job = $inventory_retrieval_jobs[0] ) ) {
								if ( empty( $job['completed'] ) ) {
									$response['errors'][] = __( 'Amazon Glacier is still retrieving this vault. Please come back later.', AI1WMKE_PLUGIN_NAME );
								} else {

									// Get job output
									$inventory_retrieval_archives = $glacier->get_job_output( $job['id'], $vault_name, $region_name );

									// Loop over inventory retrieval archives
									if ( count( $inventory_retrieval_archives ) > 0 ) {
										foreach ( $inventory_retrieval_archives as $archive ) {
											$response['items'][] = array(
												'id'          => isset( $archive['id'] ) ? $archive['id'] : null,
												'name'        => isset( $archive['name'] ) ? $archive['name'] : null,
												'label'       => isset( $archive['name'] ) ? $archive['name'] : null,
												'date'        => isset( $archive['date'] ) ? human_time_diff( $archive['date'] ) : null,
												'size'        => isset( $archive['bytes'] ) ? ai1wm_size_format( $archive['bytes'] ) : null,
												'bytes'       => isset( $archive['bytes'] ) ? $archive['bytes'] : null,
												'type'        => isset( $archive['type'] ) ? $archive['type'] : null,
												'completed'   => isset( $archive_retrieval_jobs[ $archive['id'] ]['completed'] ) ? $archive_retrieval_jobs[ $archive['id'] ]['completed'] : null,
												'job_id'      => isset( $archive_retrieval_jobs[ $archive['id'] ]['id'] ) ? $archive_retrieval_jobs[ $archive['id'] ]['id'] : null,
												'vault_name'  => $vault_name,
												'region_name' => $region_name,
											);
										}
									} else {
										$response['errors'][] = __( 'There are no archives in this vault.', AI1WMKE_PLUGIN_NAME );
									}
								}
							}
						} else {

							// Initiate inventory retrieval job
							if ( $glacier->initiate_inventory_retrieval( $vault_name, $region_name ) ) {
								$response['errors'][] = __( 'We have initiated retrieval of the vault. This process takes 4-5 hours. Please come back later to restore your backup.', AI1WMKE_PLUGIN_NAME );
							}
						}
					} catch ( Ai1wmke_Error_Exception $e ) {
						try {

							// Initiate inventory retrieval job
							if ( $glacier->initiate_inventory_retrieval( $vault_name, $region_name ) ) {
								$response['errors'][] = __( 'We have initiated retrieval of the vault. This process takes 4-5 hours. Please come back later to restore your backup.', AI1WMKE_PLUGIN_NAME );
							}
						} catch ( Ai1wmke_Error_Exception $e ) {
							$response['errors'][] = __( 'Unable to initiate retrieval of the vault. Please check your data retrieval policy in Amazon Glacier Console.', AI1WMKE_PLUGIN_NAME );
						}
					}
				}
			} else {

				try {

					// Get vaults
					$vaults = $glacier->get_vaults( $region_name, array( 'marker' => $marker ) );

					// Loop over vaults
					if ( count( $vaults ) > 0 ) {
						foreach ( $vaults as $vault ) {
							$response['items'][] = array(
								'arn'         => isset( $vault['arn'] ) ? $vault['arn'] : null,
								'name'        => isset( $vault['name'] ) ? $vault['name'] : null,
								'label'       => isset( $vault['name'] ) ? $vault['name'] : null,
								'date'        => isset( $vault['date'] ) ? human_time_diff( $vault['date'] ) : null,
								'size'        => isset( $vault['bytes'] ) ? ai1wm_size_format( $vault['bytes'] ) : null,
								'bytes'       => isset( $vault['bytes'] ) ? $vault['bytes'] : null,
								'type'        => isset( $vault['type'] ) ? $vault['type'] : null,
								'region_name' => $region_name,
							);
						}
					} else {
						$response['errors'][] = __( 'There are no vaults in this region.', AI1WMKE_PLUGIN_NAME );
					}
				} catch ( Ai1wmke_Error_Exception $e ) {
					$response['errors'][] = __( 'There are no vaults in this region.', AI1WMKE_PLUGIN_NAME );
				}
			}
		} else {

			try {

				// Get regions
				$regions = $glacier->get_regions();

				// Loop over regions
				if ( count( $regions ) > 0 ) {
					foreach ( $regions as $region_name => $region_label ) {
						$response['items'][] = array(
							'name'  => $region_name,
							'label' => $region_label,
							'type'  => 'region',
						);
					}
				} else {
					$response['errors'][] = __( 'There are no regions in this account.', AI1WMKE_PLUGIN_NAME );
				}
			} catch ( Ai1wmke_Error_Exception $e ) {
				$response['errors'][] = __( 'There are no regions in this account.', AI1WMKE_PLUGIN_NAME );
			}
		}

		if ( defined( 'WP_CLI' ) ) {
			return $response;
		}
		echo json_encode( $response );
		exit;
	}
}
