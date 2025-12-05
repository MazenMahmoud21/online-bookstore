<?php
/**
 * Remove from Cart Handler
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn() || isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit;
}

$itemId = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;

if ($itemId <= 0) {
    echo json_encode(['success' => false, 'message' => 'بيانات غير صالحة']);
    exit;
}

$customerId = getCurrentUserId();

// Verify item belongs to customer's cart
$item = dbQuerySingle(
    "SELECT ci.id 
     FROM cart_items ci 
     JOIN shopping_cart sc ON ci.cart_id = sc.id 
     WHERE ci.id = ? AND sc.customer_id = ?",
    [$itemId, $customerId]
);

if (!$item) {
    echo json_encode(['success' => false, 'message' => 'العنصر غير موجود']);
    exit;
}

dbExecute("DELETE FROM cart_items WHERE id = ?", [$itemId]);

// Get updated totals
$cart = dbQuerySingle("SELECT id FROM shopping_cart WHERE customer_id = ?", [$customerId]);

$totals = dbQuerySingle(
    "SELECT COALESCE(SUM(ci.qty), 0) as count, COALESCE(SUM(ci.qty * b.price), 0) as total 
     FROM cart_items ci 
     JOIN books b ON ci.book_isbn = b.isbn 
     WHERE ci.cart_id = ?",
    [$cart['id']]
);

echo json_encode([
    'success' => true,
    'cartCount' => $totals['count'],
    'total' => number_format($totals['total'], 2)
]);
