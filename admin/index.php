<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../config/supabase.php';

$supabase = new SupabaseHelper();

// Fetch summary data
// Since Supabase REST API doesn't easily support aggregations without RPC, we'll fetch basic counts using head or length.
// For prototype, we'll fetch data and count it if it's small, or use exact count param.

function getCount($supabase, $table, $query = '') {
    $res = $supabase->get($table, $query . '&select=id');
    return ($res['success'] && isset($res['data'])) ? count($res['data']) : 0;
}

$total_workers = getCount($supabase, 'workers');
$completed_trainings = getCount($supabase, 'training_attempts', 'completed=eq.true');
$pending_trainings = getCount($supabase, 'training_attempts', 'completed=eq.false');
$certificates = getCount($supabase, 'certificates', 'status=eq.valid');
$critical_failures = getCount($supabase, 'training_attempts', 'critical_fail=eq.true');

// Calculate average score
$avg_score = 0;
$scores_data = $supabase->get('training_attempts', 'completed=eq.true&select=score');
if ($scores_data['success'] && !empty($scores_data['data'])) {
    $total_score = array_sum(array_column($scores_data['data'], 'score'));
    $avg_score = round($total_score / count($scores_data['data']), 1);
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Overview of Surakshaar training platform.</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
    <!-- Card 1 -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 flex flex-col justify-center">
        <h3 class="text-sm font-medium text-gray-500 mb-1">Total Workers</h3>
        <p class="text-3xl font-bold text-slate-900"><?php echo $total_workers; ?></p>
    </div>
    
    <!-- Card 2 -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 flex flex-col justify-center">
        <h3 class="text-sm font-medium text-gray-500 mb-1">Completed Trainings</h3>
        <p class="text-3xl font-bold text-emerald-600"><?php echo $completed_trainings; ?></p>
    </div>
    
    <!-- Card 3 -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 flex flex-col justify-center">
        <h3 class="text-sm font-medium text-gray-500 mb-1">Pending Trainings</h3>
        <p class="text-3xl font-bold text-amber-500"><?php echo $pending_trainings; ?></p>
    </div>
    
    <!-- Card 4 -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 flex flex-col justify-center">
        <h3 class="text-sm font-medium text-gray-500 mb-1">Average Score</h3>
        <p class="text-3xl font-bold text-blue-600"><?php echo $avg_score; ?>%</p>
    </div>
    
    <!-- Card 5 -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 flex flex-col justify-center">
        <h3 class="text-sm font-medium text-gray-500 mb-1">Critical Failures</h3>
        <p class="text-3xl font-bold text-red-600"><?php echo $critical_failures; ?></p>
    </div>
    
    <!-- Card 6 -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 flex flex-col justify-center">
        <h3 class="text-sm font-medium text-gray-500 mb-1">Valid Certificates</h3>
        <p class="text-3xl font-bold text-indigo-600"><?php echo $certificates; ?></p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Recent Training Activity</h2>
        <?php
        $recent_training = $supabase->get('training_attempts', 'order=created_at.desc&limit=5&select=*,workers(name),modules(name)');
        if ($recent_training['success'] && !empty($recent_training['data'])):
        ?>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left border-b border-gray-200">
                            <th class="pb-3 font-semibold text-gray-600">Worker</th>
                            <th class="pb-3 font-semibold text-gray-600">Module</th>
                            <th class="pb-3 font-semibold text-gray-600">Score</th>
                            <th class="pb-3 font-semibold text-gray-600">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach($recent_training['data'] as $t): ?>
                        <tr>
                            <td class="py-3"><?php echo htmlspecialchars($t['workers']['name'] ?? 'Unknown'); ?></td>
                            <td class="py-3"><?php echo htmlspecialchars($t['modules']['name'] ?? 'Unknown'); ?></td>
                            <td class="py-3"><?php echo $t['score']; ?>%</td>
                            <td class="py-3">
                                <?php if($t['completed']): ?>
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Completed</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">Pending</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-8 text-gray-500">
                <p>No training data available yet.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Recent Certificates</h2>
        <?php
        $recent_certs = $supabase->get('certificates', 'order=created_at.desc&limit=5&select=*,workers(name),modules(name)');
        if ($recent_certs['success'] && !empty($recent_certs['data'])):
        ?>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left border-b border-gray-200">
                            <th class="pb-3 font-semibold text-gray-600">ID</th>
                            <th class="pb-3 font-semibold text-gray-600">Worker</th>
                            <th class="pb-3 font-semibold text-gray-600">Module</th>
                            <th class="pb-3 font-semibold text-gray-600">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach($recent_certs['data'] as $c): ?>
                        <tr>
                            <td class="py-3 font-medium text-slate-700"><?php echo htmlspecialchars($c['certificate_id']); ?></td>
                            <td class="py-3"><?php echo htmlspecialchars($c['workers']['name'] ?? 'Unknown'); ?></td>
                            <td class="py-3"><?php echo htmlspecialchars($c['modules']['name'] ?? 'Unknown'); ?></td>
                            <td class="py-3 text-gray-500"><?php echo date('M d, Y', strtotime($c['issue_date'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-8 text-gray-500">
                <p>No certificates available yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
