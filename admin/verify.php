<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../config/supabase.php';

$supabase = new SupabaseHelper();
$result = null;
$search_id = $_GET['id'] ?? '';

if (!empty($search_id)) {
    $query = 'certificate_id=eq.' . urlencode($search_id) . '&select=*,workers(name,worker_id),modules(name)';
    $res = $supabase->get('certificates', $query);
    if ($res['success'] && !empty($res['data'])) {
        $result = $res['data'][0];
        log_audit('admin_verify_cert', 'certificates', $result['certificate_id']);
    } else {
        $result = false; // Not found
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Verify Certificate</h1>
    <p class="text-sm text-gray-500 mt-1">Internal tool to check certificate status.</p>
</div>

<div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 max-w-2xl">
    <form method="GET" action="" class="flex gap-4 mb-8">
        <input type="text" name="id" value="<?php echo htmlspecialchars($search_id); ?>" placeholder="Enter Certificate ID (e.g. CERT-1234)" class="flex-1 border border-gray-300 rounded-md shadow-sm py-2 px-4 focus:outline-none focus:ring-slate-500 focus:border-slate-500">
        <button type="submit" class="bg-slate-900 text-white px-6 py-2 rounded-md font-medium hover:bg-slate-800 transition">Verify</button>
    </form>

    <?php if ($result === false): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 p-6 rounded-lg text-center">
            <svg class="w-12 h-12 text-red-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <h3 class="text-xl font-bold mb-1">INVALID</h3>
            <p>No certificate found with ID: <?php echo htmlspecialchars($search_id); ?></p>
        </div>
    <?php elseif ($result !== null): ?>
        <div class="border <?php echo $result['status'] === 'valid' ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'; ?> p-6 rounded-lg">
            <div class="text-center mb-6">
                <?php if ($result['status'] === 'valid'): ?>
                    <svg class="w-12 h-12 text-green-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <h3 class="text-xl font-bold text-green-700 mb-1">VALID CERTIFICATE</h3>
                <?php else: ?>
                    <svg class="w-12 h-12 text-red-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <h3 class="text-xl font-bold text-red-700 mb-1">REVOKED CERTIFICATE</h3>
                <?php endif; ?>
            </div>
            
            <div class="grid grid-cols-2 gap-y-4 gap-x-8 text-sm">
                <div>
                    <span class="block text-gray-500 mb-1">Certificate ID</span>
                    <strong class="text-gray-900 text-base"><?php echo htmlspecialchars($result['certificate_id']); ?></strong>
                </div>
                <div>
                    <span class="block text-gray-500 mb-1">Issue Date</span>
                    <strong class="text-gray-900 text-base"><?php echo date('F d, Y', strtotime($result['issue_date'])); ?></strong>
                </div>
                <div>
                    <span class="block text-gray-500 mb-1">Worker Name</span>
                    <strong class="text-gray-900 text-base"><?php echo htmlspecialchars($result['workers']['name'] ?? 'Unknown'); ?></strong>
                </div>
                <div>
                    <span class="block text-gray-500 mb-1">Worker ID</span>
                    <strong class="text-gray-900 text-base"><?php echo htmlspecialchars($result['workers']['worker_id'] ?? 'Unknown'); ?></strong>
                </div>
                <div class="col-span-2 pt-4 border-t border-gray-200 mt-2">
                    <span class="block text-gray-500 mb-1">Training Module</span>
                    <strong class="text-gray-900 text-lg"><?php echo htmlspecialchars($result['modules']['name'] ?? 'Unknown'); ?></strong>
                </div>
                <div>
                    <span class="block text-gray-500 mb-1">Score</span>
                    <strong class="text-gray-900 text-base"><?php echo $result['score']; ?>%</strong>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
