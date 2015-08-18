<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, and ABSPATH. You can find more information by visiting
 * {@link https://codex.wordpress.org/Editing_wp-config.php Editing wp-config.php}
 * Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'perfecthavoc');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         'y4w=o+VRDV<4(0i_tiW#whmfA4~zYs-t#Y iiP!vtr1a2cm^B$gfnE+#K0{2e/X[');
define('SECURE_AUTH_KEY',  'L:=B4<A-^$h4+}yY0-{4%Sg$vu;6||S- W^&;kK`{:Dd6`^V;+x}HJ_;BP9RG g+');
define('LOGGED_IN_KEY',    '<-lC2M}Ki;N9n!@ mHU>uA<`H6|V$Rwi4|th*g3dX7j-iw N[o%5VweG$DC2g#=_');
define('NONCE_KEY',        '3Yq6KK~hin1gN??+mK$L?-FcS|;|M5>iW*+d{~+` WT-T^.Z)^}PVmjC.HH@}ip%');
define('AUTH_SALT',        'dvkpg5c6Mn!Otl/GeGGX]-rex&:*Kln9IP.c[LXZ$+5TV-}uQ,dliRA<pXS0~3lm');
define('SECURE_AUTH_SALT', '8vW#13+h}cmVD-{IkrTZ2-QK/a|7CC0p)_B[pFDY4U7LJHA[);}aA@15I,v8yt$R');
define('LOGGED_IN_SALT',   'Bwqe#-A`g%iowac^JF5rL-v5,b?v6Na1bl^A#VB(KDBEt% ]]vU;&}|XjaZSZ/}/');
define('NONCE_SALT',       'GRv3v;,&PNaRLa^)+a[h~!1-/OzhASuN(T~Ee#$$`)fT)D[Qw#QnnUpB|Q~+G%sv');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
