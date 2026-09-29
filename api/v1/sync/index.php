<?php
require_once __DIR__ . '/../api_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['success' => false, 'error' => 'Method not allowed'], 405);
}

$worker = authenticate_worker($supabase);
$data = get_json_input();

if (!isset($data['sync_data']) || !is_array($data['sync_data'])) {
    send_json(['success' => false, 'error' => 'sync_data array is required'], 400);
}

$results = [
    'successful' => 0,
    'failed' => 0,
    'errors' => []
];

$modules_response = $supabase->get('modules', 'select=id,module_code');
$modules = [];
if ($modules_response['success'] && !empty($modules_response['data'])) {
    foreach ($modules_response['data'] as $m) {
        $modules[$m['module_code']] = $m['id'];
    }
}

foreach ($data['sync_data'] as $item) {
    if (!isset($item['type']) || !isset($item['payload'])) {
        $results['failed']++;
        $results['errors'][] = ['error' => 'Invalid sync item format', 'item' => $item];
        continue;
    }
    
    $type = $item['type'];
    $payload = $item['payload'];
    
    if ($type === 'training_result') {
        if (!isset($payload['module_code']) || !isset($modules[$payload['module_code']])) {
            $results['failed']++;
            $results['errors'][] = ['error' => 'Invalid module_code', 'item' => $item];
            continue;
        }
        
        $insert_data = [
            'worker_id' => $worker['id'],
            'module_id' => $modules[$payload['module_code']],
            'score' => (int)($payload['score'] ?? 0),
            'attempts' => (int)($payload['attempts'] ?? 1),
            'completed' => (bool)($payload['completed'] ?? false),
            'critical_fail' => (bool)($payload['critical_fail'] ?? false),
            'ar_used' => (bool)($payload['ar_used'] ?? true),
        ];
        
        if (isset($payload['scenario_id'])) $insert_data['scenario_id'] = $payload['scenario_id'];
        if (isset($payload['started_at'])) $insert_data['started_at'] = $payload['started_at'];
        if (isset($payload['completed_at'])) $insert_data['completed_at'] = $payload['completed_at'];
        
        $resp = $supabase->insert('training_attempts', $insert_data);
        if ($resp['success']) {
            $results['successful']++;
        } else {
            $results['failed']++;
            $results['errors'][] = ['error' => $resp['error'], 'item' => $item];
        }
    } else if ($type === 'certificate') {
        if (!isset($payload['certificate_id']) || !isset($payload['module_code']) || !isset($modules[$payload['module_code']])) {
            $results['failed']++;
            $results['errors'][] = ['error' => 'Missing certificate_id or invalid module_code', 'item' => $item];
            continue;
        }
        
        $insert_data = [
            'certificate_id' => $payload['certificate_id'],
            'worker_id' => $worker['id'],
            'module_id' => $modules[$payload['module_code']],
            'score' => (int)($payload['score'] ?? 0),
        ];
        
        if (isset($payload['issue_date'])) $insert_data['issue_date'] = $payload['issue_date'];
        if (isset($payload['status'])) $insert_data['status'] = $payload['status'];
        if (isset($payload['verification_token'])) $insert_data['verification_token'] = $payload['verification_token'];
        
        $resp = $supabase->insert('certificates', $insert_data);
        if ($resp['success'] || (is_string($resp['error']) && strpos($resp['error'], 'duplicate key') !== false)) {
            $results['successful']++;
        } else {
            $results['failed']++;
            $results['errors'][] = ['error' => $resp['error'], 'item' => $item];
        }
    } else {
        $results['failed']++;
        $results['errors'][] = ['error' => 'Unknown sync type', 'item' => $item];
    }
}

send_json([
    'success' => true,
    'message' => 'Sync completed',
    'results' => $results
]);
