<?php
/**
 * Stripe Payment Integration
 * Handles payment processing, checkouts, webhooks, and refunds (test mode)
 */

require_once __DIR__ . '/APIIntegration.php';

class StripeIntegration extends APIIntegration {
    private $secretKey;
    private $publishableKey;
    private $webhookSecret;
    private $apiUrl = 'https://api.stripe.com/v1';

    public function __construct() {
        parent::__construct('Stripe Payment');

        $this->secretKey = $this->getConfig('secret_key') ?? $_ENV['STRIPE_SECRET_KEY'] ?? '';
        $this->publishableKey = $this->getConfig('publishable_key') ?? $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '';
        $this->webhookSecret = $this->getConfig('webhook_secret') ?? $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '';
    }

    public function testConnection() {
        if (empty($this->secretKey)) {
            return [
                'success' => false,
                'error' => 'Stripe secret key not configured'
            ];
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/balance',
            'GET',
            null,
            $this->getHeaders()
        );

        return $response;
    }

    public function createPaymentIntent($amount, $currency = 'usd', $metadata = []) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Stripe integration is disabled'];
        }

        if (empty($this->secretKey)) {
            return ['success' => false, 'error' => 'Stripe secret key not configured'];
        }

        $amountInCents = (int)($amount * 100);

        $data = [
            'amount' => $amountInCents,
            'currency' => $currency,
            'automatic_payment_methods[enabled]' => 'true'
        ];

        foreach ($metadata as $key => $value) {
            $data["metadata[{$key}]"] = $value;
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/payment_intents',
            'POST',
            http_build_query($data),
            $this->getHeaders('application/x-www-form-urlencoded')
        );

        if ($response['success']) {
            $this->logTransactionToDatabase($response['data'], 'payment_intent_created');
        }

        return $response;
    }

    public function createCheckoutSession($lineItems, $options = []) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Stripe integration is disabled'];
        }

        $data = [
            'mode' => $options['mode'] ?? 'payment',
            'success_url' => $options['success_url'] ?? 'https://adilgfx.com/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $options['cancel_url'] ?? 'https://adilgfx.com/cancel'
        ];

        foreach ($lineItems as $index => $item) {
            $data["line_items[{$index}][price_data][currency]"] = $item['currency'] ?? 'usd';
            $data["line_items[{$index}][price_data][product_data][name]"] = $item['name'];
            $data["line_items[{$index}][price_data][unit_amount]"] = (int)($item['amount'] * 100);
            $data["line_items[{$index}][quantity]"] = $item['quantity'] ?? 1;

            if (!empty($item['description'])) {
                $data["line_items[{$index}][price_data][product_data][description]"] = $item['description'];
            }

            if (!empty($item['images'])) {
                foreach ($item['images'] as $imgIndex => $imageUrl) {
                    $data["line_items[{$index}][price_data][product_data][images][{$imgIndex}]"] = $imageUrl;
                }
            }
        }

        if (!empty($options['customer_email'])) {
            $data['customer_email'] = $options['customer_email'];
        }

        if (!empty($options['metadata'])) {
            foreach ($options['metadata'] as $key => $value) {
                $data["metadata[{$key}]"] = $value;
            }
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/checkout/sessions',
            'POST',
            http_build_query($data),
            $this->getHeaders('application/x-www-form-urlencoded')
        );

        if ($response['success']) {
            $this->logTransactionToDatabase($response['data'], 'checkout_session_created');
        }

        return $response;
    }

    public function retrieveCheckoutSession($sessionId) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Stripe integration is disabled'];
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/checkout/sessions/' . $sessionId,
            'GET',
            null,
            $this->getHeaders()
        );

        return $response;
    }

    public function retrievePaymentIntent($paymentIntentId) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Stripe integration is disabled'];
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/payment_intents/' . $paymentIntentId,
            'GET',
            null,
            $this->getHeaders()
        );

        return $response;
    }

    public function capturePaymentIntent($paymentIntentId, $amountToCapture = null) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Stripe integration is disabled'];
        }

        $data = [];
        if ($amountToCapture !== null) {
            $data['amount_to_capture'] = (int)($amountToCapture * 100);
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/payment_intents/' . $paymentIntentId . '/capture',
            'POST',
            http_build_query($data),
            $this->getHeaders('application/x-www-form-urlencoded')
        );

        if ($response['success']) {
            $this->logTransactionToDatabase($response['data'], 'payment_captured');
        }

        return $response;
    }

    public function createRefund($paymentIntentId, $amount = null, $reason = null) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Stripe integration is disabled'];
        }

        $data = [
            'payment_intent' => $paymentIntentId
        ];

        if ($amount !== null) {
            $data['amount'] = (int)($amount * 100);
        }

        if ($reason !== null) {
            $data['reason'] = $reason;
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/refunds',
            'POST',
            http_build_query($data),
            $this->getHeaders('application/x-www-form-urlencoded')
        );

        if ($response['success']) {
            $this->logTransactionToDatabase($response['data'], 'refund_created');
        }

        return $response;
    }

    public function listRefunds($paymentIntentId = null, $limit = 10) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Stripe integration is disabled'];
        }

        $params = ['limit' => $limit];
        if ($paymentIntentId) {
            $params['payment_intent'] = $paymentIntentId;
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/refunds?' . http_build_query($params),
            'GET',
            null,
            $this->getHeaders()
        );

        return $response;
    }

    public function createCustomer($email, $name = null, $metadata = []) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Stripe integration is disabled'];
        }

        $data = ['email' => $email];

        if ($name !== null) {
            $data['name'] = $name;
        }

        foreach ($metadata as $key => $value) {
            $data["metadata[{$key}]"] = $value;
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/customers',
            'POST',
            http_build_query($data),
            $this->getHeaders('application/x-www-form-urlencoded')
        );

        return $response;
    }

    public function retrieveCustomer($customerId) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Stripe integration is disabled'];
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/customers/' . $customerId,
            'GET',
            null,
            $this->getHeaders()
        );

        return $response;
    }

    public function listCharges($limit = 10, $customerId = null) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Stripe integration is disabled'];
        }

        $params = ['limit' => $limit];
        if ($customerId) {
            $params['customer'] = $customerId;
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/charges?' . http_build_query($params),
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
            $signedPayload = $payload;
            $expectedSignature = hash_hmac('sha256', $signedPayload, $this->webhookSecret);

            $signatures = [];
            foreach (explode(',', $signature) as $element) {
                $parts = explode('=', $element, 2);
                if (count($parts) === 2) {
                    if ($parts[0] === 'v1') {
                        $signatures[] = $parts[1];
                    }
                }
            }

            $isValid = false;
            foreach ($signatures as $sig) {
                if (hash_equals($expectedSignature, $sig)) {
                    $isValid = true;
                    break;
                }
            }

            if ($isValid) {
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
        $eventData = $event['data']['object'] ?? [];

        $this->logWebhookToDatabase($eventType, $eventData);

        switch ($eventType) {
            case 'payment_intent.succeeded':
                $this->logTransactionToDatabase($eventData, 'payment_succeeded');
                break;

            case 'payment_intent.payment_failed':
                $this->logTransactionToDatabase($eventData, 'payment_failed');
                break;

            case 'checkout.session.completed':
                $this->logTransactionToDatabase($eventData, 'checkout_completed');
                break;

            case 'charge.refunded':
                $this->logTransactionToDatabase($eventData, 'charge_refunded');
                break;

            default:
                break;
        }

        return ['success' => true, 'event_type' => $eventType];
    }

    public function getPublishableKey() {
        return $this->publishableKey;
    }

    private function getHeaders($contentType = 'application/json') {
        return [
            'Authorization: Bearer ' . $this->secretKey,
            'Content-Type: ' . $contentType
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

            $transactionId = $transactionData['id'] ?? null;
            $amount = isset($transactionData['amount']) ? ($transactionData['amount'] / 100) : 0;
            $currency = $transactionData['currency'] ?? 'usd';
            $status = $transactionData['status'] ?? 'unknown';

            $stmt->execute([
                $transactionId,
                'stripe',
                $amount,
                $currency,
                $status,
                $eventType,
                json_encode($transactionData)
            ]);
        } catch (Exception $e) {
            error_log("Failed to log Stripe transaction to database: " . $e->getMessage());
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
                'stripe',
                $eventType,
                json_encode($eventData),
                1
            ]);
        } catch (Exception $e) {
            error_log("Failed to log Stripe webhook to database: " . $e->getMessage());
        }
    }
}
