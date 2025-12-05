<?php
/**
 * Confirm Publisher Order - تأكيد طلب التوريد
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$orderId) {
    header('Location: ' . url('admin/view_orders.php'));
    exit;
}

// Verify order exists and is pending
$order = dbQuerySingle(
    "SELECT * FROM orders_from_publishers WHERE id = ? AND status = 'pending'",
    [$orderId]
);

if (!$order) {
    header('Location: ' . url('admin/view_orders.php'));
    exit;
}

// Use stored procedure to confirm order
try {
    callProcedure('confirm_publisher_order', [$orderId]);
    header('Location: /admin/view_orders.php?confirmed=1');
    exit;
} catch (PDOException $e) {
    header('Location: /admin/view_orders.php?error=1');
    exit;
}
