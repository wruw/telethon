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
define( 'DB_NAME', 'telethon' );

/** MySQL database username */
define( 'DB_USER', 'telethon' );

/** MySQL database password */
define( 'DB_PASSWORD', 'telethon' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );
    
define('FS_METHOD', 'direct');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'K(xmw9x;0_JVU%;dt(0uUZ>(gA%*7S~_9:&27fN[m4J=qey(A,PL[|laFWT.1@Z+' );
define( 'SECURE_AUTH_KEY',  'g+ad{cvLOMFGnE?_)>2#TBJ n4q+q<BW:qXo]Gord@VJjSc S!-UeN!dvl.*^Td5' );
define( 'LOGGED_IN_KEY',    '4cVb;[5;JBdNbvD?{7pT!Aw?i&IsbY0qTz7>V<%_r@cDsE:s[H89:]o<=Pd}~)&l' );
define( 'NONCE_KEY',        'J64VZvhmF%/@`R&qd&L-HZqz*h/lF:Swc>{)09cnc*F=cZx?P}wN,G1$=+&}[z$!' );
define( 'AUTH_SALT',        '?|@GcL0=3~6`@_hTQfTAsJdNN/d@~IlaEQ:s^b!?J 5p4<*I6UAhe84SM2<.BTv)' );
define( 'SECURE_AUTH_SALT', 'C[Wqf;U9;Q$.;6-xmM* h*q]i2t`XOtMZA<+.pC0HwmrnaFXzqDz) Z>M.q2TLg#' );
define( 'LOGGED_IN_SALT',   '@Q@lzo!7.B,xum:XN_/ptNb(w& oPjw CH0.n]gYyk%[4S~}?eq y%hJO>k8R+Hc' );
define( 'NONCE_SALT',       'N9/Us-TRXC#w9s:=pAi4~4:Qu]a.h>^0QQWz{|wDn-=FM[o<ErRR:=(_i~gxvImp' );

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
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );

