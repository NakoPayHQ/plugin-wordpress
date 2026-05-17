# Changelog

## 1.0.0 (2026-05-01)

### Added
- Admin settings page (Settings -> NakoPay) with mode, API key, webhook secret
- `[nakopay_button]` shortcode for fixed-amount pay buttons
- `[nakopay_donate]` shortcode with suggested amounts and custom donation form
- Gutenberg block `nakopay/pay-button` with live preview and inspector controls
- Block supports 3 button styles: default (filled), outline, minimal
- REST webhook endpoint at `/wp-json/nakopay/v1/webhook` with HMAC-SHA256 verification
- Event logging table (`wp_nakopay_events`) created on activation
- `nakopay_event` action hook for custom integrations
- API client with idempotency keys and `X-NakoPay-Version` header
- Brand orange (#EA6828) button styling
