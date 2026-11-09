# EPIF Marketplace

The EPIF storefront, packaged as a plugin so it survives theme changes: the
design system, the landing page, item browsing with filters, the product page,
the site header and footer, listing details on products, and an EPIF skin for
WooCommerce. The EPIF theme (`wp-content/themes/epif`) supplies the page
templates that put these pieces together.

Everything on the store pages renders on the server from real WooCommerce data,
so the site never shows items, counts or figures that aren't true. With
WooCommerce off, the store sections show a short "shop isn't open yet" note
instead.

## Pages

| Page | Template (EPIF theme) | What's on it |
| --- | --- | --- |
| Home | `front-page.html` | The landing page pattern: hero with product search and live stats, live auctions ending soonest, buy-it-now grid with category chips, how it works, tokens, four ways to work with EPIF, the clear-out request form (`#book`), FAQ, closing CTA. |
| Shop, category, tag, attribute, product search | `archive-product.html`, `taxonomy-product_cat.html`, `taxonomy-product_tag.html`, `taxonomy-product_attribute.html`, `product-search-results.html` (all use the `epif/shop-browse` pattern) | Breadcrumb, title, search, *Everything / Live auctions / Buy it now* tabs, category chips, filter sidebar, result count with removable filter pills and sort, item grid, pagination, empty states. |
| Product | `single-product.html` | Breadcrumb and WooCommerce's product page (gallery, price, add to cart, tabs, related items) with the EPIF countdown and listing tags added under the title. |
| Cart, checkout, My Account, legal and other pages | `page-cart.html`, `page-checkout.html`, `page.html` | Title and page content in the EPIF shell. |

The header and footer template parts use the *EPIF site header* and *EPIF site
footer* patterns. Coming-soon and early-access keep their own simpler footer.

## Browse filters

Filters are plain links and GET forms, so every filtered view has its own URL
and works without JavaScript:

| Parameter | Values |
| --- | --- |
| `listing` | `auction` (live auctions) or `buy` (everything that isn't an auction) |
| `condition` | comma-separated: `new`, `like-new`, `good`, `fair`, `parts` |
| `fulfillment` | `ship` or `pickup` (items marked "both" match either) |
| `instock` | `1` hides sold items |
| `min_price`, `max_price`, `orderby`, `product_cat` | WooCommerce's own; `orderby=ending` sorts auctions by end time |

## Listing details on each product

**Products → Edit → Product data → General → EPIF listing**:

- **Condition** — New, Like new, Good, Fair, For parts / repair. Shown on cards
  and the product page; used by the Condition filter.
- **Shipping / pickup** — Ships nationwide, Pickup in Westminster, MD, or both.
  Used by the "Getting it" filter.
- **Tokens earned** — shows a green "+N tokens" badge.
- **Auction ends** — set a date and time (site timezone) to list the item as a
  live auction: orange "Live" tag, countdown, "Starting bid" label, listed
  under *Live auctions*.

### Auctions and bidding

This plugin displays auctions; it does not take bids. While an auction is live
the item can't be added to the cart (otherwise anyone could buy it at the
starting price). The product page instead says online bidding isn't open yet,
with an *Ask about this item* button that opens the clear-out request form
(`#book`) with "I'd like to bid on: …" already filled in. Bids arrive under
**Leads**.

To take bids online, add an auction plugin, then:

- return its end time from the `epif_auction_end` filter so cards and
  countdowns use it, and
- `add_filter( 'epif_auction_blocks_purchase', '__return_false' );` if the
  plugin handles its own checkout.

## Settings → EPIF Store

- **Pickup location** — default "Westminster, MD".
- **Service area** — default "Carroll County and the Baltimore area".
- **Kept out of landfill** — e.g. "12 tons". Empty by default, which hides the
  stat on the landing page and the footer badge. Only enter a real figure.

## Blocks

All under the **EPIF** names in the block inserter. They show a live preview in
the editor. Each one is also a shortcode, e.g. `[epif_products type="buy" limit="8"]`.

| Block | Shortcode | Use |
| --- | --- | --- |
| EPIF item grid (`epif/products`) | `[epif_products]` | `type` all/auction/buy/sale, `limit`, `category` (slugs), `columns`, `more` (adds a "N more items" tile). |
| EPIF category chips | `[epif_category_chips]` | Top-level product categories. |
| EPIF store stats | `[epif_store_stats]` | Items listed, auctions closing today, landfill figure, location. |
| EPIF live auction count | `[epif_auction_summary]` | "N live auctions · See every auction →". |
| EPIF shop header / filters / toolbar / browse grid | `[epif_shop_header]` … | The browse page pieces. |
| EPIF product page | `[epif_single_product]` | Product page body. |
| EPIF cart link | `[epif_header_cart]` | Cart link with count; stays current after AJAX add to cart. |
| EPIF clear-out request form | `[epif_booking_form]` | EPIF Core's lead form with the four services; saves to **Leads**. |

## Design system

- **`assets/epif.css`** — the whole design system, scoped under `.epif`. Brand
  colours, type and shape live in the `:root` block at the top. Colour carries
  meaning: orange = auction, blue = buy, green = tokens and impact.
  Text-on-colour combinations meet WCAG AA: orange buttons use ink text, buy
  buttons use the darker blue, and orange text uses `--epif-orange-text`.
- **`assets/epif-woocommerce.css`** — WooCommerce in the EPIF brand (classic
  templates and the block cart/checkout), for any theme. It is scoped to
  WooCommerce's own markup, because WooCommerce adds `.woocommerce` to
  `<body>` on shop pages.
- **Fraunces** (SIL OFL) self-hosted in `assets/fonts/` — no Google Fonts
  requests.
- **Button block styles** — EPIF Bid, Buy, Token and Outline, in the editor's
  Styles panel on any Button.
- **`assets/epif-timers.js`** — ticks every `.epif-timer` down to its
  `data-epif-end` time.

## Before this goes in front of customers

- The **FAQ answers** in `patterns/landing-page.php` (`$epif_faq`) are drafts:
  pricing, the won't-take list, the pickup window, the seven-day damage window,
  the bid-extension rule and the token values all need checking against real
  EPIF policy.
- The **tokens section** describes a programme (earning rates, 100 tokens =
  $2.50, tiers) that the site doesn't run yet. Tokens set on products only
  show as badges; nothing is credited or redeemed at checkout. Edit or remove
  that section until the programme exists.
- Fill in **Settings → EPIF Business Info** (contact email shows in the footer)
  and publish the legal pages (they appear in the footer once published).

## Local preview

`tools/render-preview.php` at the repo root renders header + landing page +
footer into one standalone HTML file, outside WordPress:

```
php tools/render-preview.php > full-preview.html
```

The live `epif/*` blocks show as labelled placeholders there; they need
WordPress and WooCommerce to render.
