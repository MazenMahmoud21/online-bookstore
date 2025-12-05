<?php
/**
 * Add to Cart Handler
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn() || isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit;
}

$bookIsbn = isset($_POST['book_isbn']) ? sanitize($_POST['book_isbn']) : '';
$quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

if (empty($bookIsbn)) {
    echo json_encode(['success' => false, 'message' => 'بيانات غير صالحة']);
    exit;
}

// Check if book exists and has stock
$book = dbQuerySingle("SELECT isbn, stock FROM books WHERE isbn = ?", [$bookIsbn]);

if (!$book) {
    echo json_encode(['success' => false, 'message' => 'الكتاب غير موجود']);
    exit;
}

if ($book['stock'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'الكمية المطلوبة غير متوفرة']);
    exit;
}

$customerId = getCurrentUserId();

// Get or create cart
$cart = dbQuerySingle("SELECT id FROM shopping_cart WHERE customer_id = ?", [$customerId]);

if (!$cart) {
    dbExecute("INSERT INTO shopping_cart (customer_id) VALUES (?)", [$customerId]);
    $cartId = dbLastInsertId();
} else {
    $cartId = $cart['id'];
}

// Check if item already in cart
$existingItem = dbQuerySingle(
    "SELECT id, qty FROM cart_items WHERE cart_id = ? AND book_isbn = ?",
    [$cartId, $bookIsbn]
);

if ($existingItem) {
    $newQty = $existingItem['qty'] + $quantity;
    if ($newQty > $book['stock']) {
        $newQty = $book['stock'];
    }
    dbExecute(
        "UPDATE cart_items SET qty = ? WHERE id = ?",
        [$newQty, $existingItem['id']]
    );
} else {
    dbExecute(
        "INSERT INTO cart_items (cart_id, book_isbn, qty) VALUES (?, ?, ?)",
        [$cartId, $bookIsbn, $quantity]
    );
}

// Get updated cart count
$cartCount = dbQuerySingle(
    "SELECT COALESCE(SUM(qty), 0) as count FROM cart_items WHERE cart_id = ?",
    [$cartId]
)['count'];

echo json_encode([
    'success' => true,
    'message' => 'تمت الإضافة إلى السلة',
    'cartCount' => $cartCount
]);
