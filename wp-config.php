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
define('DB_NAME', 'chukyso');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

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
define('AUTH_KEY',         ' 8u}*kY$FiV$,^>c6Sj)I4bXN~+H)//3aUbb/~BVi1I>_/Vu~FX=gjS#IuR%|t=+');
define('SECURE_AUTH_KEY',  't~: G5|8^.._Xdk}r+kXwFb4+p2fb/Yn]aFdd5/lAvv#CQk*AZ$-g(WC)OwR1G^3');
define('LOGGED_IN_KEY',    'hOQw%S/[#3^zt,-w<NjlTMw~C`N(Kb1U=,AGzwUw-Vg9nfU9`bk(/j5=wPTeVFXS');
define('NONCE_KEY',        '*nEe4=3?4FCoi%3|i8le{$eo<R*.!r6Nm12$.HUG^O.c[$w/Sl/]JJ,!y@@9KtR)');
define('AUTH_SALT',        '} {X@sxjw6~YhiftVrn.&2 [|`W!jZ<rd*HMIGC+lU?B1VMg [J/Xcx,Ag$cY92D');
define('SECURE_AUTH_SALT', 'jt*L:pU >j+Ir$}Fg+GRTWZ@}T`NgwF,W4L!652oE6H#@[HF,tc=&}lSy<sC#T}i');
define('LOGGED_IN_SALT',   'F8Ut2cOERT.#aSRZ{i>Z$m&r*:sO3`@B[@=1]pVyts2OYa/f-dOQ?sSujeg_`8YN');
define('NONCE_SALT',       '+nH&EOz5,`rWDfz?&3/@,d)<}vt9QF`65l;xVY?&Re}Qzz,E.Kh7{~A=Ic tqedh');

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
