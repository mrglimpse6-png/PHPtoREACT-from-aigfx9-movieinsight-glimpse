<?php
/**
 * Google PageSpeed Insights Integration
 * Runs performance tests and tracks page speed metrics
 */

require_once __DIR__ . '/APIIntegration.php';

class PageSpeedInsightsIntegration extends APIIntegration {
    private $apiKey;
    private $apiUrl = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';

    public function __construct() {
        parent::__construct('PageSpeed Insights');

        $this->apiKey = $this->getConfig('api_key') ?? $_ENV['GOOGLE_API_KEY'] ?? '';
    }

    public function testConnection() {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'Google API key not configured'
            ];
        }

        $response = $this->runTest('https://google.com', 'mobile');

        return $response;
    }

    public function runTest($url, $strategy = 'mobile', $categories = ['performance', 'accessibility', 'best-practices', 'seo']) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'PageSpeed Insights integration is disabled'];
        }

        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'Google API key not configured'];
        }

        $params = [
            'url' => $url,
            'key' => $this->apiKey,
            'strategy' => $strategy,
            'category' => $categories
        ];

        $response = $this->makeRequest(
            $this->apiUrl . '?' . http_build_query($params),
            'GET',
            null,
            []
        );

        if ($response['success']) {
            $this->saveResultToDatabase($url, $strategy, $response['data']);
        }

        return $response;
    }

    public function runMobileTest($url) {
        return $this->runTest($url, 'mobile');
    }

    public function runDesktopTest($url) {
        return $this->runTest($url, 'desktop');
    }

    public function runBothTests($url) {
        $mobileResult = $this->runMobileTest($url);
        $desktopResult = $this->runDesktopTest($url);

        return [
            'success' => $mobileResult['success'] && $desktopResult['success'],
            'mobile' => $mobileResult,
            'desktop' => $desktopResult
        ];
    }

    public function getScores($testResult) {
        if (!isset($testResult['data']['lighthouseResult']['categories'])) {
            return ['success' => false, 'error' => 'Invalid test result format'];
        }

        $categories = $testResult['data']['lighthouseResult']['categories'];

        $scores = [
            'performance' => isset($categories['performance']) ? round($categories['performance']['score'] * 100) : null,
            'accessibility' => isset($categories['accessibility']) ? round($categories['accessibility']['score'] * 100) : null,
            'best_practices' => isset($categories['best-practices']) ? round($categories['best-practices']['score'] * 100) : null,
            'seo' => isset($categories['seo']) ? round($categories['seo']['score'] * 100) : null
        ];

        return [
            'success' => true,
            'scores' => $scores
        ];
    }

    public function getMetrics($testResult) {
        if (!isset($testResult['data']['lighthouseResult']['audits'])) {
            return ['success' => false, 'error' => 'Invalid test result format'];
        }

        $audits = $testResult['data']['lighthouseResult']['audits'];

        $metrics = [
            'first_contentful_paint' => $audits['first-contentful-paint']['displayValue'] ?? null,
            'speed_index' => $audits['speed-index']['displayValue'] ?? null,
            'largest_contentful_paint' => $audits['largest-contentful-paint']['displayValue'] ?? null,
            'time_to_interactive' => $audits['interactive']['displayValue'] ?? null,
            'total_blocking_time' => $audits['total-blocking-time']['displayValue'] ?? null,
            'cumulative_layout_shift' => $audits['cumulative-layout-shift']['displayValue'] ?? null
        ];

        return [
            'success' => true,
            'metrics' => $metrics
        ];
    }

    public function getOpportunities($testResult) {
        if (!isset($testResult['data']['lighthouseResult']['audits'])) {
            return ['success' => false, 'error' => 'Invalid test result format'];
        }

        $audits = $testResult['data']['lighthouseResult']['audits'];
        $opportunities = [];

        foreach ($audits as $auditId => $audit) {
            if (isset($audit['details']['type']) &&
                $audit['details']['type'] === 'opportunity' &&
                isset($audit['details']['overallSavingsMs']) &&
                $audit['details']['overallSavingsMs'] > 0) {

                $opportunities[] = [
                    'id' => $auditId,
                    'title' => $audit['title'] ?? '',
                    'description' => $audit['description'] ?? '',
                    'savings_ms' => $audit['details']['overallSavingsMs'] ?? 0,
                    'savings_bytes' => $audit['details']['overallSavingsBytes'] ?? 0
                ];
            }
        }

        usort($opportunities, function($a, $b) {
            return $b['savings_ms'] - $a['savings_ms'];
        });

        return [
            'success' => true,
            'opportunities' => $opportunities
        ];
    }

    public function getDiagnostics($testResult) {
        if (!isset($testResult['data']['lighthouseResult']['audits'])) {
            return ['success' => false, 'error' => 'Invalid test result format'];
        }

        $audits = $testResult['data']['lighthouseResult']['audits'];
        $diagnostics = [];

        foreach ($audits as $auditId => $audit) {
            if (isset($audit['details']['type']) &&
                $audit['details']['type'] === 'debugdata' &&
                isset($audit['score']) &&
                $audit['score'] < 1) {

                $diagnostics[] = [
                    'id' => $auditId,
                    'title' => $audit['title'] ?? '',
                    'description' => $audit['description'] ?? '',
                    'score' => $audit['score'] ?? 0
                ];
            }
        }

        return [
            'success' => true,
            'diagnostics' => $diagnostics
        ];
    }

    public function getSimplifiedReport($url, $strategy = 'mobile') {
        $result = $this->runTest($url, $strategy);

        if (!$result['success']) {
            return $result;
        }

        $scores = $this->getScores($result);
        $metrics = $this->getMetrics($result);
        $opportunities = $this->getOpportunities($result);

        return [
            'success' => true,
            'url' => $url,
            'strategy' => $strategy,
            'tested_at' => date('Y-m-d H:i:s'),
            'scores' => $scores['scores'] ?? [],
            'metrics' => $metrics['metrics'] ?? [],
            'top_opportunities' => array_slice($opportunities['opportunities'] ?? [], 0, 5)
        ];
    }

    public function compareResults($oldResult, $newResult) {
        $oldScores = $this->getScores($oldResult);
        $newScores = $this->getScores($newResult);

        if (!$oldScores['success'] || !$newScores['success']) {
            return ['success' => false, 'error' => 'Invalid test results for comparison'];
        }

        $comparison = [];
        foreach ($newScores['scores'] as $category => $newScore) {
            $oldScore = $oldScores['scores'][$category] ?? 0;
            $comparison[$category] = [
                'old' => $oldScore,
                'new' => $newScore,
                'change' => $newScore - $oldScore,
                'improved' => $newScore > $oldScore
            ];
        }

        return [
            'success' => true,
            'comparison' => $comparison
        ];
    }

    public function getHistoricalData($url, $strategy = 'mobile', $limit = 10) {
        try {
            $stmt = $this->conn->prepare("
                SELECT * FROM pagespeed_results
                WHERE url = ? AND strategy = ?
                ORDER BY tested_at DESC
                LIMIT ?
            ");

            $stmt->execute([$url, $strategy, $limit]);
            $results = $stmt->fetchAll();

            return [
                'success' => true,
                'count' => count($results),
                'results' => $results
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to retrieve historical data: ' . $e->getMessage()
            ];
        }
    }

    public function getAverageScores($url, $days = 30) {
        try {
            $stmt = $this->conn->prepare("
                SELECT
                    strategy,
                    AVG(performance_score) as avg_performance,
                    AVG(accessibility_score) as avg_accessibility,
                    AVG(best_practices_score) as avg_best_practices,
                    AVG(seo_score) as avg_seo,
                    COUNT(*) as test_count
                FROM pagespeed_results
                WHERE url = ? AND tested_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY strategy
            ");

            $stmt->execute([$url, $days]);
            $results = $stmt->fetchAll();

            return [
                'success' => true,
                'period_days' => $days,
                'averages' => $results
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to calculate average scores: ' . $e->getMessage()
            ];
        }
    }

    private function saveResultToDatabase($url, $strategy, $resultData) {
        try {
            if (!isset($resultData['lighthouseResult']['categories'])) {
                return;
            }

            $categories = $resultData['lighthouseResult']['categories'];

            $stmt = $this->conn->prepare("
                INSERT INTO pagespeed_results (
                    url, strategy, performance_score, accessibility_score,
                    best_practices_score, seo_score, result_data, tested_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $performanceScore = isset($categories['performance']) ? round($categories['performance']['score'] * 100) : null;
            $accessibilityScore = isset($categories['accessibility']) ? round($categories['accessibility']['score'] * 100) : null;
            $bestPracticesScore = isset($categories['best-practices']) ? round($categories['best-practices']['score'] * 100) : null;
            $seoScore = isset($categories['seo']) ? round($categories['seo']['score'] * 100) : null;

            $stmt->execute([
                $url,
                $strategy,
                $performanceScore,
                $accessibilityScore,
                $bestPracticesScore,
                $seoScore,
                json_encode($resultData)
            ]);

        } catch (Exception $e) {
            error_log("Failed to save PageSpeed result to database: " . $e->getMessage());
        }
    }
}
