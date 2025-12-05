<?php
/**
 * Search Results Page - صفحة نتائج البحث
 */

$pageTitle = 'Search Results';
require_once 'includes/db.php';
require_once 'includes/auth.php';
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
    <h1><i data-feather="search"></i> Search Results</h1>
    <?php if ($query): ?>
        <p>Results for: "<?php echo htmlspecialchars($query); ?>"</p>
    <?php endif; ?>
</div>

<!-- Search Form -->
<div class="card" style="margin-bottom: 30px;">
    <div class="card-body">
        <form method="GET" action="" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 200px;">
                <label for="q">Search</label>
                <input type="text" name="q" id="q" class="form-control" 
                       placeholder="Enter your search..."
                       value="<?php echo htmlspecialchars($query); ?>" required>
            </div>
            
            <div class="form-group" style="margin-bottom: 0; min-width: 150px;">
                <label for="by">Search In</label>
                <select name="by" id="by" class="form-control">
                    <option value="all" <?php echo $searchBy === 'all' ? 'selected' : ''; ?>>All</option>
                    <option value="isbn" <?php echo $searchBy === 'isbn' ? 'selected' : ''; ?>>ISBN</option>
                    <option value="title" <?php echo $searchBy === 'title' ? 'selected' : ''; ?>>Title</option>
                    <option value="author" <?php echo $searchBy === 'author' ? 'selected' : ''; ?>>Author</option>
                    <option value="publisher" <?php echo $searchBy === 'publisher' ? 'selected' : ''; ?>>Publisher</option>
                    <option value="category" <?php echo $searchBy === 'category' ? 'selected' : ''; ?>>Category</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary"><i data-feather="search"></i> Search</button>
        </form>
    </div>
</div>

<!-- Results -->

<?php if (!$query): ?>
    <div class="empty-state">
        <div class="empty-state-icon"><i data-feather="search"></i></div>
        <h3>Search for a Book</h3>
        <p>Enter a search term to find books</p>
    </div>
<?php elseif (empty($books)): ?>
    <div class="empty-state">
        <div class="empty-state-icon"><i data-feather="alert-circle"></i></div>
        <h3>No Results Found</h3>
        <p>We couldn't find any books matching "<?php echo htmlspecialchars($query); ?>"</p>
        <a href="/books.php" class="btn btn-primary">Browse All Books</a>
    </div>
<?php else: ?>
    <p style="margin-bottom: 20px; color: var(--text-light);">
        Found <?php echo count($books); ?> results
    </p>
    
    <div class="books-grid">
        <?php foreach ($books as $book): ?>
            <div class="book-card">
                <div class="book-card-image"><svg viewBox="0 0 200 280" xmlns="http://www.w3.org/2000/svg"><rect width="200" height="280" fill="#f0f0f0" rx="4"/><rect x="20" y="30" width="160" height="40" fill="#1a4d2e" opacity="0.1" rx="3"/><rect x="20" y="90" width="120" height="8" fill="#1a4d2e" opacity="0.05" rx="2"/><rect x="20" y="110" width="140" height="8" fill="#1a4d2e" opacity="0.05" rx="2"/><rect x="20" y="130" width="100" height="8" fill="#1a4d2e" opacity="0.05" rx="2"/></svg></div>
                <div class="book-card-content">
                    <span class="book-card-category"><?php echo htmlspecialchars($book['category']); ?></span>
                    <h3 class="book-card-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                    <p class="book-card-author"><?php echo htmlspecialchars($book['authors']); ?></p>
                    <div class="book-card-price">EGP <?php echo number_format($book['price'], 2); ?></div>
                    <div class="book-card-stock <?php echo $book['stock'] <= 0 ? 'out' : ($book['stock'] < $book['threshold'] ? 'low' : ''); ?>">
                        <i data-feather="<?php echo $book['stock'] > 0 ? 'check-circle' : 'x-circle'; ?>"></i>
                        <?php 
                        if ($book['stock'] <= 0) {
                            echo 'Out of Stock';
                        } elseif ($book['stock'] < $book['threshold']) {
                            echo 'Limited Stock';
                        } else {
                            echo 'In Stock';
                        }
                        ?>
                    </div>
                </div>
                <div class="book-card-actions">
                    <a href="/book.php?isbn=<?php echo urlencode($book['isbn']); ?>" class="btn btn-secondary btn-sm" style="flex: 1;"><i data-feather="eye"></i> View Details</a>
                    <?php if (isLoggedIn() && !isAdmin() && $book['stock'] > 0): ?>
                        <button onclick="addToCart('<?php echo $book['isbn']; ?>')" class="btn btn-primary btn-sm">
                            <i data-feather="shopping-cart"></i> Add to Cart
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
