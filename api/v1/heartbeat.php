<?php
require_once __DIR__ . '/../api_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['success' => false, 'error' => 'Method not allowed'], 405);
}

$worker = authenticate_worker($supabase);

$now = date('Y-m-d\TH:i:sP');

$update_data = [
    'last_seen_at' => $now
];

$update_response = $supabase->update('workers', $update_data, 'id=eq.' . urlencode($worker['id']));

if ($update_response['success']) {
    send_json(['success' => true, 'last_seen_at' => $now]);
} else {
    send_json(['success' => false, 'error' => 'Failed to update heartbeat'], 500);
}
