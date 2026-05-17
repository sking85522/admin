<?php
// admin/modules/plugins/index.php

Auth::requirePermission('manage_plugins');

$plugins = PluginManager::getAvailablePlugins();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifyToken($_POST['csrf_token'])) die("Invalid CSRF");

    if (isset($_FILES['plugin_zip'])) {
        $result = PluginManager::installFromZip($_FILES['plugin_zip']['tmp_name']);
        if ($result['success']) {
            Session::setFlash('success', 'Plugin installed successfully!');
        } else {
            Session::setFlash('error', $result['error']);
        }
    }
    redirect(APP_URL . '/plugins');
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Plugin Manager</h1>
</div>

<div class="bg-white p-6 rounded-lg shadow mb-8">
    <h2 class="text-lg font-bold mb-4">Install New Plugin</h2>
    <form action="" method="POST" enctype="multipart/form-data" class="flex items-end gap-4">
        <?= Csrf::getTokenField() ?>
        <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-2">Upload Plugin ZIP File</label>
            <input type="file" name="plugin_zip" accept=".zip" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-blue-600">
        </div>
        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded font-bold hover:bg-green-700">Install</button>
    </form>
</div>

<h2 class="text-xl font-bold mb-4">Installed Plugins (Modules)</h2>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($plugins as $plugin): ?>
    <div class="bg-white rounded-lg shadow p-6 border-t-4 border-gray-800">
        <div class="flex items-center mb-4">
            <i class="<?= h($plugin['icon'] ?? 'fas fa-puzzle-piece') ?> text-2xl text-gray-400 mr-4"></i>
            <div>
                <h3 class="font-bold text-lg"><?= h($plugin['name'] ?? $plugin['slug']) ?></h3>
                <span class="text-xs bg-gray-200 text-gray-600 px-2 rounded">v<?= h($plugin['version'] ?? '1.0') ?></span>
            </div>
        </div>
        <p class="text-gray-600 text-sm mb-4 h-10 overflow-hidden"><?= h($plugin['description'] ?? 'No description.') ?></p>
        <div class="flex justify-end gap-2 border-t pt-4">
            <a href="<?= APP_URL ?>/<?= $plugin['slug'] ?>" class="text-primary hover:underline text-sm font-bold">Open Module</a>
        </div>
    </div>
    <?php endforeach; ?>
</div>