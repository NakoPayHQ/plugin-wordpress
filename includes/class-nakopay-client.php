<?php
/**
 * Thin wrapper around the NakoPay REST API using wp_remote_*.
 */
if (!defined('ABSPATH') && !defined('NAKOPAY_CLI')) { exit; }

class NakoPay_Client
{
    private $api_key;
    private $base;

    public function __construct($api_key = null, $base = null)
    {
        $this->api_key = $api_key ?: trim((string) get_option('nakopay_api_key', ''));
        $this->base    = rtrim($base ?: NAKOPAY_API_BASE, '/');
    }

    public function is_configured(): bool
    {
        return $this->api_key !== '';
    }

    /**
     * Create a hosted payment link.
     *
     * @return array{ok:bool,status:int,body:array}
     */
    public function create_payment_link(float $amount, string $currency, string $description = '', array $metadata = []): array
    {
        $body = [
            'amount'      => (string) $amount,
            'currency'    => strtoupper($currency),
        ];
        if ($description !== '') {
            $body['description'] = $description;
        }
        if (!empty($metadata)) {
            $body['metadata'] = $metadata;
        }
        return $this->request('POST', '/payment-links', $body);
    }

    /**
     * Create an invoice for a one-time payment.
     *
     * @return array{ok:bool,status:int,body:array}
     */
    public function create_invoice(float $amount, string $currency, string $coin = 'BTC', string $description = '', array $metadata = []): array
    {
        $body = [
            'amount'   => (string) $amount,
            'currency' => strtoupper($currency),
            'coin'     => strtoupper($coin),
        ];
        if ($description !== '') {
            $body['description'] = $description;
        }
        if (!empty($metadata)) {
            $body['metadata'] = $metadata;
        }
        return $this->request('POST', '/invoices-create', $body);
    }

    /**
     * Retrieve an invoice by ID.
     *
     * @return array{ok:bool,status:int,body:array}
     */
    public function get_invoice(string $id): array
    {
        return $this->request('GET', '/invoices-get?id=' . urlencode($id));
    }

    private function request(string $method, string $path, ?array $body = null): array
    {
        if (!$this->is_configured()) {
            return ['ok' => false, 'status' => 0, 'body' => ['error' => 'NakoPay API key not configured']];
        }
        $url  = $this->base . $path;
        $args = [
            'method'  => $method,
            'timeout' => 20,
            'headers' => [
                'Authorization'    => 'Bearer ' . $this->api_key,
                'Content-Type'     => 'application/json',
                'Accept'           => 'application/json',
                'X-NakoPay-Version' => '2025-04-20',
                'User-Agent'       => 'NakoPay-WordPress/' . NAKOPAY_WP_VERSION,
            ],
        ];
        if ($body !== null) {
            $args['headers']['Idempotency-Key'] = 'idem_' . bin2hex(random_bytes(16));
            $args['body'] = wp_json_encode($body);
        }
        $resp = wp_remote_request($url, $args);
        if (is_wp_error($resp)) {
            return ['ok' => false, 'status' => 0, 'body' => ['error' => $resp->get_error_message()]];
        }
        $code = (int) wp_remote_retrieve_response_code($resp);
        $raw  = (string) wp_remote_retrieve_body($resp);
        $json = json_decode($raw, true);
        if (!is_array($json)) {
            $json = ['raw' => $raw];
        }
        return ['ok' => $code >= 200 && $code < 300, 'status' => $code, 'body' => $json];
    }
}
