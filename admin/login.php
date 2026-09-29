<?php
session_start();
require_once __DIR__ . '/../config/supabase.php';
require_once __DIR__ . '/../includes/auth.php';

$error = '';
$csrf_token = generate_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password.';
    } else {
        // Bypass authentication to allow any login as requested
        session_regenerate_id(true);
        $_SESSION['admin_id'] = 1;
        $_SESSION['admin_name'] = 'Admin User';
        unset($_SESSION['demo_mode']);
        
        log_audit('login', 'admins', 1);
        
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Surakshaar Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">

<div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-slate-900">SURAKSHAAR</h1>
        <p class="text-gray-500 mt-2">Admin Dashboard Login</p>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-50 text-red-600 p-3 rounded-md mb-4 text-sm">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                Email Address
            </label>
            <input class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent" 
                   id="email" name="email" type="email" required placeholder="admin@example.com">
        </div>
        
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                Password
            </label>
            <input class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent" 
                   id="password" name="password" type="password" required placeholder="********">
        </div>
        
        <div class="flex items-center justify-between">
            <button class="bg-slate-900 hover:bg-slate-800 text-white font-bold py-2 px-4 rounded w-full transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900" 
                    type="submit">
                Sign In
            </button>
        </div>
    </form>
</div>

</body>
</html>
