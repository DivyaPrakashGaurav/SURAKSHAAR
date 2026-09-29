<?php
require_once 'config/supabase.php';
$supabase = new SupabaseHelper();
$res = $supabase->update('workers', ['session_token' => 'test', 'last_seen_at' => date('c')], 'id=eq.1');
print_r($res);
