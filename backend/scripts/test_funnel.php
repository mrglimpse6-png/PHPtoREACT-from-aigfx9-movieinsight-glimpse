<?php
/**
 * Funnel Flow Testing Script
 * Tests all funnel scenarios and validates results
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/FunnelTester.php';

echo "=== Funnel Flow Validation Test ===\n\n";

$db = new Database();
$conn = $db->getConnection();
$funnelTester = new FunnelTester($conn);

$scenarios = ['standard', 'high_intent', 'low_intent', 'abandoned'];
$results = [];
$passed = 0;
$failed = 0;

foreach ($scenarios as $scenario) {
    echo "Testing Scenario: {$scenario}\n";

    try {
        // Simulate funnel flow
        $simulation = $funnelTester->simulateFunnelFlow($scenario);

        // Validate simulation structure
        if (!isset($simulation['scenario']) || !isset($simulation['steps']) || !isset($simulation['outcome'])) {
            throw new Exception("Invalid simulation structure");
        }

        // Validate steps
        if (!is_array($simulation['steps']) || empty($simulation['steps'])) {
            throw new Exception("Simulation steps are invalid or empty");
        }

        // Validate outcome
        $validOutcomes = ['converted', 'abandoned', 'browsing'];
        if (!in_array($simulation['outcome'], $validOutcomes)) {
            throw new Exception("Invalid outcome: " . $simulation['outcome']);
        }

        // Check expected outcomes for each scenario
        $expectedOutcomes = [
            'standard' => ['converted', 'browsing'],
            'high_intent' => ['converted'],
            'low_intent' => ['browsing', 'abandoned'],
            'abandoned' => ['abandoned']
        ];

        if (!in_array($simulation['outcome'], $expectedOutcomes[$scenario])) {
            throw new Exception("Unexpected outcome '{$simulation['outcome']}' for scenario '{$scenario}'");
        }

        echo "  ✅ Steps: " . count($simulation['steps']) . "\n";
        echo "  ✅ Outcome: {$simulation['outcome']}\n";
        echo "  ✅ Duration: {$simulation['duration']}s\n";

        $results[$scenario] = [
            'status' => 'PASS',
            'steps' => count($simulation['steps']),
            'outcome' => $simulation['outcome'],
            'duration' => $simulation['duration']
        ];

        $passed++;
        echo "  ✅ PASSED\n\n";

    } catch (Exception $e) {
        echo "  ❌ FAILED: " . $e->getMessage() . "\n\n";
        $results[$scenario] = [
            'status' => 'FAIL',
            'error' => $e->getMessage()
        ];
        $failed++;
    }
}

// Test funnel reporting
echo "Testing Funnel Report Generation\n";

try {
    // Run multiple simulations to generate data
    for ($i = 0; $i < 10; $i++) {
        $randomScenario = $scenarios[array_rand($scenarios)];
        $funnelTester->simulateFunnelFlow($randomScenario);
    }

    $report = $funnelTester->getFunnelReport();

    // Validate report structure
    if (!isset($report['totalSessions']) || !isset($report['conversions']) || !isset($report['conversionRate'])) {
        throw new Exception("Invalid report structure");
    }

    echo "  ✅ Total Sessions: {$report['totalSessions']}\n";
    echo "  ✅ Conversions: {$report['conversions']}\n";
    echo "  ✅ Conversion Rate: {$report['conversionRate']}%\n";
    echo "  ✅ Avg Duration: {$report['avgSessionDuration']}s\n";

    if (isset($report['stepConversions'])) {
        echo "  ✅ Step Conversions Tracked\n";
    }

    $passed++;
    echo "  ✅ PASSED\n\n";

} catch (Exception $e) {
    echo "  ❌ FAILED: " . $e->getMessage() . "\n\n";
    $failed++;
}

// Summary
echo "=== Test Summary ===\n";
echo "Total Tests: " . ($passed + $failed) . "\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";

if ($failed === 0) {
    echo "\n✅ ALL TESTS PASSED\n";
    echo "Funnel tester is working correctly!\n";
    exit(0);
} else {
    echo "\n❌ SOME TESTS FAILED\n";
    echo "Review the errors above and fix issues.\n";
    exit(1);
}
