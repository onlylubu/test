<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'ekmatra');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'news@0071');

/** MySQL hostname */
define('DB_HOST', '192.168.1.106');

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
define('AUTH_KEY',         'Oh:#H $DL/=}FB2R!oOy,7-<Z4>E]PJ/{+^o)<vwcLsn(2:8rOA#+rX%.~x-mu;R');
define('SECURE_AUTH_KEY',  'dH#BEx Y|y hW-QkS=FdeO[b7{#._Dh0)>5O9}RSJZ90.WY2N>id E@s[;#|&~2Q');
define('LOGGED_IN_KEY',    'y_22FeAZiQk{:(7 .decz@u~&U?~c28`u96_2b;gjZKkK]|A>f.?Udov86`~y/8O');
define('NONCE_KEY',        'kA]O*APq8X[$)IDahFoEZK}O#N0CNbmi5zJs%#May<379r/j),RE^CW40CQ<pXk:');
define('AUTH_SALT',        '}%ho$VrF^d@SzU!Cq`uKVqv!3D&%)KUlL}O=MRr32esV6{J%gm$<#:Y2Il5d}+z}');
define('SECURE_AUTH_SALT', '`V%{R*hP!;y(<o~q23(`jHjD`DDKWuw?YO5%-.!u_3CN4&r0L,#o]:sfc]!{`fRu');
define('LOGGED_IN_SALT',   'v(z~5,kI/|g+Q&dy3+&ym]U]erjRAI}^&;{4lup,@D>ZhN4]XK D)WwW<UPBV;,D');
define('NONCE_SALT',       'aRCq>S{joZcu).O6onlgHV]){,y{5aR~mW<yo?iUH]pnc5`->~Ngy(vI9MtaHj91');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'ekmatra_';

define( 'WP_CONTENT_DIR', $_SERVER['DOCUMENT_ROOT'] . '/eshop-ekmatra' );
define( 'WP_CONTENT_URL', 'http://ekmatral.com/eshop-ekmatra'); 


define( 'WP_PLUGIN_DIR', $_SERVER['DOCUMENT_ROOT'] . '/eshop-ekmatra/applications' );
define( 'WP_PLUGIN_URL', 'http://ekmatral.com/eshop-ekmatra/applications');

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

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
