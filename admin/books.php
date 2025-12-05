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
    header('Location: /admin/books.php?deleted=1');
    exit;
}

$pageTitle = 'إدارة الكتب';
require_once '../includes/header.php';
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <h3>⚙️ الإدارة</h3>
        <ul class="admin-nav">
            <li><a href="/admin/dashboard.php">📊 لوحة التحكم</a></li>
            <li><a href="/admin/books.php" class="active">📚 إدارة الكتب</a></li>
            <li><a href="/admin/add_book.php">➕ إضافة كتاب</a></li>
            <li><a href="/admin/publishers.php">🏢 الناشرين</a></li>
            <li><a href="/admin/view_orders.php">📦 طلبات التوريد</a></li>
            <li><a href="/admin/customers.php">👥 العملاء</a></li>
            <li><a href="/admin/sales.php">💰 المبيعات</a></li>
            <li><a href="/admin/reports.php">📈 التقارير</a></li>
        </ul>
    </aside>
    
    <main>
        <div class="page-header">
            <h1>📚 إدارة الكتب</h1>
            <p>عرض وإدارة جميع الكتب في المكتبة</p>
        </div>
        
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">تم حذف الكتاب بنجاح</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['added'])): ?>
            <div class="alert alert-success">تمت إضافة الكتاب بنجاح</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success">تم تحديث الكتاب بنجاح</div>
        <?php endif; ?>
        
        <!-- Search & Actions -->
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <form method="GET" action="" style="display: flex; gap: 10px;">
                        <input type="text" name="search" class="form-control" placeholder="بحث بالعنوان، ISBN، أو المؤلف..."
                               value="<?php echo htmlspecialchars($search); ?>" style="min-width: 300px;">
                        <button type="submit" class="btn btn-primary">🔍 بحث</button>
                        <?php if ($search): ?>
                            <a href="/admin/books.php" class="btn btn-secondary">إلغاء</a>
                        <?php endif; ?>
                    </form>
                    <a href="/admin/add_book.php" class="btn btn-success">➕ إضافة كتاب جديد</a>
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
                            <th>العنوان</th>
                            <th>المؤلف</th>
                            <th>دار النشر</th>
                            <th>السعر</th>
                            <th>المخزون</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($books)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px;">
                                    لا توجد كتب
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
                                    <td><?php echo number_format($book['price'], 2); ?> ريال</td>
                                    <td>
                                        <span class="badge <?php echo $book['stock'] <= 0 ? 'badge-cancelled' : ($book['stock'] < $book['threshold'] ? 'badge-pending' : 'badge-confirmed'); ?>">
                                            <?php echo $book['stock']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/admin/update_book.php?isbn=<?php echo urlencode($book['isbn']); ?>" class="btn btn-secondary btn-sm">تعديل</a>
                                        <form method="POST" action="" style="display: inline;" onsubmit="return confirm('هل تريد حذف هذا الكتاب؟');">
                                            <input type="hidden" name="delete_isbn" value="<?php echo htmlspecialchars($book['isbn']); ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">حذف</button>
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
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">السابق</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">التالي</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <p style="margin-top: 20px; color: var(--text-light);">
            إجمالي: <?php echo $totalCount; ?> كتاب
        </p>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
