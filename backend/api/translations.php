<?php
/**
 * Translations API
 * Public endpoint for fetching translations
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../classes/TranslationManager.php';
require_once __DIR__ . '/../classes/Auth.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '/';

try {
    $translationManager = new TranslationManager();

    switch ($method) {
        case 'GET':
            handleGet($translationManager, $path);
            break;

        case 'POST':
            handlePost($translationManager);
            break;

        case 'PUT':
            handlePut($translationManager);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }

} catch (Exception $e) {
    error_log("Translations API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'message' => $e->getMessage()
    ]);
}

function handleGet($translationManager, $path) {
    if ($path === '/languages') {
        $activeOnly = isset($_GET['active_only']) ? filter_var($_GET['active_only'], FILTER_VALIDATE_BOOLEAN) : true;
        $languages = $translationManager->getSupportedLanguages($activeOnly);

        echo json_encode([
            'success' => true,
            'languages' => $languages
        ]);
        return;
    }

    if ($path === '/stats') {
        $langCode = $_GET['lang_code'] ?? null;
        $stats = $translationManager->getTranslationStats($langCode);

        echo json_encode([
            'success' => true,
            'stats' => $stats
        ]);
        return;
    }

    if ($path === '/batch') {
        $contentType = $_GET['content_type'] ?? null;
        $contentId = $_GET['content_id'] ?? null;
        $langCode = $_GET['lang_code'] ?? 'en';

        if (!$contentType || !$contentId) {
            http_response_code(400);
            echo json_encode(['error' => 'content_type and content_id are required']);
            return;
        }

        $translations = $translationManager->getTranslations($contentType, $contentId, $langCode);

        echo json_encode([
            'success' => true,
            'content_type' => $contentType,
            'content_id' => $contentId,
            'lang_code' => $langCode,
            'translations' => $translations
        ]);
        return;
    }

    $contentType = $_GET['content_type'] ?? null;
    $contentId = $_GET['content_id'] ?? null;
    $fieldName = $_GET['field_name'] ?? null;
    $langCode = $_GET['lang_code'] ?? 'en';
    $fallback = $_GET['fallback'] ?? null;

    if (!$contentType || !$fieldName) {
        http_response_code(400);
        echo json_encode(['error' => 'content_type and field_name are required']);
        return;
    }

    $translation = $translationManager->getTranslation(
        $contentType,
        $contentId,
        $fieldName,
        $langCode,
        $fallback
    );

    echo json_encode([
        'success' => true,
        'translation' => $translation,
        'lang_code' => $langCode
    ]);
}

function handlePost($translationManager) {
    $auth = new Auth();
    $user = $auth->validateToken();

    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data']);
        return;
    }

    $action = $data['action'] ?? 'save';

    if ($action === 'save') {
        $required = ['content_type', 'field_name', 'lang_code', 'original_text', 'translated_text'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Missing required field: {$field}"]);
                return;
            }
        }

        $result = $translationManager->saveTranslation(
            $data['content_type'],
            $data['content_id'] ?? null,
            $data['field_name'],
            $data['lang_code'],
            $data['original_text'],
            $data['translated_text'],
            $data['manual_override'] ?? false
        );

        echo json_encode([
            'success' => $result,
            'message' => 'Translation saved successfully'
        ]);
        return;
    }

    if ($action === 'auto_translate') {
        $text = $data['text'] ?? null;
        $sourceLang = $data['source_lang'] ?? 'en';
        $targetLang = $data['target_lang'] ?? null;

        if (!$text || !$targetLang) {
            http_response_code(400);
            echo json_encode(['error' => 'text and target_lang are required']);
            return;
        }

        $translated = $translationManager->autoTranslate($text, $sourceLang, $targetLang);

        echo json_encode([
            'success' => true,
            'original' => $text,
            'translated' => $translated,
            'source_lang' => $sourceLang,
            'target_lang' => $targetLang
        ]);
        return;
    }

    if ($action === 'bulk_translate') {
        $contentType = $data['content_type'] ?? null;
        $targetLang = $data['target_lang'] ?? null;
        $limit = $data['limit'] ?? 50;

        if (!$contentType || !$targetLang) {
            http_response_code(400);
            echo json_encode(['error' => 'content_type and target_lang are required']);
            return;
        }

        $result = $translationManager->bulkAutoTranslate($contentType, $targetLang, $limit);

        echo json_encode([
            'success' => true,
            'result' => $result
        ]);
        return;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);
}

function handlePut($translationManager) {
    $auth = new Auth();
    $user = $auth->validateToken();

    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data']);
        return;
    }

    if (isset($data['id'])) {
        $result = $translationManager->saveTranslation(
            $data['content_type'],
            $data['content_id'] ?? null,
            $data['field_name'],
            $data['lang_code'],
            $data['original_text'],
            $data['translated_text'],
            true
        );

        echo json_encode([
            'success' => $result,
            'message' => 'Translation updated successfully'
        ]);
        return;
    }

    if (isset($data['lang_code']) && isset($data['active'])) {
        $result = $translationManager->updateLanguageStatus(
            $data['lang_code'],
            $data['active']
        );

        echo json_encode([
            'success' => $result,
            'message' => 'Language status updated successfully'
        ]);
        return;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
}
