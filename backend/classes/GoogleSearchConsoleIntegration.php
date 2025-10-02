<?php
/**
 * Google Search Console Integration
 * Handles sitemap submission, indexing status, and search analytics
 */

require_once __DIR__ . '/APIIntegration.php';

class GoogleSearchConsoleIntegration extends APIIntegration {
    private $apiKey;
    private $siteUrl;
    private $apiUrl = 'https://searchconsole.googleapis.com/v1';

    public function __construct() {
        parent::__construct('Google Search Console');

        $this->apiKey = $this->getConfig('api_key') ?? $_ENV['GOOGLE_API_KEY'] ?? '';
        $this->siteUrl = $this->getConfig('site_url') ?? $_ENV['SITE_URL'] ?? 'https://adilgfx.com';
    }

    public function testConnection() {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'Google API key not configured'
            ];
        }

        $response = $this->makeRequest(
            $this->apiUrl . '/webmasters/v3/sites',
            'GET',
            null,
            $this->getHeaders()
        );

        return $response;
    }

    public function submitSitemap($sitemapUrl) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Google Search Console integration is disabled'];
        }

        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'Google API key not configured'];
        }

        $encodedSiteUrl = urlencode($this->siteUrl);
        $encodedSitemapUrl = urlencode($sitemapUrl);

        $response = $this->makeRequest(
            $this->apiUrl . "/webmasters/v3/sites/{$encodedSiteUrl}/sitemaps/{$encodedSitemapUrl}",
            'PUT',
            [],
            $this->getHeaders()
        );

        if ($response['success']) {
            $this->logMetricToDatabase('sitemap_submitted', ['sitemap_url' => $sitemapUrl]);
        }

        return $response;
    }

    public function getSitemaps() {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Google Search Console integration is disabled'];
        }

        $encodedSiteUrl = urlencode($this->siteUrl);

        $response = $this->makeRequest(
            $this->apiUrl . "/webmasters/v3/sites/{$encodedSiteUrl}/sitemaps",
            'GET',
            null,
            $this->getHeaders()
        );

        return $response;
    }

    public function deleteSitemap($sitemapUrl) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Google Search Console integration is disabled'];
        }

        $encodedSiteUrl = urlencode($this->siteUrl);
        $encodedSitemapUrl = urlencode($sitemapUrl);

        $response = $this->makeRequest(
            $this->apiUrl . "/webmasters/v3/sites/{$encodedSiteUrl}/sitemaps/{$encodedSitemapUrl}",
            'DELETE',
            null,
            $this->getHeaders()
        );

        return $response;
    }

    public function requestIndexing($url) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Google Search Console integration is disabled'];
        }

        $data = [
            'url' => $url,
            'type' => 'URL_UPDATED'
        ];

        $response = $this->makeRequest(
            'https://indexing.googleapis.com/v3/urlNotifications:publish',
            'POST',
            $data,
            $this->getHeaders()
        );

        if ($response['success']) {
            $this->logMetricToDatabase('indexing_requested', ['url' => $url]);
        }

        return $response;
    }

    public function getIndexingStatus($url) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Google Search Console integration is disabled'];
        }

        $response = $this->makeRequest(
            'https://indexing.googleapis.com/v3/urlNotifications/metadata?url=' . urlencode($url),
            'GET',
            null,
            $this->getHeaders()
        );

        return $response;
    }

    public function getSearchAnalytics($startDate, $endDate, $dimensions = ['query'], $rowLimit = 1000) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Google Search Console integration is disabled'];
        }

        $encodedSiteUrl = urlencode($this->siteUrl);

        $data = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'dimensions' => $dimensions,
            'rowLimit' => $rowLimit
        ];

        $response = $this->makeRequest(
            $this->apiUrl . "/webmasters/v3/sites/{$encodedSiteUrl}/searchAnalytics/query",
            'POST',
            $data,
            $this->getHeaders()
        );

        if ($response['success']) {
            $this->logMetricToDatabase('search_analytics_fetched', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'dimensions' => implode(',', $dimensions)
            ]);
        }

        return $response;
    }

    public function getTopQueries($startDate, $endDate, $limit = 25) {
        $response = $this->getSearchAnalytics($startDate, $endDate, ['query'], $limit);

        if ($response['success'] && isset($response['data']['rows'])) {
            $queries = [];
            foreach ($response['data']['rows'] as $row) {
                $queries[] = [
                    'query' => $row['keys'][0] ?? '',
                    'clicks' => $row['clicks'] ?? 0,
                    'impressions' => $row['impressions'] ?? 0,
                    'ctr' => $row['ctr'] ?? 0,
                    'position' => $row['position'] ?? 0
                ];
            }
            return ['success' => true, 'queries' => $queries];
        }

        return $response;
    }

    public function getTopPages($startDate, $endDate, $limit = 25) {
        $response = $this->getSearchAnalytics($startDate, $endDate, ['page'], $limit);

        if ($response['success'] && isset($response['data']['rows'])) {
            $pages = [];
            foreach ($response['data']['rows'] as $row) {
                $pages[] = [
                    'page' => $row['keys'][0] ?? '',
                    'clicks' => $row['clicks'] ?? 0,
                    'impressions' => $row['impressions'] ?? 0,
                    'ctr' => $row['ctr'] ?? 0,
                    'position' => $row['position'] ?? 0
                ];
            }
            return ['success' => true, 'pages' => $pages];
        }

        return $response;
    }

    public function getDeviceBreakdown($startDate, $endDate) {
        $response = $this->getSearchAnalytics($startDate, $endDate, ['device'], 10);

        if ($response['success'] && isset($response['data']['rows'])) {
            $devices = [];
            foreach ($response['data']['rows'] as $row) {
                $devices[] = [
                    'device' => $row['keys'][0] ?? '',
                    'clicks' => $row['clicks'] ?? 0,
                    'impressions' => $row['impressions'] ?? 0,
                    'ctr' => $row['ctr'] ?? 0,
                    'position' => $row['position'] ?? 0
                ];
            }
            return ['success' => true, 'devices' => $devices];
        }

        return $response;
    }

    public function getCountryBreakdown($startDate, $endDate, $limit = 10) {
        $response = $this->getSearchAnalytics($startDate, $endDate, ['country'], $limit);

        if ($response['success'] && isset($response['data']['rows'])) {
            $countries = [];
            foreach ($response['data']['rows'] as $row) {
                $countries[] = [
                    'country' => $row['keys'][0] ?? '',
                    'clicks' => $row['clicks'] ?? 0,
                    'impressions' => $row['impressions'] ?? 0,
                    'ctr' => $row['ctr'] ?? 0,
                    'position' => $row['position'] ?? 0
                ];
            }
            return ['success' => true, 'countries' => $countries];
        }

        return $response;
    }

    public function getTotalMetrics($startDate, $endDate) {
        $response = $this->getSearchAnalytics($startDate, $endDate, [], 1);

        if ($response['success'] && isset($response['data']['rows'][0])) {
            $row = $response['data']['rows'][0];
            return [
                'success' => true,
                'metrics' => [
                    'clicks' => $row['clicks'] ?? 0,
                    'impressions' => $row['impressions'] ?? 0,
                    'ctr' => $row['ctr'] ?? 0,
                    'position' => $row['position'] ?? 0
                ]
            ];
        }

        return $response;
    }

    public function getURLInspection($url) {
        if (!$this->isEnabled()) {
            return ['success' => false, 'error' => 'Google Search Console integration is disabled'];
        }

        $encodedSiteUrl = urlencode($this->siteUrl);

        $data = [
            'inspectionUrl' => $url,
            'siteUrl' => $this->siteUrl
        ];

        $response = $this->makeRequest(
            $this->apiUrl . "/urlInspection/index:inspect",
            'POST',
            $data,
            $this->getHeaders()
        );

        return $response;
    }

    private function getHeaders() {
        return [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ];
    }

    private function logMetricToDatabase($metricType, $data) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO seo_metrics (
                    metric_type, metric_data, recorded_at
                ) VALUES (?, ?, NOW())
            ");

            $stmt->execute([
                $metricType,
                json_encode($data)
            ]);
        } catch (Exception $e) {
            error_log("Failed to log Google Search Console metric to database: " . $e->getMessage());
        }
    }
}
