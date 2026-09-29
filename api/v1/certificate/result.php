<?php
require_once __DIR__ . '/../api_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['success' => false, 'error' => 'Method not allowed'], 405);
}

$worker = authenticate_worker($supabase);
$data = get_json_input();

if (empty($data['certificate_id']) || empty($data['module_code']) || !isset($data['score'])) {
    send_json(['success' => false, 'error' => 'certificate_id, module_code, and score are required'], 400);
}

$module_response = $supabase->get('modules', 'module_code=eq.' . urlencode($data['module_code']));
if (!$module_response['success'] || empty($module_response['data'])) {
    send_json(['success' => false, 'error' => 'Invalid module_code'], 400);
}
$module_id = $module_response['data'][0]['id'];

$insert_data = [
    'certificate_id' => $data['certificate_id'],
    'worker_id' => $worker['id'],
    'module_id' => $module_id,
    'score' => (int)$data['score'],
];

if (isset($data['issue_date'])) $insert_data['issue_date'] = $data['issue_date'];
if (isset($data['status'])) $insert_data['status'] = $data['status'];
if (isset($data['verification_token'])) $insert_data['verification_token'] = $data['verification_token'];

$response = $supabase->insert('certificates', $insert_data);
if ($response['success']) {
    send_json(['success' => true, 'message' => 'Certificate saved']);
} else {
    // If conflict on certificate_id, it might return an error string like "duplicate key"
    if (is_string($response['error']) && strpos($response['error'], 'duplicate key') !== false) {
        send_json(['success' => true, 'message' => 'Certificate already exists (idempotent success)']);
    }
    send_json(['success' => false, 'error' => 'Failed to save certificate', 'details' => $response['error']], 500);
}
