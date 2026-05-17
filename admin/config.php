<?php
// admin/config.php

define('APP_NAME', 'Admin Panel');
define('APP_VERSION', '1.0.0'); // Base version for Updater module

// Dynamically determine the base URL to make it truly portable
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'];
$directory = dirname($_SERVER['SCRIPT_NAME']);
// Remove trailing slash if script is in root directory
$directory = rtrim($directory, '/');
define('APP_URL', $protocol . $domainName . $directory);

define('BASE_PATH', __DIR__);

// Storage Paths
define('STORAGE_PATH', BASE_PATH . '/storage');
define('USERS_PATH', STORAGE_PATH . '/users');
define('CONTENT_PATH', STORAGE_PATH . '/content');
define('UPLOADS_PATH', STORAGE_PATH . '/uploads');
define('LOGS_PATH', STORAGE_PATH . '/logs');

// Module & Theme Paths
define('MODULES_PATH', BASE_PATH . '/modules');
define('THEMES_PATH', BASE_PATH . '/themes/default');

// JWT Secret (If used later, or general hash salt)
define('APP_SECRET', 'super_secret_key_change_this_in_production');

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('UTC');
