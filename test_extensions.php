<?php
echo "PHP Version: " . phpversion() . "\n";
echo "=== Loaded Extensions ===\n";
echo "PDO: " . (extension_loaded('pdo') ? "✓ Loaded" : "✗ Not Loaded") . "\n";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? "✓ Loaded" : "✗ Not Loaded") . "\n";
echo "MySQLi: " . (extension_loaded('mysqli') ? "✓ Loaded" : "✗ Not Loaded") . "\n";
echo "\n=== All Loaded Extensions ===\n";
print_r(get_loaded_extensions());
?>
