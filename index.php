<?php
/**
 * Homepage - الصفحة الرئيسية
 */

$pageTitle = 'الصفحة الرئيسية';
require_once 'includes/db.php';
require_once 'includes/header.php';

// Get featured books (latest 8)
$featuredBooks = dbQuery(
    "SELECT b.*, p.name as publisher_name 
     FROM books b 
     LEFT JOIN publishers p ON b.publisher_id = p.id 
     ORDER BY b.created_at DESC 
     LIMIT 8"
);

// Get categories
$categories = dbQuery("SELECT DISTINCT category FROM books ORDER BY category");

// Get statistics
$stats = dbQuerySingle(
    "SELECT 
        (SELECT COUNT(*) FROM books) as book_count,
        (SELECT COUNT(*) FROM customers WHERE is_admin = 0) as customer_count,
        (SELECT COUNT(*) FROM publishers) as publisher_count"
);
?>

<!-- Hero Section -->
<section class="hero">
    <h1>🏠 مرحباً بكم في المكتبة الإلكترونية</h1>
    <p>وجهتكم الأولى للكتب العربية في المملكة العربية السعودية</p>
    
    <form class="search-box" action="/search.php" method="GET">
        <input type="text" name="q" placeholder="ابحث عن كتاب بالعنوان، المؤلف، أو ISBN..." required>
        <button type="submit" class="btn btn-secondary">🔍 بحث</button>
    </form>
</section>

<!-- Statistics -->
<section class="dashboard-grid" style="margin-bottom: 40px;">
    <div class="stat-card">
        <div class="icon">📚</div>
        <div class="value"><?php echo number_format($stats['book_count']); ?></div>
        <div class="label">كتاب متوفر</div>
    </div>
    <div class="stat-card">
        <div class="icon">👥</div>
        <div class="value"><?php echo number_format($stats['customer_count']); ?></div>
        <div class="label">عميل مسجل</div>
    </div>
    <div class="stat-card">
        <div class="icon">🏢</div>
        <div class="value"><?php echo number_format($stats['publisher_count']); ?></div>
        <div class="label">دار نشر</div>
    </div>
    <div class="stat-card">
        <div class="icon">🚚</div>
        <div class="value">مجاني</div>
        <div class="label">التوصيل للرياض</div>
    </div>
</section>

<!-- Categories -->
<section style="margin-bottom: 40px;">
    <div class="page-header">
        <h2>📂 تصفح حسب التصنيف</h2>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <?php foreach ($categories as $cat): ?>
            <a href="/books.php?category=<?php echo urlencode($cat['category']); ?>" class="btn btn-secondary">
                <?php echo htmlspecialchars($cat['category']); ?>
            </a>
        <?php endforeach; ?>
        <a href="/books.php" class="btn btn-primary">عرض الكل</a>
    </div>
</section>

<!-- Featured Books -->
<section>
    <div class="page-header">
        <h2>📖 أحدث الكتب</h2>
        <p>اكتشف أحدث الإصدارات في مكتبتنا</p>
    </div>
    
    <?php if (empty($featuredBooks)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📚</div>
            <h3>لا توجد كتب حالياً</h3>
            <p>سيتم إضافة كتب جديدة قريباً</p>
        </div>
    <?php else: ?>
        <div class="books-grid">
            <?php foreach ($featuredBooks as $book): ?>
                <div class="book-card">
                    <div class="book-card-image">📕</div>
                    <div class="book-card-content">
                        <span class="book-card-category"><?php echo htmlspecialchars($book['category']); ?></span>
                        <h3 class="book-card-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                        <p class="book-card-author"><?php echo htmlspecialchars($book['authors']); ?></p>
                        <div class="book-card-price"><?php echo number_format($book['price'], 2); ?> ريال</div>
                        <div class="book-card-stock <?php echo $book['stock'] <= 0 ? 'out' : ($book['stock'] < $book['threshold'] ? 'low' : ''); ?>">
                            <?php 
                            if ($book['stock'] <= 0) {
                                echo 'غير متوفر';
                            } elseif ($book['stock'] < $book['threshold']) {
                                echo 'كمية محدودة (' . $book['stock'] . ')';
                            } else {
                                echo 'متوفر';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="book-card-actions">
                        <a href="/book.php?isbn=<?php echo urlencode($book['isbn']); ?>" class="btn btn-secondary btn-sm" style="flex: 1;">التفاصيل</a>
                        <?php if (isLoggedIn() && !isAdmin() && $book['stock'] > 0): ?>
                            <button onclick="addToCart('<?php echo $book['isbn']; ?>')" class="btn btn-primary btn-sm">
                                🛒 أضف للسلة
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="/books.php" class="btn btn-primary btn-lg">عرض جميع الكتب</a>
        </div>
    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>
