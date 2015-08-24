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
define('AUTH_KEY',         'I%M$|[(~Y{4u8H;`3i;*n+t0YpGgA|C|l9T)43[I=7C~29=G v]do*O0<Bm-LiE9');
define('SECURE_AUTH_KEY',  '<S4T%VBp(e3oVnzjOaJ|Suqe%+/5MTNa0UF@oF?Q!NG9,?BLL8)+$`3a586/Q.~o');
define('LOGGED_IN_KEY',    '7!`1=asL5[g|zBh+7%u4+PU4zjWWf[7TvpuNuN[XI.mTC1=GtftNcR^Dn=BZId{`');
define('NONCE_KEY',        '4@4ObHBq5r<|p~&jLG+GR?gPkWp3iagzIHI-&:haa0i}hWjqj2Tk(s^N^]<D73Fh');
define('AUTH_SALT',        'GV=g<I$PKK=4Xc@FWX`KO:EH2Mm#|~t&a0}Z8qZzOSivL+eK c1Y5m@#>6o%/:_k');
define('SECURE_AUTH_SALT', 'k@s=qWOQ)le9}(!+`};f~N,N9K$$]|i6dMJ#JO&j|LIL+6, 0f=gZM#2B=1fM`eJ');
define('LOGGED_IN_SALT',   '/zmQP[=.wiQTn7w.4m+jxy0~ssU6DxZ6V)bk%r]?4uRy>o sODa:]Uai2XpS&p=p');
define('NONCE_SALT',       'gixY|I}g% xEz.Q:{<gO,EGLHh}|1{Q3LTv.Jj(GKmu8KE+TszS34 @uc`xn3zu2');

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
