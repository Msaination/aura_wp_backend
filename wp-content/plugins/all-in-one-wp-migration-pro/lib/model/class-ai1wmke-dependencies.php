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

class Ai1wmke_Dependencies {

	/**
	 * Check missing PHP extensions
	 *
	 * @param  array $extensions PHP extensions
	 * @return array
	 */
	public static function check( $extensions ) {
		$or_missing  = array();
		$and_missing = array();
		foreach ( $extensions as $required ) {
			if ( is_array( $required ) ) {
				if ( ! array_filter( $required, 'extension_loaded' ) ) {
					for ( $i = 0; $i < count( $required ); $i++ ) {
						if ( $i === count( $required ) - 1 ) {
							$required[ $i ] = sprintf( __( 'or %s', AI1WMKE_PLUGIN_NAME ), $required[ $i ] );
						}

						$or_missing[] = $required[ $i ];
					}
				}
			} else {
				if ( ! extension_loaded( $required ) ) {
					$and_missing[] = $required;
				}
			}
		}

		$text_missing = array();
		if ( ! empty( $and_missing ) ) {
			$text_missing[] = implode( ', ', $and_missing );
		}

		if ( ! empty( $or_missing ) ) {
			if ( count( $or_missing ) > 2 ) {
				$text_missing[] = sprintf( __( 'at least one of %s', AI1WMKE_PLUGIN_NAME ), implode( ', ', $or_missing ) );
			} else {
				$text_missing[] = sprintf( __( 'at least one of %s', AI1WMKE_PLUGIN_NAME ), implode( ' ', $or_missing ) );
			}
		}

		$messages = array();
		if ( ! empty( $text_missing ) ) {
			$messages[] = sprintf( __( 'Required PHP extensions are missing: %s. <a href="https://help.servmask.com/knowledgebase/dependencies/" target="_blank">Technical details</a>', AI1WMKE_PLUGIN_NAME ), implode( ' and ', $text_missing ) );
		}

		return $messages;
	}
}
