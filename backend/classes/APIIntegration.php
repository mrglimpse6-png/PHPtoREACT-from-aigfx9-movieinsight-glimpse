<?php
/**
 * Base API Integration Class
 * Abstract class that all API integrations must extend
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Cache.php';

abstract class APIIntegration {
    protected $db;
    protected $conn;
    protected $cache;
    protected $name;
    protected $provider;
    protected $category;
    protected $config;
    protected $enabled;
    protected $rateLimitPerHour;

    public function __construct($integrationName) {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->cache = new Cache();
        $this->name = $integrationName;

        // Load configuration from database
        $this->loadConfig();
    }

    /**
     * Load integration configuration from database
     */
    protected function loadConfig() {
        try {
            $stmt = $this->conn->prepare("
                SELECT provider, category, enabled, config, rate_limit_per_hour
                FROM api_integrations
                WHERE name = ?
            ");
            $stmt->execute([$this->name]);
            $integration = $stmt->fetch();

            if ($integration) {
                $this->provider = $integration['provider'];
                $this->category = $integration['category'];
                $this->enabled = (bool)$integration['enabled'];
                $this->config = json_decode($integration['config'], true) ?? [];
                $this->rateLimitPerHour = (int)$integration['rate_limit_per_hour'];
            } else {
                $this->enabled = false;
            }
        } catch (Exception $e) {
            error_log("Failed to load API integration config: " . $e->getMessage());
            $this->enabled = false;
        }
    }

    /**
     * Check if integration is enabled
     */
    public function isEnabled() {
        return $this->enabled;
    }

    /**
     * Check rate limit before making request
     */
    protected function checkRateLimit() {
        try {
            $stmt = $this->conn->prepare("
                SELECT requests_today, rate_limit_per_hour
                FROM api_integrations
                WHERE name = ?
            ");
            $stmt->execute([$this->name]);
            $integration = $stmt->fetch();

            if (!$integration) {
                return false;
            }

            $requestsPerHourLimit = (int)$integration['rate_limit_per_hour'];
            $requestsToday = (int)$integration['requests_today'];

            // Simple daily limit check (24 * hourly rate)
            $dailyLimit = $requestsPerHourLimit * 24;

            return $requestsToday < $dailyLimit;
        } catch (Exception $e) {
            error_log("Rate limit check failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log API request and response
     */
    protected function logRequest($endpoint, $method, $requestData, $responseData, $statusCode, $responseTime, $error = null) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO api_logs (
                    integration_name, endpoint, method, request_data, response_data,
                    status_code, response_time_ms, error, ip_address
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $this->name,
                $endpoint,
                $method,
                json_encode($requestData),
                json_encode($responseData),
                $statusCode,
                $responseTime,
                $error,
                $_SERVER['REMOTE_ADDR'] ?? 'CLI'
            ]);
        } catch (Exception $e) {
            error_log("Failed to log API request: " . $e->getMessage());
        }
    }

    /**
     * Make HTTP request with error handling and logging
     */
    protected function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
        if (!$this->enabled) {
            throw new Exception("API integration '{$this->name}' is not enabled");
        }

        if (!$this->checkRateLimit()) {
            throw new Exception("Rate limit exceeded for '{$this->name}'");
        }

        $startTime = microtime(true);

        try {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

            // Set method
            if ($method !== 'GET') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            }

            // Set request body
            if ($data !== null) {
                if (is_array($data)) {
                    $data = json_encode($data);
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

                // Add Content-Type header if not already set
                $hasContentType = false;
                foreach ($headers as $header) {
                    if (stripos($header, 'Content-Type:') === 0) {
                        $hasContentType = true;
                        break;
                    }
                }
                if (!$hasContentType) {
                    $headers[] = 'Content-Type: application/json';
                }
            }

            // Set headers
            if (!empty($headers)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }

            $response = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            $responseTime = round((microtime(true) - $startTime) * 1000);

            $responseData = json_decode($response, true) ?? ['raw' => $response];

            // Log the request
            $this->logRequest(
                $url,
                $method,
                $data ? json_decode($data, true) : null,
                $responseData,
                $statusCode,
                $responseTime,
                $error ?: null
            );

            if ($error) {
                throw new Exception("cURL error: {$error}");
            }

            if ($statusCode >= 400) {
                throw new Exception("HTTP {$statusCode}: " . ($responseData['error'] ?? $response));
            }

            return [
                'success' => true,
                'data' => $responseData,
                'status_code' => $statusCode,
                'response_time' => $responseTime
            ];

        } catch (Exception $e) {
            $responseTime = round((microtime(true) - $startTime) * 1000);

            $this->logRequest(
                $url,
                $method,
                $data ? json_decode($data, true) : null,
                null,
                $statusCode ?? 0,
                $responseTime,
                $e->getMessage()
            );

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status_code' => $statusCode ?? 0,
                'response_time' => $responseTime
            ];
        }
    }

    /**
     * Get configuration value
     */
    protected function getConfig($key, $default = null) {
        return $this->config[$key] ?? $default;
    }

    /**
     * Update integration configuration
     */
    public function updateConfig($newConfig) {
        try {
            // Encrypt sensitive values before storing
            $encryptedConfig = $this->encryptSensitiveData($newConfig);

            $stmt = $this->conn->prepare("
                UPDATE api_integrations
                SET config = ?, updated_at = NOW()
                WHERE name = ?
            ");

            $stmt->execute([
                json_encode($encryptedConfig),
                $this->name
            ]);

            $this->config = $newConfig;
            return true;
        } catch (Exception $e) {
            error_log("Failed to update API config: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enable or disable integration
     */
    public function setEnabled($enabled) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE api_integrations
                SET enabled = ?, updated_at = NOW()
                WHERE name = ?
            ");

            $stmt->execute([$enabled ? 1 : 0, $this->name]);
            $this->enabled = $enabled;

            return true;
        } catch (Exception $e) {
            error_log("Failed to update API enabled status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Encrypt sensitive configuration data
     */
    protected function encryptSensitiveData($data) {
        // In production, implement proper encryption
        // For now, just mark sensitive fields
        $sensitiveKeys = ['api_key', 'secret', 'password', 'token', 'access_token'];

        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $sensitiveKeys)) {
                // TODO: Implement actual encryption using openssl_encrypt
                // For now, just prefix to indicate it should be encrypted
                $data[$key] = 'ENCRYPTED:' . $value;
            }
        }

        return $data;
    }

    /**
     * Decrypt sensitive configuration data
     */
    protected function decryptSensitiveData($data) {
        // Reverse of encryption
        foreach ($data as $key => $value) {
            if (is_string($value) && strpos($value, 'ENCRYPTED:') === 0) {
                $data[$key] = substr($value, strlen('ENCRYPTED:'));
            }
        }

        return $data;
    }

    /**
     * Get integration statistics
     */
    public function getStats() {
        try {
            $stmt = $this->conn->prepare("
                SELECT
                    requests_today,
                    requests_this_month,
                    success_count,
                    error_count,
                    last_request,
                    last_success,
                    last_error
                FROM api_integrations
                WHERE name = ?
            ");

            $stmt->execute([$this->name]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Failed to get API stats: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Test API connection
     * Must be implemented by child classes
     */
    abstract public function testConnection();

    /**
     * Get API provider name
     */
    public function getProvider() {
        return $this->provider;
    }

    /**
     * Get API category
     */
    public function getCategory() {
        return $this->category;
    }

    /**
     * Get integration name
     */
    public function getName() {
        return $this->name;
    }
}
