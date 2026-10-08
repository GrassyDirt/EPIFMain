<?php
/**
 * Title: EPIF early access (digital mall)
 * Slug: epif/early-access
 * Categories: epif, banner
 * Description: Full early-access page for the EPIF digital mall: hero, how it works, rollout, founding spots, join form and FAQ. Shown to visitors while EPIF_COMING_SOON is on.
 * Inserter: true
 *
 * Bracketed [VALUES] are deliberate placeholders: fill them in before launch.
 *
 * @package EPIF
 */

$epif_info = static function ( $field ) {
	return class_exists( 'EPIF_Business' ) ? EPIF_Business::get( $field ) : '';
};
$epif_phone = $epif_info( 'phone' );
$epif_email = $epif_info( 'email' );
$epif_email = is_email( $epif_email ) ? $epif_email : 'info@epifservices.com';
$epif_mail  = '<a href="mailto:' . esc_attr( antispambot( $epif_email ) ) . '">' . esc_html( antispambot( $epif_email ) ) . '</a>';
$epif_tel   = $epif_phone ? esc_html( $epif_phone ) : '<mark>[PHONE]</mark>';

/** Founding stores in the first round. */
$epif_spots = (int) apply_filters( 'epif_founding_spots', 25 );
?>
<!-- wp:html -->
<div class="epif-ea">

<header class="epif-ea-wrap epif-ea-header">
	<div class="epif-ea-logo">EPIF</div>
	<nav aria-label="<?php esc_attr_e( 'Page sections', 'epif' ); ?>" class="epif-ea-nav">
		<a href="#tenants"><?php esc_html_e( 'For store owners', 'epif' ); ?></a>
		<a href="#join"><?php esc_html_e( 'Join the list', 'epif' ); ?></a>
	</nav>
</header>

<main>
<section class="epif-ea-wrap epif-ea-hero">
	<div class="epif-ea-hero-text">
		<h1><?php esc_html_e( 'The first digital mall, made of local stores you can walk through.', 'epif' ); ?></h1>
		<p class="epif-ea-lede"><?php esc_html_e( 'EPIF scans shops in 3D and puts them together in one mall that opens in your browser. Shoppers can look around a store and buy from it. Store owners can reserve a founding spot now.', 'epif' ); ?></p>
		<div class="epif-ea-ctas">
			<a class="epif-ea-btn" href="#join"><?php esc_html_e( 'Join the list', 'epif' ); ?></a>
			<a class="epif-ea-btn epif-ea-btn--line" href="#tenants"><?php esc_html_e( 'Reserve a founding spot', 'epif' ); ?></a>
		</div>
	</div>
	<div class="epif-ea-photo epif-ea-photo--hero"><?php esc_html_e( 'Photo goes here: a capture of a real store, or a job-site photo from EPIF.', 'epif' ); ?></div>
</section>

<section class="epif-ea-band">
	<div class="epif-ea-wrap epif-ea-split">
		<h2><?php esc_html_e( 'How the mall works', 'epif' ); ?></h2>
		<div class="epif-ea-body">
			<p><?php esc_html_e( 'A shopper opens the mall in a browser and walks into a store that has been scanned in 3D. You can look at the shelves from any angle, tap an item, and go to that store\'s own shop to buy it or arrange a pickup.', 'epif' ); ?></p>
			<p><?php esc_html_e( 'A store owner books a scan. We come to the shop, capture it, process the scan, and place it in the mall. The store pays monthly rent for its spot, plus a fee when we come back to update it or take it down.', 'epif' ); ?></p>
			<p><?php esc_html_e( 'We run junk removal and resale in Carroll County, Maryland, and we are starting with stores in and around the county.', 'epif' ); ?></p>
		</div>
	</div>
</section>

<section class="epif-ea-band">
	<div class="epif-ea-wrap epif-ea-split">
		<h2><?php esc_html_e( 'What gets built, in order', 'epif' ); ?></h2>
		<dl class="epif-ea-body epif-ea-steps">
			<div><dt><?php esc_html_e( 'First', 'epif' ); ?></dt><dd><?php esc_html_e( 'Stores scanned in 3D and open in the browser, with a link each owner can share and embed on their own site.', 'epif' ); ?></dd></div>
			<div><dt><?php esc_html_e( 'Then', 'epif' ); ?></dt><dd><?php esc_html_e( 'Clickable items inside a scan that open the store\'s own checkout.', 'epif' ); ?></dd></div>
			<div><dt><?php esc_html_e( 'Later', 'epif' ); ?></dt><dd><?php esc_html_e( 'Motion captures and live tours with someone from the store in the room. No date yet.', 'epif' ); ?></dd></div>
		</dl>
	</div>
</section>

<section id="tenants" class="epif-ea-tint">
	<div class="epif-ea-wrap">
		<h2><?php esc_html_e( 'Founding spots for store owners', 'epif' ); ?></h2>
		<p class="epif-ea-muted epif-ea-intro">
			<?php
			/* translators: %d: number of founding spots. */
			echo esc_html( sprintf( __( 'We scan each store by hand, so the first round is limited to %d stores.', 'epif' ), $epif_spots ) );
			?>
			<?php esc_html_e( 'Founding stores keep their monthly rate for', 'epif' ); ?> <mark>[TERM]</mark> <?php esc_html_e( 'and choose their location in the mall before anyone else does.', 'epif' ); ?>
		</p>

		<div class="epif-ea-cols">
			<div>
				<h3><?php esc_html_e( 'What you get', 'epif' ); ?></h3>
				<ul class="epif-ea-muted">
					<li><?php esc_html_e( 'Scan and install fee waived or discounted', 'epif' ); ?></li>
					<li><?php esc_html_e( 'Monthly rate held for', 'epif' ); ?> <mark>[TERM]</mark></li>
					<li><?php esc_html_e( 'First choice of location in the mall', 'epif' ); ?></li>
					<li><?php esc_html_e( 'A link and embed code for your own website, usable before the mall opens to shoppers', 'epif' ); ?></li>
					<li><?php esc_html_e( 'A refundable deposit holds your spot until your scan date', 'epif' ); ?></li>
				</ul>
				<h3><?php esc_html_e( 'What is left out for now', 'epif' ); ?></h3>
				<p class="epif-ea-muted"><?php esc_html_e( 'Live tours, motion capture, and automatic item tagging. We will tell you when each one is ready.', 'epif' ); ?></p>
			</div>
			<div>
				<h3><?php esc_html_e( 'What it costs', 'epif' ); ?></h3>
				<dl class="epif-ea-prices">
					<div><dt><?php esc_html_e( 'Scan and install, one time', 'epif' ); ?></dt><dd><mark>[YOUR PRICE]</mark></dd></div>
					<div><dt><?php esc_html_e( 'Virtual rent, per month', 'epif' ); ?></dt><dd><mark>[YOUR PRICE]</mark></dd></div>
					<div><dt><?php esc_html_e( 'Update or rescan visit', 'epif' ); ?></dt><dd><mark>[YOUR PRICE]</mark></dd></div>
					<div><dt><?php esc_html_e( 'Taking the store down', 'epif' ); ?></dt><dd><mark>[YOUR PRICE]</mark></dd></div>
				</dl>
				<p class="epif-ea-muted epif-ea-small"><?php esc_html_e( 'Rent starts when the mall opens to shoppers:', 'epif' ); ?> <mark>[YOUR START TERMS]</mark>. <?php esc_html_e( 'If you leave, exporting your scan and data is free. We only charge when we have to visit in person.', 'epif' ); ?></p>
				<p class="epif-ea-muted epif-ea-small"><?php esc_html_e( 'You will see the tenant agreement before you pay anything. It covers who owns the scan, what we need in order to display it, anyone who appears in it, takedowns, and refunds.', 'epif' ); ?></p>
			</div>
		</div>
		<p class="epif-ea-ctas"><a class="epif-ea-btn" href="#join"><?php esc_html_e( 'Reserve a founding spot', 'epif' ); ?></a></p>
	</div>
</section>

<section class="epif-ea-plain">
	<div class="epif-ea-wrap epif-ea-cols">
		<div>
			<h2><?php esc_html_e( 'Who you would be working with', 'epif' ); ?></h2>
			<p class="epif-ea-muted"><?php esc_html_e( 'EPIF is locally owned and based in Carroll County, Maryland. We do junk removal and resale, and we resell and donate what we can to keep it out of the landfill.', 'epif' ); ?> <mark>[OWNER NAMES]</mark> <?php esc_html_e( 'run EPIF.', 'epif' ); ?></p>
			<p class="epif-ea-muted"><?php esc_html_e( 'Call or text', 'epif' ); ?> <?php echo $epif_tel; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?>, <?php esc_html_e( 'or email', 'epif' ); ?> <?php echo $epif_mail; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?>.</p>
		</div>
		<div class="epif-ea-photo"><?php esc_html_e( 'Photo goes here: the owners at a real job.', 'epif' ); ?></div>
	</div>
</section>

<section id="join" class="epif-ea-tint">
	<div class="epif-ea-narrow">
		<h2><?php esc_html_e( 'Join the list', 'epif' ); ?></h2>
		<p class="epif-ea-muted"><?php esc_html_e( 'Shoppers and store owners both start here. Founding spots go in the order store owners sign up.', 'epif' ); ?></p>
		<?php
		// Rendered here, not as [shortcode] text: templates run shortcodes before patterns expand.
		echo shortcode_exists( 'epif_join_form' ) ? do_shortcode( '[epif_join_form button="Join the list"]' ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput
		?>
	</div>
</section>

<section class="epif-ea-plain">
	<div class="epif-ea-wrap epif-ea-split">
		<h2><?php esc_html_e( 'Questions', 'epif' ); ?></h2>
		<div class="epif-ea-body epif-ea-faq">
			<details><summary><?php esc_html_e( 'Is the mall open yet?', 'epif' ); ?></summary><p><?php esc_html_e( 'No. We are booking founding store scans first. People on the list get the first walk-through when it opens.', 'epif' ); ?></p></details>
			<details><summary><?php esc_html_e( 'What does a store owner pay?', 'epif' ); ?></summary><p><?php esc_html_e( 'A one-time scan and install fee, monthly rent for the spot, and a fee when we visit to update or take down your store. Founding stores hold their rates for', 'epif' ); ?> <mark>[TERM]</mark>. <?php esc_html_e( 'Exact numbers are in the table above and are shared again before you commit.', 'epif' ); ?></p></details>
			<details><summary><?php esc_html_e( 'What if I want to leave?', 'epif' ); ?></summary><p><?php esc_html_e( 'You can. Exporting your scan and data is free. We only charge when we need to come to the store to take down an installation.', 'epif' ); ?></p></details>
			<details><summary><?php esc_html_e( 'Who owns my scan?', 'epif' ); ?></summary><p><?php esc_html_e( 'The tenant agreement says so in plain language, and you will see it before you pay anything.', 'epif' ); ?></p></details>
			<details><summary><?php esc_html_e( 'What about customers or staff who are in the store during a scan?', 'epif' ); ?></summary><p><?php esc_html_e( 'We agree on timing and signage with you before the scan, and the agreement covers anyone who appears in it.', 'epif' ); ?></p></details>
			<details><summary><?php esc_html_e( 'Do shoppers need any equipment?', 'epif' ); ?></summary><p><?php esc_html_e( 'A modern phone or laptop with a browser should be enough. We will confirm that during testing.', 'epif' ); ?></p></details>
			<details><summary><?php esc_html_e( 'Where is EPIF based?', 'epif' ); ?></summary><p><?php esc_html_e( 'Carroll County, Maryland.', 'epif' ); ?></p></details>
		</div>
	</div>
</section>
</main>

</div>
<!-- /wp:html -->
