<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../config/supabase.php';

$supabase = new SupabaseHelper();

$logs_res = $supabase->get('audit_logs', 'order=created_at.desc&select=*,admins(name)');
$logs = $logs_res['success'] ? $logs_res['data'] : [];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Audit Logs</h1>
        <p class="text-sm text-gray-500 mt-1">System activity and admin actions.</p>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-left text-gray-600">
                    <th class="px-6 py-3 font-semibold">Date / Time</th>
                    <th class="px-6 py-3 font-semibold">Admin</th>
                    <th class="px-6 py-3 font-semibold">Action</th>
                    <th class="px-6 py-3 font-semibold">Entity Type</th>
                    <th class="px-6 py-3 font-semibold">Entity ID</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(empty($logs)): ?>
                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No logs found.</td></tr>
                <?php else: foreach($logs as $l): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-gray-500 text-xs">
                        <?php echo date('M d, Y H:i:s', strtotime($l['created_at'])); ?>
                    </td>
                    <td class="px-6 py-4 font-medium text-slate-700">
                        <?php echo htmlspecialchars($l['admins']['name'] ?? 'System'); ?>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-mono">
                            <?php echo htmlspecialchars($l['action']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($l['entity_type'] ?? '-'); ?></td>
                    <td class="px-6 py-4 text-gray-600 font-mono text-xs"><?php echo htmlspecialchars($l['entity_id'] ?? '-'); ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
