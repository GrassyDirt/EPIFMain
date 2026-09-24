<?php
/**
 * Plugin Name: EPIF Core
 * Description: Site backend for epifservices.com — landing page lead capture, lead storage, and security/performance defaults. Loaded as a must-use plugin so it cannot be deactivated from the dashboard.
 * Version:     1.0.0
 * Author:      EPIF Services
 * License:     GPL-2.0-or-later
 *
 * @package EPIF_Core
 */

defined( 'ABSPATH' ) || exit;

define( 'EPIF_CORE_VERSION', '1.0.0' );
define( 'EPIF_CORE_DIR', __DIR__ . '/epif-core/' );

// Settings can be overridden in wp-config.php before WordPress loads.
if ( ! defined( 'EPIF_LEAD_NOTIFY_EMAIL' ) ) {
	// Empty string = send to the site admin email (Settings → General).
	define( 'EPIF_LEAD_NOTIFY_EMAIL', '' );
}
if ( ! defined( 'EPIF_LEAD_RATE_LIMIT' ) ) {
	// Max lead submissions allowed per IP per hour.
	define( 'EPIF_LEAD_RATE_LIMIT', 5 );
}

require_once EPIF_CORE_DIR . 'includes/class-epif-leads.php';
require_once EPIF_CORE_DIR . 'includes/class-epif-lead-api.php';
require_once EPIF_CORE_DIR . 'includes/class-epif-lead-form.php';
require_once EPIF_CORE_DIR . 'includes/hardening.php';
require_once EPIF_CORE_DIR . 'includes/performance.php';

EPIF_Leads::init();
EPIF_Lead_API::init();
EPIF_Lead_Form::init();
