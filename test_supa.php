<?php
require_once __DIR__ . '/config/supabase.php';

$supabase = new SupabaseHelper();

// Check if we can get workers
$res = $supabase->get('workers', 'limit=1');
echo "GET workers:\n";
print_r($res);

// Try to add column if not exists? No, we can't run ALTER TABLE via REST API. We'd have to use a SQL endpoint if available, but the REST API is just CRUD.
// Wait, the prompt says "Use: ALTER TABLE workers ADD COLUMN IF NOT EXISTS..."
// But how do I run SQL against Supabase here? There might be a pg admin URL or a tool. Or maybe they just want me to ensure the DB has it by running psql. Let me check the seed file.
