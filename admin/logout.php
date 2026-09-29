<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';

if (isset($_SESSION['admin_id'])) {
    log_audit('logout', 'admins', $_SESSION['admin_id']);
}

session_unset();
session_destroy();
header("Location: login.php");
exit;
