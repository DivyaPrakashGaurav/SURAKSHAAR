<?php

function get_env($key, $default = null) {
    if (getenv($key) !== false) {
        return getenv($key);
    }
    if (isset($_ENV[$key])) {
        return $_ENV[$key];
    }
    
    // Fallback to parse .env file if it exists and variables aren't loaded in $_ENV
    $env_file = __DIR__ . '/../.env';
    if (file_exists($env_file)) {
        $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if ($name === $key) {
                return $value;
            }
        }
    }
    
    return $default;
}

class SupabaseHelper {
    private $url;
    private $key;

    public function __construct() {
        $this->url = get_env('SUPABASE_URL');
        $this->key = get_env('SUPABASE_SECRET_KEY');

        if (!$this->url || !$this->key) {
            die('Supabase configuration missing.');
        }
    }

    private function request($method, $endpoint, $data = null) {
        $ch = curl_init();
        
        $headers = [
            "apikey: {$this->key}",
            "Authorization: Bearer {$this->key}",
            "Content-Type: application/json",
            "Prefer: return=representation"
        ];

        $url = rtrim($this->url, '/') . '/rest/v1/' . ltrim($endpoint, '/');

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        if ($data && in_array($method, ['POST', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => $error];
        }

        $decoded = json_decode($response, true);
        
        if ($http_code >= 200 && $http_code < 300) {
            return ['success' => true, 'data' => $decoded];
        } else {
            return ['success' => false, 'error' => isset($decoded['message']) ? $decoded['message'] : 'API Error', 'code' => $http_code];
        }
    }

    public function get($table, $query = '') {
        $endpoint = $table . ($query ? '?' . $query : '');
        return $this->request('GET', $endpoint);
    }

    public function insert($table, $data) {
        return $this->request('POST', $table, $data);
    }

    public function update($table, $data, $query) {
        $endpoint = $table . '?' . $query;
        return $this->request('PATCH', $endpoint, $data);
    }

    public function delete($table, $query) {
        $endpoint = $table . '?' . $query;
        return $this->request('DELETE', $endpoint);
    }
}
