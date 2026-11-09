<?php
/**
 * Renders header + landing page + footer into one standalone HTML file so the
 * whole thing can be eyeballed outside WordPress.
 *
 * Development aid only — it is never deployed (.cpanel.yml copies just the
 * plugin folder). Run it from the repo root:
 *
 *     php tools/render-preview.php > full-preview.html
 *
 * It stubs the few WordPress functions the patterns call, stands in a static
 * form for the dynamic core/search block, and shows a labelled placeholder for
 * each live epif/* block (item grids, stats, booking form), which need
 * WordPress and WooCommerce to render.
 */

define( 'ABSPATH', true );
define( 'EPIF_URL', './wp-content/plugins/epif-marketplace/' );
define( 'EPIF_DIR', dirname( __DIR__ ) . '/wp-content/plugins/epif-marketplace/' );

function esc_html( $s ) { return htmlspecialchars( $s, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $s ) { return htmlspecialchars( $s, ENT_QUOTES, 'UTF-8' ); }
function esc_url( $s )  { return htmlspecialchars( $s, ENT_QUOTES, 'UTF-8' ); }
function home_url( $p = '/' ) { return '#'; }
function add_query_arg( $args, $url = '' ) { return '#'; }

// Stand-in for the plugin's store class; live data needs WordPress.
class EPIF_Store {
	public static function shop_url() { return '#'; }
	public static function setting( $key ) {
		$values = array( 'pickup_location' => 'Westminster, MD', 'service_area' => 'Carroll County and the Baltimore area', 'diverted' => '' );
		return $values[ $key ] ?? '';
	}
}

function epif_part( $slug ) {
	ob_start();
	include EPIF_DIR . 'patterns/' . $slug . '.php';
	return ob_get_clean();
}

$parts = epif_part( 'site-header' ) . epif_part( 'landing-page' ) . epif_part( 'site-footer' );

// core/search is a dynamic block, so render a stand-in for each one in place
// BEFORE the block delimiters get stripped.
$n = 0;
$html = preg_replace_callback(
	'/<!--\s*wp:search\s*(\{.*?\})\s*\/-->/s',
	function ( $m ) use ( &$n ) {
		$n++;
		$attrs       = json_decode( $m[1], true ) ?: array();
		$placeholder = htmlspecialchars( $attrs['placeholder'] ?? 'Search…', ENT_QUOTES, 'UTF-8' );
		$button      = htmlspecialchars( $attrs['buttonText'] ?? 'Search', ENT_QUOTES, 'UTF-8' );

		return '<div class="wp-block-search epif-searchblock"><form role="search" method="get" action="#">'
			. '<label class="screen-reader-text" for="epif-s' . $n . '">Search</label>'
			. '<div class="wp-block-search__inside-wrapper">'
			. '<input class="wp-block-search__input" id="epif-s' . $n . '" type="search" name="s" placeholder="' . $placeholder . '" />'
			. '<button class="wp-block-search__button wp-element-button" type="submit">' . $button . '</button>'
			. '</div></form></div>';
	},
	$parts
);

// EPIF blocks render live store data inside WordPress; show where each one goes.
$html = preg_replace_callback(
	'/<!--\s*wp:epif\/([a-z-]+)[^>]*?\/-->/',
	function ( $m ) {
		return '<div class="epif-empty"><p class="epif-empty__title">Live block: epif/' . $m[1] . '</p><p>Renders from WooCommerce inside WordPress.</p></div>';
	},
	$html
);

// Strip the remaining Gutenberg block delimiters — what's left is the HTML
// WordPress would serve.
$html = preg_replace( '/<!--\s*\/?wp:[^>]*?-->/s', '', $html );

echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'
	. '<meta name="viewport" content="width=device-width, initial-scale=1">'
	. '<title>EPIF — full page preview</title>'
	. '<link rel="stylesheet" href="./wp-content/plugins/epif-marketplace/assets/epif.css">'
	. '<style>body{margin:0;background:#FAF8F4}</style>'
	. '</head><body class="epif-classic-align">'
	. $html
	. '<script src="./wp-content/plugins/epif-marketplace/assets/epif-timers.js"></script>'
	. '</body></html>';
