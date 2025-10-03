<?php
/**
 * End-to-End (E2E) Testing Script
 * Simulates complete user journeys through the platform
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$baseUrl = getenv('API_BASE_URL') ?: 'http://localhost/backend';
$testResults = [];
$passed = 0;
$failed = 0;

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║              End-to-End Test Suite                       ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HEADER, false);

    $defaultHeaders = ['Content-Type: application/json'];
    $allHeaders = array_merge($defaultHeaders, $headers);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'status' => $httpCode,
        'response' => $response,
        'data' => json_decode($response, true),
        'error' => $error
    ];
}

function testResult($testName, $passed, $details = '') {
    global $testResults;
    if ($passed) {
        echo "  ✅ {$testName}\n";
        if ($details) echo "     {$details}\n";
        $testResults[] = ['name' => $testName, 'status' => 'PASS'];
        return true;
    } else {
        echo "  ❌ {$testName}\n";
        if ($details) echo "     {$details}\n";
        $testResults[] = ['name' => $testName, 'status' => 'FAIL'];
        return false;
    }
}

echo "╭─────────────────────────────────────────────────────────╮\n";
echo "│ Journey 1: Anonymous Visitor → Lead Capture            │\n";
echo "╰─────────────────────────────────────────────────────────╯\n\n";

$testEmail = 'e2e_test_' . time() . '@example.com';
$journey1Passed = true;

echo "Step 1: Homepage Load\n";
$homepage = makeRequest($baseUrl . '/api/settings.php');
$journey1Passed &= testResult(
    "Homepage settings loaded",
    $homepage['status'] === 200,
    "Status: {$homepage['status']}"
);

echo "\nStep 2: Services List\n";
$services = makeRequest($baseUrl . '/api/services.php');
$journey1Passed &= testResult(
    "Services retrieved",
    $services['status'] === 200 && isset($services['data']['data']),
    "Found " . (is_array($services['data']['data'] ?? []) ? count($services['data']['data']) : 0) . " services"
);

echo "\nStep 3: Newsletter Signup (Lead Capture)\n";
$newsletter = makeRequest(
    $baseUrl . '/api/newsletter.php?action=subscribe',
    'POST',
    [
        'email' => $testEmail,
        'name' => 'E2E Test User',
        'source' => 'e2e_test'
    ]
);
$journey1Passed &= testResult(
    "Newsletter subscription",
    in_array($newsletter['status'], [200, 201]),
    "Lead captured successfully"
);

echo "\nStep 4: Contact Form Submission\n";
$contact = makeRequest(
    $baseUrl . '/api/contact.php',
    'POST',
    [
        'name' => 'E2E Test User',
        'email' => $testEmail,
        'phone' => '+1234567890',
        'service' => 'Logo Design',
        'budget' => '$500-1000',
        'timeline' => '1-2 weeks',
        'message' => 'E2E test contact form submission'
    ]
);
$journey1Passed &= testResult(
    "Contact form submitted",
    in_array($contact['status'], [200, 201]),
    "Contact inquiry recorded"
);

if ($journey1Passed) {
    echo "\n✅ Journey 1: PASSED - Full visitor to lead flow successful\n\n";
    $passed++;
} else {
    echo "\n❌ Journey 1: FAILED\n\n";
    $failed++;
}

echo "═══════════════════════════════════════════════════════════\n\n";

echo "╭─────────────────────────────────────────────────────────╮\n";
echo "│ Journey 2: Content Discovery Flow                       │\n";
echo "╰─────────────────────────────────────────────────────────╯\n\n";

$journey2Passed = true;

echo "Step 1: Browse Blog Posts\n";
$blogs = makeRequest($baseUrl . '/api/blogs.php');
$journey2Passed &= testResult(
    "Blog posts retrieved",
    $blogs['status'] === 200 && isset($blogs['data']['data']),
    "Found " . (is_array($blogs['data']['data'] ?? []) ? count($blogs['data']['data']) : 0) . " blog posts"
);

echo "\nStep 2: View Portfolio Items\n";
$portfolio = makeRequest($baseUrl . '/api/portfolio.php');
$journey2Passed &= testResult(
    "Portfolio items retrieved",
    $portfolio['status'] === 200 && isset($portfolio['data']['data']),
    "Found " . (is_array($portfolio['data']['data'] ?? []) ? count($portfolio['data']['data']) : 0) . " portfolio items"
);

echo "\nStep 3: Read Testimonials\n";
$testimonials = makeRequest($baseUrl . '/api/testimonials.php');
$journey2Passed &= testResult(
    "Testimonials retrieved",
    $testimonials['status'] === 200 && isset($testimonials['data']['data']),
    "Found " . (is_array($testimonials['data']['data'] ?? []) ? count($testimonials['data']['data']) : 0) . " testimonials"
);

echo "\nStep 4: Check Service Details\n";
if (isset($services['data']['data']) && is_array($services['data']['data']) && count($services['data']['data']) > 0) {
    $firstService = $services['data']['data'][0];
    $journey2Passed &= testResult(
        "Service details available",
        isset($firstService['title']) && isset($firstService['description']),
        "Service: " . ($firstService['title'] ?? 'Unknown')
    );
} else {
    $journey2Passed &= testResult("Service details available", false, "No services found");
}

if ($journey2Passed) {
    echo "\n✅ Journey 2: PASSED - Content discovery flow successful\n\n";
    $passed++;
} else {
    echo "\n❌ Journey 2: FAILED\n\n";
    $failed++;
}

echo "═══════════════════════════════════════════════════════════\n\n";

echo "╭─────────────────────────────────────────────────────────╮\n";
echo "│ Journey 3: Multi-language Support                       │\n";
echo "╰─────────────────────────────────────────────────────────╯\n\n";

$journey3Passed = true;

echo "Step 1: Fetch English Content\n";
$translationsEN = makeRequest($baseUrl . '/api/translations.php?lang=en');
$journey3Passed &= testResult(
    "English translations loaded",
    $translationsEN['status'] === 200 && isset($translationsEN['data']['data']),
    "English language pack available"
);

echo "\nStep 2: Fetch Arabic Content\n";
$translationsAR = makeRequest($baseUrl . '/api/translations.php?lang=ar');
$journey3Passed &= testResult(
    "Arabic translations loaded",
    $translationsAR['status'] === 200 && isset($translationsAR['data']['data']),
    "Arabic language pack available"
);

echo "\nStep 3: Verify Translation Keys\n";
if (isset($translationsEN['data']['data']) && is_array($translationsEN['data']['data'])) {
    $enKeys = array_keys($translationsEN['data']['data']);
    $journey3Passed &= testResult(
        "Translation keys present",
        count($enKeys) > 0,
        "Found " . count($enKeys) . " translation keys"
    );
} else {
    $journey3Passed &= testResult("Translation keys present", false, "No translation data");
}

if ($journey3Passed) {
    echo "\n✅ Journey 3: PASSED - Multi-language support verified\n\n";
    $passed++;
} else {
    echo "\n❌ Journey 3: FAILED\n\n";
    $failed++;
}

echo "═══════════════════════════════════════════════════════════\n\n";

echo "╭─────────────────────────────────────────────────────────╮\n";
echo "│ Journey 4: API Error Handling                           │\n";
echo "╰─────────────────────────────────────────────────────────╯\n\n";

$journey4Passed = true;

echo "Step 1: Invalid Newsletter Submission\n";
$badNewsletter = makeRequest(
    $baseUrl . '/api/newsletter.php?action=subscribe',
    'POST',
    ['email' => 'invalid-email']
);
$journey4Passed &= testResult(
    "Invalid email rejected",
    in_array($badNewsletter['status'], [400, 422]),
    "Status: {$badNewsletter['status']}"
);

echo "\nStep 2: Missing Required Fields in Contact\n";
$badContact = makeRequest(
    $baseUrl . '/api/contact.php',
    'POST',
    ['name' => 'Test']
);
$journey4Passed &= testResult(
    "Incomplete contact rejected",
    in_array($badContact['status'], [400, 422]),
    "Status: {$badContact['status']}"
);

echo "\nStep 3: Non-existent Blog Post\n";
$nonExistentBlog = makeRequest($baseUrl . '/api/blogs.php?id=999999');
$journey4Passed &= testResult(
    "Non-existent blog handled",
    in_array($nonExistentBlog['status'], [404, 200]),
    "Status: {$nonExistentBlog['status']}"
);

if ($journey4Passed) {
    echo "\n✅ Journey 4: PASSED - Error handling works correctly\n\n";
    $passed++;
} else {
    echo "\n❌ Journey 4: FAILED\n\n";
    $failed++;
}

echo "═══════════════════════════════════════════════════════════\n\n";

echo "╭─────────────────────────────────────────────────────────╮\n";
echo "│ Journey 5: Performance & Response Times                 │\n";
echo "╰─────────────────────────────────────────────────────────╯\n\n";

$journey5Passed = true;

echo "Testing API response times...\n\n";

$endpoints = [
    ['url' => '/api/services.php', 'name' => 'Services', 'threshold' => 300],
    ['url' => '/api/blogs.php', 'name' => 'Blogs', 'threshold' => 300],
    ['url' => '/api/portfolio.php', 'name' => 'Portfolio', 'threshold' => 300],
    ['url' => '/api/testimonials.php', 'name' => 'Testimonials', 'threshold' => 300],
    ['url' => '/api/settings.php', 'name' => 'Settings', 'threshold' => 200],
];

foreach ($endpoints as $endpoint) {
    $start = microtime(true);
    $response = makeRequest($baseUrl . $endpoint['url']);
    $duration = round((microtime(true) - $start) * 1000, 2);

    $journey5Passed &= testResult(
        "{$endpoint['name']} response time",
        $duration < $endpoint['threshold'] && $response['status'] === 200,
        "{$duration}ms (threshold: {$endpoint['threshold']}ms)"
    );
}

if ($journey5Passed) {
    echo "\n✅ Journey 5: PASSED - Performance targets met\n\n";
    $passed++;
} else {
    echo "\n❌ Journey 5: FAILED - Performance issues detected\n\n";
    $failed++;
}

echo "═══════════════════════════════════════════════════════════\n\n";

$totalJourneys = $passed + $failed;

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║                E2E TEST SUMMARY                           ║\n";
echo "╠═══════════════════════════════════════════════════════════╣\n";
echo "║ Total Journeys:   " . str_pad($totalJourneys, 39) . "║\n";
echo "║ Passed:           " . str_pad($passed . " ✅", 45) . "║\n";
echo "║ Failed:           " . str_pad($failed . ($failed > 0 ? " ❌" : " ✅"), 45) . "║\n";
echo "╠═══════════════════════════════════════════════════════════╣\n";
echo "║ Test Coverage:                                            ║\n";
echo "║   - Visitor to Lead Flow                                  ║\n";
echo "║   - Content Discovery                                     ║\n";
echo "║   - Multi-language Support                                ║\n";
echo "║   - Error Handling                                        ║\n";
echo "║   - Performance Testing                                   ║\n";
echo "╠═══════════════════════════════════════════════════════════╣\n";

if ($failed === 0) {
    echo "║         ✅ ALL E2E TESTS PASSED! ✅                       ║\n";
    echo "║                                                           ║\n";
    echo "║  All user journeys work as expected!                     ║\n";
    echo "╚═══════════════════════════════════════════════════════════╝\n";
    exit(0);
} else {
    echo "║        ❌ SOME TESTS FAILED ❌                            ║\n";
    echo "║                                                           ║\n";
    echo "║  Please review the failures above.                       ║\n";
    echo "╚═══════════════════════════════════════════════════════════╝\n";
    exit(1);
}
