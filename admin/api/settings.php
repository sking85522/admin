<?php
// admin/api/settings.php

require_once '../config.php';
require_once '../core/app.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $db = new JsonDB(CONTENT_PATH . '/settings.json');
    $settings = $db->getAll();

    $formatted = [];
    foreach ($settings as $setting) {
        $formatted[$setting['key']] = $setting['value'];
    }

    jsonResponse(['success' => true, 'data' => $formatted]);
} else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}
