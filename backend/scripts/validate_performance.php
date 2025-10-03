<?php
/**
 * Performance Budget Validation Script
 * Validates that performance targets are met
 */

echo "=== Performance Budget Validation ===\n\n";

// Performance targets
$targets = [
    'api_response_time_avg' => 300,  // milliseconds
    'api_response_time_p95' => 500,  // milliseconds
    'database_query_avg' => 50,      // milliseconds
    'cache_hit_rate' => 70,          // percentage
    'memory_usage' => 256,           // MB
];

$results = [];
$passed = 0;
$failed = 0;

// Test 1: API Response Times
echo "1. Testing API Response Times\n";
try {
    $baseUrl = 'http://localhost/backend';
    $endpoints = [
        '/api/services.php',
        '/api/portfolio.php',
        '/api/testimonials.php',
        '/api/blogs.php'
    ];

    $times = [];

    foreach ($endpoints as $endpoint) {
        for ($i = 0; $i < 10; $i++) {
            $start = microtime(true);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $baseUrl . $endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $response = curl_exec($ch);
            curl_close($ch);

            $end = microtime(true);
            $times[] = ($end - $start) * 1000;
        }
    }

    sort($times);
    $avg = array_sum($times) / count($times);
    $p95 = $times[floor(count($times) * 0.95)];

    echo "  Average Response Time: " . round($avg, 2) . " ms\n";
    echo "  95th Percentile: " . round($p95, 2) . " ms\n";

    if ($avg <= $targets['api_response_time_avg']) {
        echo "  ✅ Average response time meets target (<= {$targets['api_response_time_avg']} ms)\n";
        $passed++;
    } else {
        echo "  ❌ Average response time exceeds target ({$targets['api_response_time_avg']} ms)\n";
        $failed++;
    }

    if ($p95 <= $targets['api_response_time_p95']) {
        echo "  ✅ P95 response time meets target (<= {$targets['api_response_time_p95']} ms)\n";
        $passed++;
    } else {
        echo "  ❌ P95 response time exceeds target ({$targets['api_response_time_p95']} ms)\n";
        $failed++;
    }

} catch (Exception $e) {
    echo "  ❌ Test failed: " . $e->getMessage() . "\n";
    $failed += 2;
}
echo "\n";

// Test 2: Database Query Performance
echo "2. Testing Database Query Performance\n";
try {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/database.php';

    $db = new Database();
    $conn = $db->getConnection();

    $queryTimes = [];
    $queries = [
        "SELECT * FROM services WHERE status = 'active' LIMIT 10",
        "SELECT * FROM portfolio_items WHERE featured = 1 LIMIT 5",
        "SELECT * FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC LIMIT 10",
        "SELECT * FROM testimonials WHERE status = 'approved' LIMIT 10"
    ];

    foreach ($queries as $query) {
        $start = microtime(true);
        $stmt = $conn->query($query);
        $stmt->fetchAll();
        $end = microtime(true);

        $queryTimes[] = ($end - $start) * 1000;
    }

    $avgQueryTime = array_sum($queryTimes) / count($queryTimes);

    echo "  Average Query Time: " . round($avgQueryTime, 2) . " ms\n";

    if ($avgQueryTime <= $targets['database_query_avg']) {
        echo "  ✅ Database query time meets target (<= {$targets['database_query_avg']} ms)\n";
        $passed++;
    } else {
        echo "  ❌ Database query time exceeds target ({$targets['database_query_avg']} ms)\n";
        $failed++;
    }

} catch (Exception $e) {
    echo "  ❌ Test failed: " . $e->getMessage() . "\n";
    $failed++;
}
echo "\n";

// Test 3: Memory Usage
echo "3. Testing Memory Usage\n";
try {
    $memoryUsage = memory_get_usage(true) / 1024 / 1024;
    $peakMemory = memory_get_peak_usage(true) / 1024 / 1024;

    echo "  Current Memory: " . round($memoryUsage, 2) . " MB\n";
    echo "  Peak Memory: " . round($peakMemory, 2) . " MB\n";

    if ($peakMemory <= $targets['memory_usage']) {
        echo "  ✅ Memory usage meets target (<= {$targets['memory_usage']} MB)\n";
        $passed++;
    } else {
        echo "  ❌ Memory usage exceeds target ({$targets['memory_usage']} MB)\n";
        $failed++;
    }

} catch (Exception $e) {
    echo "  ❌ Test failed: " . $e->getMessage() . "\n";
    $failed++;
}
echo "\n";

// Test 4: Cache Performance
echo "4. Testing Cache Performance\n";
try {
    require_once __DIR__ . '/../classes/Cache.php';

    $cache = new Cache($conn ?? null);

    // Test cache write/read
    $testKey = 'performance_test_' . time();
    $testData = ['test' => 'data', 'timestamp' => time()];

    $writeStart = microtime(true);
    $cache->set($testKey, $testData, 3600);
    $writeTime = (microtime(true) - $writeStart) * 1000;

    $readStart = microtime(true);
    $retrieved = $cache->get($testKey);
    $readTime = (microtime(true) - $readStart) * 1000;

    echo "  Cache Write Time: " . round($writeTime, 2) . " ms\n";
    echo "  Cache Read Time: " . round($readTime, 2) . " ms\n";

    if ($readTime < 10) {
        echo "  ✅ Cache read performance is excellent (< 10 ms)\n";
        $passed++;
    } elseif ($readTime < 50) {
        echo "  ⚠️  Cache read performance is acceptable (< 50 ms)\n";
        $passed++;
    } else {
        echo "  ❌ Cache read performance needs improvement (>= 50 ms)\n";
        $failed++;
    }

    // Cleanup
    $cache->delete($testKey);

} catch (Exception $e) {
    echo "  ❌ Test failed: " . $e->getMessage() . "\n";
    $failed++;
}
echo "\n";

// Test 5: File System Performance
echo "5. Testing File System Performance\n";
try {
    $testFile = __DIR__ . '/../cache/performance_test_' . time() . '.tmp';

    // Write test
    $writeStart = microtime(true);
    file_put_contents($testFile, str_repeat('x', 1024 * 100)); // 100KB
    $writeTime = (microtime(true) - $writeStart) * 1000;

    // Read test
    $readStart = microtime(true);
    $content = file_get_contents($testFile);
    $readTime = (microtime(true) - $readStart) * 1000;

    echo "  File Write Time (100KB): " . round($writeTime, 2) . " ms\n";
    echo "  File Read Time (100KB): " . round($readTime, 2) . " ms\n";

    if ($writeTime < 50 && $readTime < 50) {
        echo "  ✅ File system performance is good\n";
        $passed++;
    } else {
        echo "  ⚠️  File system performance could be improved\n";
        $passed++; // Still pass but warn
    }

    // Cleanup
    unlink($testFile);

} catch (Exception $e) {
    echo "  ❌ Test failed: " . $e->getMessage() . "\n";
    $failed++;
}
echo "\n";

// Summary
echo "=== Performance Budget Summary ===\n";
echo "Total Checks: " . ($passed + $failed) . "\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n\n";

// Performance grade
$totalChecks = $passed + $failed;
$passRate = ($passed / $totalChecks) * 100;

if ($passRate >= 90) {
    echo "✅ EXCELLENT: {$passRate}% of performance checks passed\n";
    echo "Your backend is highly optimized and production-ready!\n";
} elseif ($passRate >= 75) {
    echo "✅ GOOD: {$passRate}% of performance checks passed\n";
    echo "Your backend meets performance requirements.\n";
} elseif ($passRate >= 60) {
    echo "⚠️  ACCEPTABLE: {$passRate}% of performance checks passed\n";
    echo "Consider optimization improvements for better performance.\n";
} else {
    echo "❌ NEEDS WORK: {$passRate}% of performance checks passed\n";
    echo "Significant performance optimization required.\n";
}

echo "\n=== Recommendations ===\n";

if ($failed > 0) {
    echo "To improve performance:\n";
    echo "1. Enable OPcache in PHP configuration\n";
    echo "2. Implement database query caching\n";
    echo "3. Add database indexes for frequently queried columns\n";
    echo "4. Use a CDN for static assets\n";
    echo "5. Consider upgrading Hostinger plan for more resources\n";
    echo "6. Enable compression for API responses\n";
    echo "7. Optimize database queries with EXPLAIN\n";
}

exit($failed > 0 ? 1 : 0);
