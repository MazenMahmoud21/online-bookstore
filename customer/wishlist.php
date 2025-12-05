<?php
/**
 * Customer Wishlist Page
 * صفحة قائمة الأمنيات للعميل
 */

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Require login
requireLogin();

$pageTitle = 'قائمة الأمنيات';
$customerId = getCurrentUserId();
$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Validate CSRF
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'خطأ في التحقق. يرجى المحاولة مرة أخرى.';
        $messageType = 'error';
    } else {
        if ($action === 'remove') {
            $isbn = sanitizeInput($_POST['isbn'] ?? '');
            
            if (!empty($isbn)) {
                try {
                    dbExecute(
                        "DELETE FROM wishlists WHERE customer_id = ? AND book_isbn = ?",
                        [$customerId, $isbn]
                    );
                    $message = 'تم إزالة الكتاب من قائمة الأمنيات.';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'حدث خطأ أثناء إزالة الكتاب.';
                    $messageType = 'error';
                }
            }
        } elseif ($action === 'add_to_cart') {
            $isbn = sanitizeInput($_POST['isbn'] ?? '');
            
            if (!empty($isbn)) {
                // Check stock
                $book = dbQuerySingle("SELECT * FROM books WHERE isbn = ?", [$isbn]);
                
                if (!$book || $book['stock'] <= 0) {
                    $message = 'عذراً، الكتاب غير متوفر حالياً.';
                    $messageType = 'error';
                } else {
                    try {
                        // Get or create cart
                        $cart = dbQuerySingle(
                            "SELECT id FROM shopping_cart WHERE customer_id = ?",
                            [$customerId]
                        );
                        
                        if (!$cart) {
                            dbExecute(
                                "INSERT INTO shopping_cart (customer_id) VALUES (?)",
                                [$customerId]
                            );
                            $cartId = dbLastInsertId();
                        } else {
                            $cartId = $cart['id'];
                        }
                        
                        // Check if book already in cart
                        $existingItem = dbQuerySingle(
                            "SELECT id, qty FROM cart_items WHERE cart_id = ? AND book_isbn = ?",
                            [$cartId, $isbn]
                        );
                        
                        if ($existingItem) {
                            dbExecute(
                                "UPDATE cart_items SET qty = qty + 1 WHERE id = ?",
                                [$existingItem['id']]
                            );
                        } else {
                            dbExecute(
                                "INSERT INTO cart_items (cart_id, book_isbn, qty) VALUES (?, ?, 1)",
                                [$cartId, $isbn]
                            );
                        }
                        
                        // Remove from wishlist
                        dbExecute(
                            "DELETE FROM wishlists WHERE customer_id = ? AND book_isbn = ?",
                            [$customerId, $isbn]
                        );
                        
                        $message = 'تم إضافة الكتاب إلى سلة المشتريات.';
                        $messageType = 'success';
                    } catch (Exception $e) {
                        $message = 'حدث خطأ أثناء الإضافة إلى السلة.';
                        $messageType = 'error';
                    }
                }
            }
        } elseif ($action === 'clear_all') {
            try {
                dbExecute(
                    "DELETE FROM wishlists WHERE customer_id = ?",
                    [$customerId]
                );
                $message = 'تم مسح قائمة الأمنيات.';
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'حدث خطأ أثناء مسح القائمة.';
                $messageType = 'error';
            }
        }
    }
}

// Get wishlist items
$wishlistItems = [];
try {
    $wishlistItems = dbQuery(
        "SELECT w.*, b.title, b.authors, b.price, b.stock, b.image_url, b.average_rating, b.review_count,
                p.name as publisher_name
         FROM wishlists w
         JOIN books b ON w.book_isbn = b.isbn
         LEFT JOIN publishers p ON b.publisher_id = p.id
         WHERE w.customer_id = ?
         ORDER BY w.created_at DESC",
        [$customerId]
    );
} catch (Exception $e) {
    // Table might not exist yet
    $wishlistItems = [];
}

require_once '../includes/header.php';
?>

<main class="wishlist-page">
    <div class="container">
        <div class="page-header">
            <h1>❤️ قائمة الأمنيات</h1>
            <p>الكتب التي ترغب في شرائها لاحقاً</p>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($wishlistItems)): ?>
            <div class="empty-wishlist">
                <span class="empty-icon">💔</span>
                <h2>قائمة الأمنيات فارغة</h2>
                <p>لم تضف أي كتب إلى قائمة الأمنيات بعد.</p>
                <a href="<?php echo url('books.php'); ?>" class="btn btn-primary">تصفح الكتب</a>
            </div>
        <?php else: ?>
            <div class="wishlist-actions">
                <span class="item-count"><?php echo count($wishlistItems); ?> كتاب</span>
                <form method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من مسح جميع العناصر؟');">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="clear_all">
                    <button type="submit" class="btn btn-text">🗑️ مسح الكل</button>
                </form>
            </div>
            
            <div class="wishlist-grid">
                <?php foreach ($wishlistItems as $item): ?>
                    <div class="wishlist-card">
                        <div class="book-image">
                            <?php if ($item['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <?php else: ?>
                                <div class="no-image">📚</div>
                            <?php endif; ?>
                            <?php if ($item['stock'] <= 0): ?>
                                <span class="badge out-of-stock">غير متوفر</span>
                            <?php elseif ($item['stock'] <= 5): ?>
                                <span class="badge low-stock">كمية محدودة</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="book-details">
                            <h3>
                                <a href="<?php echo url('book.php?isbn=' . urlencode($item['book_isbn'])); ?>">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </a>
                            </h3>
                            <p class="author"><?php echo htmlspecialchars($item['authors']); ?></p>
                            
                            <?php if ($item['publisher_name']): ?>
                                <p class="publisher"><?php echo htmlspecialchars($item['publisher_name']); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($item['average_rating'] > 0): ?>
                                <div class="rating">
                                    <?php
                                    $rating = round($item['average_rating']);
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= $rating ? '⭐' : '☆';
                                    }
                                    ?>
                                    <span>(<?php echo $item['review_count']; ?>)</span>
                                </div>
                            <?php endif; ?>
                            
                            <p class="price"><?php echo number_format($item['price'], 2); ?> ر.س</p>
                            
                            <p class="added-date">
                                أضيف في: <?php echo date('d/m/Y', strtotime($item['created_at'])); ?>
                            </p>
                        </div>
                        
                        <div class="book-actions">
                            <?php if ($item['stock'] > 0): ?>
                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="action" value="add_to_cart">
                                    <input type="hidden" name="isbn" value="<?php echo htmlspecialchars($item['book_isbn']); ?>">
                                    <button type="submit" class="btn btn-primary">
                                        🛒 أضف للسلة
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-disabled" disabled>غير متوفر</button>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="isbn" value="<?php echo htmlspecialchars($item['book_isbn']); ?>">
                                <button type="submit" class="btn btn-remove" onclick="return confirm('إزالة من القائمة؟');">
                                    🗑️ إزالة
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
.wishlist-page {
    padding: 40px 20px;
    min-height: calc(100vh - 200px);
}

.page-header {
    text-align: center;
    margin-bottom: 40px;
}

.page-header h1 {
    color: #006c35;
    font-size: 2rem;
    margin-bottom: 10px;
}

.page-header p {
    color: #666;
}

.wishlist-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
}

.item-count {
    font-weight: bold;
    color: #333;
}

.btn-text {
    background: none;
    border: none;
    color: #e74c3c;
    cursor: pointer;
    font-size: 0.95rem;
}

.wishlist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
}

.wishlist-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: transform 0.3s, box-shadow 0.3s;
}

.wishlist-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

.book-image {
    position: relative;
    height: 200px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.book-image img {
    max-height: 100%;
    max-width: 100%;
    object-fit: contain;
}

.no-image {
    font-size: 4rem;
}

.badge {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 0.8rem;
    font-weight: bold;
}

.out-of-stock {
    background: #e74c3c;
    color: white;
}

.low-stock {
    background: #f39c12;
    color: white;
}

.book-details {
    padding: 20px;
}

.book-details h3 {
    margin: 0 0 10px;
    font-size: 1.1rem;
    line-height: 1.4;
}

.book-details h3 a {
    color: #333;
    text-decoration: none;
}

.book-details h3 a:hover {
    color: #006c35;
}

.author, .publisher {
    color: #666;
    font-size: 0.9rem;
    margin: 5px 0;
}

.rating {
    margin: 10px 0;
    font-size: 0.9rem;
}

.rating span {
    color: #666;
    margin-right: 5px;
}

.price {
    font-size: 1.3rem;
    font-weight: bold;
    color: #006c35;
    margin: 15px 0 5px;
}

.added-date {
    font-size: 0.8rem;
    color: #999;
}

.book-actions {
    padding: 15px 20px 20px;
    display: flex;
    gap: 10px;
}

.book-actions form {
    flex: 1;
}

.book-actions .btn {
    width: 100%;
    padding: 10px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: background 0.3s;
}

.btn-primary {
    background: linear-gradient(135deg, #006c35, #00a651);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #005a2b, #008841);
}

.btn-disabled {
    background: #ccc;
    color: #666;
    cursor: not-allowed;
}

.btn-remove {
    background: #f8f9fa;
    color: #e74c3c;
}

.btn-remove:hover {
    background: #fde8e8;
}

.empty-wishlist {
    text-align: center;
    padding: 60px 20px;
}

.empty-icon {
    font-size: 5rem;
    display: block;
    margin-bottom: 20px;
}

.empty-wishlist h2 {
    color: #333;
    margin-bottom: 15px;
}

.empty-wishlist p {
    color: #666;
    margin-bottom: 25px;
}

.alert {
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 25px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

@media (max-width: 576px) {
    .wishlist-grid {
        grid-template-columns: 1fr;
    }
    
    .wishlist-actions {
        flex-direction: column;
        gap: 10px;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>
