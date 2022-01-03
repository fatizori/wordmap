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
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress-map' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

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
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '=]uY4O6=hUH6]H6HjdeeX;li6(+F{=AH]B,);R5/DWY;CPH{N;b8(I9^>tuLivXG' );
define( 'SECURE_AUTH_KEY',  'yjuBB/u>|/YJ-0-:E,l|TVG>V&pE]*/h9.+_3WDn9?:1H4%.>55E.+#Ns/@#4.&2' );
define( 'LOGGED_IN_KEY',    'MXK8cth=+D_y(!bz?OK~MjL365Io3YbSnNz;8Itx:=V-t=Z $RaVYH0/F3z[@~X`' );
define( 'NONCE_KEY',        ':j%`X#H-asor(![F$A%RGU!Kwzeh*s.;/{bVn=njL|dORxoFjkrZha~GHu:?mmTM' );
define( 'AUTH_SALT',        '8#Pv8Tm u]vpq0<GJ|?QAAG[36n`~H6@[=gfv_V*5Ko/%},(Us+*S<`(r~9_Z2//' );
define( 'SECURE_AUTH_SALT', 'W|K~#Uu9dI)ANuF(Jku#=v,9}/^v*.GQd(}5EXudTJ4tVKVA;an)hIAO?1DrafqZ' );
define( 'LOGGED_IN_SALT',   'uMqb*bRKE]VKg]goO/mBB}[vL.bmOm9x5%Jtt+;dW$]u]ULg 6yYEh]E}^Alz>`J' );
define( 'NONCE_SALT',       'v}s?wz(y0J>1NqzM$r+q=&Pxe2s&>gbFNEy8x1hVU.TM*j{*9 t6<K;DEIh@9ARQ' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_map';

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
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
