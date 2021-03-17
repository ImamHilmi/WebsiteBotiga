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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
/* Themify Cache Start */
define('WP_CACHE',true);
/* Themify Cache End */

define( 'DB_NAME', 'botiga' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         ', L}R%Z8hZb6P$Dyee;94jg&Qf2(L)t5.=fY$kFz3d;n09/~q}hUJ~T:^UPvG~}N' );
define( 'SECURE_AUTH_KEY',  'ZOM4TeRAw9l~Mzh^nHFV9jjzUSP(Jm@|XW>C8Kwi4K_CEq1&?M(IoYxqjSK<Kt;M' );
define( 'LOGGED_IN_KEY',    'H0-|s,u.7+AX{(Tix*NEaBtSB;C;WwkJN[6.QX49eM#+)23AMMR +aw%$F:dO?Zb' );
define( 'NONCE_KEY',        'bq*=w/{~P:+zS9h)l#oV7XsV~vNRIY:0O]$:TV=RCA:eJVNd$v#!;P$>HGy,sDZ=' );
define( 'AUTH_SALT',        '+}dh,nfW7.MHD|yG`l%SAxdTvV,u/uNi>,BiGFgYeyUWvut&OR#]CF0>+N$NIn9V' );
define( 'SECURE_AUTH_SALT', 'Ni>-@`}xCv),X@5]} ng^ml</n]p}J9T]knY8ZcN[u]`X6W9hPuagp5#KHRRt8{G' );
define( 'LOGGED_IN_SALT',   'Fm~o+7yrrG&$Bb|Xmknkamy24%q)/k1PA*{kz98ie=og9-I6bna8[p8[Q{j;Q#Ll' );
define( 'NONCE_SALT',       'RN&(EK(R-BVYegSc^%gpDT1tZvnxSXsx![wFt,?!u1YJi;u_c hOu6#a.[zP~J!G' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
