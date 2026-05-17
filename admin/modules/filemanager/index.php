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

<!-- Drag and Drop Upload Zone -->
<div id="dropzone" class="mb-6 border-2 border-dashed border-gray-300 rounded-lg bg-white p-8 text-center hover:bg-gray-50 transition-colors cursor-pointer relative" onclick="document.getElementById('fileInput').click()">
    <div class="flex flex-col items-center justify-center">
        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
        <p class="text-gray-600 font-medium">Drag & Drop files here or click to upload</p>
        <p class="text-xs text-gray-400 mt-1">Supports JPG, PNG, GIF, WEBP up to 5MB</p>
    </div>
    <input type="file" id="fileInput" class="hidden" accept="image/*" multiple>

    <!-- Upload Progress -->
    <div id="uploadProgress" class="absolute inset-0 bg-white bg-opacity-90 flex items-center justify-center hidden rounded-lg">
        <div class="w-2/3">
            <div class="flex justify-between mb-1">
                <span class="text-sm font-medium text-primary">Uploading...</span>
                <span class="text-sm font-medium text-primary" id="progressText">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-primary h-2.5 rounded-full" style="width: 0%" id="progressBar"></div>
            </div>
        </div>
    </div>
</div>

<!-- Media Grid -->
<div class="bg-white rounded-lg shadow p-6">
    <?php if (empty($files)): ?>
        <div class="text-center py-10 text-gray-500">
            <i class="fas fa-folder-open text-4xl mb-3 text-gray-300"></i>
            <p>No media files found.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <?php foreach ($files as $file):
                $isImage = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file['name']);
            ?>
                <div class="group relative bg-gray-100 rounded-lg overflow-hidden border border-gray-200 aspect-square flex items-center justify-center">
                    <?php if ($isImage): ?>
                        <img src="<?= h($file['path']) ?>" alt="<?= h($file['name']) ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <i class="fas fa-file-alt text-4xl text-gray-400"></i>
                    <?php endif; ?>

                    <!-- Overlay info -->
                    <div class="absolute inset-0 bg-black bg-opacity-70 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-between p-2">
                        <div class="text-white text-xs truncate w-full font-mono" title="<?= h($file['name']) ?>"><?= h($file['name']) ?></div>

                        <div class="flex justify-between items-end">
                            <span class="text-gray-300 text-[10px]"><?= formatBytes($file['size']) ?></span>

                            <div class="flex gap-1">
                                <a href="<?= h($file['path']) ?>" target="_blank" class="bg-blue-600 text-white p-1.5 rounded hover:bg-blue-500 transition-colors" title="View"><i class="fas fa-eye fa-fw text-xs"></i></a>

                                <form action="<?= APP_URL ?>/filemanager" method="POST" class="inline" onsubmit="return confirm('Delete this file forever?');">
                                    <?= Csrf::getTokenField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="filename" value="<?= h($file['name']) ?>">
                                    <button type="submit" class="bg-red-600 text-white p-1.5 rounded hover:bg-red-500 transition-colors" title="Delete"><i class="fas fa-trash fa-fw text-xs"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');
    const progressOverlay = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults (e) { e.preventDefault(); e.stopPropagation(); }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) { dropzone.classList.add('border-primary', 'bg-blue-50'); }
    function unhighlight(e) { dropzone.classList.remove('border-primary', 'bg-blue-50'); }

    dropzone.addEventListener('drop', handleDrop, false);
    fileInput.addEventListener('change', function() { handleFiles(this.files); });

    function handleDrop(e) {
        let dt = e.dataTransfer;
        let files = dt.files;
        handleFiles(files);
    }

    function handleFiles(files) {
        if(files.length === 0) return;

        progressOverlay.classList.remove('hidden');
        let formData = new FormData();
        // Just upload the first file for simplicity in this demo, could loop for multi
        formData.append('file', files[0]);

        let xhr = new XMLHttpRequest();
        xhr.open('POST', '<?= APP_URL ?>/api/upload.php', true);

        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                let percentComplete = (e.loaded / e.total) * 100;
                progressBar.style.width = percentComplete + '%';
                progressText.innerText = Math.round(percentComplete) + '%';
            }
        };

        xhr.onload = function() {
            if (xhr.status === 200) {
                window.location.reload(); // Reload to show new file in grid
            } else {
                alert('Upload failed. Check file size/type constraints.');
                progressOverlay.classList.add('hidden');
            }
        };

        xhr.send(formData);
    }
</script>