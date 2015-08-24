<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'admin_havoc');

/** MySQL database username */
define('DB_USER', 'perfect');

/** MySQL database password */
define('DB_PASSWORD', 'havoc');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'gMn<VZ/2/EEiD0?=*|>{=r{Q-ZB,/JygGL?FVDhQcW eE2LH}b9E.o/s>yXvsZ-1');
define('SECURE_AUTH_KEY',  'z7lx+X/h?)3l-=_n%a9`MQ(Gd}$!n9`p#)l!ckR?o$+fU+d5l6LN_ `<{y2SV1y7');
define('LOGGED_IN_KEY',    '( GzgIW*pw-ZRDZ}bj5(Dd;1>i -3ymha&imDtl4ux*l0Bc>Ok@BU-i wd<~v5?9');
define('NONCE_KEY',        'U_ntQd3;W807`6R,B=>v$)]E%a,_-;Z}CqkW)L=#nQ4#hbbRs0`pZlRceS=8$zU(');
define('AUTH_SALT',        'YYB-<0AE--y{-0zuKiRy g~:4E|;6l*Y#Rn8Auss@*4 z#Ke=N$08X@PRjz4k7xa');
define('SECURE_AUTH_SALT', 'M&4;RbGz>|s!/N!1|k!qso@<l}EqvtC:rXSwnjTk?*+G|P,).|PaePq@HVD0@pns');
define('LOGGED_IN_SALT',   '#:R?-n&Zf)dnlLCR1?r`[HX-vdYg]|EYU{|^h0}Fn-$Wb+o6d3P#j?3~<qxb}|yD');
define('NONCE_SALT',       '<N-D+v7$/.*2}!E!M%No]8Zd}|| wyJJ)t^q2A`Of>#;~A1b_}ki]IJ@+u>;U|$h');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

