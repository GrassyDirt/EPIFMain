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

// ** Database credentials and auth salts ** //
/*
 * Secrets live in wp-config-local.php, which is git-ignored and exists only on the server.
 * Copy wp-config-local-sample.php to wp-config-local.php and fill it in.
 * It is looked for one directory above the web root first (safest), then next to this file.
 */
if ( file_exists( dirname( __DIR__ ) . '/wp-config-local.php' ) ) {
	require_once dirname( __DIR__ ) . '/wp-config-local.php';
} elseif ( file_exists( __DIR__ . '/wp-config-local.php' ) ) {
	require_once __DIR__ . '/wp-config-local.php';
} else {
	header( 'HTTP/1.1 503 Service Unavailable' );
	exit( 'Site configuration missing: create wp-config-local.php from wp-config-local-sample.php.' );
}

if ( ! defined( 'DB_HOST' ) ) {
	define( 'DB_HOST', 'localhost' );
}

/** utf8mb4 supports the full Unicode range (emoji, all languages). */
defined( 'DB_CHARSET' ) || define( 'DB_CHARSET', 'utf8mb4' );
defined( 'DB_COLLATE' ) || define( 'DB_COLLATE', '' );

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

/* ------------------------------------------------------------------
 * EPIF site settings (epifservices.com)
 * Every define is guarded so a host-level or wp-config-local.php value wins
 * (this also fixes the "Constant WP_DEBUG already defined" warnings in error_log).
 * ------------------------------------------------------------------ */
$epif_defaults = array(
	// Environment. Override to 'staging' or 'development' in wp-config-local.php off production.
	'WP_ENVIRONMENT_TYPE' => 'production',

	// Debugging: never display errors to visitors; log them only when debugging is on.
	'WP_DEBUG'            => false,
	'WP_DEBUG_DISPLAY'    => false,
	'WP_DEBUG_LOG'        => false,
	'SCRIPT_DEBUG'        => false,

	// Security.
	'DISALLOW_FILE_EDIT'  => true,   // No theme/plugin code editor in the dashboard.
	'FORCE_SSL_ADMIN'     => true,   // Logins and admin over HTTPS only.
	'WP_AUTO_UPDATE_CORE' => 'minor', // Automatic security/maintenance releases.

	// Database hygiene.
	'WP_POST_REVISIONS'   => 10,
	'AUTOSAVE_INTERVAL'   => 120,
	'EMPTY_TRASH_DAYS'    => 14,

	// Resources.
	'WP_MEMORY_LIMIT'     => '256M',
	'WP_MAX_MEMORY_LIMIT' => '512M',

	// EPIF Core (wp-content/mu-plugins/epif-core.php).
	'EPIF_COMING_SOON'             => true,  // Visitors see the coming-soon page. Set false at launch, then purge the cache.
	'EPIF_NEWSLETTER_DOUBLE_OPTIN' => true,  // Subscribers confirm by email before they count.
	'EPIF_NEWSLETTER_RATE_LIMIT'   => 5,     // Newsletter signup attempts per IP per hour.
	'EPIF_LEAD_NOTIFY_EMAIL'       => '',    // Where new-lead emails go; empty = admin email.
	'EPIF_LEAD_RATE_LIMIT'         => 5,     // Lead submissions per IP per hour.
	'EPIF_HSTS'                    => false, // Set true once HTTPS works on every URL.
);
foreach ( $epif_defaults as $epif_name => $epif_value ) {
	if ( ! defined( $epif_name ) ) {
		define( $epif_name, $epif_value );
	}
}
unset( $epif_defaults, $epif_name, $epif_value );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
