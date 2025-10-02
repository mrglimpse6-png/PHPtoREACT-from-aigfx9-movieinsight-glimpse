<?php
/**
 * Telegram Bot API Integration
 * Handles admin notifications, alerts, and bot commands
 */

require_once __DIR__ . '/APIIntegration.php';

class TelegramIntegration extends APIIntegration {
    private $botToken;
    private $adminChatId;
    private $apiUrl = 'https://api.telegram.org/bot';

    public function __construct() {
        parent::__construct('Telegram Bot');

        $this->botToken = $this->getConfig('bot_token') ?? $_ENV['TELEGRAM_BOT_TOKEN'] ?? '';
        $this->adminChatId = $this->getConfig('admin_chat_id') ?? $_ENV['TELEGRAM_ADMIN_CHAT_ID'] ?? '';
    }

    public function testConnection() {
        if (empty($this->botToken)) {
            return [
                'success' => false,
                'error' => 'Telegram bot token not configured'
            ];
        }

        $response = $this->makeRequest(
            $this->apiUrl . $this->botToken . '/getMe',
            'GET'
        );

        return $response;
    }

    public function sendMessage($chatId, $text, $options = []) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Telegram integration is disabled'];
        }

        if (empty($this->botToken)) {
            return ['success' => false, 'error' => 'Telegram bot token not configured'];
        }

        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $options['parse_mode'] ?? 'HTML',
            'disable_web_page_preview' => $options['disable_preview'] ?? false,
            'disable_notification' => $options['silent'] ?? false
        ];

        if (isset($options['reply_markup'])) {
            $data['reply_markup'] = json_encode($options['reply_markup']);
        }

        $response = $this->makeRequest(
            $this->apiUrl . $this->botToken . '/sendMessage',
            'POST',
            $data
        );

        if ($response['success']) {
            $this->logNotificationToDatabase($chatId, 'message', $text, $response['data']);
        }

        return $response;
    }

    public function sendPhoto($chatId, $photoUrl, $caption = '', $options = []) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Telegram integration is disabled'];
        }

        $data = [
            'chat_id' => $chatId,
            'photo' => $photoUrl,
            'caption' => $caption,
            'parse_mode' => $options['parse_mode'] ?? 'HTML'
        ];

        $response = $this->makeRequest(
            $this->apiUrl . $this->botToken . '/sendPhoto',
            'POST',
            $data
        );

        if ($response['success']) {
            $this->logNotificationToDatabase($chatId, 'photo', $caption, $response['data']);
        }

        return $response;
    }

    public function sendDocument($chatId, $documentUrl, $caption = '', $options = []) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Telegram integration is disabled'];
        }

        $data = [
            'chat_id' => $chatId,
            'document' => $documentUrl,
            'caption' => $caption,
            'parse_mode' => $options['parse_mode'] ?? 'HTML'
        ];

        $response = $this->makeRequest(
            $this->apiUrl . $this->botToken . '/sendDocument',
            'POST',
            $data
        );

        if ($response['success']) {
            $this->logNotificationToDatabase($chatId, 'document', $caption, $response['data']);
        }

        return $response;
    }

    public function sendLocation($chatId, $latitude, $longitude) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Telegram integration is disabled'];
        }

        $data = [
            'chat_id' => $chatId,
            'latitude' => $latitude,
            'longitude' => $longitude
        ];

        $response = $this->makeRequest(
            $this->apiUrl . $this->botToken . '/sendLocation',
            'POST',
            $data
        );

        return $response;
    }

    public function editMessage($chatId, $messageId, $newText, $options = []) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Telegram integration is disabled'];
        }

        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $newText,
            'parse_mode' => $options['parse_mode'] ?? 'HTML'
        ];

        if (isset($options['reply_markup'])) {
            $data['reply_markup'] = json_encode($options['reply_markup']);
        }

        $response = $this->makeRequest(
            $this->apiUrl . $this->botToken . '/editMessageText',
            'POST',
            $data
        );

        return $response;
    }

    public function deleteMessage($chatId, $messageId) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Telegram integration is disabled'];
        }

        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId
        ];

        $response = $this->makeRequest(
            $this->apiUrl . $this->botToken . '/deleteMessage',
            'POST',
            $data
        );

        return $response;
    }

    public function getUpdates($offset = 0, $limit = 100) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Telegram integration is disabled'];
        }

        $params = [
            'offset' => $offset,
            'limit' => $limit,
            'timeout' => 30
        ];

        $response = $this->makeRequest(
            $this->apiUrl . $this->botToken . '/getUpdates?' . http_build_query($params),
            'GET'
        );

        return $response;
    }

    public function setWebhook($url, $options = []) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Telegram integration is disabled'];
        }

        $data = [
            'url' => $url,
            'max_connections' => $options['max_connections'] ?? 40,
            'allowed_updates' => $options['allowed_updates'] ?? ['message', 'callback_query']
        ];

        if (isset($options['secret_token'])) {
            $data['secret_token'] = $options['secret_token'];
        }

        $response = $this->makeRequest(
            $this->apiUrl . $this->botToken . '/setWebhook',
            'POST',
            $data
        );

        return $response;
    }

    public function getWebhookInfo() {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Telegram integration is disabled'];
        }

        $response = $this->makeRequest(
            $this->apiUrl . $this->botToken . '/getWebhookInfo',
            'GET'
        );

        return $response;
    }

    public function notifyAdmin($message, $priority = 'normal') {
        if (empty($this->adminChatId)) {
            return ['success' => false, 'error' => 'Admin chat ID not configured'];
        }

        $icon = match($priority) {
            'high' => '🚨',
            'critical' => '⚠️',
            'info' => 'ℹ️',
            'success' => '✅',
            default => '📢'
        };

        $formattedMessage = "{$icon} <b>Adil GFX Alert</b>\n\n{$message}";

        return $this->sendMessage($this->adminChatId, $formattedMessage, [
            'silent' => $priority === 'info'
        ]);
    }

    public function notifyNewLead($leadData) {
        $name = $leadData['name'] ?? 'Unknown';
        $email = $leadData['email'] ?? 'N/A';
        $phone = $leadData['phone'] ?? 'N/A';
        $source = $leadData['source'] ?? 'Direct';
        $message = $leadData['message'] ?? 'No message';

        $text = "🎯 <b>New Lead Received!</b>\n\n";
        $text .= "👤 <b>Name:</b> {$name}\n";
        $text .= "📧 <b>Email:</b> {$email}\n";
        $text .= "📱 <b>Phone:</b> {$phone}\n";
        $text .= "🌐 <b>Source:</b> {$source}\n\n";
        $text .= "💬 <b>Message:</b>\n{$message}\n\n";
        $text .= "⏰ <b>Time:</b> " . date('Y-m-d H:i:s');

        return $this->notifyAdmin($text, 'high');
    }

    public function notifyNewOrder($orderData) {
        $orderNumber = $orderData['order_number'] ?? 'N/A';
        $userName = $orderData['user_name'] ?? 'Unknown';
        $service = $orderData['service'] ?? 'Service';
        $amount = $orderData['amount'] ?? '0.00';
        $paymentMethod = $orderData['payment_method'] ?? 'Unknown';

        $text = "💰 <b>New Order Received!</b>\n\n";
        $text .= "📦 <b>Order #:</b> {$orderNumber}\n";
        $text .= "👤 <b>Customer:</b> {$userName}\n";
        $text .= "🎨 <b>Service:</b> {$service}\n";
        $text .= "💵 <b>Amount:</b> \${$amount}\n";
        $text .= "💳 <b>Payment:</b> {$paymentMethod}\n\n";
        $text .= "⏰ <b>Time:</b> " . date('Y-m-d H:i:s');

        return $this->notifyAdmin($text, 'high');
    }

    public function notifyPaymentReceived($paymentData) {
        $amount = $paymentData['amount'] ?? '0.00';
        $userName = $paymentData['user_name'] ?? 'Unknown';
        $method = $paymentData['method'] ?? 'Unknown';
        $transactionId = $paymentData['transaction_id'] ?? 'N/A';

        $text = "✅ <b>Payment Received!</b>\n\n";
        $text .= "💰 <b>Amount:</b> \${$amount}\n";
        $text .= "👤 <b>From:</b> {$userName}\n";
        $text .= "💳 <b>Method:</b> {$method}\n";
        $text .= "🔖 <b>Transaction ID:</b> {$transactionId}\n\n";
        $text .= "⏰ <b>Time:</b> " . date('Y-m-d H:i:s');

        return $this->notifyAdmin($text, 'success');
    }

    public function notifyFunnelTestStarted($testId, $source) {
        $text = "🧪 <b>Funnel Test Started</b>\n\n";
        $text .= "🔬 <b>Test ID:</b> {$testId}\n";
        $text .= "🌐 <b>Traffic Source:</b> {$source}\n\n";
        $text .= "⏰ <b>Started:</b> " . date('Y-m-d H:i:s');

        return $this->notifyAdmin($text, 'info');
    }

    public function notifyFunnelTestCompleted($testId, $results) {
        $success = $results['success'] ?? false;
        $stepsCompleted = $results['steps_completed'] ?? 0;
        $totalSteps = $results['total_steps'] ?? 0;
        $duration = $results['duration'] ?? 0;

        $icon = $success ? '✅' : '❌';

        $text = "{$icon} <b>Funnel Test Completed</b>\n\n";
        $text .= "🔬 <b>Test ID:</b> {$testId}\n";
        $text .= "📊 <b>Steps:</b> {$stepsCompleted}/{$totalSteps}\n";
        $text .= "⏱️ <b>Duration:</b> {$duration}s\n";
        $text .= "📈 <b>Status:</b> " . ($success ? 'Success' : 'Failed') . "\n\n";
        $text .= "⏰ <b>Completed:</b> " . date('Y-m-d H:i:s');

        return $this->notifyAdmin($text, $success ? 'success' : 'high');
    }

    public function notifySystemError($errorMessage, $context = []) {
        $text = "⚠️ <b>System Error</b>\n\n";
        $text .= "❌ <b>Error:</b> {$errorMessage}\n\n";

        if (!empty($context)) {
            $text .= "<b>Context:</b>\n";
            foreach ($context as $key => $value) {
                $text .= "• {$key}: {$value}\n";
            }
        }

        $text .= "\n⏰ <b>Time:</b> " . date('Y-m-d H:i:s');

        return $this->notifyAdmin($text, 'critical');
    }

    public function notifyAPIRateLimitWarning($apiName, $remaining) {
        $text = "⚠️ <b>API Rate Limit Warning</b>\n\n";
        $text .= "🔌 <b>API:</b> {$apiName}\n";
        $text .= "📊 <b>Remaining:</b> {$remaining} requests\n\n";
        $text .= "Please check the API usage and consider increasing limits or optimizing requests.\n\n";
        $text .= "⏰ <b>Time:</b> " . date('Y-m-d H:i:s');

        return $this->notifyAdmin($text, 'high');
    }

    private function logNotificationToDatabase($chatId, $type, $content, $responseData) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO telegram_notifications (
                    chat_id, notification_type, content, message_id, status, sent_at
                ) VALUES (?, ?, ?, ?, ?, NOW())
            ");

            $messageId = $responseData['result']['message_id'] ?? null;
            $status = $messageId ? 'sent' : 'failed';

            $stmt->execute([
                $chatId,
                $type,
                $content,
                $messageId,
                $status
            ]);
        } catch (Exception $e) {
            error_log("Failed to log Telegram notification to database: " . $e->getMessage());
        }
    }
}
