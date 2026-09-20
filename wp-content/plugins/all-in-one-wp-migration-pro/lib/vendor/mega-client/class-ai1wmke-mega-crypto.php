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

class Ai1wmke_Mega_Crypto {

	public static function prepare_key( $str ) {
		$password_key = Ai1wmke_Mega_Utils::a32_to_str(
			array(
				0x93C467E3,
				0x7DB0C7A4,
				0xD1BE3F81,
				0x0152CB56,
			)
		);

		$total = count( $str );

		for ( $r = 65536; $r--; ) {
			for ( $j = 0; $j < $total; $j += 4 ) {
				$key = array( 0, 0, 0, 0 );
				for ( $i = 0; $i < 4; $i++ ) {
					if ( $i + $j < $total ) {
						$key[ $i ] = $str[ $i + $j ];
					}
				}

				$password_key = self::encrypt_aes_cbc( Ai1wmke_Mega_Utils::a32_to_str( $key ), $password_key );
			}
		}

		return $password_key;
	}

	/**
	 * Prepare key with string input
	 *
	 * @param  string $password Input data
	 * @return string
	 */
	public static function prepare_key_pwd( $password ) {
		return self::prepare_key( Ai1wmke_Mega_Utils::str_to_a32( $password ) );
	}

	public static function string_hash( $s, $aes_key ) {
		$s32 = Ai1wmke_Mega_Utils::str_to_a32( $s );
		$h32 = array( 0, 0, 0, 0 );

		for ( $i = 0; $i < count( $s32 ); $i++ ) {
			$h32[ $i & 3 ] ^= $s32[ $i ];
		}

		$h32 = Ai1wmke_Mega_Utils::a32_to_str( $h32 );

		for ( $i = 16384; $i--; ) {
			$h32 = self::encrypt_aes_cbc( $aes_key, $h32 );
		}

		$h32 = Ai1wmke_Mega_Utils::str_to_a32( $h32 );

		return Ai1wmke_Mega_Utils::a32_to_base64( array( $h32[0], $h32[2] ) );
	}

	/**
	 * AES encrypt in CBC mode (zero IV)
	 *
	 * @param  string $key  Input data
	 * @param  string $data Input data
	 * @return string
	 */
	public static function encrypt_aes_cbc( $key, $data ) {
		if ( version_compare( PHP_VERSION, '5.4.0', '>=' ) && extension_loaded( 'openssl' ) && in_array( 'AES-128-CBC', array_map( 'strtoupper', @openssl_get_cipher_methods() ) ) ) {
			$iv = str_repeat( "\0", @openssl_cipher_iv_length( 'AES-128-CBC' ) );

			$data = Ai1wmke_Mega_Utils::str_pad( $data );

			return @openssl_encrypt( $data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv );
		} else {
			$iv = str_repeat( "\0", @mcrypt_get_iv_size( MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC ) );

			return @mcrypt_encrypt( MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv );
		}
	}

	public static function encrypt_aes_cbc_a32( $key, $data ) {
		return Ai1wmke_Mega_Utils::str_to_a32(
			Ai1wmke_Mega_Crypto::encrypt_aes_cbc(
				Ai1wmke_Mega_Utils::a32_to_str( $key ),
				Ai1wmke_Mega_Utils::a32_to_str( $data )
			)
		);
	}

	/**
	 * AES encrypt in CBC mode (zero IV)
	 *
	 * @param  string $key Input data
	 * @param  string $data Input data
	 * @return string
	 */
	public static function decrypt_aes_cbc( $key, $data ) {
		if ( version_compare( PHP_VERSION, '5.4.0', '>=' ) && extension_loaded( 'openssl' ) && in_array( 'AES-128-CBC', array_map( 'strtoupper', @openssl_get_cipher_methods() ) ) ) {
			$iv = str_repeat( "\0", @openssl_cipher_iv_length( 'AES-128-CBC' ) );

			$data = Ai1wmke_Mega_Utils::str_pad( $data );

			return @openssl_decrypt( $data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv );
		} else {
			$iv = str_repeat( "\0", @mcrypt_get_iv_size( MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC ) );

			return @mcrypt_decrypt( MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv );
		}
	}

	public static function decrypt_aes_cbc_a32( $key, $a ) {
		return Ai1wmke_Mega_Utils::str_to_a32(
			Ai1wmke_Mega_Crypto::decrypt_aes_cbc(
				Ai1wmke_Mega_Utils::a32_to_str( $key ),
				Ai1wmke_Mega_Utils::a32_to_str( $a )
			)
		);
	}

	public static function aes_ctr_encrypt( $data, $key, $iv ) {
		if ( version_compare( PHP_VERSION, '5.4.0', '>=' ) && extension_loaded( 'openssl' ) && in_array( 'AES-128-CTR', array_map( 'strtoupper', @openssl_get_cipher_methods() ) ) ) {
			return @openssl_encrypt( $data, 'AES-128-CTR', $key, OPENSSL_RAW_DATA, $iv );
		} else {
			return @mcrypt_encrypt( MCRYPT_RIJNDAEL_128, $key, $data, 'ctr', $iv );
		}
	}

	public static function aes_ctr_decrypt( $data, $key, $iv ) {
		if ( version_compare( PHP_VERSION, '5.4.0', '>=' ) && extension_loaded( 'openssl' ) && in_array( 'AES-128-CTR', array_map( 'strtoupper', @openssl_get_cipher_methods() ) ) ) {
			return @openssl_decrypt( $data, 'AES-128-CTR', $key, OPENSSL_RAW_DATA, $iv );
		} else {
			return @mcrypt_decrypt( MCRYPT_RIJNDAEL_128, $key, $data, 'ctr', $iv );
		}
	}

	public static function encrypt_key( $key, $a ) {
		$x = array();
		for ( $i = 0; $i < count( $a ); $i += 4 ) {
			$x = array_merge( $x, self::encrypt_aes_cbc_a32( $key, array_slice( $a, $i, 4 ) ) );
		}

		return $x;
	}

	public static function decrypt_key( $key, $a ) {
		$x = array();
		for ( $i = 0; $i < count( $a ); $i += 4 ) {
			$x = array_merge( $x, self::decrypt_aes_cbc_a32( $key, array_slice( $a, $i, 4 ) ) );
		}

		return $x;
	}

	public static function encrypt_node_attribute( $attr, $key ) {
		$attr = 'MEGA' . json_encode( $attr );
		if ( strlen( $attr ) % 16 ) {
			$attr .= str_repeat( "\0", ( 16 - strlen( $attr ) % 16 ) );
		}

		return self::encrypt_aes_cbc( Ai1wmke_Mega_Utils::a32_to_str( $key ), $attr );
	}

	public static function decrypt_node_attribute( $attr, $key ) {
		if ( count( $key ) !== 4 ) {
			$key = array(
				$key[0] ^ $key[4],
				$key[1] ^ $key[5],
				$key[2] ^ $key[6],
				$key[3] ^ $key[7],
			);
		}

		$key = Ai1wmke_Mega_Utils::a32_to_str( $key );

		$attr = self::decrypt_aes_cbc( $key, $attr );
		$attr = Ai1wmke_Mega_Utils::str_depad( $attr );

		if ( substr( $attr, 0, 6 ) !== 'MEGA{"' ) {
			return false;
		}

		$attr = json_decode( Ai1wmke_Mega_Utils::from8( substr( $attr, 4 ) ), true );

		if ( is_null( $attr ) ) {
			$attr = array(
				'n' => 'MALFORMED_ATTRIBUTES',
			);
		}

		return $attr;
	}

	/**
	 * Generate a PBKDF2 key derivation of a supplied password
	 *
	 * @param  string  $algorithm  The hash algorithm to use. Recommended: SHA256
	 * @param  string  $password   The password
	 * @param  string  $salt       A salt that is unique to the password
	 * @param  integer $iterations Iteration count. Higher is better, but slower. Recommended: At least 1000
	 * @param  integer $length     The length of the derived key in bytes
	 * @param  boolean $raw_output If true, the key is returned in raw binary format. Hex encoded otherwise
	 * @return string
	 */
	public static function hash_pbkdf2( $algorithm, $password, $salt, $iterations, $length = 0, $raw_output = false ) {
		if ( function_exists( 'hash_pbkdf2' ) ) {
			return hash_pbkdf2( $algorithm, $password, $salt, $iterations, $length, $raw_output );
		}

		// Pre-hash for optimization if password length > hash length
		$hash_length = strlen( hash( $algorithm, '', true ) );
		switch ( $algorithm ) {
			case 'sha224':
			case 'sha256':
				$block_size = 64;
				break;
			case 'sha384':
			case 'sha512':
				$block_size = 128;
				break;
			default:
				$block_size = $hash_length;
				break;
		}

		if ( $length < 1 ) {
			$length = $hash_length;
			if ( ! $raw_output ) {
				$length <<= 1;
			}
		}

		// Number of blocks needed to create the derived key
		$blocks = ceil( $length / $hash_length );
		$digest = '';
		if ( strlen( $password ) > $block_size ) {
			$password = hash( $algorithm, $password, true );
		}

		for ( $i = 1; $i <= $blocks; ++$i ) {
			$ib = $block = hash_hmac( $algorithm, $salt . pack( 'N', $i ), $password, true );

			// Iterations
			for ( $j = 1; $j < $iterations; ++$j ) {
				$ib ^= ( $block = hash_hmac( $algorithm, $block, $password, true ) );
			}

			$digest .= $ib;
		}

		if ( ! $raw_output ) {
			$digest = bin2hex( $digest );
		}

		return substr( $digest, 0, $length );
	}
}
