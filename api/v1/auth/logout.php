<?php
require_once __DIR__ . '/../api_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['success' => false, 'error' => 'Method not allowed'], 405);
}

$worker = authenticate_worker($supabase);

$update_data = [
    'session_token' => null
];

$update_response = $supabase->update('workers', $update_data, 'id=eq.' . urlencode($worker['id']));

if ($update_response['success']) {
    send_json(['success' => true, 'message' => 'Logged out successfully']);
} else {
    send_json(['success' => false, 'error' => 'Failed to logout'], 500);
}
