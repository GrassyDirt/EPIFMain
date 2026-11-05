<?php
/**
 * EPIF marketplace landing page pattern.
 *
 * Emitted as Gutenberg block markup so every heading, paragraph and button is
 * editable in the editor. The genuinely bespoke bits — the stat strip, chip
 * row, tier ladder and earn list — are HTML blocks, since no core block
 * produces that shape.
 *
 * @package epif
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$epif_placeholder = esc_url( EPIF_URL . 'assets/item-placeholder.svg' );

/**
 * Auctions in the "ending soon" band.
 * timer values are starting points — the front-end script ticks them down.
 */
$epif_auctions = array(
	array(
		'title'  => 'Pyrex mixing bowl set, 4 pc',
		'timer'  => '21:44',
		'price'  => '$31',
		'meta'   => '14 bids',
		'bid'    => 'Bid $33',
		'tokens' => '+25 tokens',
	),
	array(
		'title'  => 'Impact wrench, corded',
		'timer'  => '38:02',
		'price'  => '$47',
		'meta'   => '6 bids',
		'bid'    => 'Bid $50',
		'tokens' => '',
	),
	array(
		'title'  => 'Alternator, 2012 Honda Civic',
		'timer'  => '1h 12m',
		'price'  => '$42',
		'meta'   => '9 bids &middot; reserve met',
		'bid'    => 'Bid $45',
		'tokens' => '+30 tokens',
	),
	array(
		'title'  => 'Trinitron CRT, 27 in',
		'timer'  => '4h 03m',
		'price'  => '$18',
		'meta'   => '3 bids',
		'bid'    => 'Bid $20',
		'tokens' => '',
	),
);

/**
 * Fixed-price listings.
 */
$epif_products = array(
	array( 'title' => 'Vintage Levi&#039;s 501, 34x32', 'price' => '$65', 'meta' => 'Ships in 2 days',        'tokens' => '+40 tokens' ),
	array( 'title' => 'Lego bulk bin, 4.2 lb',          'price' => '$28', 'meta' => 'Warehouse pickup',      'tokens' => '' ),
	array( 'title' => 'Kids winter coats, lot of 6',    'price' => '$34', 'meta' => 'Ships in 2 days',       'tokens' => '+15 tokens' ),
	array( 'title' => 'Cast iron skillet, 10 in',       'price' => '$22', 'meta' => 'Seasoned, ready to use', 'tokens' => '' ),
	array( 'title' => 'Mid-century side table, teak',   'price' => '$95', 'meta' => 'Local pickup only',     'tokens' => '+50 tokens' ),
	array( 'title' => 'Craftsman socket set, 42 pc',    'price' => '$39', 'meta' => 'Complete, tested',      'tokens' => '' ),
	array( 'title' => 'Vinyl LPs, jazz lot of 20',      'price' => '$48', 'meta' => 'Ships in 3 days',       'tokens' => '+20 tokens' ),
);

/**
 * FAQ.
 *
 * IMPORTANT: these answers are drafts, not policy. They describe how a
 * business like this usually works — check every one against what EPIF
 * actually does (especially the pricing, the exclusions list and the returns
 * window) before this page goes live.
 */
$epif_faq = array(
	array(
		'q' => 'What does a clear-out cost?',
		'a' => 'Nothing, in the normal case — we&#039;re paid out of what sells. If we look at your photos and think the load is mostly disposal rather than resale, we&#039;ll tell you that up front and quote a haul-away fee instead of quietly turning up and charging you later.',
	),
	array(
		'q' => 'What will you take?',
		'a' => 'Furniture, tools, auto parts, electronics, clothing, kitchen and household goods, toys, books, records, collectibles, and most things in a garage. We&#039;d rather look at a full basement than have you pre-sort it — people throw out the valuable things and keep the worthless ones more often than you&#039;d think.',
	),
	array(
		'q' => 'What won&#039;t you take?',
		'a' => 'Hazardous material: paint, solvents, propane, oil, batteries in bulk, anything with a flammable or corrosive label. Also no mattresses in most cases, no appliances containing refrigerant unless arranged in advance, and nothing showing signs of pests or mould.',
	),
	array(
		'q' => 'How soon can you come?',
		'a' => 'Usually within a week across Carroll County and the Baltimore area, from our base in Westminster, MD. Send photos first — a few phone snaps of each room is enough for us to size the truck and the crew, which is what actually determines the date.',
	),
	array(
		'q' => 'Are bids binding?',
		'a' => 'Yes. Placing a bid is a commitment to buy at that price if you win, and you&#039;re only charged if you win. Auctions extend by two minutes when a bid lands in the final two, so nothing is decided by whoever has the faster connection.',
	),
	array(
		'q' => 'How do tokens work?',
		'a' => 'You earn them for keeping objects in circulation — handing us a clear-out, having one of your items sell, buying secondhand here instead of new. 100 tokens is $2.50 off at checkout, and they don&#039;t expire.',
	),
	array(
		'q' => 'Can I see something before I bid?',
		'a' => 'Everything is photographed from several angles and tested where testing is possible, and the description says plainly what&#039;s wrong with it. You&#039;re also welcome at the warehouse during pickup hours to look at anything currently listed.',
	),
	array(
		'q' => 'What if it arrives broken?',
		'a' => 'If an item shows up damaged in transit, or materially different from its description, tell us within seven days of delivery and we&#039;ll refund it. Secondhand goods sold as-is and described accurately aren&#039;t returnable just for buyer&#039;s remorse — that&#039;s the trade-off that keeps prices where they are.',
	),
);
?>
<!-- wp:group {"align":"full","className":"epif epif-hero","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull epif epif-hero">
<!-- wp:group {"className":"epif-inner","layout":{"type":"default"}} -->
<div class="wp-block-group epif-inner">

<!-- wp:paragraph {"className":"epif-eyebrow"} -->
<p class="epif-eyebrow">Enjoy the past &middot; Improve the future</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">Someone else's clear-out is your next good find.</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"epif-lede"} -->
<p class="epif-lede">EPIF recovers whole households, garages and estates before they hit the dumpster, then puts everything worth keeping back into circulation — at auction or straight up for sale. Bid on it, buy it, or hand us your own clear-out and earn tokens for what we save.</p>
<!-- /wp:paragraph -->

<!-- wp:search {"label":"Search","showLabel":false,"placeholder":"Search recovered parts, clothing, tools, home goods…","buttonText":"Search","className":"epif-searchblock"} /-->

<!-- wp:buttons {"className":"epif-buttons"} -->
<div class="wp-block-buttons epif-buttons">
<!-- wp:button {"className":"is-style-epif-bid"} -->
<div class="wp-block-button is-style-epif-bid"><a class="wp-block-button__link wp-element-button" href="#">Browse live auctions</a></div>
<!-- /wp:button -->
<!-- wp:button {"className":"is-style-epif-ghost"} -->
<div class="wp-block-button is-style-epif-ghost"><a class="wp-block-button__link wp-element-button" href="#">Book a free clear-out</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

<!-- wp:html -->
<div class="epif-stats">
	<div class="epif-stat"><b>248</b><span>items listed right now</span></div>
	<div class="epif-stat"><b>14</b><span>auctions closing today</span></div>
	<div class="epif-stat epif-stat--green"><b>96 tons</b><span>kept out of landfill</span></div>
	<div class="epif-stat"><b>Westminster, MD</b><span>pickup across the Baltimore area &amp; nationwide shipping</span></div>
</div>
<!-- /wp:html -->

</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->


<!-- wp:group {"align":"full","className":"epif epif-section epif-band","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull epif epif-section epif-band">
<!-- wp:group {"className":"epif-inner","layout":{"type":"default"}} -->
<div class="wp-block-group epif-inner">

<!-- wp:group {"className":"epif-sectionhead","layout":{"type":"default"}} -->
<div class="wp-block-group epif-sectionhead">
<!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group">
<!-- wp:paragraph {"className":"epif-eyebrow"} -->
<p class="epif-eyebrow">Going, going…</p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2 class="wp-block-heading">Ending in the next few hours</h2>
<!-- /wp:heading -->
</div>
<!-- /wp:group -->
<!-- wp:html -->
<div class="epif-sectionhead__aside">
	<span class="epif-tag epif-tag--live">14 live auctions</span>
	<a class="epif-link" href="#">See every auction →</a>
</div>
<!-- /wp:html -->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"epif-grid epif-grid--cards","layout":{"type":"default"}} -->
<div class="wp-block-group epif-grid epif-grid--cards">
<?php foreach ( $epif_auctions as $epif_item ) : ?>

<!-- wp:group {"className":"epif-card","layout":{"type":"default"}} -->
<div class="wp-block-group epif-card">
<!-- wp:group {"className":"epif-card__media","layout":{"type":"default"}} -->
<div class="wp-block-group epif-card__media">
<!-- wp:image -->
<figure class="wp-block-image"><img src="<?php echo $epif_placeholder; ?>" alt="<?php echo esc_attr( html_entity_decode( $epif_item['title'] ) ); ?>"/></figure>
<!-- /wp:image -->
<!-- wp:group {"className":"epif-card__badges","layout":{"type":"default"}} -->
<div class="wp-block-group epif-card__badges">
<!-- wp:paragraph {"className":"epif-tag epif-tag--live"} -->
<p class="epif-tag epif-tag--live">Live</p>
<!-- /wp:paragraph -->
<?php if ( $epif_item['tokens'] ) : ?>
<!-- wp:paragraph {"className":"epif-tag epif-tag--token"} -->
<p class="epif-tag epif-tag--token"><?php echo $epif_item['tokens']; ?></p>
<!-- /wp:paragraph -->
<?php endif; ?>
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
<!-- wp:group {"className":"epif-card__body","layout":{"type":"default"}} -->
<div class="wp-block-group epif-card__body">
<!-- wp:paragraph {"className":"epif-timer"} -->
<p class="epif-timer"><?php echo $epif_item['timer']; ?></p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":3,"className":"epif-card__title"} -->
<h3 class="wp-block-heading epif-card__title"><a href="#"><?php echo $epif_item['title']; ?></a></h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"className":"epif-card__meta"} -->
<p class="epif-card__meta"><span class="epif-price epif-price--live"><?php echo $epif_item['price']; ?></span> <span><?php echo $epif_item['meta']; ?></span></p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"className":"epif-card__foot"} -->
<div class="wp-block-buttons epif-card__foot">
<!-- wp:button {"className":"is-style-epif-bid"} -->
<div class="wp-block-button is-style-epif-bid"><a class="wp-block-button__link wp-element-button" href="#"><?php echo $epif_item['bid']; ?></a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
<?php endforeach; ?>
</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->


<!-- wp:group {"align":"full","className":"epif epif-section epif-section--white","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull epif epif-section epif-section--white">
<!-- wp:group {"className":"epif-inner","layout":{"type":"default"}} -->
<div class="wp-block-group epif-inner">

<!-- wp:group {"className":"epif-sectionhead","layout":{"type":"default"}} -->
<div class="wp-block-group epif-sectionhead">
<!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group">
<!-- wp:paragraph {"className":"epif-eyebrow"} -->
<p class="epif-eyebrow">No bidding required</p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2 class="wp-block-heading">Buy it now</h2>
<!-- /wp:heading -->
</div>
<!-- /wp:group -->
<!-- wp:html -->
<div class="epif-sectionhead__aside"><a class="epif-link" href="#">Shop everything →</a></div>
<!-- /wp:html -->
</div>
<!-- /wp:group -->

<!-- wp:html -->
<div class="epif-tags">
	<a class="epif-tag epif-tag--on" href="#">All</a>
	<a class="epif-tag" href="#">Auto parts</a>
	<a class="epif-tag" href="#">Tools</a>
	<a class="epif-tag" href="#">Electronics</a>
	<a class="epif-tag" href="#">Clothing</a>
	<a class="epif-tag" href="#">Home &amp; kitchen</a>
	<a class="epif-tag" href="#">Furniture</a>
	<a class="epif-tag" href="#">Toys &amp; games</a>
	<a class="epif-tag" href="#">Music &amp; media</a>
</div>
<!-- /wp:html -->

<!-- wp:group {"className":"epif-grid epif-grid--cards","layout":{"type":"default"}} -->
<div class="wp-block-group epif-grid epif-grid--cards">
<?php foreach ( $epif_products as $epif_item ) : ?>

<!-- wp:group {"className":"epif-card","layout":{"type":"default"}} -->
<div class="wp-block-group epif-card">
<!-- wp:group {"className":"epif-card__media","layout":{"type":"default"}} -->
<div class="wp-block-group epif-card__media">
<!-- wp:image -->
<figure class="wp-block-image"><img src="<?php echo $epif_placeholder; ?>" alt="<?php echo esc_attr( html_entity_decode( $epif_item['title'] ) ); ?>"/></figure>
<!-- /wp:image -->
<!-- wp:group {"className":"epif-card__badges","layout":{"type":"default"}} -->
<div class="wp-block-group epif-card__badges">
<!-- wp:paragraph {"className":"epif-tag epif-tag--buy"} -->
<p class="epif-tag epif-tag--buy">Buy now</p>
<!-- /wp:paragraph -->
<?php if ( $epif_item['tokens'] ) : ?>
<!-- wp:paragraph {"className":"epif-tag epif-tag--token"} -->
<p class="epif-tag epif-tag--token"><?php echo $epif_item['tokens']; ?></p>
<!-- /wp:paragraph -->
<?php endif; ?>
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
<!-- wp:group {"className":"epif-card__body","layout":{"type":"default"}} -->
<div class="wp-block-group epif-card__body">
<!-- wp:heading {"level":3,"className":"epif-card__title"} -->
<h3 class="wp-block-heading epif-card__title"><a href="#"><?php echo $epif_item['title']; ?></a></h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"className":"epif-card__meta"} -->
<p class="epif-card__meta"><span class="epif-price"><?php echo $epif_item['price']; ?></span> <span><?php echo $epif_item['meta']; ?></span></p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"className":"epif-card__foot"} -->
<div class="wp-block-buttons epif-card__foot">
<!-- wp:button {"className":"is-style-epif-primary"} -->
<div class="wp-block-button is-style-epif-primary"><a class="wp-block-button__link wp-element-button" href="#">Buy it now</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
<?php endforeach; ?>

<!-- wp:html -->
<a class="epif-card epif-card--more" href="#">
	<b>232 more items</b>
	<span>Browse the full marketplace →</span>
</a>
<!-- /wp:html -->
</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->


<!-- wp:group {"align":"full","anchor":"how-it-works","className":"epif epif-section epif-section--paper","layout":{"type":"default"}} -->
<div id="how-it-works" class="wp-block-group alignfull epif epif-section epif-section--paper">
<!-- wp:group {"className":"epif-inner","layout":{"type":"default"}} -->
<div class="wp-block-group epif-inner">

<!-- wp:paragraph {"className":"epif-eyebrow"} -->
<p class="epif-eyebrow">How EPIF works</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">We do the hauling, sorting and listing. You just show up.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"epif-lede"} -->
<p class="epif-lede">Nothing on this site was listed by a stranger in a hurry. Every item came through our warehouse, got looked at by a person, and was photographed and described honestly before it went live.</p>
<!-- /wp:paragraph -->

<!-- wp:spacer {"height":"32px"} -->
<div style="height:32px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:group {"className":"epif-grid epif-grid--3","layout":{"type":"default"}} -->
<div class="wp-block-group epif-grid epif-grid--3">

<!-- wp:group {"className":"epif-step","layout":{"type":"default"}} -->
<div class="wp-block-group epif-step">
<!-- wp:paragraph {"className":"epif-step__num"} -->
<p class="epif-step__num">1</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">We recover it</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>A basement, a garage, an estate, a closing storefront. We clear the whole thing in one visit and haul it back to the warehouse — free for the household, paid for by resale.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"epif-step","layout":{"type":"default"}} -->
<div class="wp-block-group epif-step">
<!-- wp:paragraph {"className":"epif-step__num"} -->
<p class="epif-step__num">2</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">We sort and appraise it</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Everything gets tested, graded and photographed. Rare or in-demand pieces go to auction. Everyday goods get a fair fixed price. What's genuinely finished gets recycled properly.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"epif-step epif-step--accent","layout":{"type":"default"}} -->
<div class="wp-block-group epif-step epif-step--accent">
<!-- wp:paragraph {"className":"epif-step__num"} -->
<p class="epif-step__num">3</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">It finds a second owner</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>You bid or you buy. The original household earns tokens on every item that sells, and the weight we kept out of a landfill shows up on both your profiles.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->


<!-- wp:group {"align":"full","className":"epif epif-section epif-section--white","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull epif epif-section epif-section--white">
<!-- wp:group {"className":"epif-inner","layout":{"type":"default"}} -->
<div class="wp-block-group epif-inner">

<!-- wp:html -->
<div class="epif-panel">
	<div>
		<p class="epif-eyebrow">EPIF tokens</p>
		<h2>Get paid in something better than nothing.</h2>
		<p class="epif-lede">Every time you keep an object in circulation — by handing us a clear-out, or by buying secondhand instead of new — you earn tokens. Spend them at checkout: 100 tokens is $2.50 off.</p>
		<ul class="epif-tier">
			<li class="is-earned">Seed</li>
			<li class="is-earned">Sprout</li>
			<li class="is-earned">Sapling</li>
			<li>Tree</li>
			<li>Matured tree</li>
		</ul>
		<p class="epif-note">Your account grows a tree as you go. Every tier unlocks better redemption rates — and a new resident in the branches.</p>
	</div>

	<ul class="epif-earnlist">
		<li><span>Book a clear-out pickup</span> <b>+150</b></li>
		<li><span>An item from your clear-out sells</span> <b>+40</b></li>
		<li><span>Buy secondhand instead of new</span> <b>+10</b></li>
		<li><span>Donate a whole lot to consignment</span> <b>+200</b></li>
		<li><span>Refer a household that books</span> <b>+100</b></li>
	</ul>
</div>
<!-- /wp:html -->

<!-- wp:buttons {"className":"epif-buttons"} -->
<div class="wp-block-buttons epif-buttons">
<!-- wp:button {"className":"is-style-epif-token"} -->
<div class="wp-block-button is-style-epif-token"><a class="wp-block-button__link wp-element-button" href="#">Start earning tokens</a></div>
<!-- /wp:button -->
<!-- wp:button {"className":"is-style-epif-ghost"} -->
<div class="wp-block-button is-style-epif-ghost"><a class="wp-block-button__link wp-element-button" href="#">How tokens work</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->


<!-- wp:group {"align":"full","anchor":"work-with-us","className":"epif epif-section epif-section--paper","layout":{"type":"default"}} -->
<div id="work-with-us" class="wp-block-group alignfull epif epif-section epif-section--paper">
<!-- wp:group {"className":"epif-inner","layout":{"type":"default"}} -->
<div class="wp-block-group epif-inner">

<!-- wp:group {"className":"epif-sectionhead","layout":{"type":"default"}} -->
<div class="wp-block-group epif-sectionhead">
<!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group">
<!-- wp:paragraph {"className":"epif-eyebrow"} -->
<p class="epif-eyebrow">Work with us</p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2 class="wp-block-heading">Four ways in — pick the one that fits.</h2>
<!-- /wp:heading -->
</div>
<!-- /wp:group -->
<!-- wp:html -->
<div class="epif-sectionhead__aside"><a class="epif-link" href="#">Talk to a person →</a></div>
<!-- /wp:html -->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"epif-grid","layout":{"type":"default"}} -->
<div class="wp-block-group epif-grid">

<!-- wp:group {"className":"epif-path epif-path--featured","layout":{"type":"default"}} -->
<div class="wp-block-group epif-path epif-path--featured">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><a href="#">Have us clear it out</a></h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>We haul, sort, photograph and appraise it. Anything with life left gets sold here — you get tokens and the diversion stats on your profile.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"epif-tag epif-tag--token"} -->
<p class="epif-tag epif-tag--token">Tokens + impact stats</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"epif-path","layout":{"type":"default"}} -->
<div class="wp-block-group epif-path">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><a href="#">Have us list your inventory</a></h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Already have stock but no time to list it? We photograph, write and list it on the EPIF marketplace and eBay for a flat fee.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"epif-tag"} -->
<p class="epif-tag">Flat fee</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"epif-path","layout":{"type":"default"}} -->
<div class="wp-block-group epif-path">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><a href="#">Source inventory from us</a></h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Buy recovered goods by the pallet or the lot for your own shop, booth or resale channel. Sorted, graded and ready to move.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"epif-tag"} -->
<p class="epif-tag">Wholesale</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"epif-path","layout":{"type":"default"}} -->
<div class="wp-block-group epif-path">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><a href="#">Partner as a fulfillment center</a></h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Have warehouse space sitting idle? Hold and ship network inventory out of your building and earn on every order.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"epif-tag"} -->
<p class="epif-tag">Partner</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->


<!-- wp:group {"align":"full","anchor":"faq","className":"epif epif-section epif-section--white","layout":{"type":"default"}} -->
<div id="faq" class="wp-block-group alignfull epif epif-section epif-section--white">
<!-- wp:group {"className":"epif-inner","layout":{"type":"default"}} -->
<div class="wp-block-group epif-inner">

<!-- wp:paragraph {"className":"epif-eyebrow"} -->
<p class="epif-eyebrow">Before you call</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">The questions we get every week.</h2>
<!-- /wp:heading -->

<!-- wp:group {"className":"epif-faq","layout":{"type":"default"}} -->
<div class="wp-block-group epif-faq">
<?php foreach ( $epif_faq as $epif_i => $epif_qa ) : ?>

<!-- wp:details <?php echo 0 === $epif_i ? '{"showContent":true}' : '{}'; ?> -->
<details class="wp-block-details"<?php echo 0 === $epif_i ? ' open' : ''; ?>><summary><?php echo $epif_qa['q']; ?></summary>
<!-- wp:paragraph -->
<p><?php echo $epif_qa['a']; ?></p>
<!-- /wp:paragraph -->
</details>
<!-- /wp:details -->
<?php endforeach; ?>
</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->


<!-- wp:group {"align":"full","className":"epif epif-section epif-section--ink epif-cta","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull epif epif-section epif-section--ink epif-cta">
<!-- wp:group {"className":"epif-inner","layout":{"type":"default"}} -->
<div class="wp-block-group epif-inner">

<!-- wp:heading -->
<h2 class="wp-block-heading">Got a house, a garage or a unit to empty?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"epif-lede"} -->
<p class="epif-lede">Send us four photos and a zip code. We'll come back with a pickup date, a token estimate, and an honest read on what's actually worth selling. No charge, no obligation, and nothing goes to a dumpster if it doesn't have to.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"epif-buttons"} -->
<div class="wp-block-buttons epif-buttons">
<!-- wp:button {"className":"is-style-epif-token"} -->
<div class="wp-block-button is-style-epif-token"><a class="wp-block-button__link wp-element-button" href="#">Request a free pickup</a></div>
<!-- /wp:button -->
<!-- wp:button {"className":"is-style-epif-ghost"} -->
<div class="wp-block-button is-style-epif-ghost"><a class="wp-block-button__link wp-element-button" href="#">See what we take</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
