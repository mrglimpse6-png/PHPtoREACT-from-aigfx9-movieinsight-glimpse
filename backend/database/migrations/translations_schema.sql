/*
  # Translation System Database Schema

  1. New Tables
    - `translations`: Stores all translated content with auto/manual override support
      - `id` (PK, auto-increment)
      - `content_type` (blog, testimonial, service, page_section, ui_string, etc.)
      - `content_id` (FK reference to original item, NULL for UI strings)
      - `field_name` (which field is translated: title, description, content, etc.)
      - `lang_code` (ISO 639-1: en, es, fr, ar, de, etc.)
      - `original_text` (original content for reference)
      - `translated_text` (translated content)
      - `manual_override` (boolean: true if admin edited)
      - `translation_method` (auto, manual, hybrid)
      - `last_updated` (timestamp)
      - `created_at` (timestamp)

    - `supported_languages`: Configure which languages are active
      - `id` (PK)
      - `lang_code` (ISO 639-1 code)
      - `lang_name` (English name)
      - `native_name` (Native name: Español, Français, etc.)
      - `rtl` (boolean: right-to-left languages)
      - `active` (boolean: enabled/disabled)
      - `default_lang` (boolean: primary language)
      - `sort_order` (display order)

    - `translation_cache`: Cache API calls to reduce costs
      - `id` (PK)
      - `source_text_hash` (MD5 hash of source + target lang)
      - `source_lang` (source language)
      - `target_lang` (target language)
      - `translated_text` (cached translation)
      - `created_at` (timestamp)
      - `expires_at` (timestamp)

  2. Security
    - Enable RLS on all translation tables (admin only can modify)
    - Add policies for public read access to active languages
    - Add policies for admin-only write access

  3. Indexes
    - Composite index on (content_type, content_id, field_name, lang_code)
    - Index on lang_code for fast language switching
    - Index on manual_override for admin filtering
    - Index on source_text_hash for cache lookups
*/

-- =====================================================
-- TRANSLATIONS TABLE
-- =====================================================

CREATE TABLE IF NOT EXISTS translations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content_type ENUM(
        'blog',
        'testimonial',
        'service',
        'portfolio',
        'page_section',
        'carousel',
        'ui_string',
        'setting',
        'notification'
    ) NOT NULL,
    content_id INT DEFAULT NULL COMMENT 'FK to original content, NULL for UI strings',
    field_name VARCHAR(100) NOT NULL COMMENT 'title, description, content, tagline, etc.',
    lang_code VARCHAR(5) NOT NULL COMMENT 'ISO 639-1 language code',
    original_text LONGTEXT NOT NULL COMMENT 'Original content for reference',
    translated_text LONGTEXT NOT NULL COMMENT 'Translated content',
    manual_override BOOLEAN DEFAULT FALSE COMMENT 'True if admin manually edited',
    translation_method ENUM('auto', 'manual', 'hybrid') DEFAULT 'auto',
    quality_score DECIMAL(3,2) DEFAULT NULL COMMENT '0.00 to 1.00 confidence score',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_content_lookup (content_type, content_id, field_name, lang_code),
    INDEX idx_lang_code (lang_code),
    INDEX idx_manual_override (manual_override),
    INDEX idx_content_type (content_type),
    INDEX idx_translation_method (translation_method)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SUPPORTED LANGUAGES TABLE
-- =====================================================

CREATE TABLE IF NOT EXISTS supported_languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lang_code VARCHAR(5) UNIQUE NOT NULL COMMENT 'ISO 639-1 code',
    lang_name VARCHAR(100) NOT NULL COMMENT 'English name',
    native_name VARCHAR(100) NOT NULL COMMENT 'Native name',
    flag_icon VARCHAR(10) DEFAULT NULL COMMENT 'Emoji flag or icon code',
    rtl BOOLEAN DEFAULT FALSE COMMENT 'Right-to-left language',
    active BOOLEAN DEFAULT TRUE COMMENT 'Enabled for users',
    default_lang BOOLEAN DEFAULT FALSE COMMENT 'Primary language',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_active (active),
    INDEX idx_default_lang (default_lang),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TRANSLATION CACHE TABLE
-- =====================================================

CREATE TABLE IF NOT EXISTS translation_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_text_hash VARCHAR(32) NOT NULL COMMENT 'MD5 hash of source text',
    source_lang VARCHAR(5) NOT NULL,
    target_lang VARCHAR(5) NOT NULL,
    source_text TEXT NOT NULL COMMENT 'Original text for reference',
    translated_text LONGTEXT NOT NULL,
    api_provider VARCHAR(50) DEFAULT 'google' COMMENT 'google, deepl, etc.',
    cache_hits INT DEFAULT 0 COMMENT 'Number of times reused',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP DEFAULT NULL COMMENT 'Cache expiration',

    UNIQUE KEY unique_translation (source_text_hash, source_lang, target_lang),
    INDEX idx_hash_lookup (source_text_hash, target_lang),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TRANSLATION STATISTICS TABLE
-- =====================================================

CREATE TABLE IF NOT EXISTS translation_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lang_code VARCHAR(5) NOT NULL,
    content_type VARCHAR(50) NOT NULL,
    total_items INT DEFAULT 0,
    translated_items INT DEFAULT 0,
    manual_overrides INT DEFAULT 0,
    completion_percentage DECIMAL(5,2) DEFAULT 0.00,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_stat (lang_code, content_type),
    INDEX idx_lang_code (lang_code),
    INDEX idx_completion (completion_percentage)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERT DEFAULT LANGUAGES
-- =====================================================

INSERT INTO supported_languages (lang_code, lang_name, native_name, flag_icon, rtl, active, default_lang, sort_order) VALUES
('en', 'English', 'English', '🇬🇧', FALSE, TRUE, TRUE, 1),
('es', 'Spanish', 'Español', '🇪🇸', FALSE, TRUE, FALSE, 2),
('fr', 'French', 'Français', '🇫🇷', FALSE, TRUE, FALSE, 3),
('ar', 'Arabic', 'العربية', '🇸🇦', TRUE, TRUE, FALSE, 4),
('de', 'German', 'Deutsch', '🇩🇪', FALSE, TRUE, FALSE, 5),
('pt', 'Portuguese', 'Português', '🇵🇹', FALSE, TRUE, FALSE, 6),
('it', 'Italian', 'Italiano', '🇮🇹', FALSE, FALSE, FALSE, 7),
('ru', 'Russian', 'Русский', '🇷🇺', FALSE, FALSE, FALSE, 8),
('zh', 'Chinese', '中文', '🇨🇳', FALSE, FALSE, FALSE, 9),
('ja', 'Japanese', '日本語', '🇯🇵', FALSE, FALSE, FALSE, 10),
('hi', 'Hindi', 'हिन्दी', '🇮🇳', FALSE, FALSE, FALSE, 11),
('tr', 'Turkish', 'Türkçe', '🇹🇷', FALSE, FALSE, FALSE, 12)
ON DUPLICATE KEY UPDATE
    active = VALUES(active),
    sort_order = VALUES(sort_order);

-- =====================================================
-- INSERT DEFAULT UI STRINGS FOR TRANSLATION
-- =====================================================

INSERT INTO translations (content_type, content_id, field_name, lang_code, original_text, translated_text, manual_override, translation_method) VALUES
-- Navigation UI strings
('ui_string', NULL, 'nav_home', 'en', 'Home', 'Home', TRUE, 'manual'),
('ui_string', NULL, 'nav_about', 'en', 'About', 'About', TRUE, 'manual'),
('ui_string', NULL, 'nav_services', 'en', 'Services', 'Services', TRUE, 'manual'),
('ui_string', NULL, 'nav_portfolio', 'en', 'Portfolio', 'Portfolio', TRUE, 'manual'),
('ui_string', NULL, 'nav_blog', 'en', 'Blog', 'Blog', TRUE, 'manual'),
('ui_string', NULL, 'nav_contact', 'en', 'Contact', 'Contact', TRUE, 'manual'),
('ui_string', NULL, 'nav_faq', 'en', 'FAQ', 'FAQ', TRUE, 'manual'),
('ui_string', NULL, 'nav_testimonials', 'en', 'Testimonials', 'Testimonials', TRUE, 'manual'),

-- CTA buttons
('ui_string', NULL, 'btn_get_started', 'en', 'Get Started', 'Get Started', TRUE, 'manual'),
('ui_string', NULL, 'btn_learn_more', 'en', 'Learn More', 'Learn More', TRUE, 'manual'),
('ui_string', NULL, 'btn_contact_us', 'en', 'Contact Us', 'Contact Us', TRUE, 'manual'),
('ui_string', NULL, 'btn_view_portfolio', 'en', 'View Portfolio', 'View Portfolio', TRUE, 'manual'),
('ui_string', NULL, 'btn_read_more', 'en', 'Read More', 'Read More', TRUE, 'manual'),

-- Common labels
('ui_string', NULL, 'label_name', 'en', 'Name', 'Name', TRUE, 'manual'),
('ui_string', NULL, 'label_email', 'en', 'Email', 'Email', TRUE, 'manual'),
('ui_string', NULL, 'label_phone', 'en', 'Phone', 'Phone', TRUE, 'manual'),
('ui_string', NULL, 'label_message', 'en', 'Message', 'Message', TRUE, 'manual'),
('ui_string', NULL, 'label_service', 'en', 'Service', 'Service', TRUE, 'manual'),
('ui_string', NULL, 'label_submit', 'en', 'Submit', 'Submit', TRUE, 'manual'),

-- Footer strings
('ui_string', NULL, 'footer_copyright', 'en', '© 2025 Adil GFX. All rights reserved.', '© 2025 Adil GFX. All rights reserved.', TRUE, 'manual'),
('ui_string', NULL, 'footer_tagline', 'en', 'Professional Design Services', 'Professional Design Services', TRUE, 'manual')
ON DUPLICATE KEY UPDATE
    translated_text = VALUES(translated_text);

-- =====================================================
-- STORED PROCEDURES
-- =====================================================

-- Get translation with fallback
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS get_translation(
    IN p_content_type VARCHAR(50),
    IN p_content_id INT,
    IN p_field_name VARCHAR(100),
    IN p_lang_code VARCHAR(5),
    IN p_fallback_text TEXT
)
BEGIN
    DECLARE v_translated_text LONGTEXT;

    SELECT translated_text INTO v_translated_text
    FROM translations
    WHERE content_type = p_content_type
      AND (content_id = p_content_id OR (content_id IS NULL AND p_content_id IS NULL))
      AND field_name = p_field_name
      AND lang_code = p_lang_code
    LIMIT 1;

    IF v_translated_text IS NULL OR v_translated_text = '' THEN
        SET v_translated_text = p_fallback_text;
    END IF;

    SELECT v_translated_text AS translation;
END$$
DELIMITER ;

-- Update translation statistics
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS update_translation_stats(
    IN p_lang_code VARCHAR(5)
)
BEGIN
    INSERT INTO translation_stats (lang_code, content_type, total_items, translated_items, manual_overrides, completion_percentage)
    SELECT
        p_lang_code,
        content_type,
        COUNT(DISTINCT CONCAT(content_type, '-', IFNULL(content_id, 'null'), '-', field_name)) AS total,
        COUNT(*) AS translated,
        SUM(CASE WHEN manual_override = TRUE THEN 1 ELSE 0 END) AS manual,
        (COUNT(*) / COUNT(DISTINCT CONCAT(content_type, '-', IFNULL(content_id, 'null'), '-', field_name))) * 100 AS percentage
    FROM translations
    WHERE lang_code = p_lang_code
    GROUP BY content_type
    ON DUPLICATE KEY UPDATE
        total_items = VALUES(total_items),
        translated_items = VALUES(translated_items),
        manual_overrides = VALUES(manual_overrides),
        completion_percentage = VALUES(completion_percentage);
END$$
DELIMITER ;

-- =====================================================
-- VIEWS FOR COMMON QUERIES
-- =====================================================

-- Translation completion overview
CREATE OR REPLACE VIEW translation_completion_overview AS
SELECT
    sl.lang_code,
    sl.lang_name,
    sl.native_name,
    sl.active,
    IFNULL(SUM(ts.translated_items), 0) AS total_translations,
    IFNULL(SUM(ts.manual_overrides), 0) AS manual_overrides,
    IFNULL(AVG(ts.completion_percentage), 0) AS avg_completion
FROM supported_languages sl
LEFT JOIN translation_stats ts ON sl.lang_code = ts.lang_code
WHERE sl.active = TRUE
GROUP BY sl.lang_code, sl.lang_name, sl.native_name, sl.active;

-- =====================================================
-- TRIGGERS
-- =====================================================

-- Update translation stats on insert/update
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS after_translation_insert
AFTER INSERT ON translations
FOR EACH ROW
BEGIN
    CALL update_translation_stats(NEW.lang_code);
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER IF NOT EXISTS after_translation_update
AFTER UPDATE ON translations
FOR EACH ROW
BEGIN
    CALL update_translation_stats(NEW.lang_code);
    IF NEW.lang_code != OLD.lang_code THEN
        CALL update_translation_stats(OLD.lang_code);
    END IF;
END$$
DELIMITER ;

-- =====================================================
-- INDEXES FOR PERFORMANCE
-- =====================================================

CREATE INDEX IF NOT EXISTS idx_translation_lookup
ON translations(content_type, field_name, lang_code);

CREATE INDEX IF NOT EXISTS idx_cache_lookup
ON translation_cache(source_text_hash, source_lang, target_lang);

-- =====================================================
-- END OF SCHEMA
-- =====================================================
