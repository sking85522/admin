<?php
// admin/modules/downloader/index.php

Auth::requirePermission('manage_settings'); // Only highly privileged users should do this

$clonesDir = STORAGE_PATH . '/clones';
if (!is_dir($clonesDir)) mkdir($clonesDir, 0755, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifyToken($_POST['csrf_token'])) die("Invalid CSRF");

    if (isset($_POST['action']) && $_POST['action'] === 'download') {
        $repoUrl = $_POST['repo_url'] ?? '';

        // Basic validation for GitHub URLs
        if (preg_match('/^https?:\/\/github\.com\/([a-zA-Z0-9_.-]+)\/([a-zA-Z0-9_.-]+)\/?$/', $repoUrl, $matches)) {
            $user = $matches[1];
            $repo = $matches[2];

            // Construct the ZIP download URL for the main/master branch
            $zipUrl = "https://github.com/{$user}/{$repo}/archive/refs/heads/main.zip";

            // Also try master branch if main fails (handled later if first download fails)
            $zipUrlMaster = "https://github.com/{$user}/{$repo}/archive/refs/heads/master.zip";

            $tempZip = STORAGE_PATH . '/cache/clone_' . time() . '.zip';

            // Set context to define User-Agent (GitHub requires it for API/Downloads)
            $context = stream_context_create([
                "http" => [
                    "header" => "User-Agent: PHP-Admin-Panel-Downloader\r\n"
                ]
            ]);

            $zipData = @file_get_contents($zipUrl, false, $context);
            if (!$zipData) {
                 // Try master branch
                 $zipData = @file_get_contents($zipUrlMaster, false, $context);
            }

            if ($zipData) {
                file_put_contents($tempZip, $zipData);

                $zip = new ZipArchive;
                if ($zip->open($tempZip) === TRUE) {
                    $extractPath = $clonesDir . '/' . $repo . '_' . time();
                    $zip->extractTo($extractPath);
                    $zip->close();
                    unlink($tempZip); // Clean up

                    Logger::log("Successfully downloaded GitHub repository: {$user}/{$repo}", 'SYSTEM');
                    Session::setFlash('success', "Repository downloaded successfully to storage/clones!");
                } else {
                    Session::setFlash('error', "Failed to open the downloaded ZIP archive.");
                }
            } else {
                Session::setFlash('error', "Could not download from GitHub. Ensure the repository is public and has a 'main' or 'master' branch.");
            }

        } else {
            Session::setFlash('error', "Invalid GitHub repository URL. Format must be: https://github.com/user/repo");
        }
        redirect(APP_URL . '/downloader');
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
         $targetDir = $clonesDir . '/' . basename($_POST['dirname']);
         if (is_dir($targetDir)) {
             deleteDir($targetDir);
             Session::setFlash('success', "Downloaded folder deleted.");
         }
         redirect(APP_URL . '/downloader');
    }
}

function deleteDir($dirPath) {
    if (!is_dir($dirPath)) return;
    $objects = scandir($dirPath);
    foreach ($objects as $object) {
        if ($object != "." && $object != "..") {
            if (is_dir($dirPath . DIRECTORY_SEPARATOR . $object) && !is_link($dirPath . "/" . $object))
                deleteDir($dirPath . DIRECTORY_SEPARATOR . $object);
            else
                unlink($dirPath . DIRECTORY_SEPARATOR . $object);
        }
    }
    rmdir($dirPath);
}

$downloadedRepos = array_diff(scandir($clonesDir), ['.', '..']);
?>

<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Git-Free Downloader</h1>
    <p class="text-sm text-gray-500">Download public GitHub repositories directly to your server without needing Git installed.</p>
</div>

<div class="bg-white rounded-lg shadow p-6 mb-8 border-t-4 border-gray-800">
    <form action="" method="POST" class="flex flex-col md:flex-row gap-4 items-end">
        <?= Csrf::getTokenField() ?>
        <input type="hidden" name="action" value="download">

        <div class="flex-1 w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1">GitHub Repository URL</label>
            <div class="flex rounded-md shadow-sm">
                <span class="inline-flex items-center rounded-l-md border border-r-0 border-gray-300 bg-gray-50 px-3 text-gray-500 sm:text-sm">
                    <i class="fab fa-github"></i>
                </span>
                <input type="url" name="repo_url" required placeholder="https://github.com/username/repository" class="block w-full min-w-0 flex-1 rounded-none rounded-r-md border border-gray-300 px-3 py-2 focus:border-primary focus:ring-primary sm:text-sm">
            </div>
        </div>

        <button type="submit" class="bg-gray-800 text-white font-bold py-2 px-6 rounded hover:bg-black transition-colors whitespace-nowrap">
            <i class="fas fa-cloud-download-alt mr-2"></i> Download Repo
        </button>
    </form>
</div>

<h2 class="text-lg font-bold mb-4">Downloaded Repositories (<code>storage/clones/</code>)</h2>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Folder Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Downloaded At</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($downloadedRepos as $repo):
                $path = $clonesDir . '/' . $repo;
                if (!is_dir($path)) continue;
            ?>
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <i class="fas fa-folder text-yellow-400 text-xl mr-3"></i>
                        <span class="text-sm font-medium text-gray-900 font-mono"><?= h($repo) ?></span>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?= date('Y-m-d H:i:s', filemtime($path)) ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <form action="" method="POST" class="inline-block" onsubmit="return confirm('Delete this downloaded folder entirely?');">
                        <?= Csrf::getTokenField() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="dirname" value="<?= h($repo) ?>">
                        <button type="submit" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i> Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($downloadedRepos)): ?>
                <tr>
                    <td colspan="3" class="px-6 py-8 whitespace-nowrap text-sm text-gray-500 text-center">
                        <i class="fas fa-box-open text-3xl mb-2 text-gray-300 block"></i>
                        No repositories downloaded yet.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>