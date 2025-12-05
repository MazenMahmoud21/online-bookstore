<?php
/**
 * System Diagnostic File
 * ملف تشخيص النظام
 */

echo "<!DOCTYPE html>
<html lang='ar' dir='rtl'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>تشخيص النظام | System Diagnostics</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        .status { margin: 15px 0; padding: 15px; border-left: 4px solid #ddd; }
        .status.ok { border-left-color: #4CAF50; background: #f1f8f6; }
        .status.error { border-left-color: #f44336; background: #fef5f5; }
        .status.warning { border-left-color: #ff9800; background: #fff8f5; }
        .label { font-weight: bold; color: #333; }
        .value { color: #666; margin-top: 5px; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
        .icon { margin-right: 10px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 تشخيص النظام | System Diagnostics</h1>";

// PHP Version
echo "<div class='status ok'>
    <span class='icon'>✓</span>
    <span class='label'>PHP Version:</span>
    <div class='value'>" . phpversion() . "</div>
</div>";

// Check Extensions
$extensions = [
    'mysqli' => 'MySQLi (Recommended)',
    'pdo_mysql' => 'PDO MySQL',
    'pdo' => 'PDO',
    'json' => 'JSON',
    'session' => 'Session',
    'filter' => 'Filter',
];

foreach ($extensions as $ext => $name) {
    $loaded = extension_loaded($ext);
    $class = $loaded ? 'ok' : 'warning';
    $icon = $loaded ? '✓' : '⚠';
    echo "<div class='status $class'>
        <span class='icon'>$icon</span>
        <span class='label'>$name ($ext):</span>
        <div class='value'>" . ($loaded ? 'Loaded' : 'Not loaded') . "</div>
    </div>";
}

// Database Configuration
echo "<h2>قاعدة البيانات | Database Configuration</h2>";
echo "<div class='status'>
    <span class='label'>Host:</span>
    <div class='value'><code>" . (defined('DB_HOST') ? DB_HOST : 'Not defined') . "</code></div>
</div>";
echo "<div class='status'>
    <span class='label'>Database Name:</span>
    <div class='value'><code>" . (defined('DB_NAME') ? DB_NAME : 'Not defined') . "</code></div>
</div>";

// Try connection
echo "<h2>اتصال قاعدة البيانات | Database Connection Test</h2>";

if (extension_loaded('mysqli')) {
    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        echo "<div class='status error'>
            <span class='icon'>✗</span>
            <span class='label'>Connection Failed:</span>
            <div class='value'>" . htmlspecialchars($conn->connect_error) . "</div>
        </div>";
    } else {
        echo "<div class='status ok'>
            <span class='icon'>✓</span>
            <span class='label'>MySQLi Connection:</span>
            <div class='value'>Success! Connected to " . htmlspecialchars($conn->get_server_info()) . "</div>
        </div>";
        $conn->close();
    }
} elseif (extension_loaded('pdo_mysql')) {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        echo "<div class='status ok'>
            <span class='icon'>✓</span>
            <span class='label'>PDO MySQL Connection:</span>
            <div class='value'>Success!</div>
        </div>";
    } catch (PDOException $e) {
        echo "<div class='status error'>
            <span class='icon'>✗</span>
            <span class='label'>PDO Connection Failed:</span>
            <div class='value'>" . htmlspecialchars($e->getMessage()) . "</div>
        </div>";
    }
} else {
    echo "<div class='status error'>
        <span class='icon'>✗</span>
        <span class='label'>No Database Driver Available:</span>
        <div class='value'>Please install mysqli or PDO MySQL extension</div>
    </div>";
}

echo "    </div>
</body>
</html>";
?>
