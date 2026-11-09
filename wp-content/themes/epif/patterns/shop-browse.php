<?php
/**
 * Title: EPIF item browsing
 * Slug: epif/shop-browse
 * Categories: epif
 * Inserter: no
 * Description: Shop, category, tag and product search layout: header band with search, listing tabs and categories; filter sidebar; result toolbar; item grid with pagination. The EPIF blocks come from the EPIF Marketplace plugin.
 *
 * @package EPIF
 */

?>
<!-- wp:group {"align":"full","className":"epif epif-section epif-section--paper epif-shop__top","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull epif epif-section epif-section--paper epif-shop__top">
<!-- wp:group {"className":"epif-inner","layout":{"type":"default"}} -->
<div class="wp-block-group epif-inner">
<!-- wp:epif/shop-header /-->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"epif epif-section epif-section--white epif-shop","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull epif epif-section epif-section--white epif-shop">
<!-- wp:group {"className":"epif-inner epif-shop__body","layout":{"type":"default"}} -->
<div class="wp-block-group epif-inner epif-shop__body">
<!-- wp:group {"tagName":"aside","className":"epif-shop__sidebar","layout":{"type":"default"}} -->
<aside class="wp-block-group epif-shop__sidebar">
<!-- wp:epif/shop-filters /-->
</aside>
<!-- /wp:group -->
<!-- wp:group {"className":"epif-shop__results","layout":{"type":"default"}} -->
<div class="wp-block-group epif-shop__results">
<!-- wp:epif/shop-toolbar /-->
<!-- wp:epif/product-loop /-->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
