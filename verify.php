<?php
require_once __DIR__ . '/config/supabase.php';

$supabase = new SupabaseHelper();
$search_id = $_GET['id'] ?? '';
$result = null;

if (!empty($search_id)) {
    // Expose only limited fields for public verification
    $query = 'certificate_id=eq.' . urlencode($search_id) . '&select=certificate_id,issue_date,status,score,modules(name),workers(name)';
    $res = $supabase->get('certificates', $query);
    
    if ($res['success'] && !empty($res['data'])) {
        $result = $res['data'][0];
        
        // Mask worker name for privacy (e.g. John Doe -> J*** D**)
        $name_parts = explode(' ', $result['workers']['name'] ?? '');
        $masked_name = array_map(function($p) {
            return mb_strlen($p) > 1 ? mb_substr($p, 0, 1) . str_repeat('*', mb_strlen($p) - 1) : $p;
        }, $name_parts);
        $result['masked_name'] = implode(' ', $masked_name);
    } else {
        $result = false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Verification - Surakshaar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center p-4">

<div class="w-full max-w-lg bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="bg-slate-900 text-white text-center py-6">
        <h1 class="text-2xl font-bold tracking-widest">SURAKSHAAR</h1>
        <p class="text-slate-400 text-sm mt-1">Digital Credential Verification</p>
    </div>

    <div class="p-8">
        <?php if (empty($search_id)): ?>
            <form method="GET" action="" class="text-center">
                <p class="text-gray-600 mb-6">Enter a certificate ID to verify its authenticity.</p>
                <input type="text" name="id" required placeholder="e.g. CERT-12345" class="w-full text-center text-lg border border-gray-300 rounded-md py-3 px-4 mb-4 focus:ring-slate-500 focus:border-slate-500">
                <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 px-4 rounded-md transition">
                    Verify
                </button>
            </form>
        <?php elseif ($result === false): ?>
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 mb-4">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Invalid Certificate</h2>
                <p class="text-gray-500 mb-6">The certificate ID <strong><?php echo htmlspecialchars($search_id); ?></strong> could not be found in our records.</p>
                <a href="verify.php" class="text-slate-600 hover:text-slate-900 font-medium">Try another ID</a>
            </div>
        <?php else: ?>
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full <?php echo $result['status'] === 'valid' ? 'bg-green-100' : 'bg-red-100'; ?> mb-4">
                    <?php if($result['status'] === 'valid'): ?>
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <?php else: ?>
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <?php endif; ?>
                </div>
                
                <h2 class="text-2xl font-bold <?php echo $result['status'] === 'valid' ? 'text-green-600' : 'text-red-600'; ?> mb-6">
                    <?php echo $result['status'] === 'valid' ? 'Verified Credential' : 'Revoked Credential'; ?>
                </h2>

                <div class="bg-gray-50 rounded-lg p-6 text-left border border-gray-100">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider">Module</p>
                            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($result['modules']['name']); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider">Recipient</p>
                            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($result['masked_name']); ?></p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Score</p>
                                <p class="font-semibold text-gray-900"><?php echo $result['score']; ?>%</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider">Date</p>
                                <p class="font-semibold text-gray-900"><?php echo date('M d, Y', strtotime($result['issue_date'])); ?></p>
                            </div>
                        </div>
                        <div class="pt-2">
                            <p class="text-xs text-gray-400">ID: <?php echo htmlspecialchars($result['certificate_id']); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8">
                    <a href="verify.php" class="text-slate-500 hover:text-slate-800 text-sm">Verify another certificate</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<div class="mt-8 text-center text-gray-400 text-sm max-w-md">
    <p>This is a digital training credential verification system. It does not replace statutory government certification.</p>
</div>

</body>
</html>
