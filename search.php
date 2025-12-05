<?php
/**
 * Search Results Page - صفحة نتائج البحث
 */

$pageTitle = 'نتائج البحث';
require_once 'includes/db.php';
require_once 'includes/header.php';

$query = sanitize($_GET['q'] ?? '');
$searchBy = sanitize($_GET['by'] ?? 'all');
$books = [];

if ($query) {
    $searchTerm = '%' . $query . '%';
    
    switch ($searchBy) {
        case 'isbn':
            $books = dbQuery(
                "SELECT b.*, p.name as publisher_name 
                 FROM books b 
                 LEFT JOIN publishers p ON b.publisher_id = p.id 
                 WHERE b.isbn LIKE ?
                 ORDER BY b.title",
                [$searchTerm]
            );
            break;
        case 'title':
            $books = dbQuery(
                "SELECT b.*, p.name as publisher_name 
                 FROM books b 
                 LEFT JOIN publishers p ON b.publisher_id = p.id 
                 WHERE b.title LIKE ?
                 ORDER BY b.title",
                [$searchTerm]
            );
            break;
        case 'author':
            $books = dbQuery(
                "SELECT b.*, p.name as publisher_name 
                 FROM books b 
                 LEFT JOIN publishers p ON b.publisher_id = p.id 
                 WHERE b.authors LIKE ?
                 ORDER BY b.title",
                [$searchTerm]
            );
            break;
        case 'publisher':
            $books = dbQuery(
                "SELECT b.*, p.name as publisher_name 
                 FROM books b 
                 LEFT JOIN publishers p ON b.publisher_id = p.id 
                 WHERE p.name LIKE ?
                 ORDER BY b.title",
                [$searchTerm]
            );
            break;
        case 'category':
            $books = dbQuery(
                "SELECT b.*, p.name as publisher_name 
                 FROM books b 
                 LEFT JOIN publishers p ON b.publisher_id = p.id 
                 WHERE b.category LIKE ?
                 ORDER BY b.title",
                [$searchTerm]
            );
            break;
        default: // all
            $books = dbQuery(
                "SELECT b.*, p.name as publisher_name 
                 FROM books b 
                 LEFT JOIN publishers p ON b.publisher_id = p.id 
                 WHERE b.isbn LIKE ? 
                    OR b.title LIKE ? 
                    OR b.authors LIKE ? 
                    OR p.name LIKE ? 
                    OR b.category LIKE ?
                 ORDER BY b.title",
                [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]
            );
    }
}
?>

<div class="page-header">
    <h1>🔍 نتائج البحث</h1>
    <?php if ($query): ?>
        <p>نتائج البحث عن: "<?php echo htmlspecialchars($query); ?>"</p>
    <?php endif; ?>
</div>

<!-- Search Form -->
<div class="card" style="margin-bottom: 30px;">
    <div class="card-body">
        <form method="GET" action="" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 200px;">
                <label for="q">البحث</label>
                <input type="text" name="q" id="q" class="form-control" 
                       placeholder="أدخل كلمة البحث..."
                       value="<?php echo htmlspecialchars($query); ?>" required>
            </div>
            
            <div class="form-group" style="margin-bottom: 0; min-width: 150px;">
                <label for="by">البحث في</label>
                <select name="by" id="by" class="form-control">
                    <option value="all" <?php echo $searchBy === 'all' ? 'selected' : ''; ?>>الكل</option>
                    <option value="isbn" <?php echo $searchBy === 'isbn' ? 'selected' : ''; ?>>ISBN</option>
                    <option value="title" <?php echo $searchBy === 'title' ? 'selected' : ''; ?>>العنوان</option>
                    <option value="author" <?php echo $searchBy === 'author' ? 'selected' : ''; ?>>المؤلف</option>
                    <option value="publisher" <?php echo $searchBy === 'publisher' ? 'selected' : ''; ?>>دار النشر</option>
                    <option value="category" <?php echo $searchBy === 'category' ? 'selected' : ''; ?>>التصنيف</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">🔍 بحث</button>
        </form>
    </div>
</div>

<!-- Results -->
<?php if (!$query): ?>
    <div class="empty-state">
        <div class="empty-state-icon">🔍</div>
        <h3>ابحث عن كتاب</h3>
        <p>أدخل كلمة البحث للعثور على الكتب</p>
    </div>
<?php elseif (empty($books)): ?>
    <div class="empty-state">
        <div class="empty-state-icon">😕</div>
        <h3>لا توجد نتائج</h3>
        <p>لم نجد أي كتب تطابق بحثك عن "<?php echo htmlspecialchars($query); ?>"</p>
        <a href="/books.php" class="btn btn-primary">تصفح جميع الكتب</a>
    </div>
<?php else: ?>
    <p style="margin-bottom: 20px; color: var(--text-light);">
        تم العثور على <?php echo count($books); ?> نتيجة
    </p>
    
    <div class="books-grid">
        <?php foreach ($books as $book): ?>
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
                            echo 'كمية محدودة';
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
                            🛒 أضف
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
