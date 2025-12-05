<?php
/**
 * Configuration File
 * ملف الإعدادات
 */

// Base URL for the application
// For XAMPP: use '/online-bookstore'
// For production or root installation: use ''
define('BASE_URL', '/online-bookstore');

// Helper function to generate URLs
function url($path = '') {
    $path = ltrim($path, '/');
    return BASE_URL . ($path ? '/' . $path : '');
}

// Helper function for asset URLs
function asset($path) {
    return url('assets/' . ltrim($path, '/'));
}
?>
