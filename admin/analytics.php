<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../config/supabase.php';

$supabase = new SupabaseHelper();

// We need to do some basic processing here since we can't do complex GROUP BY easily with Supabase REST API without RPC.
// We'll fetch all training records (or a large chunk) to process in PHP for the prototype.
$trainings_res = $supabase->get('training_attempts', 'select=*,modules(name,module_code)');
$trainings = $trainings_res['success'] ? $trainings_res['data'] : [];

$stats = [
    'fire' => ['attempts' => 0, 'completed' => 0, 'score_sum' => 0, 'critical_fails' => 0],
    'gas' => ['attempts' => 0, 'completed' => 0, 'score_sum' => 0, 'critical_fails' => 0]
];

foreach ($trainings as $t) {
    $code = $t['modules']['module_code'] ?? '';
    if (isset($stats[$code])) {
        $stats[$code]['attempts']++;
        if ($t['completed']) $stats[$code]['completed']++;
        if ($t['critical_fail']) $stats[$code]['critical_fails']++;
        $stats[$code]['score_sum'] += $t['score'];
    }
}

function calculate_avg($sum, $count) {
    return $count > 0 ? round($sum / $count, 1) : 0;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Analytics</h1>
    <p class="text-sm text-gray-500 mt-1">Module performance and training statistics.</p>
</div>

<?php if (empty($trainings)): ?>
    <div class="bg-white p-8 text-center rounded-lg shadow-sm border border-gray-100">
        <p class="text-gray-500">No training data available.</p>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <!-- Fire Safety Stats -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-red-600 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"></path></svg>
                Fire Safety
            </h2>
            <div class="space-y-4">
                <div class="flex justify-between items-center border-b border-gray-50 pb-2">
                    <span class="text-gray-600">Total Attempts</span>
                    <span class="font-semibold text-slate-900"><?php echo $stats['fire']['attempts']; ?></span>
                </div>
                <div class="flex justify-between items-center border-b border-gray-50 pb-2">
                    <span class="text-gray-600">Completed</span>
                    <span class="font-semibold text-emerald-600"><?php echo $stats['fire']['completed']; ?></span>
                </div>
                <div class="flex justify-between items-center border-b border-gray-50 pb-2">
                    <span class="text-gray-600">Average Score</span>
                    <span class="font-semibold text-blue-600"><?php echo calculate_avg($stats['fire']['score_sum'], $stats['fire']['attempts']); ?>%</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Critical Failures</span>
                    <span class="font-semibold text-red-600"><?php echo $stats['fire']['critical_fails']; ?></span>
                </div>
            </div>
        </div>

        <!-- Gas Safety Stats -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-yellow-600 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                Gas Safety
            </h2>
            <div class="space-y-4">
                <div class="flex justify-between items-center border-b border-gray-50 pb-2">
                    <span class="text-gray-600">Total Attempts</span>
                    <span class="font-semibold text-slate-900"><?php echo $stats['gas']['attempts']; ?></span>
                </div>
                <div class="flex justify-between items-center border-b border-gray-50 pb-2">
                    <span class="text-gray-600">Completed</span>
                    <span class="font-semibold text-emerald-600"><?php echo $stats['gas']['completed']; ?></span>
                </div>
                <div class="flex justify-between items-center border-b border-gray-50 pb-2">
                    <span class="text-gray-600">Average Score</span>
                    <span class="font-semibold text-blue-600"><?php echo calculate_avg($stats['gas']['score_sum'], $stats['gas']['attempts']); ?>%</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Critical Failures</span>
                    <span class="font-semibold text-red-600"><?php echo $stats['gas']['critical_fails']; ?></span>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
