<?php
// admin/modules/filemanager/index.php

$uploadDir = UPLOADS_PATH;

// Ensure upload dir exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Handle File Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!Csrf::verifyToken($_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    $filename = $_POST['filename'] ?? '';
    // Security check: ensure filename doesn't contain directory traversal chars
    if ($filename && preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
        $filepath = $uploadDir . '/' . $filename;
        if (file_exists($filepath) && is_file($filepath)) {
            unlink($filepath);
            Session::setFlash('success', 'File deleted successfully.');
        } else {
            Session::setFlash('error', 'File not found.');
        }
    } else {
        Session::setFlash('error', 'Invalid filename.');
    }
    redirect(APP_URL . '/filemanager');
}

// Get all files
$files = [];
$dir = new DirectoryIterator($uploadDir);
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot() && $fileinfo->isFile() && $fileinfo->getFilename() !== '.htaccess') {
        $files[] = [
            'name' => $fileinfo->getFilename(),
            'size' => $fileinfo->getSize(),
            'modified' => $fileinfo->getMTime(),
            'path' => APP_URL . '/storage/uploads/' . $fileinfo->getFilename()
        ];
    }
}

// Sort by modified date descending
usort($files, function($a, $b) {
    return $b['modified'] <=> $a['modified'];
});

function formatBytes($size, $precision = 2) {
    $base = log($size, 1024);
    $suffixes = array('', 'KB', 'MB', 'GB', 'TB');
    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">File Manager</h1>
    <!-- We could add an upload button here, but they are usually uploaded via the editor or specific forms -->
    <span class="text-sm text-gray-500">Total files: <?= count($files) ?></span>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preview</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Modified</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($files)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No files found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($files as $file):
                    $isImage = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file['name']);
                ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($isImage): ?>
                                <img src="<?= h($file['path']) ?>" alt="<?= h($file['name']) ?>" class="h-10 w-10 object-cover rounded border">
                            <?php else: ?>
                                <div class="h-10 w-10 flex items-center justify-center bg-gray-100 text-gray-500 rounded border">
                                    <i class="fas fa-file"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 truncate w-48" title="<?= h($file['name']) ?>">
                                <?= h($file['name']) ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= formatBytes($file['size']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= date('Y-m-d H:i', $file['modified']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="<?= h($file['path']) ?>" target="_blank" class="text-blue-600 hover:text-blue-900 mr-3"><i class="fas fa-eye"></i> View</a>
                            <form action="<?= APP_URL ?>/filemanager" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this file? This cannot be undone.');">
                                <?= Csrf::getTokenField() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="filename" value="<?= h($file['name']) ?>">
                                <button type="submit" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i> Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>