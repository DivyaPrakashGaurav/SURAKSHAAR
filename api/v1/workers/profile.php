<?php
require_once __DIR__ . '/../api_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    send_json(['success' => false, 'error' => 'Method not allowed'], 405);
}

$worker = authenticate_worker($supabase);

// Fetch full worker details
$response = $supabase->get('workers', 'id=eq.' . urlencode($worker['id']));
if ($response['success'] && !empty($response['data'])) {
    // Hide session token for security
    $profile = $response['data'][0];
    unset($profile['session_token']);
    send_json(['success' => true, 'profile' => $profile]);
} else {
    send_json(['success' => false, 'error' => 'Failed to retrieve profile'], 500);
}
