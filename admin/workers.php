<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../config/supabase.php';

$supabase = new SupabaseHelper();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $data = [
            'worker_id' => filter_input(INPUT_POST, 'worker_id', FILTER_SANITIZE_STRING),
            'name' => filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING),
            'phone' => filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING),
            'role' => filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING),
            'site' => filter_input(INPUT_POST, 'site', FILTER_SANITIZE_STRING),
            'language' => filter_input(INPUT_POST, 'language', FILTER_SANITIZE_STRING)
        ];
        
        $res = $supabase->insert('workers', $data);
        if ($res['success']) {
            $message = 'Worker added successfully.';
            log_audit('create_worker', 'workers', $data['worker_id']);
        } else {
            $message = 'Error: ' . $res['error'];
        }
    }
}

// Fetch workers
$workers_res = $supabase->get('workers', 'order=created_at.desc');
$workers = $workers_res['success'] ? $workers_res['data'] : [];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Workers</h1>
        <p class="text-sm text-gray-500 mt-1">Manage personnel and roles.</p>
    </div>
    <!-- Simple add button for demo -->
    <button onclick="document.getElementById('addWorkerModal').classList.remove('hidden')" class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-md text-sm font-medium transition">
        + Add Worker
    </button>
</div>

<?php if ($message): ?>
    <div class="bg-blue-50 text-blue-700 p-3 rounded-md mb-4 text-sm">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-left text-gray-600">
                    <th class="px-6 py-3 font-semibold">Worker ID</th>
                    <th class="px-6 py-3 font-semibold">Name</th>
                    <th class="px-6 py-3 font-semibold">Role</th>
                    <th class="px-6 py-3 font-semibold">Site</th>
                    <th class="px-6 py-3 font-semibold">Language</th>
                    <th class="px-6 py-3 font-semibold">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(empty($workers)): ?>
                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No workers found.</td></tr>
                <?php else: foreach($workers as $w): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-slate-700"><?php echo htmlspecialchars($w['worker_id']); ?></td>
                    <td class="px-6 py-4"><?php echo htmlspecialchars($w['name']); ?></td>
                    <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($w['role'] ?? '-'); ?></td>
                    <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($w['site'] ?? '-'); ?></td>
                    <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($w['language'] ?? '-'); ?></td>
                    <td class="px-6 py-4">
                        <?php if($w['status'] === 'active'): ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                        <?php else: ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div id="addWorkerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
        <h2 class="text-xl font-bold mb-4">Add New Worker</h2>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <input type="hidden" name="action" value="add">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Worker ID</label>
                    <input type="text" name="worker_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-slate-500 focus:border-slate-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-slate-500 focus:border-slate-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="text" name="phone" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-slate-500 focus:border-slate-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Role</label>
                    <input type="text" name="role" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-slate-500 focus:border-slate-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Site</label>
                    <input type="text" name="site" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-slate-500 focus:border-slate-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Language</label>
                    <input type="text" name="language" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-slate-500 focus:border-slate-500 sm:text-sm">
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="document.getElementById('addWorkerModal').classList.add('hidden')" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md shadow-sm text-sm font-medium hover:bg-gray-50">Cancel</button>
                <button type="submit" class="bg-slate-900 text-white px-4 py-2 rounded-md shadow-sm text-sm font-medium hover:bg-slate-800">Save</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
