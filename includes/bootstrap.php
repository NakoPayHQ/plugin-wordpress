<?php
/**
 * NakoPay WordPress - bootstrap.
 *
 * Wires up:
 *   - admin settings page (Settings -> NakoPay)
 *   - REST webhook endpoint (POST /wp-json/nakopay/v1/webhook)
 *   - shortcodes [nakopay_button] and [nakopay_donate]
 *   - Gutenberg block nakopay/pay-button
 *   - activation hook (creates wp_nakopay_events table)
 *
 * Keep this file thin; logic lives in the class files below.
 */

if (!defined('ABSPATH') && !defined('NAKOPAY_CLI')) {
    exit;
}

define('NAKOPAY_WP_VERSION', '1.0.0');
define('NAKOPAY_WP_DIR', dirname(__DIR__));
define('NAKOPAY_WP_INCLUDES', __DIR__);
define('NAKOPAY_API_BASE', 'https://daslrxpkbkqrbnjwouiq.supabase.co/functions/v1');
define('NAKOPAY_API_FALLBACK', 'https://api.nakopay.com/v1'); // reserved
define('NAKOPAY_DASHBOARD_KEYS_URL', 'https://nakopay.com/dashboard/api-keys');
define('NAKOPAY_DOCS_URL', 'https://nakopay.com/docs/integrations/wordpress');

require_once __DIR__ . '/class-nakopay-client.php';
require_once __DIR__ . '/class-nakopay-admin.php';
require_once __DIR__ . '/class-nakopay-shortcodes.php';
require_once __DIR__ . '/class-nakopay-webhook.php';
require_once __DIR__ . '/class-nakopay-block.php';

if (function_exists('add_action')) {
    add_action('admin_menu',           ['NakoPay_Admin', 'register_menu']);
    add_action('admin_init',           ['NakoPay_Admin', 'register_settings']);
    add_action('rest_api_init',        ['NakoPay_Webhook', 'register_routes']);
    add_action('init',                 ['NakoPay_Shortcodes', 'register']);
    add_action('init',                 ['NakoPay_Block', 'register']);
    add_action('enqueue_block_editor_assets', ['NakoPay_Block', 'enqueue_editor_assets']);
    add_action('wp_enqueue_scripts',   ['NakoPay_Shortcodes', 'enqueue_assets']);
}

if (function_exists('register_activation_hook')) {
    register_activation_hook(NAKOPAY_WP_DIR . '/nakopay.php', ['NakoPay_Webhook', 'install_table']);
}
