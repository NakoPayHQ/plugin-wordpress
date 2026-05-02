<?php
/**
 * Admin settings page: Settings -> NakoPay.
 */
if (!defined('ABSPATH') && !defined('NAKOPAY_CLI')) { exit; }

class NakoPay_Admin
{
    const OPTION_GROUP = 'nakopay_settings';
    const PAGE_SLUG    = 'nakopay';

    public static function register_menu(): void
    {
        add_options_page(
            'NakoPay',
            'NakoPay',
            'manage_options',
            self::PAGE_SLUG,
            [__CLASS__, 'render_page']
        );
    }

    public static function register_settings(): void
    {
        register_setting(self::OPTION_GROUP, 'nakopay_api_key',         ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field']);
        register_setting(self::OPTION_GROUP, 'nakopay_publishable_key', ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field']);
        register_setting(self::OPTION_GROUP, 'nakopay_webhook_secret',  ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field']);
        register_setting(self::OPTION_GROUP, 'nakopay_mode',            ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => 'test']);
    }

    public static function render_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        $webhook_url = esc_url(rest_url('nakopay/v1/webhook'));
        ?>
        <div class="wrap">
            <h1>NakoPay</h1>
            <p>
                Bitcoin pay buttons and donation forms for WordPress.
                Get an API key at <a href="<?php echo esc_url(NAKOPAY_DASHBOARD_KEYS_URL); ?>" target="_blank" rel="noopener">nakopay.com/dashboard/api-keys</a>.
                Full docs: <a href="<?php echo esc_url(NAKOPAY_DOCS_URL); ?>" target="_blank" rel="noopener">nakopay.com/docs/integrations/wordpress</a>.
            </p>
            <form method="post" action="options.php">
                <?php settings_fields(self::OPTION_GROUP); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="nakopay_mode">Mode</label></th>
                        <td>
                            <select name="nakopay_mode" id="nakopay_mode">
                                <option value="test" <?php selected(get_option('nakopay_mode', 'test'), 'test'); ?>>Test</option>
                                <option value="live" <?php selected(get_option('nakopay_mode'), 'live'); ?>>Live</option>
                            </select>
                            <p class="description">Cosmetic - the key prefix (sk_test_ vs sk_live_) decides what NakoPay actually does.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="nakopay_api_key">Secret API Key</label></th>
                        <td>
                            <input type="password" id="nakopay_api_key" name="nakopay_api_key"
                                   value="<?php echo esc_attr(get_option('nakopay_api_key', '')); ?>"
                                   class="regular-text" autocomplete="off" />
                            <p class="description">Starts with <code>sk_test_</code> or <code>sk_live_</code>. Server-side only - never exposed to the browser.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="nakopay_publishable_key">Publishable Key (optional)</label></th>
                        <td>
                            <input type="text" id="nakopay_publishable_key" name="nakopay_publishable_key"
                                   value="<?php echo esc_attr(get_option('nakopay_publishable_key', '')); ?>"
                                   class="regular-text" />
                            <p class="description">Starts with <code>pk_test_</code> or <code>pk_live_</code>. Safe to embed in client-side widgets.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="nakopay_webhook_secret">Webhook Signing Secret</label></th>
                        <td>
                            <input type="password" id="nakopay_webhook_secret" name="nakopay_webhook_secret"
                                   value="<?php echo esc_attr(get_option('nakopay_webhook_secret', '')); ?>"
                                   class="regular-text" autocomplete="off" />
                            <p class="description">Shown once when you create a webhook in the NakoPay dashboard.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Your Webhook URL</th>
                        <td>
                            <code><?php echo $webhook_url; ?></code>
                            <p class="description">Paste this into <em>Dashboard -> Webhooks -> Add endpoint</em> in NakoPay.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            <hr />
            <h2>Shortcodes</h2>
            <p>Drop these anywhere in a post, page, or widget:</p>
            <ul>
                <li><code>[nakopay_button amount="25" currency="USD" description="T-shirt"]</code></li>
                <li><code>[nakopay_donate currency="USD" suggested="5,15,50"]</code></li>
            </ul>
        </div>
        <?php
    }
}
