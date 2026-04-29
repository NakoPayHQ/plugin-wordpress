=== NakoPay ===
Contributors: nakopay
Tags: bitcoin, payments, donation, lightning, crypto
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 8.0
Stable tag: 0.1.0
License: MIT

Bitcoin pay buttons, donation forms, and tip jars for WordPress.

== Description ==

NakoPay adds Bitcoin (and Lightning, plus 6 other coins) pay buttons,
donation forms, and tip jars to any WordPress site via shortcodes. Funds
settle directly to your wallet - NakoPay never holds them.

If you run WooCommerce, install "NakoPay for WooCommerce" instead.

Shortcodes:

* `[nakopay_button amount="10" currency="USD"]`
* `[nakopay_donate currency="USD" suggested="5,10,25,50"]`

== Installation ==

Easiest path - upload via WordPress admin:

1. Download `nakopay.zip` from
   https://github.com/NakoPayHQ/plugin-wordpress/releases/latest (do NOT unzip).
2. WordPress admin -> Plugins -> Add New -> Upload Plugin.
3. Choose the zip, click Install Now, then Activate Plugin.

Or via WP-CLI:

`wp plugin install nakopay --activate`

Or manually via SFTP:

1. Unzip on your computer.
2. Upload the `nakopay/` folder to `wp-content/plugins/`.
3. Activate from the Plugins page.

After activating:

1. Get an API key at https://nakopay.com/dashboard/api-keys
2. Settings -> NakoPay -> paste API key + webhook secret.
3. Add the Webhook URL the plugin shows you to your NakoPay dashboard
   (Settings -> Webhooks).

== Frequently Asked Questions ==

= Do I need a NakoPay account? =

Yes, free at https://nakopay.com.

= Does NakoPay hold my funds? =

No. Non-custodial - payments settle to your wallet directly.

= Can I test without real Bitcoin? =

Yes. Use `sk_test_*` keys; the plugin runs against the NakoPay sandbox and
accepts Bitcoin testnet.

= I run WooCommerce - which plugin? =

Use "NakoPay for WooCommerce". This plugin is for standalone WordPress sites.

== Changelog ==

= 0.1.0 =
* Initial release.
