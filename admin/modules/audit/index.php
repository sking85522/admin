<?php
// admin/modules/audit/index.php

Auth::requirePermission('view_audit');

$auditFile = LOGS_PATH . '/audit.json';
$logs = file_exists($auditFile) ? json_decode(file_get_contents($auditFile), true) : [];

// Basic filtering
$filterUser = $_GET['user'] ?? '';
$filterLevel = $_GET['level'] ?? '';

if ($filterUser || $filterLevel) {
    $logs = array_filter($logs, function($log) use ($filterUser, $filterLevel) {
        $match = true;
        if ($filterUser && stripos($log['user'], $filterUser) === false) $match = false;
        if ($filterLevel && $log['level'] !== $filterLevel) $match = false;
        return $match;
    });
}
?>

<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Audit Logs</h1>
    <p class="text-sm text-gray-500">System activity tracker.</p>
</div>

<div class="bg-white p-4 rounded-lg shadow mb-6 flex gap-4">
    <form action="" method="GET" class="flex gap-4 items-end">
        <div>
            <label class="block text-xs text-gray-500 uppercase">User</label>
            <input type="text" name="user" value="<?= h($filterUser) ?>" class="border rounded px-2 py-1">
        </div>
        <div>
            <label class="block text-xs text-gray-500 uppercase">Level</label>
            <select name="level" class="border rounded px-2 py-1">
                <option value="">All</option>
                <option value="INFO" <?= $filterLevel === 'INFO' ? 'selected' : '' ?>>INFO</option>
                <option value="WARNING" <?= $filterLevel === 'WARNING' ? 'selected' : '' ?>>WARNING</option>
                <option value="ERROR" <?= $filterLevel === 'ERROR' ? 'selected' : '' ?>>ERROR</option>
                <option value="AUTH" <?= $filterLevel === 'AUTH' ? 'selected' : '' ?>>AUTH</option>
                <option value="SYSTEM" <?= $filterLevel === 'SYSTEM' ? 'selected' : '' ?>>SYSTEM</option>
            </select>
        </div>
        <button type="submit" class="bg-gray-800 text-white px-4 py-1 rounded">Filter</button>
        <a href="?" class="text-gray-500 underline ml-2 text-sm">Clear</a>
    </form>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php foreach (array_slice($logs, 0, 100) as $log):
                $color = match($log['level']) {
                    'ERROR' => 'bg-red-100 text-red-800',
                    'WARNING' => 'bg-yellow-100 text-yellow-800',
                    'AUTH' => 'bg-green-100 text-green-800',
                    'SYSTEM' => 'bg-purple-100 text-purple-800',
                    default => 'bg-gray-100 text-gray-800'
                };
            ?>
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= h($log['time']) ?></td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs font-bold rounded <?= $color ?>"><?= h($log['level']) ?></span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900"><?= h($log['user']) ?></td>
                <td class="px-6 py-4 text-sm text-gray-700"><?= h($log['message']) ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono"><?= h($log['ip']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($logs)): ?>
                <tr><td colspan="5" class="p-4 text-center text-gray-500">No logs found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>