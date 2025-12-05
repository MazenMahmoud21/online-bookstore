<?php
/**
 * Book Details Page - صفحة تفاصيل الكتاب
 */

require_once 'includes/db.php';

$isbn = isset($_GET['isbn']) ? sanitize($_GET['isbn']) : '';

if (empty($isbn)) {
    header('Location: /books.php');
    exit;
}

$book = dbQuerySingle(
    "SELECT b.*, p.name as publisher_name, p.phone as publisher_phone 
     FROM books b 
     LEFT JOIN publishers p ON b.publisher_id = p.id 
     WHERE b.isbn = ?",
    [$isbn]
);

if (!$book) {
    header('Location: /books.php');
    exit;
}

$pageTitle = $book['title'];
require_once 'includes/header.php';
?>

<div class="breadcrumb">
    <a href="/index.php">الرئيسية</a> &raquo;
    <a href="/books.php">الكتب</a> &raquo;
    <a href="/books.php?category=<?php echo urlencode($book['category']); ?>"><?php echo htmlspecialchars($book['category']); ?></a> &raquo;
    <span><?php echo htmlspecialchars($book['title']); ?></span>
</div>

<div class="book-details">
    <div class="book-image-large">
        📕
    </div>
    
    <div class="book-info">
        <span class="book-card-category"><?php echo htmlspecialchars($book['category']); ?></span>
        
        <h1><?php echo htmlspecialchars($book['title']); ?></h1>
        
        <div class="book-meta">
            <p><strong>المؤلف:</strong> <?php echo htmlspecialchars($book['authors']); ?></p>
            <?php if ($book['publisher_name']): ?>
                <p><strong>دار النشر:</strong> <?php echo htmlspecialchars($book['publisher_name']); ?></p>
            <?php endif; ?>
            <p><strong>سنة النشر:</strong> <?php echo $book['year']; ?></p>
            <p><strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?></p>
        </div>
        
        <div class="book-price-large"><?php echo number_format($book['price'], 2); ?> ريال سعودي</div>
        
        <div class="book-card-stock <?php echo $book['stock'] <= 0 ? 'out' : ($book['stock'] < $book['threshold'] ? 'low' : ''); ?>" style="font-size: 1.1rem; margin-bottom: 20px;">
            <?php 
            if ($book['stock'] <= 0) {
                echo '❌ غير متوفر حالياً';
            } elseif ($book['stock'] < $book['threshold']) {
                echo '⚠️ كمية محدودة (' . $book['stock'] . ' متبقية)';
            } else {
                echo '✅ متوفر في المخزون';
            }
            ?>
        </div>
        
        <?php if ($book['description']): ?>
            <div class="book-description">
                <h3>عن الكتاب</h3>
                <p><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (isLoggedIn() && !isAdmin() && $book['stock'] > 0): ?>
            <form id="addToCartForm" style="display: flex; gap: 15px; align-items: center;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="quantity">الكمية:</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" 
                           max="<?php echo $book['stock']; ?>" class="form-control" style="width: 80px;">
                </div>
                <button type="button" onclick="addToCart('<?php echo $book['isbn']; ?>', document.getElementById('quantity').value)" 
                        class="btn btn-primary btn-lg">
                    🛒 أضف إلى السلة
                </button>
            </form>
        <?php elseif (!isLoggedIn()): ?>
            <div class="alert alert-info">
                <a href="/login.php">سجل الدخول</a> لإضافة الكتاب إلى السلة
            </div>
        <?php elseif ($book['stock'] <= 0): ?>
            <div class="alert alert-warning">
                هذا الكتاب غير متوفر حالياً. يرجى المحاولة لاحقاً.
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border-color);">
            <a href="/books.php" class="btn btn-secondary">← العودة إلى الكتب</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
