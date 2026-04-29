# NakoPay for WordPress

Accept Bitcoin and other crypto in WordPress with a one-flat-fee, non-custodial
checkout. Wallet-to-wallet - NakoPay never holds your funds.

Use this plugin for **standalone WordPress sites** (donations, tip jars, pay buttons, gated content). If you run **WooCommerce**, install [NakoPay for WooCommerce](https://github.com/NakoPayHQ/plugin-woocommerce) instead.

[![Status](https://img.shields.io/badge/status-stable-blue)](https://nakopay.com/integrations/wordpress)
[![License](https://img.shields.io/badge/license-MIT-green)](../LICENSE)

## Requirements

- WordPress 6.0+
- PHP 8.0+
- A NakoPay account (free) and at least one API key from <https://nakopay.com/dashboard/api-keys>

## Download

| # | Source | When to use |
|---|--------|-------------|
| 1 | **WordPress.org Plugin Directory** - search "NakoPay" in your WP admin (`Plugins -> Add New`) | Easiest. *Listing pending wp.org review - if you can't find it yet, use option 2.* |
| 2 | **GitHub Releases zip** - <https://github.com/NakoPayHQ/plugin-wordpress/releases/latest/download/nakopay.zip> | Available today. Download `nakopay.zip`. |
| 3 | **Build from source** | See bottom of this file. |

## Install

You only need **one** of the methods below.

### Method A - Upload via WordPress admin (recommended)

1. Download `nakopay.zip` (do not unzip).
2. WordPress admin -> **Plugins -> Add New -> Upload Plugin**.
3. Choose the zip, click **Install Now**, then **Activate Plugin**.

### Method B - WP-CLI

Once approved on wp.org:

```bash
wp plugin install nakopay --activate
```

Or from the zip:

```bash
wp plugin install /path/to/nakopay.zip --activate
```

### Method C - SFTP / cPanel File Manager

1. Unzip on your computer - you get a folder called `nakopay/`.
2. Upload it to `wp-content/plugins/` so the final path is `wp-content/plugins/nakopay/nakopay.php`.
3. WordPress admin -> **Plugins** -> **Activate**.

## Configure

1. Get an API key: <https://nakopay.com/dashboard/api-keys>.
2. WordPress admin -> **Settings -> NakoPay**.
3. Paste your **API key**.
4. Copy the **Webhook URL** the plugin shows you and paste it into your NakoPay dashboard at **Settings -> Webhooks -> Add endpoint**. Subscribe to `invoice.paid`, `invoice.completed`, `invoice.expired`.
5. Paste the **signing secret** NakoPay shows back into the plugin's Webhook secret field.
6. Save.

## Use

The plugin adds a shortcode you can drop into any post or page:

```
[nakopay_button amount="10" currency="USD" label="Tip the author"]
```

Or render a donation form:

```
[nakopay_donate currency="USD" suggested="5,10,25,50"]
```

Full shortcode reference: <https://nakopay.com/docs/wordpress>.

## Verify

Add the shortcode to a draft page, preview it, click the button - you should see a QR + address. Pay with `sk_test_*` keys to confirm webhooks reach your site.

## Test mode

Use `sk_test_*` keys to run against the NakoPay sandbox. No real funds move. Flip to `sk_live_*` when ready.

## Uninstall

1. **Plugins -> Installed Plugins -> NakoPay -> Deactivate**.
2. Click **Delete** to remove files.

## Supported features

- [x] One-time checkout
- [ ] Refunds
- [ ] Subscriptions
- [x] Multi-currency display
- [x] Test mode

## Build from source

```bash
git clone https://github.com/NakoPayHQ/plugin-wordpress.git
cd plugin-wordpress
zip -r nakopay.zip . -x "*.git*" "tests/*" "*.DS_Store"
```

## Local development

See [`../CONTRIBUTING.md`](../CONTRIBUTING.md). Run `bash ../scripts/check-no-internal-urls.sh .` before PRs.

## Release

```
plugins/scripts/release.sh wordpress 0.1.0
```

Workflow at `.github/workflows/release-wordpress.yml`. Runbook in [`../PUBLISHING.md`](../PUBLISHING.md).

## Support

- Issues: <https://github.com/NakoPayHQ/plugin-wordpress/issues>
- Email: support@nakopay.com

## License

MIT - see [`../LICENSE`](../LICENSE).
