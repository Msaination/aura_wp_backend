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

class Ai1wmke_Mega_Rsa {

	public static function rsa_decrypt( $encrypted_data, $p, $q, $d ) {
		$encrypted_data = self::int2bin( $encrypted_data );
		$exp            = $d;
		$modulus        = bcmul( $p, $q );
		$data_len       = strlen( $encrypted_data );
		$chunk_len      = self::bit_len( $modulus ) - 1;
		$block_len      = intval( ceil( $chunk_len / 8 ) );
		$curr_pos       = 0;
		$bit_pos        = 0;
		$plain_data     = 0;

		while ( $curr_pos < $data_len ) {
			$tmp = self::bin2int( substr( $encrypted_data, $curr_pos, $block_len ) );
			$tmp = bcpowmod( $tmp, $exp, $modulus );

			$plain_data = self::bit_or( $plain_data, $tmp, $bit_pos );

			$bit_pos  += $chunk_len;
			$curr_pos += $block_len;
		}

		return self::int2bin( $plain_data );
	}

	private static function bin2int( $str ) {
		$result = 0;
		$n      = strlen( $str );

		do {
			$result = bcadd( bcmul( $result, 256 ), ord( $str[ --$n ] ) );
		} while ( $n > 0 );

		return $result;
	}

	private static function int2bin( $num ) {
		$result = '';

		do {
			$result .= chr( bcmod( $num, 256 ) );
			$num     = bcdiv( $num, 256 );
		} while ( bccomp( $num, 0 ) );

		return $result;
	}

	private static function bit_or( $num1, $num2, $start_pos ) {
		$start_byte = intval( $start_pos / 8 );
		$start_bit  = $start_pos % 8;
		$tmp1       = self::int2bin( $num1 );

		$num2 = bcmul( $num2, 1 << $start_bit );
		$tmp2 = self::int2bin( $num2 );

		if ( $start_byte < strlen( $tmp1 ) ) {
			$tmp2 |= substr( $tmp1, $start_byte );
			$tmp1  = substr( $tmp1, 0, $start_byte ) . $tmp2;
		} else {
			$tmp1 = str_pad( $tmp1, $start_byte, '\0' . $tmp2 );
		}

		return self::bin2int( $tmp1 );
	}

	private static function bit_len( $num ) {
		$tmp     = self::int2bin( $num );
		$bit_len = strlen( $tmp ) * 8;
		$tmp     = ord( $tmp[ strlen( $tmp ) - 1 ] );

		if ( ! $tmp ) {
			$bit_len -= 9;
		} else {
			while ( ! ( $tmp & 0x80 ) ) {
				$bit_len--;
				$tmp <<= 1;
			}
		}

		return $bit_len;
	}
}
