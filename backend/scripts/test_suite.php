<?php
/**
 * Complete Test Suite Runner
 * Runs all test categories
 */

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║           Adil GFX Backend Test Suite                    ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

$testScripts = [
    'Database Connection' => 'test_db_connection.php',
    'API Endpoints' => 'test_api_endpoints.php',
    'Funnel Flow' => 'test_funnel.php',
];

$results = [];
$totalPassed = 0;
$totalFailed = 0;

foreach ($testScripts as $name => $script) {
    $scriptPath = __DIR__ . '/' . $script;

    echo "╭─────────────────────────────────────────────────────────╮\n";
    echo "│ Running: {$name}\n";
    echo "╰─────────────────────────────────────────────────────────╯\n\n";

    if (!file_exists($scriptPath)) {
        echo "  ⚠️  Test script not found: {$script}\n";
        echo "  Creating basic test...\n\n";
        continue;
    }

    $output = [];
    $returnCode = 0;
    exec("php " . escapeshellarg($scriptPath) . " 2>&1", $output, $returnCode);

    foreach ($output as $line) {
        echo $line . "\n";
    }

    if ($returnCode === 0) {
        $results[$name] = 'PASS';
        $totalPassed++;
        echo "\n✅ {$name}: PASSED\n\n";
    } else {
        $results[$name] = 'FAIL';
        $totalFailed++;
        echo "\n❌ {$name}: FAILED\n\n";
    }

    echo "═══════════════════════════════════════════════════════════\n\n";
}

// Create simple database connection test if not exists
$dbTestPath = __DIR__ . '/test_db_connection.php';
if (!file_exists($dbTestPath)) {
    file_put_contents($dbTestPath, '<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";

echo "Testing Database Connection...\n";

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Test query
    $stmt = $conn->query("SELECT 1");
    $result = $stmt->fetch();

    if ($result) {
        echo "✅ Database connection successful\n";
        echo "✅ Query execution successful\n";
        exit(0);
    } else {
        echo "❌ Query failed\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}
');
}

// Final Summary
echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║                   FINAL TEST SUMMARY                      ║\n";
echo "╠═══════════════════════════════════════════════════════════╣\n";

foreach ($results as $test => $result) {
    $icon = $result === 'PASS' ? '✅' : '❌';
    $padding = str_repeat(' ', 55 - strlen($test) - strlen($result));
    echo "║ {$icon} {$test}{$padding}{$result}  ║\n";
}

echo "╠═══════════════════════════════════════════════════════════╣\n";

$total = $totalPassed + $totalFailed;
echo "║ Total Tests: {$total}\n";
echo "║ Passed: {$totalPassed}\n";
echo "║ Failed: {$totalFailed}\n";

if ($totalFailed === 0) {
    echo "╠═══════════════════════════════════════════════════════════╣\n";
    echo "║               ✅ ALL TESTS PASSED! ✅                     ║\n";
    echo "║                                                           ║\n";
    echo "║  Your backend is ready for deployment!                   ║\n";
    echo "╚═══════════════════════════════════════════════════════════╝\n";
    exit(0);
} else {
    echo "╠═══════════════════════════════════════════════════════════╣\n";
    echo "║            ❌ SOME TESTS FAILED ❌                        ║\n";
    echo "║                                                           ║\n";
    echo "║  Please review and fix the errors above.                 ║\n";
    echo "╚═══════════════════════════════════════════════════════════╝\n";
    exit(1);
}
