<?php
/**
 * Webhook receiver: POST /wp-json/nakopay/v1/webhook
 * Validates signature header (t=...,v1=hmac_sha256(t.body,secret)) then logs event.
 */
if (!defined('ABSPATH') && !defined('NAKOPAY_CLI')) { exit; }

class NakoPay_Webhook
{
    const TABLE       = 'nakopay_events';
    const SIG_TOLERANCE = 300;

    public static function table_name(): string
    {
        global $wpdb;
        return $wpdb->prefix . self::TABLE;
    }

    public static function install_table(): void
    {
        global $wpdb;
        $table   = self::table_name();
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            event_id VARCHAR(64) NOT NULL,
            event_type VARCHAR(64) NOT NULL,
            payload LONGTEXT NOT NULL,
            received_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_event_id (event_id),
            KEY idx_event_type (event_type)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function register_routes(): void
    {
        register_rest_route('nakopay/v1', '/webhook', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'handle'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function handle(WP_REST_Request $req)
    {
        $secret = trim((string) get_option('nakopay_webhook_secret', ''));
        $sig    = (string) $req->get_header('nakopay-signature');
        $body   = (string) $req->get_body();

        if ($secret === '' || $sig === '' || !self::verify($body, $sig, $secret)) {
            return new WP_REST_Response(['ok' => false, 'error' => 'invalid signature'], 401);
        }

        $event = json_decode($body, true);
        if (!is_array($event)) {
            return new WP_REST_Response(['ok' => false, 'error' => 'invalid json'], 400);
        }

        global $wpdb;
        $wpdb->query($wpdb->prepare(
            "INSERT IGNORE INTO " . self::table_name() . " (event_id, event_type, payload) VALUES (%s, %s, %s)",
            (string) ($event['id'] ?? wp_generate_uuid4()),
            (string) ($event['type'] ?? 'unknown'),
            $body
        ));

        do_action('nakopay_event', $event);
        return new WP_REST_Response(['ok' => true], 200);
    }

    private static function verify(string $body, string $header, string $secret): bool
    {
        $parts = [];
        foreach (explode(',', $header) as $kv) {
            $kv = trim($kv);
            if ($kv === '' || strpos($kv, '=') === false) continue;
            [$k, $v] = explode('=', $kv, 2);
            $parts[trim($k)] = trim($v);
        }
        if (empty($parts['t']) || empty($parts['v1'])) return false;
        $t = (int) $parts['t'];
        if (abs(time() - $t) > self::SIG_TOLERANCE) return false;
        $expected = hash_hmac('sha256', $t . '.' . $body, $secret);
        return hash_equals($expected, $parts['v1']);
    }
}
