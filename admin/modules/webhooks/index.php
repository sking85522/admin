<?php
// admin/modules/webhooks/index.php

Auth::requirePermission('manage_settings');

$db = new JsonDB(CONTENT_PATH . '/webhooks.json');
$webhooks = $db->getAll();

$availableEvents = [
    '*' => 'All Events',
    'post.created' => 'Post Created',
    'post.updated' => 'Post Updated',
    'user.login' => 'User Login',
    'user.created' => 'User Created'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifyToken($_POST['csrf_token'])) die("Invalid CSRF");

    if (isset($_POST['action']) && $_POST['action'] === 'create') {
        $db->insert([
            'name' => $_POST['name'],
            'url' => $_POST['url'],
            'secret' => $_POST['secret'] ?? '',
            'events' => $_POST['events'] ?? [],
            'created' => date('Y-m-d H:i:s')
        ]);
        Session::setFlash('success', 'Webhook endpoint added.');
        redirect(APP_URL . '/webhooks');
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $db->delete($_POST['id']);
        Session::setFlash('success', 'Webhook deleted.');
        redirect(APP_URL . '/webhooks');
    }
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Webhooks</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Endpoint</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Events</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($webhooks as $hook): ?>
                <tr>
                    <td class="px-6 py-4">
                        <div class="font-bold text-gray-900"><?= h($hook['name']) ?></div>
                        <div class="text-xs font-mono text-gray-500 mt-1 truncate w-48"><?= h($hook['url']) ?></div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <div class="flex flex-wrap gap-1">
                            <?php foreach($hook['events'] as $e): ?>
                                <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs border"><?= h($e) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                        <form action="" method="POST" onsubmit="return confirm('Delete this webhook?');">
                            <?= Csrf::getTokenField() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $hook['id'] ?>">
                            <button class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($webhooks)): ?>
                    <tr><td colspan="3" class="p-6 text-center text-gray-500">No webhooks configured.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-lg shadow p-6 h-fit">
        <h2 class="text-lg font-bold mb-4">Add Webhook</h2>
        <form action="" method="POST">
            <?= Csrf::getTokenField() ?>
            <input type="hidden" name="action" value="create">

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" required placeholder="e.g. Discord Alerts" class="mt-1 block w-full border border-gray-300 rounded p-2 text-sm">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Payload URL</label>
                <input type="url" name="url" required placeholder="https://..." class="mt-1 block w-full border border-gray-300 rounded p-2 text-sm">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Secret (Optional)</label>
                <input type="text" name="secret" placeholder="Used for HMAC signature" class="mt-1 block w-full border border-gray-300 rounded p-2 text-sm">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Subscribe to Events</label>
                <div class="space-y-2 border rounded p-3 bg-gray-50">
                    <?php foreach ($availableEvents as $key => $label): ?>
                    <label class="flex items-center">
                        <input type="checkbox" name="events[]" value="<?= $key ?>" class="mr-2 h-4 w-4 text-primary">
                        <span class="text-sm text-gray-700"><?= h($label) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="w-full bg-primary text-white py-2 rounded hover:bg-blue-600 font-bold shadow">Save Webhook</button>
        </form>
    </div>
</div>