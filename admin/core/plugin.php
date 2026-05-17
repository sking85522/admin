<?php
// admin/core/plugin.php

class PluginManager {
    private static $pluginsPath = MODULES_PATH; // We treat plugins as dynamic modules

    public static function getAvailablePlugins() {
        $plugins = [];
        $dirs = array_diff(scandir(self::$pluginsPath), ['.', '..']);

        foreach ($dirs as $dir) {
            $path = self::$pluginsPath . '/' . $dir;
            if (is_dir($path) && file_exists($path . '/config.json')) {
                $config = json_decode(file_get_contents($path . '/config.json'), true);
                $config['slug'] = $dir;
                $plugins[] = $config;
            }
        }
        return $plugins;
    }

    public static function installFromZip($zipFile) {
        $zip = new ZipArchive;
        $res = $zip->open($zipFile);
        if ($res === TRUE) {
            // Basic security check: ensure it has a config.json at the root of the zip
            if ($zip->locateName('config.json') === false && $zip->locateName(trim($zip->getNameIndex(0), '/') . '/config.json') === false) {
                 $zip->close();
                 return ['success' => false, 'error' => 'Invalid plugin package. Missing config.json.'];
            }

            $extractPath = self::$pluginsPath . '/temp_' . uniqid();
            $zip->extractTo($extractPath);
            $zip->close();

            // Find the actual module folder inside the extracted temp dir (in case zip has a root folder)
            $actualPluginDir = $extractPath;
            $items = array_diff(scandir($extractPath), ['.', '..']);
            if (count($items) === 1 && is_dir($extractPath . '/' . reset($items))) {
                $actualPluginDir = $extractPath . '/' . reset($items);
            }

            $config = json_decode(file_get_contents($actualPluginDir . '/config.json'), true);
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $config['name'] ?? 'unknown'));

            $finalPath = self::$pluginsPath . '/' . $slug;

            if (file_exists($finalPath)) {
                self::deleteDir($extractPath);
                return ['success' => false, 'error' => 'Plugin already exists.'];
            }

            rename($actualPluginDir, $finalPath);

            // Cleanup temp
            if (is_dir($extractPath)) {
                self::deleteDir($extractPath);
            }

            Logger::log("Plugin installed: $slug", 'SYSTEM');
            return ['success' => true, 'slug' => $slug];
        }
        return ['success' => false, 'error' => 'Failed to open zip file.'];
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
