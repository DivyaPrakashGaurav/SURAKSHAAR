<?php
require_once __DIR__ . '/config/supabase.php';
$supabase = new SupabaseHelper();

$admin_data = [
    'name' => 'Demo Admin',
    'email' => 'admin@surakshaar.com',
    'password_hash' => '$2y$10$Qj2M6E9VwB.XGgqQ/hJcQOyD7R4cE.Fj/oAOMWp.J5z4q/tG3z8rS',
    'status' => 'active'
];

$response = $supabase->insert('admins', $admin_data);
print_r($response);
