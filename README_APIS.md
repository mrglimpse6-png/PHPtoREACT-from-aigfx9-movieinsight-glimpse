# API Integrations Documentation

Complete guide to all external API integrations in the Adil GFX platform. This document covers setup, usage, free-tier limits, and examples for each integrated service.

---

## Table of Contents

1. [SendGrid Email Service](#1-sendgrid-email-service)
2. [WhatsApp Business Cloud API](#2-whatsapp-business-cloud-api)
3. [Telegram Bot API](#3-telegram-bot-api)
4. [Stripe Payment Processing](#4-stripe-payment-processing)
5. [Coinbase Commerce (Crypto Payments)](#5-coinbase-commerce)
6. [Google Search Console](#6-google-search-console)
7. [PageSpeed Insights](#7-pagespeed-insights)
8. [Admin Panel API Management](#8-admin-panel-api-management)

---

## 1. SendGrid Email Service

### Purpose
Handles all transactional emails including welcome messages, order confirmations, contact form auto-replies, and newsletter communications.

### Role in Funnel
- **Signup Stage**: Sends welcome email with token balance
- **Engagement Stage**: Automated follow-up sequences
- **Post-Purchase**: Order confirmations and project updates

### Free Tier Limits
- **100 emails/day** on free plan
- **40,000 emails/month** on Essentials plan ($19.95/mo)
- No credit card required for free tier

### Setup Instructions

#### Step 1: Get API Key
1. Sign up at [SendGrid.com](https://sendgrid.com)
2. Navigate to **Settings → API Keys**
3. Click "Create API Key"
4. Select "Full Access" permissions
5. Copy the generated API key

#### Step 2: Configure Environment
Add to `/backend/.env`:
```env
SENDGRID_API_KEY=SG.xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
FROM_EMAIL=hello@adilgfx.com
FROM_NAME=Adil GFX
```

#### Step 3: Enable in Admin Panel
1. Login to admin panel: `/backend/admin/`
2. Go to **API Integrations**
3. Find **SendGrid Email**
4. Toggle **Enabled**
5. Click **Test Connection** to verify

### PHP Usage Example

```php
<?php
require_once 'classes/SendGridIntegration.php';

$sendGrid = new SendGridIntegration();

// Send welcome email
$result = $sendGrid->sendWelcomeEmail(
    'user@example.com',
    'John Doe',
    100 // token balance
);

// Send custom email
$result = $sendGrid->sendEmail(
    'customer@example.com',
    'Your Project Update',
    '<h1>Hello!</h1><p>Your design is ready for review.</p>',
    [
        'to_name' => 'Jane Smith',
        'reply_to' => 'support@adilgfx.com'
    ]
);

// Send bulk emails
$recipients = [
    ['email' => 'user1@example.com', 'name' => 'User 1'],
    ['email' => 'user2@example.com', 'name' => 'User 2']
];

$result = $sendGrid->sendBulkEmails(
    $recipients,
    'Monthly Newsletter',
    '<h1>Latest Updates</h1><p>Check out our new services!</p>'
);

if ($result['success']) {
    echo "Email sent successfully!";
} else {
    echo "Error: " . $result['error'];
}
?>
```

### API Response Example
```json
{
  "success": true,
  "data": {
    "id": "msg_xxxxxxxxxxxxx",
    "status": "queued"
  }
}
```

---

## 2. WhatsApp Business Cloud API

### Purpose
Enables automated WhatsApp messages for real-time customer engagement, order notifications, and support.

### Role in Funnel
- **Engagement Stage**: Welcome message after signup
- **Service Selection**: Quick inquiry responses
- **Post-Purchase**: Order confirmations and updates

### Free Tier Limits
- **1,000 conversations/month** free (Meta Business)
- **Conversation = 24-hour window** after user message
- Business-initiated messages require approved templates

### Setup Instructions

#### Step 1: Create Meta Business Account
1. Go to [Meta for Developers](https://developers.facebook.com/)
2. Create an app → Select **Business**
3. Add **WhatsApp** product
4. Complete business verification

#### Step 2: Get Credentials
1. Navigate to **WhatsApp → API Setup**
2. Copy **Phone Number ID**
3. Generate **Permanent Access Token** (Settings → System Users)
4. Copy **Business Account ID**

#### Step 3: Configure Environment
Add to `/backend/.env`:
```env
WHATSAPP_ACCESS_TOKEN=EAAxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
WHATSAPP_PHONE_NUMBER_ID=123456789012345
WHATSAPP_BUSINESS_ACCOUNT_ID=987654321098765
```

#### Step 4: Verify Phone Number
1. In Meta dashboard, add your phone number
2. Complete verification via SMS
3. Test by sending a test message

### PHP Usage Example

```php
<?php
require_once 'classes/WhatsAppIntegration.php';

$whatsApp = new WhatsAppIntegration();

// Send welcome message
$result = $whatsApp->sendWelcomeMessage(
    '+12345678900',
    'John Doe'
);

// Send order confirmation
$orderDetails = [
    'order_number' => 'ORD-12345',
    'service' => 'Logo Design',
    'amount' => '299.00'
];

$result = $whatsApp->sendOrderConfirmation(
    '+12345678900',
    'Jane Smith',
    $orderDetails
);

// Send custom text message
$result = $whatsApp->sendTextMessage(
    '+12345678900',
    'Your design is ready for review! Check your dashboard: https://adilgfx.com/dashboard'
);

// Send image with caption
$result = $whatsApp->sendImageMessage(
    '+12345678900',
    'https://adilgfx.com/uploads/design-preview.jpg',
    'Here\'s your logo design preview!'
);

if ($result['success']) {
    echo "WhatsApp message sent!";
    echo "Message ID: " . $result['data']['messages'][0]['id'];
} else {
    echo "Error: " . $result['error'];
}
?>
```

### API Response Example
```json
{
  "success": true,
  "data": {
    "messaging_product": "whatsapp",
    "contacts": [
      {
        "input": "+12345678900",
        "wa_id": "12345678900"
      }
    ],
    "messages": [
      {
        "id": "wamid.HBgNMTIzNDU2Nzg5MDAVAgARGBI5QUFEMEU5RjQyM0Y0RTVGRkEA"
      }
    ]
  }
}
```

---

## 3. Telegram Bot API

### Purpose
Admin notifications for new leads, orders, payments, system errors, and funnel test results.

### Role in Funnel
- **All Stages**: Real-time admin alerts
- **Funnel Testing**: Start/complete notifications
- **Monitoring**: System error alerts and API warnings

### Free Tier Limits
- **100% Free** - No rate limits for most use cases
- **30 messages/second** per bot
- **Unlimited messages** to individual chats

### Setup Instructions

#### Step 1: Create Bot
1. Open Telegram and search for **@BotFather**
2. Send `/newbot` command
3. Choose bot name and username
4. Copy the **Bot Token**

#### Step 2: Get Chat ID
1. Start a conversation with your bot
2. Send any message to the bot
3. Visit: `https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates`
4. Find `"chat":{"id":123456789}` in the response
5. Copy your **Chat ID**

#### Step 3: Configure Environment
Add to `/backend/.env`:
```env
TELEGRAM_BOT_TOKEN=123456789:ABCdefGHIjklMNOpqrsTUVwxyz123456789
TELEGRAM_ADMIN_CHAT_ID=123456789
```

### PHP Usage Example

```php
<?php
require_once 'classes/TelegramIntegration.php';

$telegram = new TelegramIntegration();

// Notify admin about new lead
$leadData = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '+12345678900',
    'source' => 'google',
    'message' => 'Interested in logo design'
];

$result = $telegram->notifyNewLead($leadData);

// Notify about new order
$orderData = [
    'order_number' => 'ORD-12345',
    'user_name' => 'Jane Smith',
    'service' => 'Website Development',
    'amount' => '1499.00',
    'payment_method' => 'Stripe'
];

$result = $telegram->notifyNewOrder($orderData);

// Notify about payment
$paymentData = [
    'amount' => '299.00',
    'user_name' => 'John Doe',
    'method' => 'Stripe',
    'transaction_id' => 'pi_xxxxxxxxxx'
];

$result = $telegram->notifyPaymentReceived($paymentData);

// Send custom alert
$result = $telegram->notifyAdmin(
    "Server backup completed successfully!",
    'success'
);

// System error notification
$result = $telegram->notifySystemError(
    'Database connection timeout',
    [
        'server' => 'db-prod-01',
        'timeout' => '30s',
        'retry_count' => '3'
    ]
);

if ($result['success']) {
    echo "Telegram notification sent!";
} else {
    echo "Error: " . $result['error'];
}
?>
```

### API Response Example
```json
{
  "success": true,
  "data": {
    "ok": true,
    "result": {
      "message_id": 12345,
      "from": {
        "id": 123456789,
        "is_bot": true,
        "first_name": "Adil GFX Bot"
      },
      "chat": {
        "id": 987654321,
        "type": "private"
      },
      "date": 1704067200,
      "text": "🎯 New Lead Received!\n\n👤 Name: John Doe..."
    }
  }
}
```

---

## 4. Stripe Payment Processing

### Purpose
Primary payment processor for credit/debit cards, Apple Pay, Google Pay, and other payment methods.

### Role in Funnel
- **Checkout Stage**: Payment intent creation
- **Payment Processing**: Card tokenization and charging
- **Post-Payment**: Webhook handling for confirmations

### Free Tier Limits
- **No monthly fees** on standard pricing
- **2.9% + $0.30** per successful card charge
- **Test mode** unlimited transactions (no real charges)

### Setup Instructions

#### Step 1: Create Stripe Account
1. Sign up at [Stripe.com](https://stripe.com)
2. Complete business verification (for live mode)
3. Get **Test Mode** keys first (no verification needed)

#### Step 2: Get API Keys
1. Navigate to **Developers → API Keys**
2. Copy **Publishable Key** (starts with `pk_test_` or `pk_live_`)
3. Copy **Secret Key** (starts with `sk_test_` or `sk_live_`)

#### Step 3: Configure Webhook
1. Go to **Developers → Webhooks**
2. Click **Add Endpoint**
3. Endpoint URL: `https://yourdomain.com/backend/api/webhooks/stripe.php`
4. Select events: `payment_intent.succeeded`, `checkout.session.completed`
5. Copy **Webhook Secret** (starts with `whsec_`)

#### Step 4: Configure Environment
Add to `/backend/.env`:
```env
STRIPE_SECRET_KEY=sk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
STRIPE_PUBLISHABLE_KEY=pk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

### PHP Usage Example

```php
<?php
require_once 'classes/StripeIntegration.php';

$stripe = new StripeIntegration();

// Create payment intent
$result = $stripe->createPaymentIntent(
    299.00, // amount in dollars
    'usd',
    [
        'customer_email' => 'customer@example.com',
        'customer_name' => 'John Doe',
        'service' => 'Logo Design'
    ]
);

if ($result['success']) {
    $clientSecret = $result['data']['client_secret'];
    // Return client secret to frontend for payment confirmation
}

// Create checkout session (hosted payment page)
$lineItems = [
    [
        'name' => 'Premium Logo Design',
        'amount' => 299.00,
        'currency' => 'usd',
        'quantity' => 1,
        'description' => '3 concepts, unlimited revisions'
    ]
];

$result = $stripe->createCheckoutSession(
    $lineItems,
    [
        'success_url' => 'https://adilgfx.com/success?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'https://adilgfx.com/cancel',
        'customer_email' => 'customer@example.com'
    ]
);

if ($result['success']) {
    $checkoutUrl = $result['data']['url'];
    // Redirect user to $checkoutUrl
}

// Retrieve payment intent
$result = $stripe->retrievePaymentIntent('pi_xxxxxxxxxx');

// Create refund
$result = $stripe->createRefund(
    'pi_xxxxxxxxxx',
    50.00, // partial refund amount (optional)
    'requested_by_customer' // reason (optional)
);

// List recent charges
$result = $stripe->listCharges(10);

if ($result['success']) {
    foreach ($result['data']['data'] as $charge) {
        echo "Charge: {$charge['id']} - Amount: {$charge['amount']}\n";
    }
}
?>
```

### API Response Example
```json
{
  "success": true,
  "data": {
    "id": "pi_3ABCDEfghIJKL1234567890",
    "object": "payment_intent",
    "amount": 29900,
    "currency": "usd",
    "status": "requires_payment_method",
    "client_secret": "pi_3ABC_secret_DEFghiJKLmnoPQRstuVWXyz",
    "metadata": {
      "customer_email": "customer@example.com",
      "service": "Logo Design"
    }
  }
}
```

---

## 5. Coinbase Commerce

### Purpose
Accept cryptocurrency payments (Bitcoin, Ethereum, USDC, etc.) for clients preferring crypto.

### Role in Funnel
- **Checkout Stage**: Alternative payment method
- **Payment Processing**: Crypto charge creation
- **Post-Payment**: Webhook handling for confirmations

### Free Tier Limits
- **1% fee** on all cryptocurrency transactions
- **No monthly fees**
- **Unlimited transactions**

### Setup Instructions

#### Step 1: Create Coinbase Commerce Account
1. Sign up at [commerce.coinbase.com](https://commerce.coinbase.com)
2. Complete business verification
3. Set up withdrawal preferences

#### Step 2: Get API Key
1. Go to **Settings → API Keys**
2. Click **Create an API Key**
3. Select appropriate permissions
4. Copy the **API Key**

#### Step 3: Configure Webhook
1. Go to **Settings → Webhook**
2. Add webhook URL: `https://yourdomain.com/backend/api/webhooks/coinbase.php`
3. Copy **Webhook Secret**

#### Step 4: Configure Environment
Add to `/backend/.env`:
```env
COINBASE_API_KEY=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
COINBASE_WEBHOOK_SECRET=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
```

### PHP Usage Example

```php
<?php
require_once 'classes/CoinbaseIntegration.php';

$coinbase = new CoinbaseIntegration();

// Create charge
$result = $coinbase->createCharge(
    'Premium Logo Design',
    'Professional logo design with 3 concepts',
    299.00,
    'USD',
    [
        'customer_email' => 'customer@example.com',
        'customer_name' => 'John Doe'
    ]
);

if ($result['success']) {
    $hostedUrl = $result['data']['hosted_url'];
    // Redirect user to $hostedUrl for payment

    $chargeCode = $result['data']['code'];
    // Save $chargeCode to track payment
}

// Retrieve charge
$result = $coinbase->retrieveCharge('ABCDEF12');

if ($result['success']) {
    $status = $result['data']['timeline'][0]['status'];
    echo "Payment status: {$status}";
}

// List all charges
$result = $coinbase->listCharges(10);

if ($result['success']) {
    foreach ($result['data']['data'] as $charge) {
        echo "Charge: {$charge['code']} - Amount: {$charge['pricing']['local']['amount']}\n";
    }
}
?>
```

### API Response Example
```json
{
  "success": true,
  "data": {
    "id": "abc123-def4-5678-90gh-ijklmn123456",
    "code": "ABCDEF12",
    "name": "Premium Logo Design",
    "description": "Professional logo design with 3 concepts",
    "hosted_url": "https://commerce.coinbase.com/charges/ABCDEF12",
    "pricing": {
      "local": {
        "amount": "299.00",
        "currency": "USD"
      },
      "bitcoin": {
        "amount": "0.00645000",
        "currency": "BTC"
      }
    },
    "timeline": [
      {
        "time": "2025-01-01T12:00:00Z",
        "status": "NEW"
      }
    ]
  }
}
```

---

## 6. Google Search Console

### Purpose
Monitor website search performance, indexing status, and SEO health.

### Role in Funnel
- **Top-of-Funnel**: Track organic search traffic sources
- **SEO Optimization**: Identify ranking opportunities
- **Performance Monitoring**: Page indexing status

### Free Tier Limits
- **100% Free** - No limits
- **Full access** to all features
- **Real-time data** with 16-month history

### Setup Instructions

#### Step 1: Verify Website Ownership
1. Go to [Google Search Console](https://search.google.com/search-console)
2. Add property: `https://adilgfx.com`
3. Verify via DNS record or HTML file upload

#### Step 2: Enable API Access
1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Create new project: **Adil GFX**
3. Enable **Search Console API**
4. Create service account
5. Download JSON credentials

#### Step 3: Configure Environment
Add to `/backend/.env`:
```env
GOOGLE_SEARCH_CONSOLE_CREDENTIALS=/path/to/credentials.json
GOOGLE_SEARCH_CONSOLE_SITE_URL=https://adilgfx.com
```

### PHP Usage Example

```php
<?php
require_once 'classes/GoogleSearchConsoleIntegration.php';

$gsc = new GoogleSearchConsoleIntegration();

// Get search analytics (last 30 days)
$result = $gsc->getSearchAnalytics(
    date('Y-m-d', strtotime('-30 days')),
    date('Y-m-d'),
    ['query', 'page'],
    10 // limit
);

if ($result['success']) {
    foreach ($result['data']['rows'] as $row) {
        echo "Query: {$row['keys'][0]}\n";
        echo "Clicks: {$row['clicks']}\n";
        echo "Impressions: {$row['impressions']}\n";
        echo "CTR: " . ($row['ctr'] * 100) . "%\n";
        echo "Position: {$row['position']}\n\n";
    }
}

// Get site indexing status
$result = $gsc->getIndexingStatus();

if ($result['success']) {
    echo "Total indexed pages: " . $result['data']['indexed_count'];
}

// Submit URL for indexing
$result = $gsc->requestIndexing('https://adilgfx.com/blog/new-post');
?>
```

---

## 7. PageSpeed Insights

### Purpose
Analyze website performance, get Core Web Vitals metrics, and optimization suggestions.

### Role in Funnel
- **Pre-Funnel**: Ensure fast page load times
- **Optimization**: Identify performance bottlenecks
- **Monitoring**: Track performance over time

### Free Tier Limits
- **25,000 requests/day** free
- **100 requests/100 seconds** rate limit
- **Full Core Web Vitals** data included

### Setup Instructions

#### Step 1: Get API Key
1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Enable **PageSpeed Insights API**
3. Create API credentials → **API Key**
4. Restrict key to PageSpeed Insights API

#### Step 2: Configure Environment
Add to `/backend/.env`:
```env
PAGESPEED_API_KEY=AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
```

### PHP Usage Example

```php
<?php
require_once 'classes/PageSpeedInsightsIntegration.php';

$pageSpeed = new PageSpeedInsightsIntegration();

// Analyze page performance
$result = $pageSpeed->analyzeUrl(
    'https://adilgfx.com',
    'mobile' // or 'desktop'
);

if ($result['success']) {
    $scores = $result['data']['lighthouseResult']['categories'];

    echo "Performance Score: " . ($scores['performance']['score'] * 100) . "\n";
    echo "Accessibility Score: " . ($scores['accessibility']['score'] * 100) . "\n";
    echo "Best Practices Score: " . ($scores['best-practices']['score'] * 100) . "\n";
    echo "SEO Score: " . ($scores['seo']['score'] * 100) . "\n";

    // Core Web Vitals
    $cwv = $result['data']['loadingExperience']['metrics'];
    echo "\nCore Web Vitals:\n";
    echo "LCP: " . $cwv['LARGEST_CONTENTFUL_PAINT_MS']['percentile'] . "ms\n";
    echo "FID: " . $cwv['FIRST_INPUT_DELAY_MS']['percentile'] . "ms\n";
    echo "CLS: " . $cwv['CUMULATIVE_LAYOUT_SHIFT_SCORE']['percentile'] . "\n";
}

// Get optimization suggestions
if ($result['success']) {
    $audits = $result['data']['lighthouseResult']['audits'];
    foreach ($audits as $audit) {
        if (isset($audit['score']) && $audit['score'] < 1) {
            echo "Issue: {$audit['title']}\n";
            echo "Suggestion: {$audit['description']}\n\n";
        }
    }
}
?>
```

### API Response Example
```json
{
  "success": true,
  "data": {
    "lighthouseResult": {
      "categories": {
        "performance": {
          "score": 0.95
        }
      },
      "audits": {
        "first-contentful-paint": {
          "score": 1,
          "displayValue": "0.8 s"
        }
      }
    },
    "loadingExperience": {
      "metrics": {
        "LARGEST_CONTENTFUL_PAINT_MS": {
          "percentile": 1200
        }
      }
    }
  }
}
```

---

## 8. Admin Panel API Management

### Enable/Disable APIs

All API integrations can be controlled from the admin panel without code changes.

#### Access API Management
1. Login to admin panel: `https://yourdomain.com/backend/admin/`
2. Navigate to **API Integrations** section
3. View status of all integrated APIs

#### Toggle API Status

Each API integration has:
- **Enabled Toggle**: Enable/disable API calls
- **Test Connection**: Verify credentials work
- **View Logs**: See recent API calls and errors
- **Usage Stats**: Monitor API call volume

#### API Configuration

Update API settings via admin panel:

```php
// Set API configuration
POST /api/settings.php
{
  "api_integrations": {
    "sendgrid": {
      "enabled": true,
      "api_key": "SG.xxxxxx",
      "from_email": "hello@adilgfx.com"
    },
    "stripe": {
      "enabled": true,
      "secret_key": "sk_test_xxxxxx",
      "publishable_key": "pk_test_xxxxxx"
    }
  }
}
```

#### Test All APIs

Run comprehensive API test:

```bash
php backend/scripts/test_api_endpoints.php
```

Output shows connection status for each API:
```
Testing API Integrations...

✓ SendGrid: Connected (Rate Limit: 100/day remaining)
✓ WhatsApp: Connected (Phone: +1234567890)
✓ Telegram: Connected (Bot: @AdilGFXBot)
✓ Stripe: Connected (Mode: Test)
✓ Coinbase: Connected (Account verified)
✗ Google Search Console: Not configured
✗ PageSpeed Insights: API key missing
```

---

## API Rate Limits Summary

| API | Free Tier | Rate Limit | Overage Cost |
|-----|-----------|------------|--------------|
| SendGrid | 100/day | N/A | $19.95/mo for 40k |
| WhatsApp | 1,000 conv/mo | N/A | $0.005-0.09/conv |
| Telegram | Unlimited | 30 msg/sec | Free |
| Stripe | Unlimited | N/A | 2.9% + $0.30/charge |
| Coinbase | Unlimited | N/A | 1% per transaction |
| Google Search Console | Unlimited | N/A | Free |
| PageSpeed Insights | 25,000/day | 100/100s | Free |

---

## Error Handling Best Practices

All API integrations return consistent response format:

```php
// Success response
[
    'success' => true,
    'data' => [...],
    'api_call_count' => 1
]

// Error response
[
    'success' => false,
    'error' => 'Descriptive error message',
    'error_code' => 'RATE_LIMIT_EXCEEDED'
]
```

### Common Error Codes

- `API_KEY_INVALID`: Check credentials in `.env`
- `RATE_LIMIT_EXCEEDED`: Wait before retry or upgrade plan
- `INSUFFICIENT_BALANCE`: Top up API credit balance
- `NETWORK_ERROR`: Check internet connection
- `SERVICE_UNAVAILABLE`: API provider experiencing issues

---

## Monitoring & Logging

### API Call Logging

All API calls are logged to database:

```sql
SELECT * FROM api_logs
WHERE api_name = 'sendgrid'
ORDER BY created_at DESC
LIMIT 10;
```

### Usage Analytics

View API usage trends:

```php
GET /api/admin/api-usage-stats.php?days=30
```

Returns usage breakdown by API, success rate, and average response time.

---

## Support & Troubleshooting

### Common Issues

**SendGrid emails not sending:**
- Verify API key has "Full Access" permissions
- Check sender email is verified in SendGrid
- Review SendGrid activity logs for blocks

**WhatsApp messages failing:**
- Ensure phone number is in E.164 format (+12345678900)
- Verify WhatsApp Business API is approved
- Check if template message is approved (for business-initiated)

**Stripe test payments not working:**
- Use test card: `4242 4242 4242 4242`
- Ensure using test mode keys (`sk_test_` and `pk_test_`)
- Check webhook endpoint is accessible

**Telegram notifications not received:**
- Start conversation with bot first (send any message)
- Verify bot token with `/getMe` endpoint
- Check admin chat ID is correct

### Get Help

- **Documentation Issues**: Open issue on GitHub
- **API Provider Support**: Contact respective API provider
- **Integration Questions**: Check `backend/classes/` for implementation

---

## Security Checklist

- [ ] All API keys stored in `.env` (never commit to Git)
- [ ] `.env` file excluded via `.gitignore`
- [ ] Webhook endpoints validate signatures
- [ ] API calls use HTTPS only
- [ ] Rate limiting enabled to prevent abuse
- [ ] API errors logged but not exposed to users
- [ ] Test mode used until production-ready
- [ ] Webhook secrets rotated periodically

---

**Last Updated:** January 2025
**Version:** 1.0.0
**Maintainer:** Adil GFX Development Team
