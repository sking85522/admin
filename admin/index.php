<?php
// admin/index.php

require_once 'config.php';
require_once 'core/app.php';

App::init();

// Protect all routes except explicitly public ones (like login, if routed through here)
Auth::requireLogin();

// Parse URI to determine module and action
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = parse_url(APP_URL, PHP_URL_PATH);

if (strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

$requestUri = trim($requestUri, '/');
$parts = explode('/', $requestUri);

$module = !empty($parts[0]) ? $parts[0] : 'dashboard';
$action = isset($parts[1]) ? $parts[1] : 'index';
$id = isset($parts[2]) ? $parts[2] : null;

// Allow alphanumeric, dash, and underscore for module and action names
if (!preg_match('/^[a-zA-Z0-9_-]+$/', $module) || !preg_match('/^[a-zA-Z0-9_-]+$/', $action)) {
    die("Invalid request.");
}

$modulePath = MODULES_PATH . '/' . $module;
$actionFile = $modulePath . '/' . $action . '.php';

if (is_dir($modulePath)) {
    if (file_exists($actionFile)) {
        // Module file found, load it
        // We include header and footer around the module content
        require_once THEMES_PATH . '/header.php';
        require_once $actionFile;
        require_once THEMES_PATH . '/footer.php';
    } else {
        // Action not found within module
        http_response_code(404);
        require_once THEMES_PATH . '/header.php';
        echo "<div class='bg-white p-6 rounded shadow'><h2>404 - Action '$action' not found in module '$module'</h2></div>";
        require_once THEMES_PATH . '/footer.php';
    }
} else {
    // Module not found
    http_response_code(404);
    require_once THEMES_PATH . '/header.php';
    echo "<div class='bg-white p-6 rounded shadow'><h2>404 - Module '$module' not found</h2></div>";
    require_once THEMES_PATH . '/footer.php';
}