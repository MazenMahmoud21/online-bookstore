<?php
require_once 'includes/config.php';

echo "<h2>Path Testing</h2>";
echo "<p><strong>BASE_URL:</strong> " . BASE_URL . "</p>";
echo "<p><strong>url('index.php'):</strong> " . url('index.php') . "</p>";
echo "<p><strong>url('assets/css/style.css'):</strong> " . url('assets/css/style.css') . "</p>";
echo "<p><strong>asset('css/style.css'):</strong> " . asset('css/style.css') . "</p>";
echo "<p><strong>SCRIPT_NAME:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

// Test if file exists
$cssPath = $_SERVER['DOCUMENT_ROOT'] . BASE_URL . '/assets/css/style.css';
echo "<p><strong>CSS File Path:</strong> " . $cssPath . "</p>";
echo "<p><strong>CSS File Exists:</strong> " . (file_exists($cssPath) ? 'YES' : 'NO') . "</p>";

// Show actual link
echo "<hr>";
echo "<p>CSS Link Tag: <code>&lt;link rel='stylesheet' href='" . asset('css/style.css') . "'&gt;</code></p>";
echo "<p><a href='" . asset('css/style.css') . "' target='_blank'>Click to test CSS link</a></p>";
?>
