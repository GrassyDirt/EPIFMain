<?php
/**
 * EPIF site header pattern.
 *
 * Insert this into the theme's Header template part (Appearance → Editor →
 * Patterns → Header, or Design → Header in a block theme) and delete whatever
 * the theme shipped. It is registered against core/template-part/header so it
 * shows up in the header inserter rather than being buried in the page list.
 *
 * The nav is an HTML block on purpose. A five-item bar doesn't need
 * core/navigation's menu plumbing. Links point at the live store (shop,
 * auctions) and the landing page sections. The cart link is a live EPIF block
 * so its count stays current, and the search box searches products.
 *
 * @package epif
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$epif_mark = esc_url( EPIF_URL . 'assets/epif-mark.svg' );

$epif_shop = EPIF_Store::shop_url();
$epif_home = home_url( '/' );

/**
 * Primary navigation.
 */
$epif_nav = array(
	'Shop'         => $epif_shop,
	'Auctions'     => add_query_arg( array( 'listing' => 'auction', 'orderby' => 'ending' ), $epif_shop ),
	'How it works' => $epif_home . '#how-it-works',
	'Work with us' => $epif_home . '#work-with-us',
);
?>
<!-- wp:group {"align":"full","className":"epif epif-header","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull epif epif-header">
<!-- wp:group {"className":"epif-header__inner","layout":{"type":"default"}} -->
<div class="wp-block-group epif-header__inner">

<!-- wp:html -->
<a class="epif-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
	<img src="<?php echo $epif_mark; ?>" alt="" width="34" height="34" />
	<span class="epif-brand__name">EPIF</span>
	<span class="screen-reader-text">Enjoy the past, improve the future — home</span>
</a>

<nav class="epif-nav" aria-label="Primary">
	<?php foreach ( $epif_nav as $epif_label => $epif_href ) : ?>
	<a href="<?php echo esc_url( $epif_href ); ?>"><?php echo esc_html( $epif_label ); ?></a>
	<?php endforeach; ?>
</nav>

<div class="epif-header__spacer"></div>
<!-- /wp:html -->

<!-- wp:search {"label":"Search the marketplace","showLabel":false,"placeholder":"Search items…","buttonText":"Go","query":{"post_type":"product"},"className":"epif-searchblock"} /-->

<!-- wp:epif/header-cart /-->

<!-- wp:buttons {"className":"epif-buttons"} -->
<div class="wp-block-buttons epif-buttons">
<!-- wp:button {"className":"is-style-epif-bid"} -->
<div class="wp-block-button is-style-epif-bid"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $epif_home . '#book' ); ?>">Book a clear-out</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
