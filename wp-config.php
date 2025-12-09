<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

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
define( 'AUTH_KEY',          'K?*$3Da]0L*6qzX6,CsWn|Yo}L$8jNOUs&NVC?4~)bFpkmK0 o~QG[w&o46hFPNs' );
define( 'SECURE_AUTH_KEY',   '~1n(Gw{/e}f?YQ:v}eOVdElk*?YHh[GlRj;?ZPk}NB[%g4FQ!Ob|5Rl l4Ql@ep|' );
define( 'LOGGED_IN_KEY',     'pUA2`:qh5]bVp}Q;r@t8}Fk|+psqH3[$x0-Qz$ETmO[CH]lapI`^U)7pz.sAnSac' );
define( 'NONCE_KEY',         '|c.__,Y|~F~~/_WM)3+^!M]1LpY:g^ad`krsK`-S:kr`-vhT]sX-Lk+[dM:o>5(a' );
define( 'AUTH_SALT',         '0>64?>I<MM[+$:bI]`qs(s`y@Y0.$.0L7-EN.tr1`3,FS*)ZgK]^am82U#I8klbz' );
define( 'SECURE_AUTH_SALT',  'qB7:vD_IdHm)GFF Ot$m},>Q>l;U5sa;O!z>0J9G*CEAJa:s(%mb]>=.@4`WB-AU' );
define( 'LOGGED_IN_SALT',    '3@N(0+7Zmd/h#]W(TlV?LPb-^/jHNi-4N.$];dpo`t ;=*Ew<lq$ `>6{%uK7Wmc' );
define( 'NONCE_SALT',        'E?]Q_(a|s1{^0A!GcuS,CtK9=l;k@Be+;#*Dzpv%SJO=k/56,&-xF9570Q3+s#OD' );
define( 'WP_CACHE_KEY_SALT', '}jr+N=C7eM/R=xg^qmX*.S/xyV`gjC)j1Rr-jh0UMkX]jHQz;fGK,4#p5eqST`Y@' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
