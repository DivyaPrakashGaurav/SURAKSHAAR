<?php
require_once 'config/supabase.php';
$supabase = new SupabaseHelper();
$res = $supabase->get('assessments', 'limit=1');
print_r($res);
