<?php
require_once 'config/supabase.php';
$supabase = new SupabaseHelper();
$res = $supabase->get('retention_records', 'limit=1');
print_r($res);
