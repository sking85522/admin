<?php
// admin/modules/apikeys/index.php

Auth::requirePermission('manage_apikeys');

$db = new JsonDB(CONTENT_PATH . '/apikeys.json');
$keys = $db->getAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifyToken($_POST['csrf_token'])) die("Invalid CSRF");

    if (isset($_POST['action']) && $_POST['action'] === 'create') {
        $keyString = 'sk_' . bin2hex(random_bytes(16));
        $db->insert([
            'name' => $_POST['name'] ?? 'New Key',
            'key' => $keyString, // In a real app, hash this before saving! (Simplified for demo)
            'created' => date('Y-m-d H:i:s'),
            'last_used' => 'Never'
        ]);
        Session::setFlash('success', "API Key created: " . $keyString);
        Logger::log("Created new API Key", 'SYSTEM');
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $db->delete($_POST['id']);
        Session::setFlash('success', "API Key deleted.");
        Logger::log("Deleted API Key", 'SYSTEM');
    }
    redirect(APP_URL . '/apikeys');
}
?>
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">API Keys</h1>
</div>

<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-lg font-bold mb-4">Generate New Key</h2>
    <form action="" method="POST" class="flex gap-4">
        <?= Csrf::getTokenField() ?>
        <input type="hidden" name="action" value="create">
        <input type="text" name="name" required placeholder="Key Name (e.g., Mobile App)" class="border rounded px-4 py-2 flex-1">
        <button type="submit" class="bg-primary text-white px-4 py-2 rounded font-bold">Generate</button>
    </form>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Key Prefix</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php foreach ($keys as $k): ?>
            <tr>
                <td class="px-6 py-4 font-bold text-gray-900"><?= h($k['name']) ?></td>
                <td class="px-6 py-4 font-mono text-sm text-gray-500"><?= substr($k['key'], 0, 10) ?>...</td>
                <td class="px-6 py-4 text-sm text-gray-500"><?= h($k['created']) ?></td>
                <td class="px-6 py-4 text-right">
                    <form action="" method="POST" onsubmit="return confirm('Revoke this key immediately?');">
                        <?= Csrf::getTokenField() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $k['id'] ?>">
                        <button class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i> Revoke</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>