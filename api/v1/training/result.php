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
    'attempts' => isset($data['attempts']) ? (int)$data['attempts'] : 1,
    'completed' => isset($data['completed']) ? (bool)$data['completed'] : false,
    'critical_fail' => isset($data['critical_fail']) ? (bool)$data['critical_fail'] : false,
    'ar_used' => isset($data['ar_used']) ? (bool)$data['ar_used'] : true,
];

if (isset($data['scenario_id'])) $insert_data['scenario_id'] = $data['scenario_id'];
if (isset($data['started_at'])) $insert_data['started_at'] = $data['started_at'];
if (isset($data['completed_at'])) $insert_data['completed_at'] = $data['completed_at'];

$response = $supabase->insert('training_attempts', $insert_data);
if ($response['success']) {
    send_json(['success' => true, 'message' => 'Training result saved']);
} else {
    send_json(['success' => false, 'error' => 'Failed to save training result', 'details' => $response['error']], 500);
}
