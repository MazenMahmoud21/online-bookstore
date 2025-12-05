<?php
/**
 * Checkout Page - صفحة إتمام الشراء
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();
if (isAdmin()) {
    header('Location: ' . url('admin/dashboard.php'));
    exit;
}

$customerId = getCurrentUserId();
$error = '';
$success = '';

// Get cart
$cart = dbQuerySingle("SELECT id FROM shopping_cart WHERE customer_id = ?", [$customerId]);

if (!$cart) {
    header('Location: ' . url('customer/cart.php'));
    exit;
}

// Get cart items
$cartItems = dbQuery(
    "SELECT ci.*, b.title, b.authors, b.price, b.stock 
     FROM cart_items ci 
     JOIN books b ON ci.book_isbn = b.isbn 
     WHERE ci.cart_id = ?",
    [$cart['id']]
);

if (empty($cartItems)) {
    header('Location: ' . url('customer/cart.php'));
    exit;
}

$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['qty'];
}

// Get customer info
$customer = dbQuerySingle("SELECT * FROM customers WHERE id = ?", [$customerId]);

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cardNumber = sanitize($_POST['card_number'] ?? '');
    $expiry = sanitize($_POST['expiry'] ?? '');
    $cvv = sanitize($_POST['cvv'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    
    // Validate credit card
    $validation = validateCreditCard($cardNumber, $expiry, $cvv);
    
    if (!$validation['valid']) {
        $error = $validation['message'];
    } elseif (empty($address)) {
        $error = 'الرجاء إدخال عنوان التوصيل';
    } else {
        try {
            $pdo = getDBConnection();
            $pdo->beginTransaction();
            
            // Verify stock availability
            $stockError = false;
            foreach ($cartItems as $item) {
                $book = dbQuerySingle("SELECT stock FROM books WHERE isbn = ?", [$item['book_isbn']]);
                if ($book['stock'] < $item['qty']) {
                    $stockError = true;
                    $error = "الكتاب '{$item['title']}' غير متوفر بالكمية المطلوبة";
                    break;
                }
            }
            
            if (!$stockError) {
                // Create sale
                dbExecute(
                    "INSERT INTO sales (customer_id, total_amount) VALUES (?, ?)",
                    [$customerId, $total]
                );
                $saleId = dbLastInsertId();
                
                // Add sale items and update stock
                foreach ($cartItems as $item) {
                    dbExecute(
                        "INSERT INTO sales_items (sale_id, book_isbn, qty, price) VALUES (?, ?, ?, ?)",
                        [$saleId, $item['book_isbn'], $item['qty'], $item['price']]
                    );
                    
                    dbExecute(
                        "UPDATE books SET stock = stock - ? WHERE isbn = ?",
                        [$item['qty'], $item['book_isbn']]
                    );
                }
                
                // Update customer address if changed
                if ($address !== $customer['address']) {
                    dbExecute(
                        "UPDATE customers SET address = ? WHERE id = ?",
                        [$address, $customerId]
                    );
                }
                
                // Clear cart
                dbExecute("DELETE FROM cart_items WHERE cart_id = ?", [$cart['id']]);
                
                $pdo->commit();
                
                // Redirect to success page
                $_SESSION['order_success'] = $saleId;
                header('Location: ' . url('customer/order_success.php'));
                exit;
            } else {
                $pdo->rollBack();
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'حدث خطأ أثناء معالجة الطلب. الرجاء المحاولة مرة أخرى.';
        }
    }
}

$pageTitle = 'إتمام الشراء';
require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>💳 إتمام الشراء</h1>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST" action="" id="checkoutForm">
    <div style="display: grid; grid-template-columns: 1fr 400px; gap: 30px;">
        <!-- Checkout Form -->
        <div>
            <!-- Shipping Address -->
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-header">
                    <h3>📍 عنوان التوصيل</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="address">العنوان الكامل *</label>
                        <textarea id="address" name="address" class="form-control" required
                                  placeholder="المدينة، الحي، الشارع، رقم المبنى"><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>رقم الجوال</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($customer['phone']); ?>" readonly>
                    </div>
                </div>
            </div>
            
            <!-- Payment -->
            <div class="card">
                <div class="card-header">
                    <h3>💳 بيانات الدفع</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="card_number">رقم البطاقة *</label>
                        <input type="text" id="card_number" name="card_number" class="form-control" 
                               placeholder="1234 5678 9012 3456" maxlength="19" required>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label for="expiry">تاريخ الانتهاء *</label>
                            <input type="text" id="expiry" name="expiry" class="form-control" 
                                   placeholder="MM/YY" maxlength="5" required>
                        </div>
                        <div class="form-group">
                            <label for="cvv">CVV *</label>
                            <input type="text" id="cvv" name="cvv" class="form-control" 
                                   placeholder="123" maxlength="4" required>
                        </div>
                    </div>
                    
                    <div class="alert alert-info" style="margin-bottom: 0;">
                        🔒 بياناتك مؤمنة ومشفرة
                        <!-- Demo/Educational test card - Remove in production -->
                        <br><small>للاختبار استخدم: 4532015112830366 | 12/25 | 123</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div>
            <div class="cart-summary">
                <h3>ملخص الطلب</h3>
                
                <div style="max-height: 300px; overflow-y: auto; margin-bottom: 20px;">
                    <?php foreach ($cartItems as $item): ?>
                        <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                            <div>
                                <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                                <br><small>الكمية: <?php echo $item['qty']; ?></small>
                            </div>
                            <div><?php echo number_format($item['price'] * $item['qty'], 2); ?> ريال</div>
                        </div>
                    <?php endforeach; ?>
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
                
                <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 20px;">
                    ✓ تأكيد الطلب
                </button>
                
                <a href="/customer/cart.php" class="btn btn-secondary btn-block" style="margin-top: 10px;">
                    ← العودة للسلة
                </a>
            </div>
        </div>
    </div>
</form>

<?php require_once '../includes/footer.php'; ?>
