<?php
/**
 * Shopping Cart Page - صفحة سلة التسوق
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();
if (isAdmin()) {
    header('Location: ' . url('admin/dashboard.php'));
    exit;
}

$customerId = getCurrentUserId();

// Get or create cart
$cart = dbQuerySingle(
    "SELECT id FROM shopping_cart WHERE customer_id = ?",
    [$customerId]
);

if (!$cart) {
    dbExecute("INSERT INTO shopping_cart (customer_id) VALUES (?)", [$customerId]);
    $cartId = dbLastInsertId();
} else {
    $cartId = $cart['id'];
}

// Get cart items
$cartItems = dbQuery(
    "SELECT ci.*, b.title, b.authors, b.price, b.stock 
     FROM cart_items ci 
     JOIN books b ON ci.book_isbn = b.isbn 
     WHERE ci.cart_id = ?",
    [$cartId]
);

$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['qty'];
}

$pageTitle = 'Shopping Cart';
require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>
        <!-- Feather SVG shopping-cart icon -->
        <span style="vertical-align: middle; margin-right: 8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#006c35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61l1.38-7.39H6"></path></svg>
        </span>
        Shopping Cart
    </h1>
</div>

<?php if (empty($cartItems)): ?>
    <div class="empty-state">
        <div class="empty-state-icon">
            <!-- Feather SVG shopping-cart icon -->
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#e0e0e0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61l1.38-7.39H6"></path></svg>
        </div>
        <h3>Your cart is empty</h3>
        <p>You have not added any books yet.</p>
        <a href="/books.php" class="btn btn-primary">Browse Books</a>
    </div>
<?php else: ?>
    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px;">
        <!-- Cart Items -->
        <div class="card">
            <div class="card-header">
                <h3>Books in Cart (<?php echo count($cartItems); ?>)</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item" data-item-id="<?php echo $item['id']; ?>">
                        <div class="cart-item-image">
                            <!-- Feather SVG book icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#006c35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 19a2 2 0 0 0 2 2h16"></path><path d="M2 5a2 2 0 0 1 2-2h16v16"></path><path d="M2 5v14"></path></svg>
                        </div>
                        <div class="cart-item-details">
                            <h4 class="cart-item-title"><?php echo htmlspecialchars($item['title']); ?></h4>
                            <p style="color: var(--text-light); font-size: 0.9rem;">
                                <?php echo htmlspecialchars($item['authors']); ?>
                            </p>
                            <p class="cart-item-price">EGP <?php echo number_format($item['price'], 2); ?></p>
                        </div>
                        <div class="cart-item-quantity">
                            <form method="POST" action="<?php echo url('customer/update_cart.php'); ?>" style="display: flex; align-items: center; gap: 10px;">
                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                <button type="button" onclick="changeQuantity(<?php echo $item['id']; ?>, -1)" class="btn btn-sm btn-secondary">-</button>
                                <input type="number" name="quantity" value="<?php echo $item['qty']; ?>" 
                                       min="1" max="<?php echo $item['stock']; ?>" 
                                       id="qty-<?php echo $item['id']; ?>"
                                       class="form-control" style="width: 60px; text-align: center;">
                                <button type="button" onclick="changeQuantity(<?php echo $item['id']; ?>, 1)" class="btn btn-sm btn-secondary">+</button>
                            </form>
                        </div>
                        <div style="text-align: left;">
                            <strong>EGP <?php echo number_format($item['price'] * $item['qty'], 2); ?></strong>
                            <br>
                            <button onclick="removeFromCart(<?php echo $item['id']; ?>)" class="btn btn-danger btn-sm" style="margin-top: 10px;">
                                <!-- Feather SVG trash icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m5 0V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                Remove
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Cart Summary -->
        <div>
            <div class="cart-summary">
                <h3>Order Summary</h3>
                <div class="cart-summary-row">
                    <span>Books Count:</span>
                    <span><?php echo count($cartItems); ?></span>
                </div>
                <div class="cart-summary-row">
                    <span>Subtotal:</span>
                    <span>EGP <?php echo number_format($total, 2); ?></span>
                </div>
                <div class="cart-summary-row">
                    <span>Shipping:</span>
                    <span style="color: var(--success-color);">Free</span>
                </div>
                <div class="cart-summary-row cart-summary-total">
                    <span>Total:</span>
                    <span>EGP <?php echo number_format($total, 2); ?></span>
                </div>
                <a href="/customer/checkout.php" class="btn btn-primary btn-block btn-lg" style="margin-top: 20px;">
                    Proceed to Checkout →
                </a>
                <a href="/books.php" class="btn btn-secondary btn-block" style="margin-top: 10px;">
                    Continue Shopping
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
function changeQuantity(itemId, delta) {
    const input = document.getElementById('qty-' + itemId);
    let newQty = parseInt(input.value) + delta;
    const max = parseInt(input.max);
    
    if (newQty < 1) newQty = 1;
    if (newQty > max) newQty = max;
    
    input.value = newQty;
    updateCartQuantity(itemId, newQty);
}
</script>

<?php require_once '../includes/footer.php'; ?>
