<?php
// admin/core/updater.php

class Updater {
    // For demonstration, this points to a mock URL. In reality, it points to your server's releases JSON.
    private static $updateUrl = 'https://raw.githubusercontent.com/sking85522/admin/main/update_manifest.json';

    public static function check() {
        // Suppress warnings in case of network issues
        $json = @file_get_contents(self::$updateUrl);
        if (!$json) {
            return ['success' => false, 'error' => 'Could not connect to update server.'];
        }

        $data = json_decode($json, true);
        if (!$data || !isset($data['version'])) {
            return ['success' => false, 'error' => 'Invalid update manifest received.'];
        }

        $hasUpdate = version_compare($data['version'], APP_VERSION, '>');

        return [
            'success' => true,
            'has_update' => $hasUpdate,
            'latest_version' => $data['version'],
            'download_url' => $data['download_url'] ?? '',
            'changelog' => $data['changelog'] ?? 'No changelog provided.'
        ];
    }

    public static function apply($downloadUrl) {
        if (empty($downloadUrl)) return ['success' => false, 'error' => 'Invalid download URL.'];

        $tempZip = STORAGE_PATH . '/cache/update_' . time() . '.zip';
        $extractDir = STORAGE_PATH . '/cache/update_extract_' . time();

        if (!is_dir(STORAGE_PATH . '/cache')) mkdir(STORAGE_PATH . '/cache', 0755, true);

        // 1. Download the ZIP
        $zipData = @file_get_contents($downloadUrl);
        if (!$zipData) return ['success' => false, 'error' => 'Failed to download update file.'];
        file_put_contents($tempZip, $zipData);

        // 2. Extract the ZIP
        $zip = new ZipArchive;
        if ($zip->open($tempZip) === TRUE) {
            $zip->extractTo($extractDir);
            $zip->close();

            // Determine if the zip has a root wrapper folder (e.g. "admin-main/")
            $actualSource = $extractDir;
            $items = array_diff(scandir($extractDir), ['.', '..']);
            if (count($items) === 1 && is_dir($extractDir . '/' . reset($items))) {
                $actualSource = $extractDir . '/' . reset($items);
            }

            // 3. Copy files over, PROTECTING the storage directory
            self::recursiveCopy($actualSource, BASE_PATH, ['storage']);

            // 4. Cleanup
            unlink($tempZip);
            self::deleteDir($extractDir);

            Logger::log("System successfully updated from " . APP_VERSION, 'SYSTEM');
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Failed to extract update archive.'];
    }

    private static function recursiveCopy($src, $dst, $excludeDirs = []) {
        $dir = opendir($src);
        @mkdir($dst, 0755, true);
        while (( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if (is_dir($src . '/' . $file)) {
                    if (!in_array($file, $excludeDirs)) {
                        self::recursiveCopy($src . '/' . $file, $dst . '/' . $file, $excludeDirs);
                    }
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    private static function deleteDir($dirPath) {
        if (!is_dir($dirPath)) return;
        $objects = scandir($dirPath);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dirPath . DIRECTORY_SEPARATOR . $object) && !is_link($dirPath . "/" . $object))
                    self::deleteDir($dirPath . DIRECTORY_SEPARATOR . $object);
                else
                    unlink($dirPath . DIRECTORY_SEPARATOR . $object);
            }
        }
        rmdir($dirPath);
    }
}
