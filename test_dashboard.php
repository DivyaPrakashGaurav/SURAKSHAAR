<?php
require_once 'config/supabase.php';
$supabase = new SupabaseHelper();

// 1. Insert Module
$module_data = [
    'module_code' => 'fire_safety',
    'name' => 'Fire Safety Training'
];
$supabase->insert('modules', $module_data);

// 2. Fetch Module ID
$mod = $supabase->get('modules', 'module_code=eq.fire_safety');
$module_id = $mod['data'][0]['id'];

// 3. Insert Worker
$worker_data = [
    'worker_id' => 'W-TEST-1',
    'name' => 'Test Worker',
    'session_token' => 'test-token',
    'last_seen_at' => date('c')
];
// Wait, last_seen_at is not in DB yet! I will just insert name and worker_id
$worker_data2 = [
    'worker_id' => 'W-TEST-1',
    'name' => 'Test Worker'
];
$supabase->insert('workers', $worker_data2);

// 4. Fetch Worker ID
$wrk = $supabase->get('workers', 'worker_id=eq.W-TEST-1');
$worker_internal_id = $wrk['data'][0]['id'];

// 5. Insert Training
$training_data = [
    'worker_id' => $worker_internal_id,
    'module_id' => $module_id,
    'score' => 95,
    'completed' => true
];
$supabase->insert('training_attempts', $training_data);

// 6. Test Dashboard queries
$res1 = $supabase->get('training_attempts', 'order=created_at.desc&limit=5&select=*,workers(name),modules(name)');
print_r($res1);
