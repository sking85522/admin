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

    $site_name = $_POST['site_name'] ?? '';
    $site_description = $_POST['site_description'] ?? '';

    if (empty($site_name)) {
        $errors['site_name'][] = "Site Name is required.";
    }

    if (empty($errors)) {
        // Update site_name
        $found = false;
        foreach ($settings as $setting) {
            if ($setting['key'] === 'site_name') {
                $db->update($setting['id'], ['value' => $site_name]);
                $found = true;
                break;
            }
        }
        if (!$found) $db->insert(['key' => 'site_name', 'value' => $site_name]);

        // Update site_description
        $found = false;
        foreach ($settings as $setting) {
            if ($setting['key'] === 'site_description') {
                $db->update($setting['id'], ['value' => $site_description]);
                $found = true;
                break;
            }
        }
        if (!$found) $db->insert(['key' => 'site_description', 'value' => $site_description]);

        Session::setFlash('success', 'Settings updated successfully.');
        redirect(APP_URL . '/settings');
    } else {
        $settingsData['site_name'] = $site_name;
        $settingsData['site_description'] = $site_description;
    }
}
?>

<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Settings</h1>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden p-6 max-w-2xl">
    <form action="" method="POST" class="space-y-6">
        <?= Csrf::getTokenField() ?>

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

        <div class="flex justify-end">
            <button type="submit" class="bg-primary hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition-colors">
                Save Settings
            </button>
        </div>
    </form>
</div>