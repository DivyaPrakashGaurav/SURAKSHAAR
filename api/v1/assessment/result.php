<?php
require_once __DIR__ . '/../api_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['success' => false, 'error' => 'Method not allowed'], 405);
}

$worker = authenticate_worker($supabase);
$data = get_json_input();

if (empty($data['module_code']) || !isset($data['score'])) {
    send_json(['success' => false, 'error' => 'module_code and score are required'], 400);
}

$module_response = $supabase->get('modules', 'module_code=eq.' . urlencode($data['module_code']));
if (!$module_response['success'] || empty($module_response['data'])) {
    send_json(['success' => false, 'error' => 'Invalid module_code'], 400);
}
$module_id = $module_response['data'][0]['id'];

$insert_data = [
    'worker_id' => $worker['id'],
    'module_id' => $module_id,
    'score' => (int)$data['score'],
];

if (isset($data['passed'])) $insert_data['passed'] = (bool)$data['passed'];
if (isset($data['date'])) $insert_data['date'] = $data['date'];

$response = $supabase->insert('assessments', $insert_data);
if ($response['success']) {
    send_json(['success' => true, 'message' => 'Assessment result saved']);
} else {
    send_json(['success' => false, 'error' => 'Failed to save assessment result', 'details' => $response['error']], 500);
}
