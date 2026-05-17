<?php
/**
 * NakoPay Gutenberg block: nakopay/pay-button.
 *
 * Registers a server-side rendered block that embeds a pay button.
 * Uses register_block_type() with a render_callback for full PHP control.
 */
if (!defined('ABSPATH') && !defined('NAKOPAY_CLI')) { exit; }

class NakoPay_Block
{
    const BLOCK_NAME = 'nakopay/pay-button';

    public static function register(): void
    {
        if (!function_exists('register_block_type')) {
            return;
        }

        register_block_type(self::BLOCK_NAME, [
            'api_version'     => 3,
            'attributes'      => [
                'amount'      => ['type' => 'string', 'default' => '25'],
                'currency'    => ['type' => 'string', 'default' => 'USD'],
                'coin'        => ['type' => 'string', 'default' => 'BTC'],
                'description' => ['type' => 'string', 'default' => ''],
                'label'       => ['type' => 'string', 'default' => 'Pay with Bitcoin'],
                'style'       => ['type' => 'string', 'default' => 'default'],
            ],
            'render_callback' => [__CLASS__, 'render'],
            'editor_script'   => 'nakopay-block-editor',
            'editor_style'    => 'nakopay-block-editor-style',
        ]);
    }

    public static function enqueue_editor_assets(): void
    {
        wp_register_script(
            'nakopay-block-editor',
            plugins_url('../assets/block-editor.js', __FILE__),
            ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n'],
            NAKOPAY_WP_VERSION,
            true
        );
        wp_register_style(
            'nakopay-block-editor-style',
            false
        );
        wp_add_inline_style('nakopay-block-editor-style', self::get_editor_css());
    }

    public static function render(array $attrs): string
    {
        $amount   = (float) ($attrs['amount'] ?? 25);
        $currency = sanitize_text_field($attrs['currency'] ?? 'USD');
        $coin     = sanitize_text_field($attrs['coin'] ?? 'BTC');
        $desc     = sanitize_text_field($attrs['description'] ?? '');
        $label    = sanitize_text_field($attrs['label'] ?? 'Pay with Bitcoin');
        $style    = sanitize_text_field($attrs['style'] ?? 'default');

        if ($amount <= 0) {
            return '<em>NakoPay: invalid amount.</em>';
        }

        $client = new NakoPay_Client();
        if (!$client->is_configured()) {
            return '<em>NakoPay: API key not configured. Go to Settings &gt; NakoPay.</em>';
        }

        $resp = $client->create_payment_link(
            $amount,
            $currency,
            $desc ?: sprintf('%s %s payment', $amount, $currency),
            ['source' => 'wordpress-block', 'coin' => $coin, 'post_id' => (string) get_the_ID()]
        );

        if (!$resp['ok']) {
            return '<em>NakoPay: could not create payment link.</em>';
        }

        $url = $resp['body']['url']
            ?? $resp['body']['checkout_url']
            ?? $resp['body']['hosted_url']
            ?? null;

        if (!$url) {
            return '<em>NakoPay: missing checkout URL in response.</em>';
        }

        $btn_class = 'nakopay-btn';
        if ($style === 'outline') {
            $btn_class .= ' nakopay-btn--outline';
        } elseif ($style === 'minimal') {
            $btn_class .= ' nakopay-btn--minimal';
        }

        return sprintf(
            '<div class="wp-block-nakopay-pay-button"><a class="%s" href="%s" target="_blank" rel="noopener">%s</a></div>',
            esc_attr($btn_class),
            esc_url($url),
            esc_html($label)
        );
    }

    private static function get_editor_css(): string
    {
        return '.wp-block-nakopay-pay-button .nakopay-preview{display:inline-block;padding:.6em 1.2em;background:#EA6828;color:#fff;border-radius:6px;font-weight:600;cursor:default}'
             . '.wp-block-nakopay-pay-button .nakopay-preview--outline{background:transparent;border:2px solid #EA6828;color:#EA6828}'
             . '.wp-block-nakopay-pay-button .nakopay-preview--minimal{background:transparent;color:#EA6828;text-decoration:underline}';
    }
}
