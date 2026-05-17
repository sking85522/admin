<?php
// admin/modules/backups/index.php

Auth::requirePermission('manage_backups');

$backupDir = STORAGE_PATH . '/backups';
if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifyToken($_POST['csrf_token'])) die("Invalid CSRF");

    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            $filename = 'backup_' . date('Y_m_d_His') . '.zip';
            $filepath = $backupDir . '/' . $filename;

            $zip = new ZipArchive();
            if ($zip->open($filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {

                // Add all files in storage (except backups and cache)
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(STORAGE_PATH));
                foreach ($iterator as $file) {
                    if (!$file->isDir()) {
                        $realPath = $file->getRealPath();
                        $relativePath = substr($realPath, strlen(STORAGE_PATH) + 1);

                        if (strpos($relativePath, 'backups') !== 0 && strpos($relativePath, 'cache') !== 0) {
                            $zip->addFile($realPath, $relativePath);
                        }
                    }
                }
                $zip->close();
                Logger::log("Created system backup: $filename", 'SYSTEM');
                Session::setFlash('success', 'Backup created successfully.');
            } else {
                Session::setFlash('error', 'Failed to create zip archive.');
            }
        } elseif ($_POST['action'] === 'delete') {
            $file = basename($_POST['filename']);
            if (file_exists($backupDir . '/' . $file)) {
                unlink($backupDir . '/' . $file);
                Logger::log("Deleted backup: $file", 'SYSTEM');
                Session::setFlash('success', 'Backup deleted.');
            }
        } elseif ($_POST['action'] === 'restore') {
            $file = basename($_POST['filename']);
            $filepath = $backupDir . '/' . $file;
            if (file_exists($filepath)) {
                $zip = new ZipArchive;
                if ($zip->open($filepath) === TRUE) {
                    $zip->extractTo(STORAGE_PATH);
                    $zip->close();
                    Logger::log("Restored system from backup: $file", 'SYSTEM');
                    Session::setFlash('success', 'Database restored successfully.');
                } else {
                    Session::setFlash('error', 'Failed to read zip archive for restore.');
                }
            }
        }
    }

    redirect(APP_URL . '/backups');
}

$backups = array_diff(scandir($backupDir), ['.', '..']);
arsort($backups); // Newest first
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Database Backups</h1>
    <form action="" method="POST">
        <?= Csrf::getTokenField() ?>
        <input type="hidden" name="action" value="create">
        <button class="bg-primary text-white px-4 py-2 rounded hover:bg-blue-600 shadow"><i class="fas fa-download mr-2"></i> Create Backup</button>
    </form>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">File Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Size</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php foreach ($backups as $backup):
                $path = $backupDir . '/' . $backup;
            ?>
            <tr>
                <td class="px-6 py-4 whitespace-nowrap font-medium font-mono text-sm"><?= h($backup) ?></td>
                <td class="px-6 py-4 text-sm text-gray-500"><?= round(filesize($path) / 1024, 2) ?> KB</td>
                <td class="px-6 py-4 text-sm text-gray-500"><?= date('Y-m-d H:i:s', filemtime($path)) ?></td>
                <td class="px-6 py-4 text-right text-sm font-medium">
                    <form action="" method="POST" class="inline mr-3" onsubmit="return confirm('WARNING: This will overwrite your current database with this backup! Are you sure?');">
                        <?= Csrf::getTokenField() ?>
                        <input type="hidden" name="action" value="restore">
                        <input type="hidden" name="filename" value="<?= h($backup) ?>">
                        <button type="submit" class="text-blue-600 hover:text-blue-900"><i class="fas fa-undo"></i> Restore</button>
                    </form>
                    <a href="<?= APP_URL ?>/storage/backups/<?= h($backup) ?>" download class="text-green-600 hover:text-green-900 mr-3"><i class="fas fa-file-download"></i> Download</a>
                    <form action="" method="POST" class="inline" onsubmit="return confirm('Delete this backup?');">
                        <?= Csrf::getTokenField() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="filename" value="<?= h($backup) ?>">
                        <button class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($backups)): ?>
                <tr><td colspan="4" class="p-4 text-center text-gray-500">No backups found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>