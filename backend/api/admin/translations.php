<?php
/**
 * Admin Translations API
 * Admin-only endpoint for managing translations
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../middleware/cors.php';
require_once __DIR__ . '/../../classes/TranslationManager.php';
require_once __DIR__ . '/../../classes/Auth.php';

$auth = new Auth();
$user = $auth->validateToken();

if (!$user || $user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    $translationManager = new TranslationManager();

    switch ($method) {
        case 'GET':
            handleAdminGet($translationManager);
            break;

        case 'POST':
            handleAdminPost($translationManager);
            break;

        case 'PUT':
            handleAdminPut($translationManager);
            break;

        case 'DELETE':
            handleAdminDelete($translationManager);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }

} catch (Exception $e) {
    error_log("Admin Translations API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'message' => $e->getMessage()
    ]);
}

function handleAdminGet($translationManager) {
    $langCode = $_GET['lang_code'] ?? 'en';
    $contentType = $_GET['content_type'] ?? null;
    $manualOnly = isset($_GET['manual_only']) ? filter_var($_GET['manual_only'], FILTER_VALIDATE_BOOLEAN) : false;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

    $result = $translationManager->getAdminTranslations(
        $langCode,
        $contentType,
        $manualOnly,
        $page,
        $limit
    );

    echo json_encode([
        'success' => true,
        'data' => $result['items'],
        'pagination' => $result['pagination']
    ]);
}

function handleAdminPost($translationManager) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data']);
        return;
    }

    $action = $data['action'] ?? 'save';

    if ($action === 'bulk_translate') {
        $contentType = $data['content_type'] ?? null;
        $targetLang = $data['target_lang'] ?? null;
        $limit = $data['limit'] ?? 100;

        if (!$contentType || !$targetLang) {
            http_response_code(400);
            echo json_encode(['error' => 'content_type and target_lang are required']);
            return;
        }

        $result = $translationManager->bulkAutoTranslate($contentType, $targetLang, $limit);

        echo json_encode([
            'success' => true,
            'message' => "Bulk translation completed",
            'result' => $result
        ]);
        return;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);
}

function handleAdminPut($translationManager) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid data or missing ID']);
        return;
    }

    $result = $translationManager->saveTranslation(
        $data['content_type'],
        $data['content_id'] ?? null,
        $data['field_name'],
        $data['lang_code'],
        $data['original_text'] ?? '',
        $data['translated_text'],
        true
    );

    echo json_encode([
        'success' => $result,
        'message' => 'Translation updated successfully'
    ]);
}

function handleAdminDelete($translationManager) {
    http_response_code(501);
    echo json_encode(['error' => 'Delete not implemented']);
}
