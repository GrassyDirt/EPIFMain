# EPIF Marketplace

The EPIF landing page and site chrome, packaged as a plugin so they survive
theme changes.

## What it provides

- **Fraunces** (SIL OFL) self-hosted in `assets/fonts/`, so the pages make no
  requests to Google Fonts (the Privacy Policy says the site has no third-party
  requests).

- **`assets/epif.css`** — the whole design system, scoped under `.epif` so it
  can't leak into the active theme. Brand colours, type and shape live in the
  `:root` block at the top; change them there and everything follows.
- **Four button block styles** — pick them from the editor's Styles panel on
  any core Button. Colour carries meaning: orange = auction/bid,
  blue = buy, green = tokens, outline = secondary.
- **`assets/epif-timers.js`** — counts down every `.epif-timer` from whatever
  the element already says, so timers can be edited in the editor as
  `21:44`, `1h 12m` or `3d 4h`.
- **Three patterns**, all under Patterns → EPIF:
  - *EPIF marketplace landing page* — hero, ending-soon auctions, buy it now,
    how it works, tokens and growth tiers, the four ways to work with us, FAQ,
    closing CTA.
  - *EPIF site header* — sticky bar with the mark, nav, search, token chip and
    the clear-out CTA. Also offered directly in the Header template-part
    inserter.
  - *EPIF site footer* — brand blurb with the diversion figure, three link
    columns, contact line, legal links. Offered in the Footer inserter.

## Setup

1. Deploy the repo (`.cpanel.yml` copies this folder to
   `wp-content/plugins/epif-marketplace/` on the server).
2. Activate **EPIF Marketplace** in Plugins.
3. Create a page, open the inserter, go to **Patterns → EPIF**, and insert
   *EPIF marketplace landing page*.
4. Settings → Reading → set that page as the static front page.
5. Appearance → Editor → Patterns → **Header**: delete the theme's contents and
   insert *EPIF site header*. Repeat for **Footer** with *EPIF site footer*.
   (On a classic theme, put them at the top and bottom of the page instead.)

## Before this goes in front of customers

The **FAQ answers** in `patterns/landing-page.php` (`$epif_faq`) are drafts, not
facts, and need a pass from someone who knows the business. Pricing, the
won't-take list, the pickup window, the seven-day damage window and the
bid-extension rule are all plausible defaults rather than EPIF policy.

Service area is Westminster, MD (HQ) serving Carroll County and the Baltimore
area. The footer reads the contact email from **Settings → EPIF Business Info**
and links Terms and Privacy to the published legal pages (both from EPIF Core);
the email is hidden until one is saved there.

Item names, prices, bid counts and the `96 tons` / `248 items` figures are
placeholders too.

## Wiring up real data

Everything currently points at `#` with placeholder imagery. When WooCommerce
(or an auction plugin) is in:

- Replace each card grid with a Query Loop using the `.epif-card` classes.
- Point the hero and header search forms at the products archive.
- Give each timer a real end time: `<p class="epif-timer"
  data-epif-end="2026-09-01T18:00:00Z">1h 12m</p>` — the attribute wins over
  the text content.
- Swap `assets/item-placeholder.svg` for real featured images.
- Replace the header's static `1,240 tokens` chip with a shortcode reading the
  logged-in member's balance, or delete that HTML block.
- Point the nav and footer links at real pages. The header's *How it works* and
  *Work with us* items are `#how-it-works` and `#work-with-us` anchors on the
  landing page until those pages exist.

## Local preview

`tools/render-preview.php` at the repo root renders header + landing page +
footer into a single standalone HTML file, outside WordPress, for eyeballing
changes. Run it from the repo root:

```
php tools/render-preview.php > full-preview.html
```

It stubs the handful of WordPress functions the patterns call and stands in a
static form for the dynamic `core/search` block. It is a development aid only —
nothing in it ships to the server.
