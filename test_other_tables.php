<?php
require_once 'config/supabase.php';
$supabase = new SupabaseHelper();
$res1 = $supabase->get('assessment', 'limit=1');
$res2 = $supabase->get('retention', 'limit=1');
print_r($res1);
print_r($res2);
