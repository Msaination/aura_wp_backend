<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'auradev' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
// MAMP runs MySQL on port 8889, not the default 3306.
define( 'DB_HOST', '127.0.0.1:8889' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/** Set the local backend install to the MAMP-served localhost URL. */
define( 'WP_HOME', 'http://localhost:8888/AuraDev/backend/' );
define( 'WP_SITEURL', 'http://localhost:8888/AuraDev/backend/' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'clPo43l<ty1fv*:KgWrt^ya7=sei6hit,[{7#{.vBkUBFa4Cgh)A`1W1*SeH`ySH' );
define( 'SECURE_AUTH_KEY',  '#oa{+! C:n3ii)niodj(x^!rMCN_5O&9&YVs;;1I7``,A(OAIkD>V@Y$E>1.?Nt5' );
define( 'LOGGED_IN_KEY',    '#x3)]?IjZa~iVR_NcKaG(Sh/@!Ax=YXuIpMo7G{G<i}mWX[D&u+AylNzt&eFCs<4' );
define( 'NONCE_KEY',        'c40x| ~pM dh9]-5(2qL@ K|Oc6i1?bY|e~y.6>8QE9j[0XM**l;cy<zeV^H6^1)' );
define( 'AUTH_SALT',        'Y|$f-3Ew*1zBuSrmg`._h,scchZ4J!DXg920eq_G|2KAa<9)a1tCF4;+s,xn<=4?' );
define( 'SECURE_AUTH_SALT', 'n&R~2RenS>B4>]2iLj}69/fTRDcz2Q9!7/Zk|rG;Jj8f,#.,abS@#MjU?BB@H5{&' );
define( 'LOGGED_IN_SALT',   'V|1^FO@?64mi5PUS8T-GwFv]fa@[T%d~<`[!66tb/2_2Bnq1R%]0Ov{rvLZ%/F,#' );
define( 'NONCE_SALT',       '&1JTfHj?G*_Rp-G@yJrZ60uWP.f!H,&_TFs[8uU_l@;(oC&-~dr<}]urGcN{7zfs' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

define( 'WP_MEMORY_LIMIT', '768M' );
define( 'WP_MAX_MEMORY_LIMIT', '768M' );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
