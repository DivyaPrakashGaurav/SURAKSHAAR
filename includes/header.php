<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surakshaar Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 h-screen flex overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-white flex flex-col hidden md:flex">
        <div class="h-16 flex items-center justify-center border-b border-slate-800 font-bold text-xl tracking-wider">
            SURAKSHAAR
        </div>
        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1 px-3">
                <li><a href="index.php" class="block px-3 py-2 rounded-md hover:bg-slate-800 transition">Dashboard</a></li>
                <li><a href="workers.php" class="block px-3 py-2 rounded-md hover:bg-slate-800 transition">Workers</a></li>
                <li><a href="training.php" class="block px-3 py-2 rounded-md hover:bg-slate-800 transition">Training</a></li>
                <li><a href="analytics.php" class="block px-3 py-2 rounded-md hover:bg-slate-800 transition">Analytics</a></li>
                <li><a href="certificates.php" class="block px-3 py-2 rounded-md hover:bg-slate-800 transition">Certificates</a></li>
                <li><a href="verify.php" class="block px-3 py-2 rounded-md hover:bg-slate-800 transition">Verify</a></li>
                <li><a href="audit-logs.php" class="block px-3 py-2 rounded-md hover:bg-slate-800 transition">Audit Logs</a></li>
            </ul>
        </nav>
        <div class="p-4 border-t border-slate-800">
            <?php if (isset($_SESSION['demo_mode']) && $_SESSION['demo_mode']): ?>
                <a href="login.php" class="block w-full text-center px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-md text-sm font-medium transition text-white">
                    Login
                </a>
            <?php else: ?>
                <a href="logout.php" class="block w-full text-center px-4 py-2 bg-red-600 hover:bg-red-700 rounded-md text-sm font-medium transition text-white">
                    Logout
                </a>
            <?php endif; ?>
        </div>
    </aside>

    <!-- Mobile Header -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 md:justify-end">
            <div class="font-bold text-xl md:hidden text-slate-900">SURAKSHAAR</div>
            <div class="flex items-center space-x-4">
                <span class="text-sm font-medium text-gray-600">
                    <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>
                </span>
            </div>
        </header>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
