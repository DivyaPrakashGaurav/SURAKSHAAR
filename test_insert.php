<?php
require_once __DIR__ . '/config/supabase.php';
$supabase = new SupabaseHelper();

$worker_data = [
    'worker_id' => 'W-TEST-' . time(),
    'name' => 'Test Worker',
    'phone' => '+911234567890',
    'role' => 'Tester',
    'site' => 'Site Test',
    'language' => 'English',
    'status' => 'active'
];

$response = $supabase->insert('workers', $worker_data);
print_r($response);
