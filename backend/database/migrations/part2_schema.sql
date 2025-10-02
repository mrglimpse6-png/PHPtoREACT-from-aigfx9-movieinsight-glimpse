-- =====================================================
-- Part 2: API Integrations & Funnel Tester Schema
-- =====================================================
-- Version: 2.0.0
-- Purpose: Add tables for API management and funnel testing
-- =====================================================

-- API Integrations Configuration
CREATE TABLE IF NOT EXISTS api_integrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    provider VARCHAR(100) NOT NULL,
    category ENUM('seo', 'communication', 'payment', 'ai', 'crm', 'social', 'analytics') NOT NULL,
    enabled BOOLEAN DEFAULT FALSE,
    config JSON COMMENT 'Encrypted API keys and configuration',
    rate_limit_per_hour INT DEFAULT 100,
    requests_today INT DEFAULT 0,
    requests_this_month INT DEFAULT 0,
    quota_limit INT DEFAULT NULL COMMENT 'Monthly quota limit',
    last_request TIMESTAMP NULL,
    last_success TIMESTAMP NULL,
    last_error TEXT NULL,
    error_count INT DEFAULT 0,
    success_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_category (category),
    INDEX idx_enabled (enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API Request/Response Logging
CREATE TABLE IF NOT EXISTS api_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    integration_name VARCHAR(100) NOT NULL,
    endpoint VARCHAR(500),
    method VARCHAR(10),
    request_data JSON,
    response_data JSON,
    status_code INT,
    response_time_ms INT,
    error TEXT NULL,
    user_id INT NULL COMMENT 'User who triggered the API call',
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_integration (integration_name),
    INDEX idx_created (created_at),
    INDEX idx_status (status_code),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Funnel Simulations
CREATE TABLE IF NOT EXISTS funnel_simulations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    traffic_source ENUM('google', 'linkedin', 'cold_email', 'direct', 'facebook', 'instagram', 'twitter', 'organic') NOT NULL,
    campaign_name VARCHAR(255),
    mock_user_id INT,
    status ENUM('pending', 'running', 'completed', 'failed', 'partial') DEFAULT 'pending',
    current_step VARCHAR(100),
    current_stage ENUM('landing', 'signup', 'engagement', 'checkout', 'conversion') DEFAULT 'landing',
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    total_steps INT DEFAULT 0,
    successful_steps INT DEFAULT 0,
    failed_steps INT DEFAULT 0,
    conversion_value DECIMAL(10,2) DEFAULT 0,
    payment_method ENUM('stripe', 'coinbase', 'none') DEFAULT 'none',
    metadata JSON COMMENT 'Additional simulation data',
    notes TEXT,
    FOREIGN KEY (mock_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_source (traffic_source),
    INDEX idx_stage (current_stage),
    INDEX idx_started (started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Funnel Step Tracking
CREATE TABLE IF NOT EXISTS funnel_steps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    simulation_id INT NOT NULL,
    step_name VARCHAR(100) NOT NULL,
    step_order INT NOT NULL,
    stage ENUM('landing', 'signup', 'engagement', 'checkout', 'conversion') NOT NULL,
    status ENUM('pending', 'running', 'success', 'failed', 'skipped') DEFAULT 'pending',
    api_calls JSON COMMENT 'List of API calls made in this step',
    request_payload JSON,
    response_data JSON,
    error_message TEXT NULL,
    duration_ms INT,
    retry_count INT DEFAULT 0,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (simulation_id) REFERENCES funnel_simulations(id) ON DELETE CASCADE,
    INDEX idx_simulation (simulation_id),
    INDEX idx_status (status),
    INDEX idx_stage (stage),
    INDEX idx_started (started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Funnel Metrics Aggregation (for reporting)
CREATE TABLE IF NOT EXISTS funnel_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    traffic_source VARCHAR(50),
    stage ENUM('landing', 'signup', 'engagement', 'checkout', 'conversion') NOT NULL,
    total_simulations INT DEFAULT 0,
    successful_simulations INT DEFAULT 0,
    failed_simulations INT DEFAULT 0,
    avg_duration_seconds INT DEFAULT 0,
    total_conversion_value DECIMAL(12,2) DEFAULT 0,
    conversion_rate DECIMAL(5,2) COMMENT 'Percentage',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_daily_metric (date, traffic_source, stage),
    INDEX idx_date (date),
    INDEX idx_source (traffic_source),
    INDEX idx_stage (stage)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Webhook Event Log
CREATE TABLE IF NOT EXISTS webhook_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider ENUM('stripe', 'coinbase', 'sendgrid', 'whatsapp', 'telegram', 'other') NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    event_id VARCHAR(255) UNIQUE,
    payload JSON NOT NULL,
    signature VARCHAR(500),
    signature_verified BOOLEAN DEFAULT FALSE,
    processed BOOLEAN DEFAULT FALSE,
    processing_result TEXT,
    ip_address VARCHAR(45),
    received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    INDEX idx_provider (provider),
    INDEX idx_event_type (event_type),
    INDEX idx_processed (processed),
    INDEX idx_received (received_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email Campaign Tracking
CREATE TABLE IF NOT EXISTS email_campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_name VARCHAR(255) NOT NULL,
    provider ENUM('sendgrid', 'mailchimp', 'manual') NOT NULL,
    subject VARCHAR(500),
    template_id VARCHAR(100),
    segment VARCHAR(100) COMMENT 'Target audience segment',
    scheduled_at TIMESTAMP NULL,
    sent_at TIMESTAMP NULL,
    total_recipients INT DEFAULT 0,
    delivered_count INT DEFAULT 0,
    opened_count INT DEFAULT 0,
    clicked_count INT DEFAULT 0,
    bounced_count INT DEFAULT 0,
    status ENUM('draft', 'scheduled', 'sending', 'sent', 'failed') DEFAULT 'draft',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_scheduled (scheduled_at),
    INDEX idx_sent (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- WhatsApp Message Queue
CREATE TABLE IF NOT EXISTS whatsapp_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone_number VARCHAR(20) NOT NULL,
    message_type ENUM('text', 'template', 'media') NOT NULL,
    template_name VARCHAR(100),
    message_content TEXT,
    media_url VARCHAR(500),
    status ENUM('queued', 'sending', 'sent', 'delivered', 'read', 'failed') DEFAULT 'queued',
    message_id VARCHAR(255) UNIQUE COMMENT 'WhatsApp message ID',
    error_message TEXT,
    scheduled_at TIMESTAMP NULL,
    sent_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    read_at TIMESTAMP NULL,
    user_id INT NULL,
    simulation_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (simulation_id) REFERENCES funnel_simulations(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_phone (phone_number),
    INDEX idx_user (user_id),
    INDEX idx_scheduled (scheduled_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Telegram Notifications
CREATE TABLE IF NOT EXISTS telegram_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_id VARCHAR(100) NOT NULL,
    message_type ENUM('alert', 'notification', 'report', 'error') NOT NULL,
    message_text TEXT NOT NULL,
    parse_mode ENUM('HTML', 'Markdown', 'MarkdownV2') DEFAULT 'HTML',
    status ENUM('queued', 'sent', 'failed') DEFAULT 'queued',
    message_id INT COMMENT 'Telegram message ID',
    error_message TEXT,
    sent_at TIMESTAMP NULL,
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    user_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_sent (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payment Transactions (for funnel testing)
CREATE TABLE IF NOT EXISTS payment_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider ENUM('stripe', 'coinbase', 'paypal') NOT NULL,
    transaction_id VARCHAR(255) UNIQUE NOT NULL,
    order_id INT,
    user_id INT,
    simulation_id INT NULL COMMENT 'If part of funnel test',
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    status ENUM('pending', 'processing', 'completed', 'failed', 'refunded', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50),
    metadata JSON,
    test_mode BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (simulation_id) REFERENCES funnel_simulations(id) ON DELETE SET NULL,
    INDEX idx_provider (provider),
    INDEX idx_transaction (transaction_id),
    INDEX idx_order (order_id),
    INDEX idx_user (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEO Tracking (Google Search Console)
CREATE TABLE IF NOT EXISTS seo_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    page_url VARCHAR(500),
    query VARCHAR(500),
    impressions INT DEFAULT 0,
    clicks INT DEFAULT 0,
    ctr DECIMAL(5,2) COMMENT 'Click-through rate percentage',
    position DECIMAL(5,2) COMMENT 'Average position in search results',
    country VARCHAR(2),
    device ENUM('desktop', 'mobile', 'tablet'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_date (date),
    INDEX idx_page (page_url(255)),
    INDEX idx_query (query(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PageSpeed Insights Results
CREATE TABLE IF NOT EXISTS pagespeed_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_url VARCHAR(500) NOT NULL,
    device ENUM('mobile', 'desktop') NOT NULL,
    performance_score INT COMMENT '0-100',
    accessibility_score INT COMMENT '0-100',
    best_practices_score INT COMMENT '0-100',
    seo_score INT COMMENT '0-100',
    fcp_ms INT COMMENT 'First Contentful Paint',
    lcp_ms INT COMMENT 'Largest Contentful Paint',
    cls DECIMAL(4,3) COMMENT 'Cumulative Layout Shift',
    ttfb_ms INT COMMENT 'Time to First Byte',
    raw_data JSON,
    tested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_page (page_url(255)),
    INDEX idx_device (device),
    INDEX idx_tested (tested_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Insert Default API Integrations
-- =====================================================

INSERT INTO api_integrations (name, provider, category, enabled, rate_limit_per_hour) VALUES
('Google Search Console', 'google', 'seo', FALSE, 50),
('PageSpeed Insights', 'google', 'seo', FALSE, 25),
('SendGrid Email', 'sendgrid', 'communication', FALSE, 100),
('WhatsApp Business', 'meta', 'communication', FALSE, 80),
('Telegram Bot', 'telegram', 'communication', FALSE, 200),
('Stripe Payments', 'stripe', 'payment', FALSE, 100),
('Coinbase Commerce', 'coinbase', 'payment', FALSE, 50),
('Hunter.io', 'hunter', 'crm', FALSE, 50),
('OpenAI ChatGPT', 'openai', 'ai', FALSE, 60),
('LinkedIn API', 'linkedin', 'social', FALSE, 30)
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- =====================================================
-- Views for Reporting
-- =====================================================

-- Funnel conversion rates by source
CREATE OR REPLACE VIEW funnel_conversion_rates AS
SELECT
    traffic_source,
    COUNT(*) as total_simulations,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
    ROUND(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as completion_rate,
    AVG(CASE WHEN status = 'completed' THEN conversion_value ELSE 0 END) as avg_conversion_value,
    AVG(TIMESTAMPDIFF(SECOND, started_at, completed_at)) as avg_duration_seconds
FROM funnel_simulations
GROUP BY traffic_source;

-- API performance summary
CREATE OR REPLACE VIEW api_performance_summary AS
SELECT
    integration_name,
    COUNT(*) as total_requests,
    SUM(CASE WHEN status_code >= 200 AND status_code < 300 THEN 1 ELSE 0 END) as successful_requests,
    SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as failed_requests,
    AVG(response_time_ms) as avg_response_time,
    MAX(response_time_ms) as max_response_time,
    DATE(created_at) as date
FROM api_logs
GROUP BY integration_name, DATE(created_at)
ORDER BY date DESC, integration_name;

-- =====================================================
-- Stored Procedures for Funnel Testing
-- =====================================================

DELIMITER $$

-- Start a new funnel simulation
CREATE PROCEDURE IF NOT EXISTS start_funnel_simulation(
    IN p_traffic_source VARCHAR(50),
    IN p_campaign_name VARCHAR(255)
)
BEGIN
    INSERT INTO funnel_simulations (traffic_source, campaign_name, status, current_stage)
    VALUES (p_traffic_source, p_campaign_name, 'pending', 'landing');

    SELECT LAST_INSERT_ID() as simulation_id;
END$$

-- Log funnel step completion
CREATE PROCEDURE IF NOT EXISTS log_funnel_step(
    IN p_simulation_id INT,
    IN p_step_name VARCHAR(100),
    IN p_step_order INT,
    IN p_stage VARCHAR(50),
    IN p_status VARCHAR(20),
    IN p_duration_ms INT,
    IN p_api_calls JSON,
    IN p_response_data JSON,
    IN p_error_message TEXT
)
BEGIN
    INSERT INTO funnel_steps (
        simulation_id, step_name, step_order, stage, status,
        duration_ms, api_calls, response_data, error_message, completed_at
    ) VALUES (
        p_simulation_id, p_step_name, p_step_order, p_stage, p_status,
        p_duration_ms, p_api_calls, p_response_data, p_error_message, NOW()
    );

    -- Update simulation progress
    UPDATE funnel_simulations
    SET total_steps = total_steps + 1,
        successful_steps = successful_steps + CASE WHEN p_status = 'success' THEN 1 ELSE 0 END,
        failed_steps = failed_steps + CASE WHEN p_status = 'failed' THEN 1 ELSE 0 END,
        current_step = p_step_name,
        current_stage = p_stage
    WHERE id = p_simulation_id;
END$$

-- Complete funnel simulation
CREATE PROCEDURE IF NOT EXISTS complete_funnel_simulation(
    IN p_simulation_id INT,
    IN p_status VARCHAR(20),
    IN p_conversion_value DECIMAL(10,2),
    IN p_payment_method VARCHAR(20)
)
BEGIN
    UPDATE funnel_simulations
    SET status = p_status,
        conversion_value = p_conversion_value,
        payment_method = p_payment_method,
        completed_at = NOW()
    WHERE id = p_simulation_id;

    -- Aggregate daily metrics
    INSERT INTO funnel_metrics (
        date, traffic_source, stage,
        total_simulations, successful_simulations, failed_simulations,
        total_conversion_value
    )
    SELECT
        CURDATE(),
        traffic_source,
        'conversion',
        1,
        CASE WHEN p_status = 'completed' THEN 1 ELSE 0 END,
        CASE WHEN p_status = 'failed' THEN 1 ELSE 0 END,
        p_conversion_value
    FROM funnel_simulations
    WHERE id = p_simulation_id
    ON DUPLICATE KEY UPDATE
        total_simulations = total_simulations + 1,
        successful_simulations = successful_simulations + VALUES(successful_simulations),
        failed_simulations = failed_simulations + VALUES(failed_simulations),
        total_conversion_value = total_conversion_value + VALUES(total_conversion_value);
END$$

DELIMITER ;

-- =====================================================
-- Triggers
-- =====================================================

-- Log API integration usage
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS after_api_log_insert
AFTER INSERT ON api_logs
FOR EACH ROW
BEGIN
    UPDATE api_integrations
    SET requests_today = requests_today + 1,
        requests_this_month = requests_this_month + 1,
        last_request = NOW(),
        last_success = CASE WHEN NEW.status_code >= 200 AND NEW.status_code < 300 THEN NOW() ELSE last_success END,
        last_error = CASE WHEN NEW.status_code >= 400 THEN NEW.error ELSE last_error END,
        success_count = success_count + CASE WHEN NEW.status_code >= 200 AND NEW.status_code < 300 THEN 1 ELSE 0 END,
        error_count = error_count + CASE WHEN NEW.status_code >= 400 THEN 1 ELSE 0 END
    WHERE name = NEW.integration_name;
END$$
DELIMITER ;

-- =====================================================
-- Cleanup Jobs (run via cron)
-- =====================================================

-- Clean up old API logs (keep 90 days)
-- DELETE FROM api_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);

-- Clean up old funnel simulations (keep 180 days)
-- DELETE FROM funnel_simulations WHERE started_at < DATE_SUB(NOW(), INTERVAL 180 DAY);

-- Reset daily API request counters (run at midnight)
-- UPDATE api_integrations SET requests_today = 0;

-- =====================================================
-- END OF PART 2 SCHEMA
-- =====================================================
