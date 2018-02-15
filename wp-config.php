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
define('DB_NAME', 'dev_shintranet');

/** MySQL database username */
define('DB_USER', 'dennisd');

/** MySQL database password */
define('DB_PASSWORD', 'dennis123');

/** MySQL hostname */
define('DB_HOST', 'mysql-dev');

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
define('AUTH_KEY',         '[^-nZOk]n[]:~]=+T[?M&qO+~/=++[-NaD]$`5`m%`(#}}R!@c`V7x.~i|:&%4HR');
define('SECURE_AUTH_KEY',  '[HJvCDi+yP5-`o,@5gW:[|uuMr4)-&Q0&LNfu)7z)?K1Odd+pXIn2l;ErQg+_r*?');
define('LOGGED_IN_KEY',    '0L;5^`g7P#L1`qf5At0J3% `Jg=d7~tvNx&R3z`sPJoIya=}hd[(kP~*N<<MCFi6');
define('NONCE_KEY',        '7$=+6f0=cBe<Mlc|7y]ySL|*Q2Dz|w@Jt`0|`#e=.XV+UuG~y5n:6NO2IE{G{AXh');
define('AUTH_SALT',        '!0Cl?.~&??Djrrtz.H9p,U+6#@d{5vh5%:P9eL(xfDf1nLSs7;-SJsV7u(sphdSv');
define('SECURE_AUTH_SALT', '=;#)c0{GvIHx2A`_JjkV)7Z(pxb[qj=[X%Tu`-+2pc|I_=<8j&15$CkHbp2h-!#%');
define('LOGGED_IN_SALT',   ';}Fz,kT|Y#NGqcdj?w)6?a48wq=8d[gsJXDw/)x@x]plv|;f]z(j:SoEiz?KA Sx');
define('NONCE_SALT',       'vXs=&G;xFOrA`]y76#^fa=}#]sM7VZPV9-Vy.&?x[?=_V?W1:Ik,A8{b:g=?`L79');

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
