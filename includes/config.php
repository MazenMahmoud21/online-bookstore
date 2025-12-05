<?php
/**
 * Configuration File
 * ملف الإعدادات
 */

// Prevent multiple constant definitions
if (!defined('BASE_URL')) {
    // Auto-detect base URL
    // This will work whether you're in root, XAMPP, or any subfolder
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $scriptDir = dirname($scriptName);

    // If we're in a subfolder (like /online-bookstore), extract it
    if ($scriptDir !== '/' && strpos($scriptDir, '/') === 0) {
        // Get the first directory after root
        $parts = explode('/', trim($scriptDir, '/'));
        $baseFolder = '/' . $parts[0];
    } else {
        $baseFolder = '';
    }

    // You can also manually set this:
    // For XAMPP in htdocs/online-bookstore: define('BASE_URL', '/online-bookstore');
    // For production (root domain): define('BASE_URL', '');
    define('BASE_URL', $baseFolder);
}

// Helper function to generate URLs
if (!function_exists('url')) {
    function url($path = '') {
        $path = ltrim($path, '/');
        return BASE_URL . ($path ? '/' . $path : '');
    }
}

// Helper function for asset URLs
if (!function_exists('asset')) {
    function asset($path) {
        return url('assets/' . ltrim($path, '/'));
    }
}
?>

