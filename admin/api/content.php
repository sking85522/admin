<?php
// admin/api/content.php

require_once '../config.php';
require_once '../core/app.php';

header('Content-Type: application/json');

// Simplified API: requires you to be logged in to admin panel to access, or pass a mock token
// For public access (like a frontend), you'd remove Auth::check() requirement or check for a specific API key
// For this portable admin setup, we assume it's for external fetch if needed, but we'll leave it public for reading.

$type = $_GET['type'] ?? 'posts';

if (!in_array($type, ['posts', 'pages'])) {
    jsonResponse(['error' => 'Invalid content type'], 400);
}

$db = new JsonDB(CONTENT_PATH . '/' . $type . '.json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'] ?? null;

    if ($id) {
        $item = $db->getById($id);
        if ($item) {
            jsonResponse(['success' => true, 'data' => $item]);
        } else {
            jsonResponse(['error' => 'Not found'], 404);
        }
    } else {
        // Return only published items for public API, or all if admin
        $all = $db->getAll();
        if (!Auth::check()) {
            $all = array_filter($all, function($item) {
                return isset($item['status']) && $item['status'] === 'published';
            });
            $all = array_values($all);
        }
        jsonResponse(['success' => true, 'data' => $all]);
    }
} else {
     jsonResponse(['error' => 'Method not allowed'], 405);
}
