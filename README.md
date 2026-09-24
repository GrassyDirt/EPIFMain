# EPIFMain
epifservices.com — WordPress site.

## What's custom

| Path | Purpose |
| --- | --- |
| `wp-content/mu-plugins/epif-core.php` + `epif-core/` | Always-on site backend: lead capture API, **Leads** admin screen, security + performance defaults. |
| `wp-content/themes/epif/` | Child theme of Twenty Twenty-Five with a **Landing Page** template and EPIF block patterns. |
| `wp-config.php` | EPIF-tuned settings. Secrets are loaded from `wp-config-local.php` (never committed). |

## Server setup

1. Copy `wp-config-local-sample.php` to `wp-config-local.php` — preferably one directory **above** the web root — and fill in the DB credentials and fresh salts from https://api.wordpress.org/secret-key/1.1/salt/.
   `wp-config.php` refuses to boot (HTTP 503) if this file is missing.
2. Dashboard → Appearance → Themes → activate **EPIF**.
3. Create a page, choose the **Landing Page** template, then Settings → Reading → set it as the homepage.
4. Configure **GoSMTP** so lead notification emails are delivered reliably.

## Landing page lead form

- Place anywhere with the shortcode: `[epif_lead_form services="Option A|Option B" button="Get a quote"]`
- Submissions go to `POST /wp-json/epif/v1/leads`, are stored under **Leads** in the dashboard, and emailed to the admin address (override with `EPIF_LEAD_NOTIFY_EMAIL`).
- UTM / gclid / fbclid parameters from the landing URL are saved with each lead. `gtag('event','generate_lead')` and `fbq('track','Lead')` fire on success when those tags are installed.
- Spam protection: nonce, honeypot, minimum fill time, per-IP rate limit (`EPIF_LEAD_RATE_LIMIT`, default 5/hour), and Akismet when it has an API key.
- Integrations (CRM, webhooks) can hook `do_action( 'epif_lead_created', $lead_id, $data )`.

## Settings (`wp-config.php`)

Production defaults: errors never displayed, dashboard file editor disabled, admin forced to HTTPS, automatic minor core updates, `utf8mb4`, 10 revisions per post, 256M/512M memory. Every value can be overridden per server in `wp-config-local.php`.
Set `EPIF_HSTS` to `true` once HTTPS works on every URL.
