<?php
/**
 * Configuration File
 * ملف الإعدادات
 */

// Prevent multiple constant definitions
if (!defined('BASE_URL')) {
    // Simple approach: detect if we're in a subfolder
    $scriptName = $_SERVER['SCRIPT_NAME']; // e.g., /online-bookstore/index.php
    $scriptDir = str_replace('\\', '/', dirname($scriptName)); // e.g., /online-bookstore
    
    // For root installations, dirname returns '/'
    // For subfolder installations like XAMPP, it returns '/online-bookstore'
    if ($scriptDir === '/' || $scriptDir === '.') {
        $scriptDir = '';
    }
    
    define('BASE_URL', $scriptDir);
}

// Helper function to generate URLs
if (!function_exists('url')) {
    function url($path = '') {
        if (empty($path)) {
            return BASE_URL ? BASE_URL : '/';
        }
        $path = ltrim($path, '/');
        return BASE_URL . '/' . $path;
    }
}

// Helper function for asset URLs  
if (!function_exists('asset')) {
    function asset($path) {
        $path = ltrim($path, '/');
        return BASE_URL . '/assets/' . $path;
    }
}
?>

