<?php
/**
 * Books Listing Page - صفحة عرض الكتب
 */

$pageTitle = 'الكتب';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/header.php';

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Filters
$category = sanitize($_GET['category'] ?? '');
$sort = sanitize($_GET['sort'] ?? 'newest');

// Build query
$where = [];
$params = [];

if ($category) {
    $where[] = "b.category = ?";
    $params[] = $category;
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Sorting
$orderBy = match($sort) {
    'price_asc' => 'b.price ASC',
    'price_desc' => 'b.price DESC',
    'title' => 'b.title ASC',
    'oldest' => 'b.created_at ASC',
    default => 'b.created_at DESC'
};

// Get total count
$totalCount = dbQuerySingle(
    "SELECT COUNT(*) as count FROM books b $whereClause",
    $params
)['count'];

$totalPages = ceil($totalCount / $perPage);

// Get books
$params[] = $perPage;
$params[] = $offset;
$books = dbQuery(
    "SELECT b.*, p.name as publisher_name 
     FROM books b 
     LEFT JOIN publishers p ON b.publisher_id = p.id 
     $whereClause 
     ORDER BY $orderBy 
     LIMIT ? OFFSET ?",
    $params
);

// Get categories for filter
$categories = dbQuery("SELECT DISTINCT category FROM books ORDER BY category");
?>

<div class="page-header">
    <h1><i class="ph-duotone ph-books"></i> الكتب</h1>
    <p>تصفح مجموعتنا من الكتب العربية</p>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 30px;">
    <div class="card-body">
        <form method="GET" action="" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0; min-width: 150px;">
                <label for="category">التصنيف</label>
                <select name="category" id="category" class="form-control">
                    <option value="">جميع التصنيفات</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                                <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['category']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 0; min-width: 150px;">
                <label for="sort">ترتيب حسب</label>
                <select name="sort" id="sort" class="form-control">
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>الأحدث</option>
                    <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>الأقدم</option>
                    <option value="title" <?php echo $sort === 'title' ? 'selected' : ''; ?>>العنوان</option>
                    <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>السعر: الأقل أولاً</option>
                    <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>السعر: الأعلى أولاً</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">تطبيق</button>
            <a href="/books.php" class="btn btn-secondary">إعادة تعيين</a>
        </form>
    </div>
</div>

<!-- Results Info -->
<p style="margin-bottom: 20px; color: var(--text-light);">
    عرض <?php echo count($books); ?> من أصل <?php echo $totalCount; ?> كتاب
</p>

<!-- Books Grid -->
<?php if (empty($books)): ?>
    <div class="empty-state">
        <div class="empty-state-icon"><i class="ph-duotone ph-books"></i></div>
        <h3>لا توجد كتب</h3>
        <p>لم نجد أي كتب تطابق بحثك</p>
        <a href="/books.php" class="btn btn-primary">عرض جميع الكتب</a>
    </div>
<?php else: ?>
    <div class="books-grid">
        <?php foreach ($books as $book): ?>
            <div class="book-card">
                <div class="book-card-image"><i class="ph-duotone ph-book"></i></div>
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
                            <i class="ph ph-shopping-cart"></i> أضف
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&category=<?php echo urlencode($category); ?>&sort=<?php echo urlencode($sort); ?>">السابق</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="active"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>&category=<?php echo urlencode($category); ?>&sort=<?php echo urlencode($sort); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&category=<?php echo urlencode($category); ?>&sort=<?php echo urlencode($sort); ?>">التالي</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
