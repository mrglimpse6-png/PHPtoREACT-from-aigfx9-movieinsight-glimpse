<?php
/**
 * API Endpoint Testing Script
 * Tests all critical API endpoints
 */

$baseUrl = getenv('API_BASE_URL') ?: 'http://localhost/backend';
$testResults = [];
$passed = 0;
$failed = 0;
$totalTime = 0;

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║           API Endpoint Integration Tests                 ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";
echo "Base URL: {$baseUrl}\n\n";

/**
 * Test an endpoint
 */
function testEndpoint($url, $method = 'GET', $data = null, $token = null) {
    $startTime = microtime(true);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, false);

    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = "Authorization: Bearer {$token}";
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    $endTime = microtime(true);
    $responseTime = round(($endTime - $startTime) * 1000, 2);

    return [
        'status' => $httpCode,
        'response' => $response,
        'error' => $error,
        'time' => $responseTime
    ];
}

// Test Cases
$tests = [
    [
        'name' => 'Get Services (Public)',
        'method' => 'GET',
        'url' => '/api/services.php',
        'expectedStatus' => 200,
        'validateResponse' => function($response) {
            $data = json_decode($response, true);
            return isset($data['success']) && $data['success'] === true && isset($data['data']);
        }
    ],
    [
        'name' => 'Get Portfolio (Public)',
        'method' => 'GET',
        'url' => '/api/portfolio.php',
        'expectedStatus' => 200,
        'validateResponse' => function($response) {
            $data = json_decode($response, true);
            return isset($data['success']) && isset($data['data']);
        }
    ],
    [
        'name' => 'Get Testimonials (Public)',
        'method' => 'GET',
        'url' => '/api/testimonials.php',
        'expectedStatus' => 200,
        'validateResponse' => function($response) {
            $data = json_decode($response, true);
            return isset($data['success']) && isset($data['data']);
        }
    ],
    [
        'name' => 'Get Blogs (Public)',
        'method' => 'GET',
        'url' => '/api/blogs.php',
        'expectedStatus' => 200,
        'validateResponse' => function($response) {
            $data = json_decode($response, true);
            return isset($data['success']) && isset($data['data']);
        }
    ],
    [
        'name' => 'Submit Contact Form',
        'method' => 'POST',
        'url' => '/api/contact.php',
        'data' => [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+1234567890',
            'service' => 'Test Service',
            'budget' => '$100-500',
            'timeline' => '1 week',
            'message' => 'This is a test message'
        ],
        'expectedStatus' => [200, 201],
        'validateResponse' => function($response) {
            $data = json_decode($response, true);
            return isset($data['success']);
        }
    ],
    [
        'name' => 'Subscribe to Newsletter',
        'method' => 'POST',
        'url' => '/api/newsletter.php?action=subscribe',
        'data' => [
            'email' => 'test-newsletter@example.com',
            'name' => 'Test Subscriber'
        ],
        'expectedStatus' => [200, 201],
        'validateResponse' => function($response) {
            $data = json_decode($response, true);
            return isset($data['success']);
        }
    ],
    [
        'name' => 'Get Carousel Slides',
        'method' => 'GET',
        'url' => '/api/carousel.php?name=hero',
        'expectedStatus' => 200,
        'validateResponse' => function($response) {
            $data = json_decode($response, true);
            return isset($data['success']) && isset($data['data']);
        }
    ],
    [
        'name' => 'Get Page by Slug',
        'method' => 'GET',
        'url' => '/api/pages.php?slug=home',
        'expectedStatus' => [200, 404],
        'validateResponse' => function($response) {
            $data = json_decode($response, true);
            return isset($data['success']);
        }
    ],
    [
        'name' => 'Get Global Settings',
        'method' => 'GET',
        'url' => '/api/settings.php',
        'expectedStatus' => 200,
        'validateResponse' => function($response) {
            $data = json_decode($response, true);
            return isset($data['success']);
        }
    ],
    [
        'name' => 'Auth - Login (Invalid Credentials)',
        'method' => 'POST',
        'url' => '/api/auth.php?action=login',
        'data' => [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword'
        ],
        'expectedStatus' => [401, 400],
        'validateResponse' => function($response) {
            $data = json_decode($response, true);
            return isset($data['success']) && $data['success'] === false;
        }
    ]
];

// Run tests
foreach ($tests as $test) {
    echo "╭─────────────────────────────────────────────────────────╮\n";
    echo "│ {$test['name']}\n";
    echo "╰─────────────────────────────────────────────────────────╯\n";

    $url = $baseUrl . $test['url'];
    $result = testEndpoint(
        $url,
        $test['method'],
        $test['data'] ?? null,
        $test['token'] ?? null
    );

    $expectedStatus = is_array($test['expectedStatus']) ? $test['expectedStatus'] : [$test['expectedStatus']];
    $statusOk = in_array($result['status'], $expectedStatus);

    $responseOk = false;
    if ($statusOk && !empty($result['response'])) {
        if (isset($test['validateResponse'])) {
            $responseOk = $test['validateResponse']($result['response']);
        } else {
            $responseOk = true;
        }
    }

    if ($statusOk && $responseOk) {
        echo "  ✅ PASSED\n";
        echo "  Method: {$test['method']}\n";
        echo "  Status: {$result['status']}\n";
        echo "  Response Time: {$result['time']}ms\n";
        $testResults[$test['name']] = [
            'status' => 'PASS',
            'time' => $result['time']
        ];
        $totalTime += $result['time'];
        $passed++;
    } else {
        echo "  ❌ FAILED\n";
        echo "  Method: {$test['method']}\n";
        echo "  Expected Status: " . implode(' or ', $expectedStatus) . "\n";
        echo "  Actual Status: {$result['status']}\n";
        echo "  Response Time: {$result['time']}ms\n";
        if (!$responseOk && !empty($result['response'])) {
            echo "  Response: " . substr($result['response'], 0, 200) . "...\n";
        }
        if ($result['error']) {
            echo "  Error: {$result['error']}\n";
        }
        $testResults[$test['name']] = [
            'status' => 'FAIL',
            'time' => $result['time']
        ];
        $totalTime += $result['time'];
        $failed++;
    }
    echo "\n";
}

// Summary
$totalTests = $passed + $failed;
$avgTime = $totalTests > 0 ? round($totalTime / $totalTests, 2) : 0;

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║                  TEST SUMMARY                             ║\n";
echo "╠═══════════════════════════════════════════════════════════╣\n";
echo "║ Total Tests:      " . str_pad($totalTests, 39) . "║\n";
echo "║ Passed:           " . str_pad($passed . " ✅", 45) . "║\n";
echo "║ Failed:           " . str_pad($failed . ($failed > 0 ? " ❌" : " ✅"), 45) . "║\n";
echo "╠═══════════════════════════════════════════════════════════╣\n";
echo "║ Total Time:       " . str_pad($totalTime . "ms", 39) . "║\n";
echo "║ Average Time:     " . str_pad($avgTime . "ms", 39) . "║\n";
echo "╠═══════════════════════════════════════════════════════════╣\n";

if ($failed === 0) {
    echo "║         ✅ ALL API TESTS PASSED! ✅                       ║\n";
    echo "╚═══════════════════════════════════════════════════════════╝\n";
    exit(0);
} else {
    echo "║        ❌ SOME TESTS FAILED ❌                            ║\n";
    echo "║                                                           ║\n";
    echo "║  Check the errors above for details.                     ║\n";
    echo "╚═══════════════════════════════════════════════════════════╝\n";
    exit(1);
}
