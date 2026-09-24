<?php
/**
 * EPIF child theme.
 *
 * Presentation only. Site logic (lead capture, hardening) lives in
 * wp-content/mu-plugins/epif-core so it survives theme changes.
 *
 * @package EPIF
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_enqueue_scripts',
	static function () {
		$version = wp_get_theme()->get( 'Version' );

		wp_enqueue_style( 'epif-fonts', 'https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..900&family=Public+Sans:ital,wght@0,400..800;1,400&display=swap', array(), null );
		wp_enqueue_style( 'epif-style', get_stylesheet_uri(), array( 'epif-fonts' ), $version );

		// Location strip, estimate calculator and sticky mobile bar. Small, no dependencies.
		wp_enqueue_script( 'epif-site', get_stylesheet_directory_uri() . '/assets/site.js', array(), $version, array( 'strategy' => 'defer', 'in_footer' => true ) );
		wp_localize_script(
			'epif-site',
			'EPIF_AREAS',
			array(
				'places' => epif_places(),
				'zips'   => epif_zip_map(),
				'prices' => epif_prices(),
			)
		);
	}
);

// Pattern category for the landing page sections.
add_action(
	'init',
	static function () {
		register_block_pattern_category( 'epif', array( 'label' => __( 'EPIF', 'epif' ) ) );
	}
);

/** Logo URL for patterns. */
function epif_logo_url() {
	return get_stylesheet_directory_uri() . '/assets/epif-logo.png';
}

/**
 * Towns the page can personalize for. Rural counties show Barn Revitalization first;
 * non-rural ones hide it (spec §3). Prices are per state column: md, pa, dc.
 */
function epif_places() {
	return array(
		'westminster-md'   => array( 'Westminster', 'MD', 'Carroll County', true ),
		'eldersburg-md'    => array( 'Eldersburg', 'MD', 'Carroll County', true ),
		'hampstead-md'     => array( 'Hampstead', 'MD', 'Carroll County', true ),
		'taneytown-md'     => array( 'Taneytown', 'MD', 'Carroll County', true ),
		'mount-airy-md'    => array( 'Mount Airy', 'MD', 'Carroll County', true ),
		'frederick-md'     => array( 'Frederick', 'MD', 'Frederick County', true ),
		'ellicott-city-md' => array( 'Ellicott City', 'MD', 'Howard County', false ),
		'owings-mills-md'  => array( 'Owings Mills', 'MD', 'Baltimore County', false ),
		'hanover-pa'       => array( 'Hanover', 'PA', 'York County', true ),
		'mcsherrystown-pa' => array( 'McSherrystown', 'PA', 'Adams County', true ),
		'washington-dc'    => array( 'Washington', 'DC', 'District of Columbia', false ),
	);
}

/** ZIP → place key. Prefix "200" is treated as DC in site.js. Extend as towns are added. */
function epif_zip_map() {
	return array(
		'21157' => 'westminster-md',
		'21158' => 'westminster-md',
		'21784' => 'eldersburg-md',
		'21074' => 'hampstead-md',
		'21787' => 'taneytown-md',
		'21771' => 'mount-airy-md',
		'21701' => 'frederick-md',
		'21702' => 'frederick-md',
		'21703' => 'frederick-md',
		'21704' => 'frederick-md',
		'21042' => 'ellicott-city-md',
		'21043' => 'ellicott-city-md',
		'21117' => 'owings-mills-md',
		'17331' => 'hanover-pa',
		'17344' => 'mcsherrystown-pa',
	);
}

/** Business phone from Settings → EPIF Business Info (empty until filled in). */
function epif_phone() {
	return class_exists( 'EPIF_Business' ) ? (string) EPIF_Business::get( 'phone' ) : '';
}

/** tel:/sms: href for the business phone. */
function epif_phone_href( $scheme = 'tel' ) {
	return $scheme . ':' . preg_replace( '/[^0-9+]/', '', epif_phone() );
}

/**
 * Starting prices per area column (md, pa, dc), in whole dollars. null = not published yet,
 * shown as "Ask" in the table and left out of the instant estimate. Filled in once EPIF
 * confirms prices (spec §13 item 2). The estimate shows [price, price × 1.35] as a range.
 */
function epif_prices() {
	$blank = array( 'md' => null, 'pa' => null, 'dc' => null );
	return apply_filters(
		'epif_prices',
		array(
			'min'      => array( 'label' => __( 'Minimum pickup', 'epif' ), 'group' => 'load' ) + $blank,
			'quarter'  => array( 'label' => __( '¼ truck', 'epif' ), 'group' => 'load' ) + $blank,
			'half'     => array( 'label' => __( '½ truck', 'epif' ), 'group' => 'load' ) + $blank,
			'full'     => array( 'label' => __( 'Full truck', 'epif' ), 'group' => 'load' ) + $blank,
			'mattress' => array( 'label' => __( 'Mattress or box spring', 'epif' ), 'group' => 'item' ) + $blank,
			'freon'    => array( 'label' => __( 'Fridge, freezer or AC (freon)', 'epif' ), 'group' => 'item' ) + $blank,
			'tv'       => array( 'label' => __( 'TV or monitor', 'epif' ), 'group' => 'item' ) + $blank,
			'tire'     => array( 'label' => __( 'Tire', 'epif' ), 'group' => 'item' ) + $blank,
		)
	);
}

/** "$123" or "Ask". */
function epif_price_text( $value ) {
	return null === $value ? __( 'Ask', 'epif' ) : '$' . number_format_i18n( (int) $value );
}
