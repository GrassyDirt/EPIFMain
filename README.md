# EPIFMain
epifservices.com — WordPress site.

## What's custom

| Path | Purpose |
| --- | --- |
| `wp-content/mu-plugins/epif-core.php` + `epif-core/` | Always-on site backend: coming-soon mode, newsletter signups (**Subscribers**), lead capture (**Leads**), **Settings → EPIF Business Info**, legal pages, security + performance defaults. |
| `wp-content/mu-plugins/epif-core/legal/` | Draft Privacy Policy, Terms of Use, Cookie Policy, Accessibility Statement. |
| `wp-content/themes/epif/` | Child theme of Twenty Twenty-Five: coming-soon page, site footer, **Landing Page** template, EPIF block patterns. |
| `wp-config.php` | EPIF-tuned settings. Secrets are loaded from `wp-config-local.php` (never committed). |

## Landing page (theme v1.2)

- **Home page:** `templates/front-page.html` renders `patterns/home.php`: location strip, hero with trust row, services, pricing table, instant estimate, quote form, where it goes, tokens, partners, listing tool pre-order interest, FAQ (with FAQPage JSON-LD). Set **Settings → Reading → Your homepage displays → A static page** (any page) or leave "latest posts"; `front-page.html` is used either way.
- **Prices:** edit `epif_prices()` in `wp-content/themes/epif/functions.php` (or the `epif_prices` filter). `null` shows "Ask" and keeps the item out of the instant estimate.
- **Towns and ZIPs:** `epif_places()` and `epif_zip_map()` in the same file. Rural towns show Barn Revitalization first; the others hide it.
- **Personalization:** `assets/site.js` swaps the headline, price column and services only after a visitor enters a ZIP (kept in their browser). Search engines always get the default all-areas page. The IP lookup (`geo.php` with GeoLite2) is not built yet.
- **Blog:** write posts in **Posts → Add New**. `templates/single.html` adds the category, author, date, featured image and a "Get a quote" band. In the editor, insert the **EPIF diversion receipt** pattern for job stories. Set **Settings → Permalinks** to `/blog/%postname%/` and create a "Blog" page as the posts page.
- **About Us:** create a page with slug `about` and choose the **About Us** template. Put the founding story and team in the page content.
- **Phone:** header, hero and sticky mobile bar use the phone from **Settings → EPIF Business Info**.

## Server setup

1. Copy `wp-config-local-sample.php` to `wp-config-local.php` — preferably one directory **above** the web root — and fill in the DB credentials and fresh salts from https://api.wordpress.org/secret-key/1.1/salt/.
   `wp-config.php` refuses to boot (HTTP 503) if this file is missing.
2. Dashboard → Appearance → Themes → activate **EPIF**.
3. **Settings → EPIF Business Info**: fill in the legal name, contact email, mailing address, governing law, and effective date. The footer, legal pages, and emails all read from here.
4. Configure **GoSMTP** so newsletter confirmation and lead emails are delivered. Send yourself a test signup.
5. **Pages**: review the legal drafts (a dashboard notice links to them), have them checked by a lawyer, then publish. Links appear in the footer once each page is published. Publish the Privacy Policy before promoting the signup form.
6. LiteSpeed Cache → **Purge All** after deploying.

## Coming-soon mode

`EPIF_COMING_SOON` (on by default in `wp-config.php`) shows visitors the "coming soon" page with the newsletter signup. Every other URL redirects to it, except the published legal pages.
Logged-in editors see the real site; preview the visitor view at `/?epif_preview=coming-soon`. The admin bar shows **Coming soon: ON** while it's active.

At launch: set `EPIF_COMING_SOON` to `false` (in `wp-config-local.php` or `wp-config.php`) and purge the cache.

## Newsletter

- Form: `[epif_newsletter_form button="Notify me"]` (already on the coming-soon page).
- Double opt-in: signups get a confirmation email and only count once they click it (`EPIF_NEWSLETTER_DOUBLE_OPTIN`). Each subscriber stores proof of consent (the wording they agreed to, when, and a hashed IP).
- Unconfirmed signups are deleted automatically after 30 days (the Privacy Policy says so).
- **Subscribers → Download CSV** exports confirmed subscribers to import into Mailchimp, Brevo, ConvertKit, etc. `EPIF_Newsletter::unsubscribe_url( $id )` gives a signed one-click unsubscribe link, and the `epif_subscriber_confirmed` / `epif_subscriber_unsubscribed` actions are there for syncing to an email platform.

## Legal pages

Created as **drafts** the first time an admin opens the dashboard (existing pages are never overwritten). They pull company details from EPIF Business Info through `[epif_info field="…"]`, so unfilled values show as highlighted `[placeholders]`.
They describe the site as built: no analytics or ad cookies, comments off, Akismet spam checks on the contact form, and double opt-in. **Update them before adding Google Analytics, the Meta Pixel, or any other tracking.** These are starting drafts, not legal advice.

## Landing page lead form

- Place anywhere with the shortcode: `[epif_lead_form services="Option A|Option B" button="Get a quote"]`
- Submissions go to `POST /wp-json/epif/v1/leads`, are stored under **Leads** in the dashboard, and emailed to the admin address (override with `EPIF_LEAD_NOTIFY_EMAIL`).
- UTM / gclid / fbclid parameters from the landing URL are saved with each lead. `gtag('event','generate_lead')` and `fbq('track','Lead')` fire on success when those tags are installed.
- Spam protection: nonce, honeypot, minimum fill time, per-IP rate limit (`EPIF_LEAD_RATE_LIMIT`, default 5/hour), and Akismet when it has an API key.
- Integrations (CRM, webhooks) can hook `do_action( 'epif_lead_created', $lead_id, $data )`.

## Settings (`wp-config.php`)

Production defaults: errors never displayed, dashboard file editor disabled, admin forced to HTTPS, automatic minor core updates, `utf8mb4`, 10 revisions per post, 256M/512M memory. Every value can be overridden per server in `wp-config-local.php`.
Set `EPIF_HSTS` to `true` once HTTPS works on every URL.

## Deploying (cPanel Git Version Control)

`.cpanel.yml` copies only what this repo manages into `/home/amngfszdlp/epifservices.com`:
`wp-content/mu-plugins/`, `wp-content/themes/epif/`, `wp-config.php`, and the `# BEGIN EPIF Security` block of `.htaccess`.
WordPress core, other plugins, uploads, caches and backups on the server are never touched; keep updating those from the dashboard.

- `wp-config.php` is only copied when `wp-config-local.php` already exists on the server, so a deploy can't take the site down.
- `.htaccess` is merged, not replaced (`deploy/merge-htaccess.php`): the EPIF block is updated, a WordPress block is added only if missing, and LiteSpeed/Loginizer blocks are kept. The previous file is saved as `.htaccess.epif-bak`.

One-time setup: in cPanel → **Git Version Control**, clone this repo into a folder **outside** the website, for example `/home/amngfszdlp/repositories/EPIFMain`, not into `epifservices.com` itself. Plugins change files in the live folder, and cPanel refuses to deploy a clone with uncommitted changes.
To deploy: **Manage → Pull or Deploy → Update from Remote**, then **Deploy HEAD Commit**.

## Server files

- `.htaccess` (root) is tracked in git. It holds WordPress's rewrite rules plus EPIF security rules: no directory listings; `.git`, `wp-config*.php`, logs and readme files blocked; no PHP execution in uploads; XML-RPC refused.
  LiteSpeed Cache and Loginizer add their own blocks when you save their settings. After a deploy that replaces `.htaccess`, re-save **Settings → Permalinks**, **LiteSpeed Cache** settings, and any Loginizer custom admin URL.
- If the site shows **500 Internal Server Error** right after adding `.htaccess`, the host doesn't allow `Options` overrides: delete the `Options -Indexes` line.

## Locked out? (404 on /wp-admin or the login page)

1. Go to `https://epifservices.com/wp-login.php`. If Loginizer's custom admin/login URL has lost its `.htaccess` rules, EPIF Core automatically switches back to the standard addresses and shows a dashboard notice.
2. Still 404? Make sure the root `.htaccess` from this repo is on the server, then purge the LiteSpeed cache (hosting panel → LiteSpeed → Flush All), because LiteSpeed can cache 404 pages.
3. Force the standard login addresses: add `define( 'EPIF_LOGIN_RESCUE', true );` to `wp-config-local.php`.
4. Last resort: in the hosting File Manager, rename `wp-content/plugins/loginizer-security` (and `loginizer`) to `…-off`. That switches the plugin off; rename it back after logging in.
5. If admin pages redirect to HTTPS and fail, the SSL certificate isn't active yet: add `define( 'FORCE_SSL_ADMIN', false );` to `wp-config-local.php` until it is.
