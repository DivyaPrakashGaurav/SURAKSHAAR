<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../config/supabase.php';

$supabase = new SupabaseHelper();

// Fetch certificates
$certs_res = $supabase->get('certificates', 'order=created_at.desc&select=*,workers(name,worker_id),modules(name)');
$certificates = $certs_res['success'] ? $certs_res['data'] : [];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Certificates</h1>
        <p class="text-sm text-gray-500 mt-1">Manage worker certifications and validity.</p>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-left text-gray-600">
                    <th class="px-6 py-3 font-semibold">Certificate ID</th>
                    <th class="px-6 py-3 font-semibold">Worker</th>
                    <th class="px-6 py-3 font-semibold">Module</th>
                    <th class="px-6 py-3 font-semibold">Score</th>
                    <th class="px-6 py-3 font-semibold">Issue Date</th>
                    <th class="px-6 py-3 font-semibold">Status</th>
                    <th class="px-6 py-3 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(empty($certificates)): ?>
                    <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">No certificates available.</td></tr>
                <?php else: foreach($certificates as $c): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-slate-700"><?php echo htmlspecialchars($c['certificate_id']); ?></td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-800"><?php echo htmlspecialchars($c['workers']['name'] ?? 'Unknown'); ?></div>
                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($c['workers']['worker_id'] ?? ''); ?></div>
                    </td>
                    <td class="px-6 py-4 text-gray-800"><?php echo htmlspecialchars($c['modules']['name'] ?? 'Unknown'); ?></td>
                    <td class="px-6 py-4 font-medium text-slate-700"><?php echo $c['score']; ?>%</td>
                    <td class="px-6 py-4 text-gray-500"><?php echo date('M d, Y', strtotime($c['issue_date'])); ?></td>
                    <td class="px-6 py-4">
                        <?php if($c['status'] === 'valid'): ?>
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Valid</span>
                        <?php else: ?>
                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">Revoked</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="/verify.php?id=<?php echo urlencode($c['certificate_id']); ?>" target="_blank" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium mr-3">Verify Link</a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
