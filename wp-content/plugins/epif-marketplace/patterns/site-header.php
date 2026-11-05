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
 * core/navigation's menu plumbing, and keeping it as plain markup means the
 * links point wherever you type — no menu screen round trip. Edit it with
 * "Edit as HTML" on the block.
 *
 * @package epif
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$epif_mark = esc_url( EPIF_URL . 'assets/epif-mark.svg' );

/**
 * Primary navigation. Point these at the real pages as you build them.
 */
$epif_nav = array(
	'Auctions'     => '#',
	'Shop'         => '#',
	'How it works' => '#how-it-works',
	'Work with us' => '#work-with-us',
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

<!-- wp:search {"label":"Search the marketplace","showLabel":false,"placeholder":"Search items…","buttonText":"Go","className":"epif-searchblock"} /-->

<!-- wp:html -->
<!-- Token balance. Static until accounts exist — swap the number for a shortcode
     once you have real balances, or delete this line to hide it entirely. -->
<a class="epif-chip" href="#">1,240 tokens</a>
<!-- /wp:html -->

<!-- wp:buttons {"className":"epif-buttons"} -->
<div class="wp-block-buttons epif-buttons">
<!-- wp:button {"className":"is-style-epif-bid"} -->
<div class="wp-block-button is-style-epif-bid"><a class="wp-block-button__link wp-element-button" href="#">Book a clear-out</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
