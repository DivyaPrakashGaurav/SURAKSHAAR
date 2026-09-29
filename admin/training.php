<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../config/supabase.php';

$supabase = new SupabaseHelper();

// Fetch training records
$trainings_res = $supabase->get('training_attempts', 'order=created_at.desc&select=*,workers(name,worker_id),modules(name)');
$trainings = $trainings_res['success'] ? $trainings_res['data'] : [];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Training Records</h1>
    <p class="text-sm text-gray-500 mt-1">View worker training attempts and scores.</p>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-left text-gray-600">
                    <th class="px-6 py-3 font-semibold">Worker</th>
                    <th class="px-6 py-3 font-semibold">Module</th>
                    <th class="px-6 py-3 font-semibold">Score</th>
                    <th class="px-6 py-3 font-semibold">Attempts</th>
                    <th class="px-6 py-3 font-semibold">Status</th>
                    <th class="px-6 py-3 font-semibold">Critical Fail</th>
                    <th class="px-6 py-3 font-semibold">AR Used</th>
                    <th class="px-6 py-3 font-semibold">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(empty($trainings)): ?>
                    <tr><td colspan="8" class="px-6 py-8 text-center text-gray-500">No training records available.</td></tr>
                <?php else: foreach($trainings as $t): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="font-medium text-slate-700"><?php echo htmlspecialchars($t['workers']['name'] ?? 'Unknown'); ?></div>
                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($t['workers']['worker_id'] ?? ''); ?></div>
                    </td>
                    <td class="px-6 py-4 text-gray-800"><?php echo htmlspecialchars($t['modules']['name'] ?? 'Unknown'); ?></td>
                    <td class="px-6 py-4 font-medium <?php echo $t['score'] >= 80 ? 'text-green-600' : 'text-red-600'; ?>">
                        <?php echo $t['score']; ?>%
                    </td>
                    <td class="px-6 py-4 text-gray-600"><?php echo $t['attempts']; ?></td>
                    <td class="px-6 py-4">
                        <?php if($t['completed']): ?>
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Completed</span>
                        <?php else: ?>
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-gray-600">
                        <?php if($t['critical_fail']): ?>
                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">Yes</span>
                        <?php else: ?>
                            <span class="text-gray-400">No</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-gray-600">
                        <?php echo $t['ar_used'] ? 'Yes' : 'No'; ?>
                    </td>
                    <td class="px-6 py-4 text-gray-500 text-xs">
                        <?php echo date('M d, Y H:i', strtotime($t['created_at'])); ?>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
