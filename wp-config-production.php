<?php
/**
 * Production-ready WordPress configuration for AuraDev backend.
 * 
 * IMPORTANT: Update the database credentials and domain below before deploying.
 * Copy this file to wp-config.php on your production server.
 */

// ** Database settings - Update these for production ** //
/** The name of the database for WordPress */
define( 'DB_NAME', $_SERVER['DB_NAME'] ?? 'auradev' );

/** Database username */
define( 'DB_USER', $_SERVER['DB_USER'] ?? 'your_production_db_user' );

/** Database password */
define( 'DB_PASSWORD', $_SERVER['DB_PASSWORD'] ?? 'your_production_db_password' );

/** Database hostname */
define( 'DB_HOST', $_SERVER['DB_HOST'] ?? 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force users to log in again.
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
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */

// Production URLs - Update to your actual domain
define( 'WP_HOME', 'https://your-production-domain.com/backend' );
define( 'WP_SITEURL', 'https://your-production-domain.com/backend' );

// Force SSL for admin and login
define( 'FORCE_SSL_ADMIN', true );
define( 'FORCE_SSL_LOGIN', true );

// Security: Disable file editing from admin
define( 'DISALLOW_FILE_EDIT', true );

// Performance: Enable caching
define( 'WP_CACHE', true );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';