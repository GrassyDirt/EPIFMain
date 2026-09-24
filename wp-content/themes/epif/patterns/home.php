<?php
/**
 * Title: EPIF home page
 * Slug: epif/home
 * Categories: epif
 * Inserter: no
 * Description: Full landing page (spec §2). The HTML is the default all-areas version; assets/site.js swaps town, prices and services after load.
 *
 * @package EPIF
 */

$epif_prices = epif_prices();
$epif_phone  = epif_phone();
$epif_tiers  = array(
	'min'     => array( __( 'Single item', 'epif' ), __( 'Minimum', 'epif' ) ),
	'quarter' => array( __( '¼ truck', 'epif' ), __( 'About a room', 'epif' ) ),
	'half'    => array( __( '½ truck', 'epif' ), __( 'About a garage', 'epif' ) ),
	'full'    => array( __( 'Full truck', 'epif' ), __( 'About a house', 'epif' ) ),
);
$epif_faq    = array(
	array( __( 'How do you price a job?', 'epif' ), __( 'By how much of the truck your things fill (a minimum, then ¼, ½ or a full truck), plus a per-item fee for a few items like mattresses and freon appliances. Send photos and we confirm the final price before we book.', 'epif' ) ),
	array( __( 'What happens to my stuff?', 'epif' ), __( 'We sort every load. Good pieces are recirculated through our shop, usable goods are donated, materials are recycled, and only what is left goes to a landfill. After every job you get a diversion receipt showing where your items went.', 'epif' ) ),
	array( __( 'Do I need to be home for the pickup?', 'epif' ), __( "No, you don't have to be. We just need the scope of what you need done and a way in. We work on your time and stay in contact with you throughout the whole process.", 'epif' ) ),
	array( __( 'Which areas do you serve?', 'epif' ), __( 'Carroll County (our home base), Frederick, Baltimore and Howard counties in Maryland; Hanover, McSherrystown and nearby towns in Pennsylvania; and Washington, DC. We are coming to Virginia soon. Outside that area, the shop still ships nationwide.', 'epif' ) ),
	array( __( 'Do you buy or consign inventory?', 'epif' ), __( "Yes. For returns, overstock and closing-store inventory we offer consignment or an outright buyout. There's no public price: tell us what you have in the partner form and we'll talk it through.", 'epif' ) ),
	array( __( 'How do EPIF Tokens work?', 'epif' ), __( 'You earn 150 tokens when you book a clear-out, 40 when an item from your clear-out sells, and 10 when you buy secondhand in the shop. Every 100 tokens is $2.50 off.', 'epif' ) ),
);
?>
<!-- wp:html -->
<section class="epif-hero epif-wrap" aria-labelledby="epif-hero-title">
	<div class="epif-hero__copy">
		<p class="epif-eyebrow" data-epif-eyebrow><?php esc_html_e( 'Based in Carroll County, MD · Serving MD, PA & DC', 'epif' ); ?></p>
		<h1 id="epif-hero-title" data-epif-h1><?php esc_html_e( 'Junk removal across MD, PA & DC, priced from your photos', 'epif' ); ?></h1>
		<p class="epif-lead"><strong><?php esc_html_e( 'Enjoy the past, improve the future.', 'epif' ); ?></strong> <?php esc_html_e( 'We clear out houses, garages, estates and storefronts, then put what we haul back into circulation: recirculated, donated or recycled, with the landfill last.', 'epif' ); ?></p>
		<div class="epif-row">
			<a class="wp-element-button epif-btn-lg" href="#quote"><?php esc_html_e( 'Get a quote', 'epif' ); ?> →</a>
			<?php if ( $epif_phone ) : ?>
				<a class="epif-btn-outline epif-btn-lg" href="<?php echo esc_attr( epif_phone_href() ); ?>"><?php echo esc_html( sprintf( /* translators: %s phone */ __( 'Call %s', 'epif' ), $epif_phone ) ); ?></a>
			<?php endif; ?>
		</div>
		<ul class="epif-trust" aria-label="<?php esc_attr_e( 'Why customers trust EPIF', 'epif' ); ?>">
			<li><?php esc_html_e( 'Price by text or email, no obligation', 'epif' ); ?></li>
			<li><?php esc_html_e( "You don't need to be home", 'epif' ); ?></li>
			<li><?php esc_html_e( 'Diversion receipt with every job', 'epif' ); ?></li>
		</ul>
	</div>
	<figure class="epif-hero__media">
		<div class="epif-photo-slot"><?php esc_html_e( 'Add a real job photo here', 'epif' ); ?></div>
		<figcaption class="epif-route">
			<span class="epif-kicker"><?php esc_html_e( 'Every load is sorted in this order', 'epif' ); ?></span>
			<ol>
				<li><?php esc_html_e( 'Recirculate', 'epif' ); ?></li>
				<li><?php esc_html_e( 'Donate', 'epif' ); ?></li>
				<li><?php esc_html_e( 'Recycle', 'epif' ); ?></li>
				<li class="is-last"><?php esc_html_e( 'Landfill last', 'epif' ); ?></li>
			</ol>
		</figcaption>
	</figure>
</section>

<section id="services" class="epif-band epif-band--light" aria-labelledby="epif-services-title">
	<div class="epif-wrap">
		<div class="epif-head">
			<div><p class="epif-eyebrow"><?php esc_html_e( 'Services', 'epif' ); ?></p><h2 id="epif-services-title"><?php esc_html_e( 'Services you can book today', 'epif' ); ?></h2></div>
			<p class="epif-lead"><?php esc_html_e( 'Every job starts the same way: send a few photos and we reply with a price by text or email.', 'epif' ); ?></p>
		</div>
		<div class="epif-cards" data-epif-cards>
			<article class="epif-card" style="order:1">
				<h3><?php esc_html_e( 'Junk removal & cleanouts', 'epif' ); ?></h3>
				<p><?php esc_html_e( 'Houses, garages, estates and storefronts. You pay for how much of the truck you fill, and we sort it all before anything goes to a landfill.', 'epif' ); ?></p>
				<a class="wp-element-button" href="#quote" data-epif-service="Junk removal & cleanouts"><?php esc_html_e( 'Get a quote', 'epif' ); ?></a>
			</article>
			<article class="epif-card" style="order:2" data-epif-barn>
				<span class="epif-tag" data-epif-barn-tag><?php esc_html_e( 'Rural counties', 'epif' ); ?></span>
				<h3><?php esc_html_e( 'Barn Revitalization', 'epif' ); ?></h3>
				<p><?php esc_html_e( 'For barns, sheds and outbuildings across Carroll, Frederick, Adams and York counties. Priced per project.', 'epif' ); ?></p>
				<a class="wp-element-button" href="#quote" data-epif-service="Barn Revitalization"><?php esc_html_e( 'Get a quote', 'epif' ); ?></a>
			</article>
			<article class="epif-card" style="order:3">
				<h3><?php esc_html_e( 'Reverse logistics', 'epif' ); ?></h3>
				<p><?php esc_html_e( 'Returns, overstock and inventory from closing stores. We take it on consignment or buy it outright, then find it new buyers. No public price: let’s talk.', 'epif' ); ?></p>
				<a class="epif-btn-outline" href="#partners" data-epif-partner="Inventory & fulfillment"><?php esc_html_e( 'Discuss consignment or buyout', 'epif' ); ?></a>
			</article>
		</div>
		<h3 class="epif-kicker"><?php esc_html_e( 'Coming soon', 'epif' ); ?></h3>
		<div class="epif-cards epif-cards--2">
			<article class="epif-card epif-card--soon"><h4><?php esc_html_e( 'Waste sorting & diversion', 'epif' ); ?></h4><p><?php esc_html_e( 'Construction and renovation debris, sorted so less of it ends up in a landfill. Includes waste plans for LEED projects.', 'epif' ); ?></p></article>
			<article class="epif-card epif-card--soon"><h4><?php esc_html_e( '3D & AR shopping', 'epif' ); ?></h4><p><?php esc_html_e( 'See the exact item and its condition in 3D. Take a photo of your room and place EPIF items in it before you buy.', 'epif' ); ?></p></article>
		</div>
	</div>
</section>

<section id="pricing" class="epif-band" aria-labelledby="epif-pricing-title">
	<div class="epif-wrap">
		<div class="epif-head">
			<div><p class="epif-eyebrow"><?php esc_html_e( 'Pricing', 'epif' ); ?></p><h2 id="epif-pricing-title"><?php esc_html_e( 'Starting prices, confirmed from your photos', 'epif' ); ?></h2></div>
			<p class="epif-lead" data-epif-price-note aria-live="polite"><?php esc_html_e( 'Prices vary by area. Add your ZIP to highlight yours.', 'epif' ); ?></p>
		</div>
		<div class="epif-split">
			<div class="epif-table-wrap">
				<table class="epif-table">
					<caption class="screen-reader-text"><?php esc_html_e( 'Starting prices by area', 'epif' ); ?></caption>
					<thead><tr>
						<th scope="col"><?php esc_html_e( 'Starting at', 'epif' ); ?></th>
						<th scope="col" data-epif-col="md"><?php esc_html_e( 'Maryland', 'epif' ); ?></th>
						<th scope="col" data-epif-col="pa"><?php esc_html_e( 'Pennsylvania', 'epif' ); ?></th>
						<th scope="col" data-epif-col="dc"><?php esc_html_e( 'Washington, DC', 'epif' ); ?></th>
					</tr></thead>
					<tbody>
					<?php foreach ( $epif_prices as $epif_row ) : ?>
						<tr>
							<th scope="row"><?php echo esc_html( $epif_row['label'] ); ?></th>
							<?php foreach ( array( 'md', 'pa', 'dc' ) as $epif_col ) : ?>
								<td data-epif-col="<?php echo esc_attr( $epif_col ); ?>"><?php echo esc_html( epif_price_text( $epif_row[ $epif_col ] ) ); ?></td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
						<tr data-epif-barn><th scope="row"><?php esc_html_e( 'Barn Revitalization', 'epif' ); ?></th><td colspan="3"><?php esc_html_e( 'Priced per project', 'epif' ); ?></td></tr>
						<tr><th scope="row"><?php esc_html_e( 'Consignment or buyout', 'epif' ); ?></th><td colspan="3"><a href="#partners" data-epif-partner="Inventory & fulfillment"><?php esc_html_e( "No public price. Let's talk", 'epif' ); ?></a></td></tr>
					</tbody>
				</table>
			</div>
			<div class="epif-stack">
				<div class="epif-card"><h3><?php esc_html_e( 'What we take', 'epif' ); ?></h3><ul class="epif-list-2"><li><?php esc_html_e( 'Furniture & mattresses', 'epif' ); ?></li><li><?php esc_html_e( 'Appliances & electronics', 'epif' ); ?></li><li><?php esc_html_e( 'Household & garage clutter', 'epif' ); ?></li><li><?php esc_html_e( 'Estate & storefront contents', 'epif' ); ?></li><li><?php esc_html_e( 'Barn & outbuilding contents', 'epif' ); ?></li><li><?php esc_html_e( 'Yard waste & brush', 'epif' ); ?></li><li><?php esc_html_e( 'Light renovation debris', 'epif' ); ?></li><li><?php esc_html_e( 'Bikes, toys & sports gear', 'epif' ); ?></li></ul></div>
				<div class="epif-card"><h3><?php esc_html_e( "What we can't take", 'epif' ); ?></h3><ul class="epif-list-2"><li><?php esc_html_e( 'Wet paint, solvents & chemicals', 'epif' ); ?></li><li><?php esc_html_e( 'Propane tanks & fuel', 'epif' ); ?></li><li><?php esc_html_e( 'Asbestos', 'epif' ); ?></li><li><?php esc_html_e( 'Medical waste & sharps', 'epif' ); ?></li><li><?php esc_html_e( 'Motor oil & car fluids', 'epif' ); ?></li><li><?php esc_html_e( 'Ammunition & explosives', 'epif' ); ?></li></ul><p class="epif-small"><?php esc_html_e( "Not sure about something? List it in your quote and we'll tell you.", 'epif' ); ?></p></div>
			</div>
		</div>
	</div>
</section>

<section id="estimate" class="epif-band epif-band--light" aria-labelledby="epif-est-title">
	<div class="epif-wrap epif-split">
		<div><p class="epif-eyebrow"><?php esc_html_e( 'Instant estimate', 'epif' ); ?></p><h2 id="epif-est-title"><?php esc_html_e( 'Get a ballpark in 10 seconds', 'epif' ); ?></h2><p class="epif-lead"><?php esc_html_e( "Pick how much of the truck you'd fill and add any special items. Your photos confirm the final price.", 'epif' ); ?></p></div>
		<form class="epif-card epif-estimate" data-epif-estimate>
			<fieldset><legend><?php esc_html_e( 'How much stuff?', 'epif' ); ?></legend>
				<div class="epif-chips epif-chips--4">
				<?php foreach ( $epif_tiers as $epif_key => $epif_tier ) : ?>
					<label class="epif-chip"><input type="radio" name="tier" value="<?php echo esc_attr( $epif_key ); ?>" <?php checked( 'half', $epif_key ); ?>><span><?php echo esc_html( $epif_tier[0] ); ?><small><?php echo esc_html( $epif_tier[1] ); ?></small></span></label>
				<?php endforeach; ?>
				</div>
			</fieldset>
			<fieldset><legend><?php esc_html_e( 'Special items', 'epif' ); ?></legend>
				<div class="epif-chips">
					<label class="epif-chip"><input type="checkbox" name="item" value="mattress"><?php esc_html_e( 'Mattress', 'epif' ); ?></label>
					<label class="epif-chip"><input type="checkbox" name="item" value="freon"><?php esc_html_e( 'Fridge or AC', 'epif' ); ?></label>
					<label class="epif-chip"><input type="checkbox" name="item" value="tv"><?php esc_html_e( 'TV or monitor', 'epif' ); ?></label>
					<label class="epif-chip"><input type="checkbox" name="item" value="tire"><?php esc_html_e( 'Tires', 'epif' ); ?></label>
				</div>
			</fieldset>
			<div class="epif-estimate__out" aria-live="polite"><span data-epif-est-label><?php esc_html_e( 'Estimated range', 'epif' ); ?></span><strong data-epif-est-value><?php esc_html_e( 'Send photos for a price', 'epif' ); ?></strong><a class="epif-btn-light" href="#quote"><?php esc_html_e( 'Lock it in with photos', 'epif' ); ?></a></div>
		</form>
	</div>
</section>

<section id="quote" class="epif-band epif-band--tan" aria-labelledby="epif-quote-title">
	<div class="epif-wrap epif-split">
		<div class="epif-stack">
			<p class="epif-eyebrow"><?php esc_html_e( 'Get a quote', 'epif' ); ?></p>
			<h2 id="epif-quote-title"><?php esc_html_e( "Send photos. We'll send a price.", 'epif' ); ?></h2>
			<ol class="epif-steps">
				<li><strong><?php esc_html_e( 'Tell us what needs to go', 'epif' ); ?></strong><?php esc_html_e( 'One wide shot of each room or pile, plus close-ups of big items.', 'epif' ); ?></li>
				<li><strong><?php esc_html_e( 'Get a price', 'epif' ); ?></strong><?php esc_html_e( 'By text or email. No obligation.', 'epif' ); ?></li>
				<li><strong><?php esc_html_e( 'We haul it, then sort it', 'epif' ); ?></strong><?php esc_html_e( 'You get a diversion receipt afterward.', 'epif' ); ?></li>
			</ol>
		</div>
		<div class="epif-card">[epif_lead_form services="Junk removal & cleanouts|Barn Revitalization|Consignment or buyout|Not sure" button="Send for a quote"]</div>
	</div>
</section>

<section id="impact" class="epif-band epif-band--navy" aria-labelledby="epif-impact-title">
	<div class="epif-wrap">
		<div class="epif-head"><div><p class="epif-eyebrow"><?php esc_html_e( 'Where it goes', 'epif' ); ?></p><h2 id="epif-impact-title"><?php esc_html_e( 'Enjoy the past. Improve the future.', 'epif' ); ?></h2></div><p class="epif-lead"><?php esc_html_e( "We sort every load in this order, the landfill only gets what's left, and you get a diversion receipt showing where it all went.", 'epif' ); ?></p></div>
		<ol class="epif-ladder">
			<li><span>01</span><h3><?php esc_html_e( 'Recirculate', 'epif' ); ?></h3><p><?php esc_html_e( 'Good pieces are listed on epifservices.shop and shipped nationwide.', 'epif' ); ?></p></li>
			<li><span>02</span><h3><?php esc_html_e( 'Donate', 'epif' ); ?></h3><p><?php esc_html_e( 'Usable goods go to local donation partners.', 'epif' ); ?></p></li>
			<li><span>03</span><h3><?php esc_html_e( 'Recycle', 'epif' ); ?></h3><p><?php esc_html_e( 'Metal, electronics and cardboard go to recyclers.', 'epif' ); ?></p></li>
			<li class="is-last"><span>04</span><h3><?php esc_html_e( 'Landfill, last', 'epif' ); ?></h3><p><?php esc_html_e( "Only what can't be reused or recycled.", 'epif' ); ?></p></li>
		</ol>
	</div>
</section>

<section class="epif-band" aria-labelledby="epif-tokens-title">
	<div class="epif-wrap epif-tokens">
		<div><p class="epif-eyebrow"><?php esc_html_e( 'EPIF Tokens', 'epif' ); ?></p><h2 id="epif-tokens-title"><?php esc_html_e( 'Clear out. Earn it back.', 'epif' ); ?></h2><a class="epif-btn-outline" href="https://epifservices.shop"><?php esc_html_e( 'How tokens work', 'epif' ); ?> ↗</a></div>
		<ul><li><b>+150</b><?php esc_html_e( 'when you book a clear-out', 'epif' ); ?></li><li><b>+40</b><?php esc_html_e( 'when an item from your clear-out sells', 'epif' ); ?></li><li><b>+10</b><?php esc_html_e( 'when you buy secondhand in the shop', 'epif' ); ?></li></ul>
		<p><strong><?php esc_html_e( '100 tokens = $2.50 off in the shop.', 'epif' ); ?></strong></p>
	</div>
</section>

<section id="partners" class="epif-band epif-band--light" aria-labelledby="epif-partners-title">
	<div class="epif-wrap epif-split">
		<div class="epif-stack">
			<p class="epif-eyebrow"><?php esc_html_e( 'Partners', 'epif' ); ?></p>
			<h2 id="epif-partners-title"><?php esc_html_e( 'Partner with EPIF', 'epif' ); ?></h2>
			<p class="epif-lead"><?php esc_html_e( 'Property managers and realtors, contractors and builders, inventory and fulfillment (consignment or buyout), nonprofits, government and community groups, or something else. Pick your type in the form.', 'epif' ); ?></p>
		</div>
		<div class="epif-card" data-epif-partner-form>[epif_lead_form services="Property management & realtors|Contractors & builders|Inventory & fulfillment|Nonprofits, government & community|Other" button="Send to EPIF"]</div>
	</div>
</section>

<section id="listing-tool" class="epif-band" aria-labelledby="epif-lt-title">
	<div class="epif-wrap epif-split">
		<div class="epif-stack">
			<p class="epif-eyebrow"><?php esc_html_e( 'Coming soon · Listing tool · Pre-order interest', 'epif' ); ?></p>
			<h2 id="epif-lt-title"><?php esc_html_e( 'A listing tool for people who list a lot', 'epif' ); ?></h2>
			<p class="epif-lead"><?php esc_html_e( 'Built for high-volume eBay sellers, cross-platform resellers, thrift, consignment and estate sellers, and haulers and liquidators.', 'epif' ); ?></p>
			<p class="epif-note"><strong><?php esc_html_e( 'This is a pre-order interest form, not a purchase.', 'epif' ); ?></strong> <?php esc_html_e( "No payment, no card. We'll email you when pre-orders open.", 'epif' ); ?></p>
		</div>
		<div class="epif-card">[epif_newsletter_form button="Register pre-order interest"]</div>
	</div>
</section>

<section id="faq" class="epif-band epif-band--light" aria-labelledby="epif-faq-title">
	<div class="epif-wrap epif-split">
		<div><p class="epif-eyebrow"><?php esc_html_e( 'FAQ', 'epif' ); ?></p><h2 id="epif-faq-title"><?php esc_html_e( 'Questions we hear a lot', 'epif' ); ?></h2></div>
		<div class="epif-faq">
			<?php foreach ( $epif_faq as $epif_i => $epif_qa ) : ?>
				<details <?php echo 0 === $epif_i ? 'open' : ''; ?>><summary><?php echo esc_html( $epif_qa[0] ); ?></summary><p><?php echo esc_html( $epif_qa[1] ); ?></p></details>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<script type="application/ld+json"><?php echo wp_json_encode( array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map( static function ( $qa ) { return array( '@type' => 'Question', 'name' => $qa[0], 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $qa[1] ) ); }, $epif_faq ) ) ); ?></script>
<!-- /wp:html -->
