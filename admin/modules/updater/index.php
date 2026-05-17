<?php
// admin/modules/updater/index.php

Auth::requirePermission('manage_settings'); // Assuming if you can manage settings, you can update

$updateCheck = Updater::check();
$error = !$updateCheck['success'] ? $updateCheck['error'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifyToken($_POST['csrf_token'])) die("Invalid CSRF");

    if (isset($_POST['action']) && $_POST['action'] === 'apply_update' && $updateCheck['has_update']) {
        $result = Updater::apply($updateCheck['download_url']);
        if ($result['success']) {
            Session::setFlash('success', 'System updated successfully to ' . $updateCheck['latest_version'] . '!');
            // Redirect to dashboard after update
            redirect(APP_URL . '/');
        } else {
            $error = $result['error'];
        }
    }
}
?>

<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900"><?= __('System Updates') ?></h1>
    <p class="text-sm text-gray-500">Over-The-Air (OTA) Update Manager</p>
</div>

<?php if ($error): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
    <span class="block sm:inline"><?= h($error) ?></span>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white rounded-lg shadow p-6 border-t-4 border-gray-800 flex flex-col justify-center items-center text-center">
        <h2 class="text-xl font-bold mb-2">Current Version</h2>
        <div class="text-4xl font-mono font-bold text-gray-400 mb-2">v<?= h(APP_VERSION) ?></div>
        <p class="text-sm text-gray-500">Running Portable Admin</p>
    </div>

    <?php if ($updateCheck['success']): ?>
        <div class="bg-white rounded-lg shadow p-6 border-t-4 <?= $updateCheck['has_update'] ? 'border-green-500' : 'border-blue-500' ?>">
            <h2 class="text-xl font-bold mb-4">Update Status</h2>

            <?php if ($updateCheck['has_update']): ?>
                <div class="flex items-center text-green-600 mb-4">
                    <i class="fas fa-arrow-circle-up text-3xl mr-3"></i>
                    <div>
                        <span class="block font-bold text-lg">Update Available!</span>
                        <span class="text-sm">Version v<?= h($updateCheck['latest_version']) ?> is ready to install.</span>
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded text-sm text-gray-700 mb-6 border font-mono whitespace-pre-wrap">
                    <?= h($updateCheck['changelog']) ?>
                </div>

                <form action="" method="POST" onsubmit="return confirm('WARNING: It is highly recommended to create a Backup before updating. Are you sure you want to proceed?');">
                    <?= Csrf::getTokenField() ?>
                    <input type="hidden" name="action" value="apply_update">
                    <button type="submit" class="w-full bg-green-600 text-white font-bold py-3 px-4 rounded hover:bg-green-700 shadow flex justify-center items-center">
                        <i class="fas fa-download mr-2"></i> Install Update Now
                    </button>
                </form>
            <?php else: ?>
                <div class="flex flex-col items-center justify-center py-6 text-blue-600">
                    <i class="fas fa-check-circle text-5xl mb-4"></i>
                    <span class="block font-bold text-lg">You are up to date!</span>
                    <span class="text-sm text-gray-500 text-center mt-2">No new versions found on the remote server.</span>
                </div>
                <div class="text-center mt-4">
                    <a href="?" class="text-sm text-gray-500 hover:text-gray-900 underline"><i class="fas fa-sync-alt mr-1"></i> Check Again</a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>