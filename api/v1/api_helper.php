<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/supabase.php';

$supabase = new SupabaseHelper();

function send_json($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function get_json_input() {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        send_json(['success' => false, 'error' => 'Invalid JSON input'], 400);
    }
    return $data;
}

function get_auth_token() {
    $auth_header = '';
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $auth_header = trim($_SERVER['HTTP_AUTHORIZATION']);
    } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $auth_header = trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
    } else {
        $headers = function_exists('apache_request_headers') ? apache_request_headers() : [];
        if (isset($headers['Authorization'])) {
            $auth_header = trim($headers['Authorization']);
        }
    }
    
    if (preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
        return $matches[1];
    }
    return null;
}

function authenticate_worker($supabase) {
    $token = get_auth_token();
    if (empty($token)) {
        send_json(['success' => false, 'error' => 'Missing or invalid Authorization header'], 401);
    }
    
    $response = $supabase->get('workers', 'session_token=eq.' . urlencode($token) . '&select=id,worker_id,name,status');
    if ($response['success'] && !empty($response['data'])) {
        $worker = $response['data'][0];
        if ($worker['status'] !== 'active') {
            send_json(['success' => false, 'error' => 'Worker account is not active'], 403);
        }
        return $worker;
    }
    
    send_json(['success' => false, 'error' => 'Invalid or expired session token'], 401);
}
