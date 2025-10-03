<?php
/**
 * Performance Testing Script
 * Measures API response times, database query performance, and load capacity
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$baseUrl = getenv('API_BASE_URL') ?: 'http://localhost/backend';

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║            Performance Testing Suite                     ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

function benchmarkRequest($url, $iterations = 10) {
    $times = [];
    $successful = 0;
    $failed = 0;

    for ($i = 0; $i < $iterations; $i++) {
        $start = microtime(true);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $duration = (microtime(true) - $start) * 1000;
        $times[] = $duration;

        if ($httpCode === 200) {
            $successful++;
        } else {
            $failed++;
        }
    }

    sort($times);
    $count = count($times);

    return [
        'min' => round(min($times), 2),
        'max' => round(max($times), 2),
        'avg' => round(array_sum($times) / $count, 2),
        'median' => round($times[floor($count / 2)], 2),
        'p95' => round($times[floor($count * 0.95)], 2),
        'p99' => round($times[floor($count * 0.99)], 2),
        'successful' => $successful,
        'failed' => $failed,
        'success_rate' => round(($successful / $iterations) * 100, 2)
    ];
}

function testDatabaseQuery($query, $description) {
    try {
        $db = new Database();
        $conn = $db->getConnection();

        $start = microtime(true);
        $stmt = $conn->query($query);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $duration = (microtime(true) - $start) * 1000;

        return [
            'success' => true,
            'duration' => round($duration, 2),
            'rows' => count($result),
            'description' => $description
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'duration' => 0,
            'error' => $e->getMessage(),
            'description' => $description
        ];
    }
}

echo "╭─────────────────────────────────────────────────────────╮\n";
echo "│ 1. API Endpoint Performance (10 iterations each)        │\n";
echo "╰─────────────────────────────────────────────────────────╯\n\n";

$endpoints = [
    ['url' => '/api/services.php', 'name' => 'Services API', 'target' => 300],
    ['url' => '/api/blogs.php', 'name' => 'Blogs API', 'target' => 300],
    ['url' => '/api/portfolio.php', 'name' => 'Portfolio API', 'target' => 300],
    ['url' => '/api/testimonials.php', 'name' => 'Testimonials API', 'target' => 300],
    ['url' => '/api/settings.php', 'name' => 'Settings API', 'target' => 200],
    ['url' => '/api/translations.php?lang=en', 'name' => 'Translations API', 'target' => 200],
];

$apiResults = [];

foreach ($endpoints as $endpoint) {
    echo "Testing: {$endpoint['name']}\n";
    $result = benchmarkRequest($baseUrl . $endpoint['url']);
    $apiResults[] = array_merge(['name' => $endpoint['name'], 'target' => $endpoint['target']], $result);

    $status = $result['avg'] <= $endpoint['target'] ? '✅' : '❌';
    echo "  {$status} Min: {$result['min']}ms | Avg: {$result['avg']}ms | P95: {$result['p95']}ms | Max: {$result['max']}ms\n";
    echo "     Success Rate: {$result['success_rate']}% ({$result['successful']}/{$result['failed']})\n\n";
}

echo "═══════════════════════════════════════════════════════════\n\n";

echo "╭─────────────────────────────────────────────────────────╮\n";
echo "│ 2. Database Query Performance                            │\n";
echo "╰─────────────────────────────────────────────────────────╯\n\n";

$queries = [
    [
        'query' => "SELECT * FROM services WHERE status = 'published' LIMIT 10",
        'description' => 'Fetch 10 published services'
    ],
    [
        'query' => "SELECT * FROM blogs WHERE status = 'published' ORDER BY created_at DESC LIMIT 10",
        'description' => 'Fetch latest 10 blogs'
    ],
    [
        'query' => "SELECT * FROM portfolio WHERE featured = 1 LIMIT 5",
        'description' => 'Fetch featured portfolio items'
    ],
    [
        'query' => "SELECT * FROM testimonials ORDER BY rating DESC LIMIT 10",
        'description' => 'Fetch top testimonials'
    ],
    [
        'query' => "SELECT COUNT(*) as total FROM leads WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
        'description' => 'Count leads from last 7 days'
    ],
];

$dbResults = [];
$dbPassed = 0;
$dbFailed = 0;

foreach ($queries as $queryData) {
    $result = testDatabaseQuery($queryData['query'], $queryData['description']);
    $dbResults[] = $result;

    if ($result['success']) {
        $status = $result['duration'] < 100 ? '✅' : '⚠️';
        echo "{$status} {$result['description']}\n";
        echo "   Duration: {$result['duration']}ms | Rows: {$result['rows']}\n\n";

        if ($result['duration'] < 100) {
            $dbPassed++;
        } else {
            $dbFailed++;
        }
    } else {
        echo "❌ {$result['description']}\n";
        echo "   Error: {$result['error']}\n\n";
        $dbFailed++;
    }
}

echo "═══════════════════════════════════════════════════════════\n\n";

echo "╭─────────────────────────────────────────────────────────╮\n";
echo "│ 3. Load Testing (Concurrent Requests)                   │\n";
echo "╰─────────────────────────────────────────────────────────╯\n\n";

echo "Simulating 50 concurrent requests to Services API...\n\n";

$concurrentUrl = $baseUrl . '/api/services.php';
$concurrentRequests = 50;
$mh = curl_multi_init();
$handles = [];
$startTime = microtime(true);

for ($i = 0; $i < $concurrentRequests; $i++) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $concurrentUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_multi_add_handle($mh, $ch);
    $handles[] = $ch;
}

$running = null;
do {
    curl_multi_exec($mh, $running);
    curl_multi_select($mh);
} while ($running > 0);

$totalDuration = (microtime(true) - $startTime) * 1000;
$successCount = 0;
$failCount = 0;

foreach ($handles as $ch) {
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode === 200) {
        $successCount++;
    } else {
        $failCount++;
    }
    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
}

curl_multi_close($mh);

$avgPerRequest = round($totalDuration / $concurrentRequests, 2);
$requestsPerSecond = round(($concurrentRequests / $totalDuration) * 1000, 2);

echo "Total Duration: " . round($totalDuration, 2) . "ms\n";
echo "Avg per Request: {$avgPerRequest}ms\n";
echo "Requests/Second: {$requestsPerSecond}\n";
echo "Successful: {$successCount}\n";
echo "Failed: {$failCount}\n";
echo "Success Rate: " . round(($successCount / $concurrentRequests) * 100, 2) . "%\n\n";

$loadTestPassed = $successCount >= ($concurrentRequests * 0.95) && $avgPerRequest < 500;

if ($loadTestPassed) {
    echo "✅ Load test passed - System handles concurrent requests well\n\n";
} else {
    echo "❌ Load test failed - Performance degradation under load\n\n";
}

echo "═══════════════════════════════════════════════════════════\n\n";

echo "╭─────────────────────────────────────────────────────────╮\n";
echo "│ 4. Memory & Resource Usage                              │\n";
echo "╰─────────────────────────────────────────────────────────╯\n\n";

$memoryUsage = memory_get_usage(true);
$memoryPeak = memory_get_peak_usage(true);

echo "Current Memory: " . round($memoryUsage / 1024 / 1024, 2) . " MB\n";
echo "Peak Memory: " . round($memoryPeak / 1024 / 1024, 2) . " MB\n\n";

if ($memoryPeak < 128 * 1024 * 1024) {
    echo "✅ Memory usage within acceptable limits\n\n";
} else {
    echo "⚠️ High memory usage detected\n\n";
}

echo "═══════════════════════════════════════════════════════════\n\n";

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║              PERFORMANCE TEST SUMMARY                     ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

echo "API Endpoint Performance:\n";
echo "┌─────────────────────────────┬─────────┬─────────┬─────────┬────────┐\n";
echo "│ Endpoint                    │ Target  │ Avg     │ P95     │ Status │\n";
echo "├─────────────────────────────┼─────────┼─────────┼─────────┼────────┤\n";

$apiPassed = 0;
$apiFailed = 0;

foreach ($apiResults as $result) {
    $status = $result['avg'] <= $result['target'] ? '✅ PASS' : '❌ FAIL';
    $nameCol = str_pad(substr($result['name'], 0, 27), 27);
    $targetCol = str_pad($result['target'] . 'ms', 7);
    $avgCol = str_pad($result['avg'] . 'ms', 7);
    $p95Col = str_pad($result['p95'] . 'ms', 7);
    $statusCol = str_pad($status, 6);

    echo "│ {$nameCol} │ {$targetCol} │ {$avgCol} │ {$p95Col} │ {$statusCol} │\n";

    if ($result['avg'] <= $result['target']) {
        $apiPassed++;
    } else {
        $apiFailed++;
    }
}

echo "└─────────────────────────────┴─────────┴─────────┴─────────┴────────┘\n\n";

echo "Database Query Performance:\n";
echo "  Target: <100ms per query\n";
echo "  Passed: {$dbPassed} / " . count($queries) . "\n";
echo "  Status: " . ($dbPassed === count($queries) ? "✅ PASS" : "⚠️  NEEDS OPTIMIZATION") . "\n\n";

echo "Load Testing:\n";
echo "  Concurrent Requests: {$concurrentRequests}\n";
echo "  Success Rate: " . round(($successCount / $concurrentRequests) * 100, 2) . "%\n";
echo "  Avg Response: {$avgPerRequest}ms\n";
echo "  Status: " . ($loadTestPassed ? "✅ PASS" : "❌ FAIL") . "\n\n";

$overallPassed = $apiFailed === 0 && $dbPassed === count($queries) && $loadTestPassed;

echo "╔═══════════════════════════════════════════════════════════╗\n";
if ($overallPassed) {
    echo "║       ✅ ALL PERFORMANCE TARGETS MET! ✅                  ║\n";
    echo "║                                                           ║\n";
    echo "║  System meets production performance requirements        ║\n";
    echo "╚═══════════════════════════════════════════════════════════╝\n";
    exit(0);
} else {
    echo "║      ⚠️  PERFORMANCE ISSUES DETECTED ⚠️                   ║\n";
    echo "║                                                           ║\n";
    echo "║  Some targets not met. Review optimization strategies    ║\n";
    echo "╚═══════════════════════════════════════════════════════════╝\n";
    exit(1);
}
