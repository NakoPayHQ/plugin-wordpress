<?php
/**
 * Shortcodes: [nakopay_button] and [nakopay_donate].
 */
if (!defined('ABSPATH') && !defined('NAKOPAY_CLI')) { exit; }

class NakoPay_Shortcodes
{
    public static function register(): void
    {
        add_shortcode('nakopay_button', [__CLASS__, 'render_button']);
        add_shortcode('nakopay_donate', [__CLASS__, 'render_donate']);
    }

    public static function enqueue_assets(): void
    {
        // Inline styles only - no external assets to keep the install lean.
        $css = '.nakopay-btn{display:inline-block;padding:.6em 1.2em;background:#EA6828;color:#fff;border-radius:6px;text-decoration:none;font-weight:600;border:none;cursor:pointer;font-size:1em}'
             . '.nakopay-btn:hover{filter:brightness(1.08)}'
             . '.nakopay-btn--outline{background:transparent;border:2px solid #EA6828;color:#EA6828}'
             . '.nakopay-btn--minimal{background:transparent;color:#EA6828;text-decoration:underline;padding:.3em .6em}'
             . '.nakopay-donate-form input{padding:.5em;margin-right:.5em;border:1px solid #ccc;border-radius:4px}';
        wp_register_style('nakopay-inline', false);
        wp_enqueue_style('nakopay-inline');
        wp_add_inline_style('nakopay-inline', $css);
    }

    public static function render_button($atts, $content = null): string
    {
        $atts = shortcode_atts([
            'amount'      => '0',
            'currency'    => 'USD',
            'description' => '',
            'label'       => 'Pay with Bitcoin',
        ], $atts, 'nakopay_button');

        $amount = (float) $atts['amount'];
        if ($amount <= 0) {
            return '<em>NakoPay: invalid amount.</em>';
        }

        $client = new NakoPay_Client();
        if (!$client->is_configured()) {
            return '<em>NakoPay: API key not configured.</em>';
        }
        $resp = $client->create_payment_link(
            $amount,
            (string) $atts['currency'],
            (string) $atts['description'],
            ['source' => 'wordpress', 'post_id' => (int) get_the_ID()]
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
        return sprintf(
            '<a class="nakopay-btn" href="%s" target="_blank" rel="noopener">%s</a>',
            esc_url($url),
            esc_html($atts['label'])
        );
    }

    public static function render_donate($atts, $content = null): string
    {
        $atts = shortcode_atts([
            'currency'  => 'USD',
            'suggested' => '5,25,100',
            'label'     => 'Donate with Bitcoin',
        ], $atts, 'nakopay_donate');

        $action = esc_url(admin_url('admin-post.php'));
        $nonce  = wp_create_nonce('nakopay_donate');
        $sugg   = array_filter(array_map('trim', explode(',', (string) $atts['suggested'])));

        // Pre-create a placeholder link via the form action route for simplicity:
        // we redirect the user through admin-post which calls the API server-side.
        ob_start();
        ?>
        <form class="nakopay-donate-form" method="post" action="<?php echo $action; ?>">
            <input type="hidden" name="action" value="nakopay_donate" />
            <input type="hidden" name="_wpnonce" value="<?php echo esc_attr($nonce); ?>" />
            <input type="hidden" name="currency" value="<?php echo esc_attr($atts['currency']); ?>" />
            <input type="number" name="amount" min="1" step="any"
                   placeholder="Amount (<?php echo esc_attr($atts['currency']); ?>)"
                   list="nakopay-suggested-<?php echo esc_attr(get_the_ID()); ?>" required />
            <datalist id="nakopay-suggested-<?php echo esc_attr(get_the_ID()); ?>">
                <?php foreach ($sugg as $s): ?>
                    <option value="<?php echo esc_attr($s); ?>" />
                <?php endforeach; ?>
            </datalist>
            <button type="submit" class="nakopay-btn"><?php echo esc_html($atts['label']); ?></button>
        </form>
        <?php
        // Wire the admin-post handler once.
        if (!has_action('admin_post_nopriv_nakopay_donate')) {
            add_action('admin_post_nopriv_nakopay_donate', [__CLASS__, 'handle_donate']);
            add_action('admin_post_nakopay_donate',        [__CLASS__, 'handle_donate']);
        }
        return (string) ob_get_clean();
    }

    public static function handle_donate(): void
    {
        check_admin_referer('nakopay_donate');
        $amount   = isset($_POST['amount']) ? (float) $_POST['amount'] : 0.0;
        $currency = isset($_POST['currency']) ? sanitize_text_field((string) $_POST['currency']) : 'USD';
        if ($amount <= 0) {
            wp_die('Invalid amount.');
        }
        $client = new NakoPay_Client();
        $resp   = $client->create_payment_link($amount, $currency, 'Donation', ['source' => 'wordpress-donate']);
        if (!$resp['ok']) {
            wp_die('Could not create payment link. Check your API key and try again.');
        }
        $url = $resp['body']['url'] ?? $resp['body']['checkout_url'] ?? $resp['body']['hosted_url'] ?? null;
        if (!$url) {
            wp_die('NakoPay did not return a checkout URL.');
        }
        wp_redirect($url);
        exit;
    }
}
