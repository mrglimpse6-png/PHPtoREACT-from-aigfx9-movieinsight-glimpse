<?php
/**
 * WhatsApp Business Cloud API Integration
 * Handles automated messages, notifications, and template messages
 */

require_once __DIR__ . '/APIIntegration.php';

class WhatsAppIntegration extends APIIntegration {
    private $accessToken;
    private $phoneNumberId;
    private $businessAccountId;
    private $apiUrl = 'https://graph.facebook.com/v18.0';

    public function __construct() {
        parent::__construct('WhatsApp Business');

        $this->accessToken = $this->getConfig('access_token') ?? $_ENV['WHATSAPP_ACCESS_TOKEN'] ?? '';
        $this->phoneNumberId = $this->getConfig('phone_number_id') ?? $_ENV['WHATSAPP_PHONE_NUMBER_ID'] ?? '';
        $this->businessAccountId = $this->getConfig('business_account_id') ?? $_ENV['WHATSAPP_BUSINESS_ACCOUNT_ID'] ?? '';
    }

    public function testConnection() {
        if (empty($this->accessToken) || empty($this->phoneNumberId)) {
            return [
                'success' => false,
                'error' => 'WhatsApp credentials not configured'
            ];
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/' . $this->phoneNumberId,
            'GET',
            null,
            $this->getHeaders()
        );

        return $response;
    }

    public function sendTextMessage($to, $message) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'WhatsApp integration is disabled'];
        }

        if (empty($this->accessToken) || empty($this->phoneNumberId)) {
            return ['success' => false, 'error' => 'WhatsApp credentials not configured'];
        }

        $to = $this->formatPhoneNumber($to);

        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'preview_url' => true,
                'body' => $message
            ]
        ];

        $response = $this->makeRequest(
            $this->apiUrl . '/' . $this->phoneNumberId . '/messages',
            'POST',
            $data,
            $this->getHeaders()
        );

        if ($response['success']) {
            $this->logMessageToDatabase($to, 'text', $message, $response['data']);
        }

        return $response;
    }

    public function sendTemplateMessage($to, $templateName, $languageCode = 'en_US', $components = []) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'WhatsApp integration is disabled'];
        }

        $to = $this->formatPhoneNumber($to);

        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $languageCode
                ]
            ]
        ];

        if (!empty($components)) {
            $data['template']['components'] = $components;
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/' . $this->phoneNumberId . '/messages',
            'POST',
            $data,
            $this->getHeaders()
        );

        if ($response['success']) {
            $this->logMessageToDatabase($to, 'template', $templateName, $response['data']);
        }

        return $response;
    }

    public function sendImageMessage($to, $imageUrl, $caption = '') {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'WhatsApp integration is disabled'];
        }

        $to = $this->formatPhoneNumber($to);

        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'image',
            'image' => [
                'link' => $imageUrl
            ]
        ];

        if (!empty($caption)) {
            $data['image']['caption'] = $caption;
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/' . $this->phoneNumberId . '/messages',
            'POST',
            $data,
            $this->getHeaders()
        );

        if ($response['success']) {
            $this->logMessageToDatabase($to, 'image', $imageUrl, $response['data']);
        }

        return $response;
    }

    public function sendDocumentMessage($to, $documentUrl, $filename, $caption = '') {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'WhatsApp integration is disabled'];
        }

        $to = $this->formatPhoneNumber($to);

        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'document',
            'document' => [
                'link' => $documentUrl,
                'filename' => $filename
            ]
        ];

        if (!empty($caption)) {
            $data['document']['caption'] = $caption;
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/' . $this->phoneNumberId . '/messages',
            'POST',
            $data,
            $this->getHeaders()
        );

        if ($response['success']) {
            $this->logMessageToDatabase($to, 'document', $filename, $response['data']);
        }

        return $response;
    }

    public function sendButtonMessage($to, $bodyText, $buttons) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'WhatsApp integration is disabled'];
        }

        $to = $this->formatPhoneNumber($to);

        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => [
                    'text' => $bodyText
                ],
                'action' => [
                    'buttons' => $buttons
                ]
            ]
        ];

        $response = $this->makeRequest(
            $this->apiUrl . '/' . $this->phoneNumberId . '/messages',
            'POST',
            $data,
            $this->getHeaders()
        );

        if ($response['success']) {
            $this->logMessageToDatabase($to, 'interactive_button', $bodyText, $response['data']);
        }

        return $response;
    }

    public function sendListMessage($to, $bodyText, $buttonText, $sections) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'WhatsApp integration is disabled'];
        }

        $to = $this->formatPhoneNumber($to);

        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'list',
                'body' => [
                    'text' => $bodyText
                ],
                'action' => [
                    'button' => $buttonText,
                    'sections' => $sections
                ]
            ]
        ];

        $response = $this->makeRequest(
            $this->apiUrl . '/' . $this->phoneNumberId . '/messages',
            'POST',
            $data,
            $this->getHeaders()
        );

        if ($response['success']) {
            $this->logMessageToDatabase($to, 'interactive_list', $bodyText, $response['data']);
        }

        return $response;
    }

    public function markMessageAsRead($messageId) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'WhatsApp integration is disabled'];
        }

        $data = [
            'messaging_product' => 'whatsapp',
            'status' => 'read',
            'message_id' => $messageId
        ];

        $response = $this->makeRequest(
            $this->apiUrl . '/' . $this->phoneNumberId . '/messages',
            'POST',
            $data,
            $this->getHeaders()
        );

        return $response;
    }

    public function getMessageTemplates() {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'WhatsApp integration is disabled'];
        }

        if (empty($this->businessAccountId)) {
            return ['success' => false, 'error' => 'Business account ID not configured'];
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/' . $this->businessAccountId . '/message_templates',
            'GET',
            null,
            $this->getHeaders()
        );

        return $response;
    }

    public function sendWelcomeMessage($to, $userName) {
        $message = "Hi {$userName}! 👋\n\n";
        $message .= "Welcome to Adil GFX! We're thrilled to have you join our creative community.\n\n";
        $message .= "You've received 100 welcome tokens to get started! 🎁\n\n";
        $message .= "Here's what you can do:\n";
        $message .= "✨ Browse our portfolio\n";
        $message .= "🎨 Explore services\n";
        $message .= "💬 Chat with us anytime\n";
        $message .= "🚀 Start your first project\n\n";
        $message .= "Need help? Just reply to this message!\n\n";
        $message .= "Best regards,\nAdil & Team";

        return $this->sendTextMessage($to, $message);
    }

    public function sendOrderConfirmation($to, $userName, $orderDetails) {
        $orderNumber = $orderDetails['order_number'] ?? 'N/A';
        $service = $orderDetails['service'] ?? 'Service';
        $amount = $orderDetails['amount'] ?? '0.00';

        $message = "Hi {$userName}! ✅\n\n";
        $message .= "Your order has been confirmed!\n\n";
        $message .= "📦 *Order Details*\n";
        $message .= "Order #: {$orderNumber}\n";
        $message .= "Service: {$service}\n";
        $message .= "Amount: \${$amount}\n\n";
        $message .= "🎉 What's next?\n";
        $message .= "1. Our team will review your requirements\n";
        $message .= "2. We'll start working within 24 hours\n";
        $message .= "3. You'll receive regular updates\n";
        $message .= "4. Final files will be delivered to your dashboard\n\n";
        $message .= "Track your order: https://adilgfx.com/dashboard\n\n";
        $message .= "Thank you for choosing Adil GFX!";

        return $this->sendTextMessage($to, $message);
    }

    public function sendLeadFollowUp($to, $userName) {
        $message = "Hi {$userName}! 👋\n\n";
        $message .= "I noticed you visited Adil GFX recently. Do you have any questions about our services?\n\n";
        $message .= "We specialize in:\n";
        $message .= "🎨 Logo & Brand Design\n";
        $message .= "🌐 Website Development\n";
        $message .= "📱 Social Media Graphics\n";
        $message .= "🎬 Video Editing\n\n";
        $message .= "Reply with your project details and let's create something amazing together!\n\n";
        $message .= "- Adil";

        return $this->sendTextMessage($to, $message);
    }

    public function sendProjectUpdate($to, $userName, $projectName, $status, $notes = '') {
        $message = "Hi {$userName}! 📢\n\n";
        $message .= "Update on your project: *{$projectName}*\n\n";
        $message .= "Status: {$status}\n";

        if (!empty($notes)) {
            $message .= "\n{$notes}\n";
        }

        $message .= "\nView details: https://adilgfx.com/dashboard\n\n";
        $message .= "Questions? Just reply to this message!\n\n";
        $message .= "Best regards,\nAdil GFX Team";

        return $this->sendTextMessage($to, $message);
    }

    private function getHeaders() {
        return [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json'
        ];
    }

    private function formatPhoneNumber($phoneNumber) {
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        if (strlen($phoneNumber) === 10) {
            $phoneNumber = '1' . $phoneNumber;
        }

        return $phoneNumber;
    }

    private function logMessageToDatabase($to, $type, $content, $responseData) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO whatsapp_messages (
                    recipient, message_type, content, message_id, status, sent_at
                ) VALUES (?, ?, ?, ?, ?, NOW())
            ");

            $messageId = $responseData['messages'][0]['id'] ?? null;
            $status = $messageId ? 'sent' : 'failed';

            $stmt->execute([
                $to,
                $type,
                $content,
                $messageId,
                $status
            ]);
        } catch (Exception $e) {
            error_log("Failed to log WhatsApp message to database: " . $e->getMessage());
        }
    }
}
