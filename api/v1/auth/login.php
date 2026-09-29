<?php
require_once __DIR__ . '/../api_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['success' => false, 'error' => 'Method not allowed'], 405);
}

$data = get_json_input();

if (empty($data['worker_id'])) {
    send_json(['success' => false, 'error' => 'worker_id is required'], 400);
}

$worker_id = $data['worker_id'];
$name = isset($data['name']) ? $data['name'] : 'Unknown';
$language = isset($data['language']) ? $data['language'] : 'English';
$role = isset($data['role']) ? $data['role'] : null;
$site = isset($data['site']) ? $data['site'] : null;

$response = $supabase->get('workers', 'worker_id=eq.' . urlencode($worker_id));

$token = bin2hex(random_bytes(32));
$now = date('Y-m-d\TH:i:sP'); 

if ($response['success'] && empty($response['data'])) {
    // Insert new worker
    $insert_data = [
        'worker_id' => $worker_id,
        'name' => $name,
        'language' => $language,
        'session_token' => $token,
        'last_seen_at' => $now
    ];
    if ($role) $insert_data['role'] = $role;
    if ($site) $insert_data['site'] = $site;
    
    $insert_response = $supabase->insert('workers', $insert_data);
    
    if (!$insert_response['success']) {
        send_json(['success' => false, 'error' => 'Failed to create worker: ' . ($insert_response['error'] ?? 'Unknown Error')], 500);
    }
    
    // Fetch the inserted ID
    $fetch_response = $supabase->get('workers', 'worker_id=eq.' . urlencode($worker_id));
    if ($fetch_response['success'] && !empty($fetch_response['data'])) {
        $worker_internal_id = $fetch_response['data'][0]['id'];
        send_json([
            'success' => true, 
            'session_token' => $token, 
            'worker' => [
                'id' => $worker_internal_id, 
                'worker_id' => $worker_id, 
                'name' => $name
            ]
        ]);
    } else {
        send_json(['success' => false, 'error' => 'Failed to retrieve inserted worker'], 500);
    }
} else if ($response['success'] && !empty($response['data'])) {
    $worker = $response['data'][0];
    
    // Update token and last_seen
    $update_data = [
        'session_token' => $token,
        'last_seen_at' => $now
    ];
    // Update optional fields if provided
    if (isset($data['name'])) $update_data['name'] = $data['name'];
    if (isset($data['language'])) $update_data['language'] = $data['language'];
    if (isset($data['role'])) $update_data['role'] = $data['role'];
    if (isset($data['site'])) $update_data['site'] = $data['site'];
    
    $update_response = $supabase->update('workers', $update_data, 'id=eq.' . urlencode($worker['id']));
    
    if ($update_response['success']) {
        send_json([
            'success' => true, 
            'session_token' => $token,
            'worker' => [
                'id' => $worker['id'], 
                'worker_id' => $worker_id,
                'name' => $worker['name'] ?? $update_data['name']
            ]
        ]);
    } else {
        send_json(['success' => false, 'error' => 'Failed to update session'], 500);
    }
} else {
    send_json(['success' => false, 'error' => 'Database error'], 500);
}
