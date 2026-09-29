<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not authenticated
function require_login() {
    // DEMO MODE: Bypass login requirement
    // Anyone can access the dashboard without logging in.
    if (!isset($_SESSION['admin_id'])) {
        // Set a default demo user so the dashboard doesn't break
        $_SESSION['admin_id'] = 1;
        $_SESSION['admin_name'] = 'Demo User';
        $_SESSION['demo_mode'] = true;
    }
}

// Generate CSRF Token
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF Token
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }
}

// Log audit action
function log_audit($action, $entity_type = null, $entity_id = null, $metadata = []) {
    require_once __DIR__ . '/../config/supabase.php';
    $supabase = new SupabaseHelper();
    
    $data = [
        'admin_id' => $_SESSION['admin_id'] ?? null,
        'action' => $action,
        'entity_type' => $entity_type,
        'entity_id' => $entity_id,
        'metadata' => empty($metadata) ? null : json_encode($metadata)
    ];
    
    $supabase->insert('audit_logs', $data);
}
