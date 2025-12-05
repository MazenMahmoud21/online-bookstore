<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Path Debug</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; border: 1px solid #ddd; }
        .success { color: green; }
        .error { color: red; }
        h2 { color: #333; }
        code { background: #eee; padding: 2px 6px; border-radius: 3px; }
        .test-link { display: inline-block; margin: 10px 0; padding: 10px 15px; background: #4361ee; color: white; text-decoration: none; border-radius: 5px; }
        .test-link:hover { background: #3a51d4; }
    </style>
</head>
<body>
    <h1>🔍 Path Configuration Debug</h1>
    
    <div class="box">
        <h2>Server Information</h2>
        <p><strong>HTTP_HOST:</strong> <?php echo $_SERVER['HTTP_HOST']; ?></p>
        <p><strong>SCRIPT_NAME:</strong> <?php echo $_SERVER['SCRIPT_NAME']; ?></p>
        <p><strong>DOCUMENT_ROOT:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>
        <p><strong>PHP_SELF:</strong> <?php echo $_SERVER['PHP_SELF']; ?></p>
    </div>
    
    <div class="box">
        <h2>Configuration</h2>
        <p><strong>BASE_URL:</strong> <code><?php echo BASE_URL ? BASE_URL : '(empty - root installation)'; ?></code></p>
    </div>
    
    <div class="box">
        <h2>Generated URLs</h2>
        <p><strong>url('index.php'):</strong> <code><?php echo url('index.php'); ?></code></p>
        <p><strong>url('assets/css/style.css'):</strong> <code><?php echo url('assets/css/style.css'); ?></code></p>
        <p><strong>asset('css/style.css'):</strong> <code><?php echo asset('css/style.css'); ?></code></p>
        <p><strong>asset('js/main.js'):</strong> <code><?php echo asset('js/main.js'); ?></code></p>
    </div>
    
    <div class="box">
        <h2>File System Check</h2>
        <?php
        $cssPath = __DIR__ . '/assets/css/style.css';
        $jsPath = __DIR__ . '/assets/js/main.js';
        $cssExists = file_exists($cssPath);
        $jsExists = file_exists($jsPath);
        ?>
        <p><strong>CSS File:</strong> 
            <span class="<?php echo $cssExists ? 'success' : 'error'; ?>">
                <?php echo $cssExists ? '✓ EXISTS' : '✗ NOT FOUND'; ?>
            </span>
            <?php if ($cssExists) echo ' (' . number_format(filesize($cssPath)) . ' bytes)'; ?>
        </p>
        <p><strong>JS File:</strong> 
            <span class="<?php echo $jsExists ? 'success' : 'error'; ?>">
                <?php echo $jsExists ? '✓ EXISTS' : '✗ NOT FOUND'; ?>
            </span>
            <?php if ($jsExists) echo ' (' . number_format(filesize($jsPath)) . ' bytes)'; ?>
        </p>
    </div>
    
    <div class="box">
        <h2>Test Links</h2>
        <p>Click these links to test if they work:</p>
        <a href="<?php echo asset('css/style.css'); ?>" target="_blank" class="test-link">📄 Open CSS File</a>
        <a href="<?php echo asset('js/main.js'); ?>" target="_blank" class="test-link">📄 Open JS File</a>
        <a href="<?php echo url('index.php'); ?>" class="test-link">🏠 Go to Homepage</a>
    </div>
    
    <div class="box">
        <h2>Expected CSS Link in HTML</h2>
        <code>&lt;link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>"&gt;</code>
    </div>
</body>
</html>
