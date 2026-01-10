<?php
// config.php

// Define a root path constant for includes if necessary.
// Many scripts might use __DIR__ directly, but a constant can be useful.
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__); // Assumes config.php is in the project root
}

// Site-wide settings
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'My Flat-File CMS');
}

// Debug mode - useful for development to show more errors or debug info
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', true); // Set to false for production
}

if (DEBUG_MODE) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0); // Consider E_ALL & ~E_NOTICE for production logs
}

// Default directories (can be overridden by passing args to class constructors)
// These are more for convention or if index.php needs them before class instantiation.
define('CONTENT_DIR_DEFAULT', ROOT_PATH . '/content/');
define('THEMES_DIR_DEFAULT', ROOT_PATH . '/themes/');
define('CACHE_DIR_DEFAULT', ROOT_PATH . '/cache/');
define('UPLOADS_DIR_DEFAULT', ROOT_PATH . '/uploads/');

// Other constants can be added here as needed.
// For example, default theme:
// define('DEFAULT_THEME', 'default');
?>
