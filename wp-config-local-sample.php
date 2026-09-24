<?php
/**
 * Server-only secrets for epifservices.com.
 *
 * Copy to wp-config-local.php (git-ignored), ideally ONE DIRECTORY ABOVE the web root,
 * and fill in the values. Never commit the real file.
 *
 * Fresh salts: https://api.wordpress.org/secret-key/1.1/salt/
 *
 * @package EPIF
 */

define( 'DB_NAME', 'database_name_here' );
define( 'DB_USER', 'username_here' );
define( 'DB_PASSWORD', 'password_here' );
define( 'DB_HOST', 'localhost' );

define( 'AUTH_KEY',         'put your unique phrase here' );
define( 'SECURE_AUTH_KEY',  'put your unique phrase here' );
define( 'LOGGED_IN_KEY',    'put your unique phrase here' );
define( 'NONCE_KEY',        'put your unique phrase here' );
define( 'AUTH_SALT',        'put your unique phrase here' );
define( 'SECURE_AUTH_SALT', 'put your unique phrase here' );
define( 'LOGGED_IN_SALT',   'put your unique phrase here' );
define( 'NONCE_SALT',       'put your unique phrase here' );

// Optional per-server overrides, e.g. on a staging copy:
// define( 'WP_ENVIRONMENT_TYPE', 'staging' );
// define( 'WP_DEBUG', true );
// define( 'WP_DEBUG_LOG', true );
// define( 'EPIF_LEAD_NOTIFY_EMAIL', 'leads@epifservices.com' );
