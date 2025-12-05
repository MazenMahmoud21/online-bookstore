<?php
/**
 * Update Cart Handler
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn() || isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit;
}

$itemId = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
$quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

if ($itemId <= 0) {
    echo json_encode(['success' => false, 'message' => 'بيانات غير صالحة']);
    exit;
}

$customerId = getCurrentUserId();

// Verify item belongs to customer's cart
$item = dbQuerySingle(
    "SELECT ci.id, ci.book_isbn, b.stock 
     FROM cart_items ci 
     JOIN shopping_cart sc ON ci.cart_id = sc.id 
     JOIN books b ON ci.book_isbn = b.isbn
     WHERE ci.id = ? AND sc.customer_id = ?",
    [$itemId, $customerId]
);

if (!$item) {
    echo json_encode(['success' => false, 'message' => 'العنصر غير موجود']);
    exit;
}

if ($quantity > $item['stock']) {
    $quantity = $item['stock'];
}

dbExecute("UPDATE cart_items SET qty = ? WHERE id = ?", [$quantity, $itemId]);

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
