<?php
/**
 * EPIF site footer pattern.
 *
 * Insert into the theme's Footer template part. Link columns are HTML blocks —
 * they're link lists, same treatment as the category chips on the landing page.
 * Use "Edit as HTML" to change them.
 *
 * Headquartered in Westminster, MD; the service area and the diversion figure
 * come from Settings → EPIF Store (the figure is hidden until one is entered).
 * The contact email and legal links come from EPIF Core (Settings → EPIF
 * Business Info and the published legal pages).
 *
 * @package epif
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Light-ring variant — the dark-ring mark disappears against the ink footer.
$epif_mark = esc_url( EPIF_URL . 'assets/epif-mark-light.svg' );

$epif_shop = EPIF_Store::shop_url();
$epif_home = home_url( '/' );

$epif_columns = array(
	'Marketplace'  => array(
		'Shop everything'    => $epif_shop,
		'Live auctions'      => add_query_arg( array( 'listing' => 'auction', 'orderby' => 'ending' ), $epif_shop ),
		'Buy it now'         => add_query_arg( 'listing', 'buy', $epif_shop ),
		'Shipping &amp; pickup' => $epif_home . '#faq',
	),
	'Work with us' => array(
		'Book a clear-out'     => $epif_home . '#book',
		'List my inventory'    => $epif_home . '#work-with-us',
		'Source inventory'     => $epif_home . '#work-with-us',
		'Fulfillment partners' => $epif_home . '#work-with-us',
	),
	'Company'      => array(
		'How EPIF works' => $epif_home . '#how-it-works',
		'What we take'   => $epif_home . '#faq',
		'Tokens'         => $epif_home . '#tokens',
		'Contact'        => $epif_home . '#book',
	),
);

// Published legal pages from EPIF Core (Privacy, Terms, Cookies, Accessibility).
$epif_legal = class_exists( 'EPIF_Legal' ) ? EPIF_Legal::links() : array();

$epif_email = '';
if ( class_exists( 'EPIF_Business' ) && ! EPIF_Business::is_placeholder( 'email' ) ) {
	$epif_email = EPIF_Business::get( 'email' );
}
$epif_diverted = EPIF_Store::setting( 'diverted' );
$epif_area     = EPIF_Store::setting( 'service_area' );
?>
<!-- wp:group {"align":"full","className":"epif epif-footer","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull epif epif-footer">

<!-- wp:html -->
<div class="epif-footer__top">

	<div class="epif-footer__brandcol">
		<a class="epif-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<img src="<?php echo $epif_mark; ?>" alt="" width="34" height="34" />
			<span>
				<span class="epif-brand__name">EPIF</span>
				<span class="epif-brand__tag">Enjoy the past &middot; Improve the future</span>
			</span>
		</a>
		<p>We clear out houses, garages, estates and closing storefronts across <?php echo esc_html( $epif_area ); ?>, then put everything with life left in it back into circulation instead of into a landfill.</p>
		<?php if ( $epif_diverted ) : ?>
		<p class="epif-footer__impact"><strong><?php echo esc_html( $epif_diverted ); ?></strong> diverted so far</p>
		<?php endif; ?>
	</div>

	<?php foreach ( $epif_columns as $epif_title => $epif_links ) : ?>
	<div>
		<p class="epif-footer__coltitle"><?php echo esc_html( $epif_title ); ?></p>
		<ul>
			<?php foreach ( $epif_links as $epif_label => $epif_href ) : ?>
			<li><a href="<?php echo esc_url( $epif_href ); ?>"><?php echo $epif_label; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php endforeach; ?>

</div>

<div class="epif-footer__bottom">
	<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> EPIF &middot; Westminster, MD &middot; Serving the Baltimore area<?php if ( $epif_email ) : ?> &middot; <a href="mailto:<?php echo esc_attr( $epif_email ); ?>"><?php echo esc_html( $epif_email ); ?></a><?php endif; ?></span>
	<?php if ( $epif_legal ) : ?>
	<ul class="epif-footer__legal">
		<?php foreach ( $epif_legal as $epif_label => $epif_href ) : ?>
		<li><a href="<?php echo esc_url( $epif_href ); ?>"><?php echo esc_html( $epif_label ); ?></a></li>
		<?php endforeach; ?>
	</ul>
	<?php endif; ?>
</div>
<!-- /wp:html -->

</div>
<!-- /wp:group -->
