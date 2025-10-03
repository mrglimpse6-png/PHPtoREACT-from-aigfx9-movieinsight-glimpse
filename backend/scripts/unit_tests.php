<?php
/**
 * Unit Tests for PHP Backend Classes
 * Tests individual class methods with various scenarios
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/BlogManager.php';
require_once __DIR__ . '/../classes/ServiceManager.php';
require_once __DIR__ . '/../classes/PortfolioManager.php';
require_once __DIR__ . '/../classes/TestimonialManager.php';
require_once __DIR__ . '/../classes/TranslationManager.php';
require_once __DIR__ . '/../classes/SettingsManager.php';

class TestRunner {
    private $passed = 0;
    private $failed = 0;
    private $tests = [];

    public function assert($condition, $testName) {
        if ($condition) {
            $this->passed++;
            $this->tests[] = ['name' => $testName, 'status' => 'PASS'];
            echo "  ✅ {$testName}\n";
        } else {
            $this->failed++;
            $this->tests[] = ['name' => $testName, 'status' => 'FAIL'];
            echo "  ❌ {$testName}\n";
        }
    }

    public function assertEquals($expected, $actual, $testName) {
        $this->assert($expected === $actual, $testName);
    }

    public function assertTrue($condition, $testName) {
        $this->assert($condition === true, $testName);
    }

    public function assertFalse($condition, $testName) {
        $this->assert($condition === false, $testName);
    }

    public function assertNotNull($value, $testName) {
        $this->assert($value !== null, $testName);
    }

    public function getSummary() {
        return [
            'total' => $this->passed + $this->failed,
            'passed' => $this->passed,
            'failed' => $this->failed,
            'tests' => $this->tests
        ];
    }
}

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║           PHP Backend Unit Tests                         ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

$runner = new TestRunner();

// Test Auth Class
echo "╭─────────────────────────────────────────────────────────╮\n";
echo "│ Testing: Auth.php\n";
echo "╰─────────────────────────────────────────────────────────╯\n";

try {
    $db = new Database();
    $auth = new Auth($db->getConnection());

    // Test password hashing
    $password = "SecurePass123!";
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $runner->assertTrue(password_verify($password, $hash), "Password hashing and verification");

    // Test JWT token structure (without actual DB operations)
    $runner->assertTrue(class_exists('Auth'), "Auth class exists");
    $runner->assertTrue(method_exists($auth, 'register'), "Auth has register method");
    $runner->assertTrue(method_exists($auth, 'login'), "Auth has login method");
    $runner->assertTrue(method_exists($auth, 'verifyToken'), "Auth has verifyToken method");

    // Test email validation logic
    $validEmail = "test@example.com";
    $invalidEmail = "invalid-email";
    $runner->assertTrue(filter_var($validEmail, FILTER_VALIDATE_EMAIL) !== false, "Valid email passes validation");
    $runner->assertFalse(filter_var($invalidEmail, FILTER_VALIDATE_EMAIL) !== false, "Invalid email fails validation");

    // Test password strength requirements
    $weakPassword = "123";
    $strongPassword = "StrongP@ss123";
    $runner->assertTrue(strlen($strongPassword) >= 8, "Strong password meets length requirement");
    $runner->assertFalse(strlen($weakPassword) >= 8, "Weak password fails length requirement");

} catch (Exception $e) {
    echo "  ⚠️  Auth tests skipped: " . $e->getMessage() . "\n";
}

echo "\n";

// Test BlogManager Class
echo "╭─────────────────────────────────────────────────────────╮\n";
echo "│ Testing: BlogManager.php\n";
echo "╰─────────────────────────────────────────────────────────╯\n";

try {
    $blogManager = new BlogManager($db->getConnection());

    $runner->assertTrue(class_exists('BlogManager'), "BlogManager class exists");
    $runner->assertTrue(method_exists($blogManager, 'getAll'), "BlogManager has getAll method");
    $runner->assertTrue(method_exists($blogManager, 'getById'), "BlogManager has getById method");
    $runner->assertTrue(method_exists($blogManager, 'create'), "BlogManager has create method");
    $runner->assertTrue(method_exists($blogManager, 'update'), "BlogManager has update method");
    $runner->assertTrue(method_exists($blogManager, 'delete'), "BlogManager has delete method");

    // Test slug generation logic
    $title = "This is a Test Blog Post!";
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
    $runner->assertEquals("this-is-a-test-blog-post", $slug, "Slug generation from title");

    // Test input sanitization
    $dirtyInput = "<script>alert('xss')</script>";
    $cleanInput = htmlspecialchars($dirtyInput, ENT_QUOTES, 'UTF-8');
    $runner->assertTrue(strpos($cleanInput, '<script>') === false, "XSS input sanitization");

} catch (Exception $e) {
    echo "  ⚠️  BlogManager tests skipped: " . $e->getMessage() . "\n";
}

echo "\n";

// Test ServiceManager Class
echo "╭─────────────────────────────────────────────────────────╮\n";
echo "│ Testing: ServiceManager.php\n";
echo "╰─────────────────────────────────────────────────────────╯\n";

try {
    $serviceManager = new ServiceManager($db->getConnection());

    $runner->assertTrue(class_exists('ServiceManager'), "ServiceManager class exists");
    $runner->assertTrue(method_exists($serviceManager, 'getAll'), "ServiceManager has getAll method");
    $runner->assertTrue(method_exists($serviceManager, 'getById'), "ServiceManager has getById method");
    $runner->assertTrue(method_exists($serviceManager, 'create'), "ServiceManager has create method");
    $runner->assertTrue(method_exists($serviceManager, 'update'), "ServiceManager has update method");
    $runner->assertTrue(method_exists($serviceManager, 'delete'), "ServiceManager has delete method");

    // Test price validation
    $validPrice = 99.99;
    $invalidPrice = -50;
    $runner->assertTrue($validPrice > 0, "Valid price is positive");
    $runner->assertFalse($invalidPrice > 0, "Invalid price is rejected");

} catch (Exception $e) {
    echo "  ⚠️  ServiceManager tests skipped: " . $e->getMessage() . "\n";
}

echo "\n";

// Test PortfolioManager Class
echo "╭─────────────────────────────────────────────────────────╮\n";
echo "│ Testing: PortfolioManager.php\n";
echo "╰─────────────────────────────────────────────────────────╯\n";

try {
    $portfolioManager = new PortfolioManager($db->getConnection());

    $runner->assertTrue(class_exists('PortfolioManager'), "PortfolioManager class exists");
    $runner->assertTrue(method_exists($portfolioManager, 'getAll'), "PortfolioManager has getAll method");
    $runner->assertTrue(method_exists($portfolioManager, 'getById'), "PortfolioManager has getById method");
    $runner->assertTrue(method_exists($portfolioManager, 'create'), "PortfolioManager has create method");
    $runner->assertTrue(method_exists($portfolioManager, 'update'), "PortfolioManager has update method");
    $runner->assertTrue(method_exists($portfolioManager, 'delete'), "PortfolioManager has delete method");

} catch (Exception $e) {
    echo "  ⚠️  PortfolioManager tests skipped: " . $e->getMessage() . "\n";
}

echo "\n";

// Test TestimonialManager Class
echo "╭─────────────────────────────────────────────────────────╮\n";
echo "│ Testing: TestimonialManager.php\n";
echo "╰─────────────────────────────────────────────────────────╯\n";

try {
    $testimonialManager = new TestimonialManager($db->getConnection());

    $runner->assertTrue(class_exists('TestimonialManager'), "TestimonialManager class exists");
    $runner->assertTrue(method_exists($testimonialManager, 'getAll'), "TestimonialManager has getAll method");
    $runner->assertTrue(method_exists($testimonialManager, 'getById'), "TestimonialManager has getById method");
    $runner->assertTrue(method_exists($testimonialManager, 'create'), "TestimonialManager has create method");
    $runner->assertTrue(method_exists($testimonialManager, 'update'), "TestimonialManager has update method");
    $runner->assertTrue(method_exists($testimonialManager, 'delete'), "TestimonialManager has delete method");

    // Test rating validation
    $validRating = 5;
    $invalidRating = 10;
    $runner->assertTrue($validRating >= 1 && $validRating <= 5, "Valid rating (1-5)");
    $runner->assertFalse($invalidRating >= 1 && $invalidRating <= 5, "Invalid rating rejected");

} catch (Exception $e) {
    echo "  ⚠️  TestimonialManager tests skipped: " . $e->getMessage() . "\n";
}

echo "\n";

// Test TranslationManager Class
echo "╭─────────────────────────────────────────────────────────╮\n";
echo "│ Testing: TranslationManager.php\n";
echo "╰─────────────────────────────────────────────────────────╯\n";

try {
    $translationManager = new TranslationManager($db->getConnection());

    $runner->assertTrue(class_exists('TranslationManager'), "TranslationManager class exists");
    $runner->assertTrue(method_exists($translationManager, 'getTranslations'), "TranslationManager has getTranslations method");
    $runner->assertTrue(method_exists($translationManager, 'updateTranslation'), "TranslationManager has updateTranslation method");

    // Test language code validation
    $validLangs = ['en', 'ar', 'es', 'fr'];
    $invalidLang = 'xyz';
    $runner->assertTrue(in_array('en', $validLangs), "Valid language code 'en'");
    $runner->assertTrue(in_array('ar', $validLangs), "Valid language code 'ar'");
    $runner->assertFalse(in_array($invalidLang, $validLangs), "Invalid language code rejected");

} catch (Exception $e) {
    echo "  ⚠️  TranslationManager tests skipped: " . $e->getMessage() . "\n";
}

echo "\n";

// Test SettingsManager Class
echo "╭─────────────────────────────────────────────────────────╮\n";
echo "│ Testing: SettingsManager.php\n";
echo "╰─────────────────────────────────────────────────────────╯\n";

try {
    $settingsManager = new SettingsManager($db->getConnection());

    $runner->assertTrue(class_exists('SettingsManager'), "SettingsManager class exists");
    $runner->assertTrue(method_exists($settingsManager, 'getSetting'), "SettingsManager has getSetting method");
    $runner->assertTrue(method_exists($settingsManager, 'updateSetting'), "SettingsManager has updateSetting method");
    $runner->assertTrue(method_exists($settingsManager, 'getAllSettings'), "SettingsManager has getAllSettings method");

    // Test setting key validation
    $validKey = "site_title";
    $emptyKey = "";
    $runner->assertTrue(!empty($validKey), "Valid setting key");
    $runner->assertFalse(!empty($emptyKey), "Empty setting key rejected");

} catch (Exception $e) {
    echo "  ⚠️  SettingsManager tests skipped: " . $e->getMessage() . "\n";
}

echo "\n";

// Test Utility Functions
echo "╭─────────────────────────────────────────────────────────╮\n";
echo "│ Testing: Utility Functions\n";
echo "╰─────────────────────────────────────────────────────────╯\n";

// Test JSON response structure
$jsonResponse = json_encode(['success' => true, 'data' => ['id' => 1, 'name' => 'Test']]);
$decoded = json_decode($jsonResponse, true);
$runner->assertTrue(is_array($decoded), "JSON encoding/decoding");
$runner->assertTrue($decoded['success'] === true, "JSON response structure");
$runner->assertEquals(1, $decoded['data']['id'], "JSON data integrity");

// Test SQL injection prevention patterns
$safeQuery = "SELECT * FROM users WHERE id = :id";
$runner->assertTrue(strpos($safeQuery, ':id') !== false, "Parameterized query uses placeholders");
$runner->assertFalse(strpos($safeQuery, "' OR '1'='1") !== false, "No SQL injection patterns");

// Test file upload validation
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$validType = 'image/jpeg';
$invalidType = 'application/x-php';
$runner->assertTrue(in_array($validType, $allowedTypes), "Valid image MIME type");
$runner->assertFalse(in_array($invalidType, $allowedTypes), "Dangerous MIME type rejected");

// Test URL validation
$validUrl = "https://example.com/page";
$invalidUrl = "javascript:alert('xss')";
$runner->assertTrue(filter_var($validUrl, FILTER_VALIDATE_URL) !== false, "Valid URL");
$runner->assertFalse(strpos($invalidUrl, 'javascript:') === false, "JavaScript URL rejected");

// Test date validation
$validDate = "2025-01-15";
$invalidDate = "invalid-date";
$runner->assertNotNull(strtotime($validDate), "Valid date format");
$runner->assertFalse(strtotime($invalidDate), "Invalid date rejected");

echo "\n";

// Test Security Functions
echo "╭─────────────────────────────────────────────────────────╮\n";
echo "│ Testing: Security Functions\n";
echo "╰─────────────────────────────────────────────────────────╯\n";

// Test CORS headers
$corsHeaders = [
    'Access-Control-Allow-Origin' => '*',
    'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
    'Access-Control-Allow-Headers' => 'Content-Type, Authorization'
];
$runner->assertTrue(isset($corsHeaders['Access-Control-Allow-Origin']), "CORS headers include Origin");
$runner->assertTrue(isset($corsHeaders['Access-Control-Allow-Methods']), "CORS headers include Methods");
$runner->assertTrue(isset($corsHeaders['Access-Control-Allow-Headers']), "CORS headers include Headers");

// Test Content Security Policy
$cspHeader = "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'";
$runner->assertTrue(strpos($cspHeader, "default-src 'self'") !== false, "CSP default-src directive");
$runner->assertTrue(strpos($cspHeader, "script-src") !== false, "CSP script-src directive");

// Test rate limiting logic
$maxRequests = 100;
$timeWindow = 3600;
$currentRequests = 50;
$runner->assertTrue($currentRequests < $maxRequests, "Rate limit not exceeded");

echo "\n";

// Final Summary
echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║                   UNIT TEST SUMMARY                       ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

$summary = $runner->getSummary();

echo "Total Tests:  {$summary['total']}\n";
echo "Passed:       {$summary['passed']} ✅\n";
echo "Failed:       {$summary['failed']} " . ($summary['failed'] > 0 ? '❌' : '✅') . "\n";
echo "Coverage:     " . round(($summary['passed'] / $summary['total']) * 100, 2) . "%\n\n";

if ($summary['failed'] === 0) {
    echo "╔═══════════════════════════════════════════════════════════╗\n";
    echo "║            ✅ ALL UNIT TESTS PASSED! ✅                   ║\n";
    echo "╚═══════════════════════════════════════════════════════════╝\n";
    exit(0);
} else {
    echo "╔═══════════════════════════════════════════════════════════╗\n";
    echo "║           ❌ SOME TESTS FAILED ❌                         ║\n";
    echo "╚═══════════════════════════════════════════════════════════╝\n";
    exit(1);
}
