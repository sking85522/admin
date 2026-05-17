<?php
// admin/modules/settings/index.php

$db = new JsonDB(CONTENT_PATH . '/settings.json');
$settings = $db->getAll();

// Convert to key-value array for easier form handling
$settingsData = [];
foreach ($settings as $setting) {
    $settingsData[$setting['key']] = $setting['value'];
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifyToken($_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    $keysToUpdate = ['site_name', 'site_description', 'maintenance_mode', 'custom_css', 'custom_js', 'global_keywords', 'language'];

    if (empty($_POST['site_name'])) {
        $errors['site_name'][] = "Site Name is required.";
    }

    if (empty($errors)) {
        foreach ($keysToUpdate as $key) {
            $value = $_POST[$key] ?? '';
            if ($key === 'maintenance_mode') $value = isset($_POST['maintenance_mode']) ? 'true' : 'false';

            $found = false;
            foreach ($settings as $setting) {
                if ($setting['key'] === $key) {
                    $db->update($setting['id'], ['value' => $value]);
                    $found = true;
                    break;
                }
            }
            if (!$found) $db->insert(['key' => $key, 'value' => $value]);
        }

        Session::setFlash('success', 'Settings updated successfully.');
        Logger::log("Global settings updated by user.", 'SYSTEM');
        redirect(APP_URL . '/settings');
    } else {
        foreach ($keysToUpdate as $key) {
             if (isset($_POST[$key])) $settingsData[$key] = $_POST[$key];
        }
    }
}
?>

<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Global Settings</h1>
</div>

<form action="" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <?= Csrf::getTokenField() ?>

    <div class="lg:col-span-2 space-y-6">
        <!-- General Settings -->
        <div class="bg-white rounded-lg shadow overflow-hidden p-6">
            <h2 class="text-lg font-bold mb-4 border-b pb-2">General</h2>
            <div class="space-y-4">
                <div>
                    <label for="site_name" class="block text-sm font-medium text-gray-700">Site Name</label>
                    <input type="text" name="site_name" id="site_name" value="<?= h($settingsData['site_name'] ?? '') ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                    <?php if (isset($errors['site_name'])): ?>
                        <p class="mt-2 text-sm text-red-600"><?= h($errors['site_name'][0]) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="site_description" class="block text-sm font-medium text-gray-700">Site Description</label>
                    <textarea id="site_description" name="site_description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"><?= h($settingsData['site_description'] ?? '') ?></textarea>
                </div>

                <div>
                    <label for="global_keywords" class="block text-sm font-medium text-gray-700">Global SEO Keywords</label>
                    <input type="text" name="global_keywords" id="global_keywords" value="<?= h($settingsData['global_keywords'] ?? '') ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm" placeholder="e.g., blog, tech, news">
                </div>

                <div>
                    <label for="language" class="block text-sm font-medium text-gray-700">System Language</label>
                    <select name="language" id="language" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md">
                        <option value="en" <?= ($settingsData['language'] ?? 'en') === 'en' ? 'selected' : '' ?>>English</option>
                        <option value="es" <?= ($settingsData['language'] ?? 'en') === 'es' ? 'selected' : '' ?>>Spanish</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Code Injection -->
        <div class="bg-white rounded-lg shadow overflow-hidden p-6">
            <h2 class="text-lg font-bold mb-4 border-b pb-2">Code Injection</h2>
            <div class="space-y-4">
                <div>
                    <label for="custom_css" class="block text-sm font-medium text-gray-700">Custom CSS (In &lt;head&gt;)</label>
                    <textarea id="custom_css" name="custom_css" rows="5" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 font-mono text-xs focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"><?= h($settingsData['custom_css'] ?? '') ?></textarea>
                </div>
                <div>
                    <label for="custom_js" class="block text-sm font-medium text-gray-700">Custom JS (Before &lt;/body&gt;)</label>
                    <textarea id="custom_js" name="custom_js" rows="5" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 font-mono text-xs focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"><?= h($settingsData['custom_js'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <!-- System Status -->
        <div class="bg-white rounded-lg shadow overflow-hidden p-6 border-t-4 border-red-500">
            <h2 class="text-lg font-bold mb-4 border-b pb-2">System Status</h2>

            <label class="flex items-center p-3 border rounded <?= (isset($settingsData['maintenance_mode']) && $settingsData['maintenance_mode'] === 'true') ? 'bg-red-50 border-red-200' : 'bg-gray-50' ?>">
                <input type="checkbox" name="maintenance_mode" value="true" class="h-5 w-5 text-red-600 rounded" <?= (isset($settingsData['maintenance_mode']) && $settingsData['maintenance_mode'] === 'true') ? 'checked' : '' ?>>
                <div class="ml-3">
                    <span class="block text-sm font-bold text-gray-900">Maintenance Mode</span>
                    <span class="block text-xs text-gray-500">Take the public site offline.</span>
                </div>
            </label>
        </div>

        <div class="bg-white p-6 rounded shadow sticky top-6">
            <button type="submit" class="w-full bg-primary hover:bg-blue-600 text-white font-bold py-3 px-4 rounded transition-colors text-lg shadow-lg">
                <i class="fas fa-save mr-2"></i> Save All Settings
            </button>
        </div>
    </div>
</form>