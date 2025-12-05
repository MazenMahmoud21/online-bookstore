<?php
/**
 * Admin Books Management - إدارة الكتب
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Search
$search = sanitize($_GET['search'] ?? '');

$where = '';
$params = [];
if ($search) {
    $where = "WHERE b.isbn LIKE ? OR b.title LIKE ? OR b.authors LIKE ?";
    $searchTerm = '%' . $search . '%';
    $params = [$searchTerm, $searchTerm, $searchTerm];
}

// Get total count
$totalCount = dbQuerySingle(
    "SELECT COUNT(*) as count FROM books b $where",
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
     $where 
     ORDER BY b.created_at DESC 
     LIMIT ? OFFSET ?",
    $params
);

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_isbn'])) {
    $isbn = sanitize($_POST['delete_isbn']);
    dbExecute("DELETE FROM books WHERE isbn = ?", [$isbn]);
    header('Location: ' . url('admin/books.php?deleted=1'));
    exit;
}

$pageTitle = 'Manage Books';
require_once '../includes/header.php';
?>

<div class="admin-layout">
    <?php require_once '../includes/admin_sidebar.php'; ?>
    
    <main>
        <div class="page-header">
            <h1>
                <span style="vertical-align: middle; margin-right: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#006c35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                </span>
                Manage Books
            </h1>
            <p>View and manage all books in the store</p>
        </div>
        
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">Book deleted successfully</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['added'])): ?>
            <div class="alert alert-success">Book added successfully</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success">Book updated successfully</div>
        <?php endif; ?>
        
        <!-- Search & Actions -->
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <form method="GET" action="" style="display: flex; gap: 10px;">
                        <input type="text" name="search" class="form-control" placeholder="Search by title, ISBN, or author..."
                               value="<?php echo htmlspecialchars($search); ?>" style="min-width: 300px;">
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            Search
                        </button>
                        <?php if ($search): ?>
                            <a href="/admin/books.php" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </form>
                    <a href="/admin/add_book.php" class="btn btn-success">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 6px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                        Add New Book
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Books Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ISBN</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Publisher</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($books)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px;">
                                    No books found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($books as $book): ?>
                                <tr>
                                    <td><small><?php echo htmlspecialchars($book['isbn']); ?></small></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($book['title']); ?></strong>
                                        <br><small><?php echo htmlspecialchars($book['category']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($book['authors']); ?></td>
                                    <td><?php echo htmlspecialchars($book['publisher_name'] ?? '-'); ?></td>
                                    <td>EGP <?php echo number_format($book['price'], 2); ?></td>
                                    <td>
                                        <span class="badge <?php echo $book['stock'] <= 0 ? 'badge-cancelled' : ($book['stock'] < $book['threshold'] ? 'badge-pending' : 'badge-confirmed'); ?>">
                                            <?php echo $book['stock']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/admin/update_book.php?isbn=<?php echo urlencode($book['isbn']); ?>" class="btn btn-secondary btn-sm">Edit</a>
                                        <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this book?');">
                                            <input type="hidden" name="delete_isbn" value="<?php echo htmlspecialchars($book['isbn']); ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <p style="margin-top: 20px; color: var(--text-light);">
            Total: <?php echo $totalCount; ?> books
        </p>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
