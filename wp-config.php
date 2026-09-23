<?php
define( 'WP_CACHE', true );
 // Added by SpeedyCache

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'amngfszdlp_wo39b11' );

/** Database username */
define( 'DB_USER', 'amngfszdlp_wo39b11' );

/** Database password */
define( 'DB_PASSWORD', '5t)).pqvS2JB))P8' );

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
define( 'AUTH_KEY',         'vs84yvgl3ert8wkmptzfcenr4zqkgdqqm9bdwrdca3clhs8hrziazwqyswhs3nlz' );
define( 'SECURE_AUTH_KEY',  'zmju7xpfvr1cblehu8xphull6bnbajbyrlnz7km1okmytyjy9y6ualhyl9gzbxhy' );
define( 'LOGGED_IN_KEY',    'mg5nje55lkaspjvtacsdum52rwps9ytxwmanjegkm0robixjdgoawfyinhqmyfp3' );
define( 'NONCE_KEY',        '8g7szmr3yhtiwckw6ggcgyudgo1hctushbumf8fwpsrejgaphxnevsc6usiocdy7' );
define( 'AUTH_SALT',        '0zjnj8qb1n3ghjvggftz0gffvmqhkwd9a8p7tthozb64qrw0fr6dhacevyp1hzsn' );
define( 'SECURE_AUTH_SALT', 'e2dd4yvdiyrea0mjife78ogvnmqy1njcaany9744dypkyndg478bh0myzreyfrfn' );
define( 'LOGGED_IN_SALT',   'rnr6vpq4x0m5iu11osyqjgzlehfkoyydzr9v49lvv4lukabbmifpq5rhvmdwvr3f' );
define( 'NONCE_SALT',       'z6we3undt3r3qcwew6op1gvzeyzpd6ipfsqdefomflfal5dxdgrpckgnj5l191r2' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'soft_';

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
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
