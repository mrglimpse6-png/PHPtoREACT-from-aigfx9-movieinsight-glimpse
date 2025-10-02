<?php
/**
 * SendGrid Email Integration
 * Handles transactional emails, newsletters, and templates
 */

require_once __DIR__ . '/APIIntegration.php';

class SendGridIntegration extends APIIntegration {
    private $apiKey;
    private $fromEmail;
    private $fromName;
    private $apiUrl = 'https://api.sendgrid.com/v3';

    public function __construct() {
        parent::__construct('SendGrid Email');

        // Load configuration
        $this->apiKey = $this->getConfig('api_key') ?? $_ENV['SENDGRID_API_KEY'] ?? '';
        $this->fromEmail = $this->getConfig('from_email') ?? $_ENV['FROM_EMAIL'] ?? '';
        $this->fromName = $this->getConfig('from_name') ?? $_ENV['FROM_NAME'] ?? 'Adil GFX';
    }

    /**
     * Test SendGrid API connection
     */
    public function testConnection() {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'API key not configured'
            ];
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/user/profile',
            'GET',
            null,
            $this->getHeaders()
        );

        return $response;
    }

    /**
     * Send a single email
     */
    public function sendEmail($to, $subject, $content, $options = []) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'SendGrid integration is disabled'];
        }

        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'SendGrid API key not configured'];
        }

        $data = [
            'personalizations' => [
                [
                    'to' => [
                        [
                            'email' => $to,
                            'name' => $options['to_name'] ?? ''
                        ]
                    ],
                    'subject' => $subject
                ]
            ],
            'from' => [
                'email' => $options['from_email'] ?? $this->fromEmail,
                'name' => $options['from_name'] ?? $this->fromName
            ],
            'content' => [
                [
                    'type' => $options['content_type'] ?? 'text/html',
                    'value' => $content
                ]
            ]
        ];

        // Add CC if provided
        if (!empty($options['cc'])) {
            $data['personalizations'][0]['cc'] = [
                ['email' => $options['cc']]
            ];
        }

        // Add BCC if provided
        if (!empty($options['bcc'])) {
            $data['personalizations'][0]['bcc'] = [
                ['email' => $options['bcc']]
            ];
        }

        // Add reply-to if provided
        if (!empty($options['reply_to'])) {
            $data['reply_to'] = [
                'email' => $options['reply_to']
            ];
        }

        // Add template ID if provided
        if (!empty($options['template_id'])) {
            $data['template_id'] = $options['template_id'];
            if (!empty($options['dynamic_template_data'])) {
                $data['personalizations'][0]['dynamic_template_data'] = $options['dynamic_template_data'];
            }
        }

        // Add custom headers if provided
        if (!empty($options['headers'])) {
            $data['headers'] = $options['headers'];
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/mail/send',
            'POST',
            $data,
            $this->getHeaders()
        );

        return $response;
    }

    /**
     * Send bulk emails (batch send)
     */
    public function sendBulkEmails($recipients, $subject, $content, $options = []) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'SendGrid integration is disabled'];
        }

        $personalizations = [];
        foreach ($recipients as $recipient) {
            $personalizations[] = [
                'to' => [
                    [
                        'email' => $recipient['email'],
                        'name' => $recipient['name'] ?? ''
                    ]
                ],
                'subject' => $subject,
                'dynamic_template_data' => $recipient['data'] ?? []
            ];
        }

        $data = [
            'personalizations' => $personalizations,
            'from' => [
                'email' => $this->fromEmail,
                'name' => $this->fromName
            ],
            'content' => [
                [
                    'type' => 'text/html',
                    'value' => $content
                ]
            ]
        ];

        if (!empty($options['template_id'])) {
            $data['template_id'] = $options['template_id'];
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/mail/send',
            'POST',
            $data,
            $this->getHeaders()
        );

        return $response;
    }

    /**
     * Send template email
     */
    public function sendTemplateEmail($to, $templateId, $templateData = [], $options = []) {
        return $this->sendEmail(
            $to,
            $options['subject'] ?? 'Email from Adil GFX',
            '',
            array_merge($options, [
                'template_id' => $templateId,
                'dynamic_template_data' => $templateData
            ])
        );
    }

    /**
     * Get email statistics
     */
    public function getEmailStats($startDate, $endDate) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'SendGrid integration is disabled'];
        }

        $params = http_build_query([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'aggregated_by' => 'day'
        ]);

        $response = $this->makeRequest(
            $this->apiUrl . '/stats?' . $params,
            'GET',
            null,
            $this->getHeaders()
        );

        return $response;
    }

    /**
     * Get suppression list (bounces, blocks, spam reports)
     */
    public function getSuppressionList($type = 'bounces') {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'SendGrid integration is disabled'];
        }

        $validTypes = ['bounces', 'blocks', 'spam_reports', 'invalid_emails'];
        if (!in_array($type, $validTypes)) {
            return ['success' => false, 'error' => 'Invalid suppression type'];
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/suppression/' . $type,
            'GET',
            null,
            $this->getHeaders()
        );

        return $response;
    }

    /**
     * Remove email from suppression list
     */
    public function removeFromSuppression($email, $type = 'bounces') {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'SendGrid integration is disabled'];
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/suppression/' . $type . '/' . $email,
            'DELETE',
            null,
            $this->getHeaders()
        );

        return $response;
    }

    /**
     * Get SendGrid templates
     */
    public function getTemplates() {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'SendGrid integration is disabled'];
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/templates?generations=dynamic',
            'GET',
            null,
            $this->getHeaders()
        );

        return $response;
    }

    /**
     * Create new template
     */
    public function createTemplate($name, $generation = 'dynamic') {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'SendGrid integration is disabled'];
        }

        $data = [
            'name' => $name,
            'generation' => $generation
        ];

        $response = $this->makeRequest(
            $this->apiUrl . '/templates',
            'POST',
            $data,
            $this->getHeaders()
        );

        return $response;
    }

    /**
     * Get request headers
     */
    private function getHeaders() {
        return [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ];
    }

    /**
     * Helper: Send welcome email (used in funnel)
     */
    public function sendWelcomeEmail($userEmail, $userName, $tokenBalance = 100) {
        $subject = 'Welcome to Adil GFX! 🎉';
        $content = $this->getWelcomeEmailTemplate($userName, $tokenBalance);

        return $this->sendEmail($userEmail, $subject, $content, [
            'to_name' => $userName
        ]);
    }

    /**
     * Helper: Send order confirmation email (used in funnel)
     */
    public function sendOrderConfirmation($userEmail, $userName, $orderDetails) {
        $subject = 'Order Confirmation - Adil GFX';
        $content = $this->getOrderConfirmationTemplate($userName, $orderDetails);

        return $this->sendEmail($userEmail, $subject, $content, [
            'to_name' => $userName
        ]);
    }

    /**
     * Helper: Send contact form auto-reply
     */
    public function sendContactAutoReply($userEmail, $userName, $message) {
        $subject = 'Thank you for contacting Adil GFX';
        $content = $this->getContactAutoReplyTemplate($userName, $message);

        return $this->sendEmail($userEmail, $subject, $content, [
            'to_name' => $userName
        ]);
    }

    /**
     * Welcome email HTML template
     */
    private function getWelcomeEmailTemplate($userName, $tokenBalance) {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #fff; padding: 30px; border: 1px solid #e0e0e0; border-top: none; }
        .tokens { background: #f7fafc; border-left: 4px solid #48bb78; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to Adil GFX!</h1>
        </div>
        <div class="content">
            <p>Hi <strong>{$userName}</strong>,</p>

            <p>Thank you for joining Adil GFX! We're excited to have you as part of our creative community.</p>

            <div class="tokens">
                <h3>🎁 Welcome Bonus!</h3>
                <p>You've received <strong>{$tokenBalance} tokens</strong> to get started. Use them to explore our services or save them for future projects!</p>
            </div>

            <p><strong>What's next?</strong></p>
            <ul>
                <li>Browse our portfolio for inspiration</li>
                <li>Check out our services and pricing</li>
                <li>Start your first project</li>
                <li>Refer friends and earn more tokens</li>
            </ul>

            <a href="https://adilgfx.com/dashboard" class="button">Go to Dashboard</a>

            <p>If you have any questions, feel free to reach out. We're here to help!</p>

            <p>Best regards,<br>
            <strong>Adil & Team</strong><br>
            Adil GFX</p>
        </div>
        <div class="footer">
            <p>© 2025 Adil GFX. All rights reserved.</p>
            <p><a href="https://adilgfx.com">Visit Website</a> | <a href="https://adilgfx.com/contact">Contact Us</a></p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Order confirmation email HTML template
     */
    private function getOrderConfirmationTemplate($userName, $orderDetails) {
        $orderNumber = $orderDetails['order_number'] ?? 'N/A';
        $amount = $orderDetails['amount'] ?? '0.00';
        $service = $orderDetails['service'] ?? 'Service';

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #48bb78; color: white; padding: 30px; text-align: center; }
        .content { background: #fff; padding: 30px; border: 1px solid #e0e0e0; }
        .order-details { background: #f7fafc; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✓ Order Confirmed!</h1>
        </div>
        <div class="content">
            <p>Hi <strong>{$userName}</strong>,</p>

            <p>Thank you for your order! We've received your payment and are excited to start working on your project.</p>

            <div class="order-details">
                <h3>Order Details</h3>
                <p><strong>Order Number:</strong> #{$orderNumber}</p>
                <p><strong>Service:</strong> {$service}</p>
                <p><strong>Amount:</strong> \${$amount}</p>
            </div>

            <p><strong>What happens next?</strong></p>
            <ol>
                <li>Our team will review your requirements</li>
                <li>We'll start working on your project within 24 hours</li>
                <li>You'll receive regular updates on progress</li>
                <li>We'll deliver the final files to your dashboard</li>
            </ol>

            <p>You can track your order status anytime from your dashboard.</p>

            <p>Best regards,<br>
            <strong>Adil & Team</strong></p>
        </div>
        <div class="footer">
            <p>© 2025 Adil GFX. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Contact form auto-reply template
     */
    private function getContactAutoReplyTemplate($userName, $userMessage) {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #667eea; color: white; padding: 20px; text-align: center; }
        .content { background: #fff; padding: 30px; border: 1px solid #e0e0e0; }
        .message-box { background: #f7fafc; padding: 15px; border-left: 4px solid #667eea; margin: 20px 0; font-style: italic; }
        .footer { text-align: center; padding: 20px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Thank You for Contacting Us!</h1>
        </div>
        <div class="content">
            <p>Hi <strong>{$userName}</strong>,</p>

            <p>Thank you for reaching out to Adil GFX! We've received your message and will get back to you within 2 hours.</p>

            <div class="message-box">
                <strong>Your message:</strong><br>
                {$userMessage}
            </div>

            <p>In the meantime, feel free to:</p>
            <ul>
                <li>Check out our <a href="https://adilgfx.com/portfolio">portfolio</a></li>
                <li>Browse our <a href="https://adilgfx.com/services">services</a></li>
                <li>Read our <a href="https://adilgfx.com/blog">blog</a> for tips and insights</li>
            </ul>

            <p>Best regards,<br>
            <strong>Adil & Team</strong></p>
        </div>
        <div class="footer">
            <p>© 2025 Adil GFX. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
