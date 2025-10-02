<?php
/**
 * Coinbase Commerce Integration
 * Handles cryptocurrency payment processing and webhooks
 */

require_once __DIR__ . '/APIIntegration.php';

class CoinbaseIntegration extends APIIntegration {
    private $apiKey;
    private $webhookSecret;
    private $apiUrl = 'https://api.commerce.coinbase.com';

    public function __construct() {
        parent::__construct('Coinbase Commerce');

        $this->apiKey = $this->getConfig('api_key') ?? $_ENV['COINBASE_API_KEY'] ?? '';
        $this->webhookSecret = $this->getConfig('webhook_secret') ?? $_ENV['COINBASE_WEBHOOK_SECRET'] ?? '';
    }

    public function testConnection() {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'Coinbase API key not configured'
            ];
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/charges',
            'GET',
            null,
            $this->getHeaders()
        );

        return $response;
    }

    public function createCharge($name, $description, $amount, $currency = 'USD', $metadata = []) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Coinbase integration is disabled'];
        }

        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'Coinbase API key not configured'];
        }

        $data = [
            'name' => $name,
            'description' => $description,
            'pricing_type' => 'fixed_price',
            'local_price' => [
                'amount' => (string)$amount,
                'currency' => $currency
            ],
            'metadata' => $metadata
        ];

        $response = $this->makeRequest(
            $this->apiUrl . '/charges',
            'POST',
            $data,
            $this->getHeaders()
        );

        if ($response['success'] && isset($response['data']['data'])) {
            $this->logTransactionToDatabase($response['data']['data'], 'charge_created');
        }

        return $response;
    }

    public function retrieveCharge($chargeId) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Coinbase integration is disabled'];
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/charges/' . $chargeId,
            'GET',
            null,
            $this->getHeaders()
        );

        return $response;
    }

    public function listCharges($limit = 25) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Coinbase integration is disabled'];
        }

        $params = ['limit' => $limit];

        $response = $this->makeRequest(
            $this->apiUrl . '/charges?' . http_build_query($params),
            'GET',
            null,
            $this->getHeaders()
        );

        return $response;
    }

    public function cancelCharge($chargeId) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Coinbase integration is disabled'];
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/charges/' . $chargeId . '/cancel',
            'POST',
            [],
            $this->getHeaders()
        );

        if ($response['success']) {
            $this->logTransactionToDatabase(['id' => $chargeId], 'charge_cancelled');
        }

        return $response;
    }

    public function resolveCharge($chargeId) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Coinbase integration is disabled'];
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/charges/' . $chargeId . '/resolve',
            'POST',
            [],
            $this->getHeaders()
        );

        return $response;
    }

    public function createCheckout($name, $description, $amount, $currency = 'USD', $options = []) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Coinbase integration is disabled'];
        }

        $data = [
            'name' => $name,
            'description' => $description,
            'pricing_type' => 'fixed_price',
            'local_price' => [
                'amount' => (string)$amount,
                'currency' => $currency
            ]
        ];

        if (!empty($options['requested_info'])) {
            $data['requested_info'] = $options['requested_info'];
        }

        if (!empty($options['redirect_url'])) {
            $data['redirect_url'] = $options['redirect_url'];
        }

        if (!empty($options['cancel_url'])) {
            $data['cancel_url'] = $options['cancel_url'];
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/checkouts',
            'POST',
            $data,
            $this->getHeaders()
        );

        return $response;
    }

    public function retrieveCheckout($checkoutId) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Coinbase integration is disabled'];
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/checkouts/' . $checkoutId,
            'GET',
            null,
            $this->getHeaders()
        );

        return $response;
    }

    public function listCheckouts($limit = 25) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Coinbase integration is disabled'];
        }

        $params = ['limit' => $limit];

        $response = $this->makeRequest(
            $this->apiUrl . '/checkouts?' . http_build_query($params),
            'GET',
            null,
            $this->getHeaders()
        );

        return $response;
    }

    public function verifyWebhook($payload, $signature) {
        if (empty($this->webhookSecret)) {
            return ['success' => false, 'error' => 'Webhook secret not configured'];
        }

        try {
            $expectedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);

            if (hash_equals($expectedSignature, $signature)) {
                return [
                    'success' => true,
                    'event' => json_decode($payload, true)
                ];
            }

            return ['success' => false, 'error' => 'Invalid signature'];

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function handleWebhookEvent($event) {
        $eventType = $event['type'] ?? '';
        $eventData = $event['data'] ?? [];

        $this->logWebhookToDatabase($eventType, $eventData);

        switch ($eventType) {
            case 'charge:created':
                $this->logTransactionToDatabase($eventData, 'charge_created');
                break;

            case 'charge:confirmed':
                $this->logTransactionToDatabase($eventData, 'charge_confirmed');
                break;

            case 'charge:failed':
                $this->logTransactionToDatabase($eventData, 'charge_failed');
                break;

            case 'charge:delayed':
                $this->logTransactionToDatabase($eventData, 'charge_delayed');
                break;

            case 'charge:pending':
                $this->logTransactionToDatabase($eventData, 'charge_pending');
                break;

            case 'charge:resolved':
                $this->logTransactionToDatabase($eventData, 'charge_resolved');
                break;

            default:
                break;
        }

        return ['success' => true, 'event_type' => $eventType];
    }

    public function getPaymentStatus($chargeId) {
        $response = $this->retrieveCharge($chargeId);

        if ($response['success'] && isset($response['data']['data'])) {
            $charge = $response['data']['data'];
            return [
                'success' => true,
                'status' => $charge['timeline'][count($charge['timeline']) - 1]['status'] ?? 'unknown',
                'payments' => $charge['payments'] ?? [],
                'pricing' => $charge['pricing'] ?? []
            ];
        }

        return $response;
    }

    public function getSupportedCurrencies() {
        return [
            'cryptocurrencies' => [
                'BTC' => 'Bitcoin',
                'ETH' => 'Ethereum',
                'LTC' => 'Litecoin',
                'BCH' => 'Bitcoin Cash',
                'USDC' => 'USD Coin',
                'DAI' => 'Dai',
                'DOGE' => 'Dogecoin'
            ],
            'fiat' => [
                'USD' => 'US Dollar',
                'EUR' => 'Euro',
                'GBP' => 'British Pound',
                'CAD' => 'Canadian Dollar',
                'AUD' => 'Australian Dollar',
                'JPY' => 'Japanese Yen'
            ]
        ];
    }

    private function getHeaders() {
        return [
            'X-CC-Api-Key: ' . $this->apiKey,
            'X-CC-Version: 2018-03-22',
            'Content-Type: application/json'
        ];
    }

    private function logTransactionToDatabase($transactionData, $eventType) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO payment_transactions (
                    transaction_id, payment_method, amount, currency, status,
                    event_type, metadata, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $transactionId = $transactionData['id'] ?? $transactionData['code'] ?? null;
            $amount = 0;
            $currency = 'USD';

            if (isset($transactionData['pricing']['local']['amount'])) {
                $amount = (float)$transactionData['pricing']['local']['amount'];
                $currency = $transactionData['pricing']['local']['currency'] ?? 'USD';
            }

            $status = 'pending';
            if (isset($transactionData['timeline']) && !empty($transactionData['timeline'])) {
                $lastEvent = end($transactionData['timeline']);
                $status = $lastEvent['status'] ?? 'pending';
            }

            $stmt->execute([
                $transactionId,
                'coinbase',
                $amount,
                $currency,
                $status,
                $eventType,
                json_encode($transactionData)
            ]);
        } catch (Exception $e) {
            error_log("Failed to log Coinbase transaction to database: " . $e->getMessage());
        }
    }

    private function logWebhookToDatabase($eventType, $eventData) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO webhook_events (
                    provider, event_type, payload, processed, received_at
                ) VALUES (?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                'coinbase',
                $eventType,
                json_encode($eventData),
                1
            ]);
        } catch (Exception $e) {
            error_log("Failed to log Coinbase webhook to database: " . $e->getMessage());
        }
    }
}
