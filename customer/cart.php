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

$pageTitle = 'سلة التسوق';
require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>🛒 سلة التسوق</h1>
</div>

<?php if (empty($cartItems)): ?>
    <div class="empty-state">
        <div class="empty-state-icon">🛒</div>
        <h3>سلة التسوق فارغة</h3>
        <p>لم تضف أي كتب إلى السلة بعد</p>
        <a href="/books.php" class="btn btn-primary">تصفح الكتب</a>
    </div>
<?php else: ?>
    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px;">
        <!-- Cart Items -->
        <div class="card">
            <div class="card-header">
                <h3>الكتب في السلة (<?php echo count($cartItems); ?>)</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item" data-item-id="<?php echo $item['id']; ?>">
                        <div class="cart-item-image">📕</div>
                        <div class="cart-item-details">
                            <h4 class="cart-item-title"><?php echo htmlspecialchars($item['title']); ?></h4>
                            <p style="color: var(--text-light); font-size: 0.9rem;">
                                <?php echo htmlspecialchars($item['authors']); ?>
                            </p>
                            <p class="cart-item-price"><?php echo number_format($item['price'], 2); ?> ريال</p>
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
                            <strong><?php echo number_format($item['price'] * $item['qty'], 2); ?> ريال</strong>
                            <br>
                            <button onclick="removeFromCart(<?php echo $item['id']; ?>)" class="btn btn-danger btn-sm" style="margin-top: 10px;">
                                🗑️ حذف
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Cart Summary -->
        <div>
            <div class="cart-summary">
                <h3>ملخص الطلب</h3>
                
                <div class="cart-summary-row">
                    <span>عدد الكتب:</span>
                    <span><?php echo count($cartItems); ?></span>
                </div>
                
                <div class="cart-summary-row">
                    <span>المجموع الفرعي:</span>
                    <span><?php echo number_format($total, 2); ?> ريال</span>
                </div>
                
                <div class="cart-summary-row">
                    <span>الشحن:</span>
                    <span style="color: var(--success-color);">مجاني</span>
                </div>
                
                <div class="cart-summary-row cart-summary-total">
                    <span>الإجمالي:</span>
                    <span><?php echo number_format($total, 2); ?> ريال سعودي</span>
                </div>
                
                <a href="/customer/checkout.php" class="btn btn-primary btn-block btn-lg" style="margin-top: 20px;">
                    إتمام الشراء ←
                </a>
                
                <a href="/books.php" class="btn btn-secondary btn-block" style="margin-top: 10px;">
                    متابعة التسوق
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
