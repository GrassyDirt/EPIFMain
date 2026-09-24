<?php
/**
 * Plugin Name: EPIF Core
 * Description: Site backend for epifservices.com — coming-soon mode, newsletter signups, lead capture, business info, legal pages, and security/performance defaults. Loaded as a must-use plugin so it cannot be deactivated from the dashboard.
 * Version:     1.1.0
 * Author:      EPIF Services
 * License:     GPL-2.0-or-later
 *
 * @package EPIF_Core
 */

defined( 'ABSPATH' ) || exit;

define( 'EPIF_CORE_VERSION', '1.1.0' );
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
if ( ! defined( 'EPIF_COMING_SOON' ) ) {
	// Show visitors the coming-soon page instead of the site.
	define( 'EPIF_COMING_SOON', false );
}
if ( ! defined( 'EPIF_NEWSLETTER_DOUBLE_OPTIN' ) ) {
	// Require subscribers to confirm by email before they count as subscribed.
	define( 'EPIF_NEWSLETTER_DOUBLE_OPTIN', true );
}
if ( ! defined( 'EPIF_NEWSLETTER_RATE_LIMIT' ) ) {
	// Max newsletter signup attempts allowed per IP per hour.
	define( 'EPIF_NEWSLETTER_RATE_LIMIT', 5 );
}

require_once EPIF_CORE_DIR . 'includes/class-epif-guard.php';
require_once EPIF_CORE_DIR . 'includes/class-epif-forms.php';
require_once EPIF_CORE_DIR . 'includes/class-epif-business.php';
require_once EPIF_CORE_DIR . 'includes/class-epif-legal.php';
require_once EPIF_CORE_DIR . 'includes/class-epif-leads.php';
require_once EPIF_CORE_DIR . 'includes/class-epif-lead-api.php';
require_once EPIF_CORE_DIR . 'includes/class-epif-lead-form.php';
require_once EPIF_CORE_DIR . 'includes/class-epif-newsletter.php';
require_once EPIF_CORE_DIR . 'includes/class-epif-coming-soon.php';
require_once EPIF_CORE_DIR . 'includes/hardening.php';
require_once EPIF_CORE_DIR . 'includes/performance.php';

EPIF_Guard::init();
EPIF_Forms::init();
EPIF_Business::init();
EPIF_Legal::init();
EPIF_Leads::init();
EPIF_Lead_API::init();
EPIF_Lead_Form::init();
EPIF_Newsletter::init();
EPIF_Coming_Soon::init();
