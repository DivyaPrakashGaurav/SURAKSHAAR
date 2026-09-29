<?php
require_once __DIR__ . '/config/supabase.php';

$supabase = new SupabaseHelper();

$token = 'test_token';
$now = date('Y-m-d\TH:i:sP'); 
$worker_id = 'W-999';

$insert_data = [
    'worker_id' => $worker_id,
    'name' => 'Name',
    'language' => 'English',
    'session_token' => $token,
    'last_seen_at' => $now
];

$insert_response = $supabase->insert('workers', $insert_data);
print_r($insert_response);
