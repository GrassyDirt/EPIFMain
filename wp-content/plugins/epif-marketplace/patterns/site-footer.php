<?php
/**
 * EPIF site footer pattern.
 *
 * Insert into the theme's Footer template part. Link columns are HTML blocks —
 * they're link lists, same treatment as the category chips on the landing page.
 * Use "Edit as HTML" to change them.
 *
 * Service area: headquartered in Westminster, MD, serving Carroll County and
 * the Baltimore area. The contact email and the legal links come from EPIF Core
 * (Settings → EPIF Business Info, and the published legal pages) when it is
 * loaded; the email is left out until one is saved there.
 *
 * @package epif
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Light-ring variant — the dark-ring mark disappears against the ink footer.
$epif_mark = esc_url( EPIF_URL . 'assets/epif-mark-light.svg' );

$epif_columns = array(
	'Marketplace' => array(
		'Live auctions'      => '#',
		'Buy it now'         => '#',
		'Ending today'       => '#',
		'How bidding works'  => '#',
		'Shipping &amp; pickup'  => '#',
	),
	'Work with us' => array(
		'Book a clear-out'      => '#',
		'List my inventory'     => '#',
		'Source inventory'      => '#',
		'Fulfillment partners'  => '#',
	),
	'Company' => array(
		'About EPIF'      => '#',
		'What we take'    => '#',
		'Tokens'          => '#',
		'Contact'         => '#',
	),
);

$epif_legal = array(
	'Terms'          => '#',
	'Privacy'        => '#',
	'Bidding rules'  => '#',
	'Returns'        => '#',
);

// Use the published legal pages from EPIF Core when they exist.
if ( class_exists( 'EPIF_Legal' ) ) {
	$epif_terms   = EPIF_Legal::url( 'terms-of-use' );
	$epif_privacy = EPIF_Legal::url( 'privacy-policy' );
	if ( $epif_terms ) {
		$epif_legal['Terms'] = $epif_terms;
	}
	if ( $epif_privacy ) {
		$epif_legal['Privacy'] = $epif_privacy;
	}
}

$epif_email = '';
if ( class_exists( 'EPIF_Business' ) && ! EPIF_Business::is_placeholder( 'email' ) ) {
	$epif_email = EPIF_Business::get( 'email' );
}
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
		<p>We clear out houses, garages, estates and closing storefronts across Carroll County and the Baltimore area, then put everything with life left in it back into circulation instead of into a landfill.</p>
		<p class="epif-footer__impact"><strong>96 tons</strong> diverted so far</p>
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
	<ul class="epif-footer__legal">
		<?php foreach ( $epif_legal as $epif_label => $epif_href ) : ?>
		<li><a href="<?php echo esc_url( $epif_href ); ?>"><?php echo esc_html( $epif_label ); ?></a></li>
		<?php endforeach; ?>
	</ul>
</div>
<!-- /wp:html -->

</div>
<!-- /wp:group -->
