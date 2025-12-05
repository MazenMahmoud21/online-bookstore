<?php
/**
 * Admin Reviews Management
 * إدارة التقييمات - لوحة المدير
 */

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Require admin
requireAdmin();

$pageTitle = 'إدارة التقييمات';
$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $reviewId = intval($_POST['review_id'] ?? 0);
    
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'خطأ في التحقق.';
        $messageType = 'error';
    } elseif ($reviewId > 0) {
        try {
            if ($action === 'approve') {
                dbExecute("UPDATE book_reviews SET status = 'approved' WHERE id = ?", [$reviewId]);
                
                // Update book rating
                $review = dbQuerySingle("SELECT book_isbn FROM book_reviews WHERE id = ?", [$reviewId]);
                if ($review) {
                    $stats = dbQuerySingle(
                        "SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM book_reviews WHERE book_isbn = ? AND status = 'approved'",
                        [$review['book_isbn']]
                    );
                    dbExecute(
                        "UPDATE books SET average_rating = ?, review_count = ? WHERE isbn = ?",
                        [$stats['avg_rating'] ?? 0, $stats['count'] ?? 0, $review['book_isbn']]
                    );
                }
                
                $message = 'تم قبول التقييم.';
                $messageType = 'success';
            } elseif ($action === 'reject') {
                dbExecute("UPDATE book_reviews SET status = 'rejected' WHERE id = ?", [$reviewId]);
                $message = 'تم رفض التقييم.';
                $messageType = 'success';
            } elseif ($action === 'delete') {
                $review = dbQuerySingle("SELECT book_isbn FROM book_reviews WHERE id = ?", [$reviewId]);
                dbExecute("DELETE FROM book_reviews WHERE id = ?", [$reviewId]);
                
                // Update book rating
                if ($review) {
                    $stats = dbQuerySingle(
                        "SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM book_reviews WHERE book_isbn = ? AND status = 'approved'",
                        [$review['book_isbn']]
                    );
                    dbExecute(
                        "UPDATE books SET average_rating = ?, review_count = ? WHERE isbn = ?",
                        [$stats['avg_rating'] ?? 0, $stats['count'] ?? 0, $review['book_isbn']]
                    );
                }
                
                $message = 'تم حذف التقييم.';
                $messageType = 'success';
            }
        } catch (Exception $e) {
            $message = 'حدث خطأ: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Filters
$statusFilter = $_GET['status'] ?? 'all';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query
$whereClause = '';
$params = [];

if ($statusFilter !== 'all') {
    $whereClause = "WHERE r.status = ?";
    $params[] = $statusFilter;
}

// Get reviews
$reviews = [];
$totalReviews = 0;

try {
    // Check if table exists
    $tableExists = dbQuery("SHOW TABLES LIKE 'book_reviews'");
    
    if (count($tableExists) > 0) {
        $countResult = dbQuerySingle(
            "SELECT COUNT(*) as total FROM book_reviews r $whereClause",
            $params
        );
        $totalReviews = $countResult['total'] ?? 0;
        
        $reviews = dbQuery(
            "SELECT r.*, 
                    b.title as book_title,
                    CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                    c.email as customer_email
             FROM book_reviews r
             JOIN books b ON r.book_isbn = b.isbn
             JOIN customers c ON r.customer_id = c.id
             $whereClause
             ORDER BY r.created_at DESC
             LIMIT $perPage OFFSET $offset",
            $params
        );
    }
} catch (Exception $e) {
    $message = 'خطأ في جلب التقييمات: ' . $e->getMessage();
    $messageType = 'error';
}

$totalPages = ceil($totalReviews / $perPage);

// Stats
$stats = [];
try {
    $tableExists = dbQuery("SHOW TABLES LIKE 'book_reviews'");
    if (count($tableExists) > 0) {
        $stats = [
            'pending' => dbQuerySingle("SELECT COUNT(*) as c FROM book_reviews WHERE status = 'pending'")['c'] ?? 0,
            'approved' => dbQuerySingle("SELECT COUNT(*) as c FROM book_reviews WHERE status = 'approved'")['c'] ?? 0,
            'rejected' => dbQuerySingle("SELECT COUNT(*) as c FROM book_reviews WHERE status = 'rejected'")['c'] ?? 0,
        ];
    }
} catch (Exception $e) {
    $stats = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
}

require_once '../includes/header.php';
?>

<div class="admin-layout">
    <?php include '../includes/admin_sidebar.php'; ?>
    
    <main class="admin-main">
        <div class="admin-header">
            <h1>⭐ إدارة التقييمات</h1>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Stats Cards -->
        <div class="stats-row">
            <div class="stat-card pending">
                <span class="stat-number"><?php echo $stats['pending']; ?></span>
                <span class="stat-label">قيد المراجعة</span>
            </div>
            <div class="stat-card approved">
                <span class="stat-number"><?php echo $stats['approved']; ?></span>
                <span class="stat-label">مقبول</span>
            </div>
            <div class="stat-card rejected">
                <span class="stat-number"><?php echo $stats['rejected']; ?></span>
                <span class="stat-label">مرفوض</span>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filters-bar">
            <a href="?status=all" class="filter-btn <?php echo $statusFilter === 'all' ? 'active' : ''; ?>">
                الكل (<?php echo array_sum($stats); ?>)
            </a>
            <a href="?status=pending" class="filter-btn <?php echo $statusFilter === 'pending' ? 'active' : ''; ?>">
                قيد المراجعة (<?php echo $stats['pending']; ?>)
            </a>
            <a href="?status=approved" class="filter-btn <?php echo $statusFilter === 'approved' ? 'active' : ''; ?>">
                مقبول (<?php echo $stats['approved']; ?>)
            </a>
            <a href="?status=rejected" class="filter-btn <?php echo $statusFilter === 'rejected' ? 'active' : ''; ?>">
                مرفوض (<?php echo $stats['rejected']; ?>)
            </a>
        </div>
        
        <!-- Reviews List -->
        <?php if (empty($reviews)): ?>
            <div class="empty-state">
                <span class="empty-icon">📝</span>
                <h2>لا توجد تقييمات</h2>
                <p>لم يتم العثور على أي تقييمات بهذه الفلترة.</p>
            </div>
        <?php else: ?>
            <div class="reviews-list">
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card status-<?php echo $review['status']; ?>">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <strong><?php echo htmlspecialchars($review['customer_name']); ?></strong>
                                <span class="email"><?php echo htmlspecialchars($review['customer_email']); ?></span>
                            </div>
                            <div class="review-meta">
                                <span class="status-badge status-<?php echo $review['status']; ?>">
                                    <?php
                                    switch ($review['status']) {
                                        case 'pending': echo 'قيد المراجعة'; break;
                                        case 'approved': echo 'مقبول'; break;
                                        case 'rejected': echo 'مرفوض'; break;
                                    }
                                    ?>
                                </span>
                                <span class="date"><?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="book-info">
                            <a href="<?php echo url('book.php?isbn=' . urlencode($review['book_isbn'])); ?>" target="_blank">
                                📚 <?php echo htmlspecialchars($review['book_title']); ?>
                            </a>
                        </div>
                        
                        <div class="rating-display">
                            <?php
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $review['rating'] ? '⭐' : '☆';
                            }
                            ?>
                            <span>(<?php echo $review['rating']; ?>/5)</span>
                        </div>
                        
                        <?php if ($review['review_text']): ?>
                            <div class="review-text">
                                <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="review-actions">
                            <?php if ($review['status'] === 'pending'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-approve">✓ قبول</button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-reject">✗ رفض</button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn btn-delete">🗑️ حذف</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?status=<?php echo $statusFilter; ?>&page=<?php echo $i; ?>" 
                           class="<?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</div>

<style>
.admin-layout {
    display: flex;
    min-height: calc(100vh - 60px);
}

.admin-sidebar {
    width: 250px;
    background: #1a1a2e;
    color: white;
}

.admin-nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.admin-nav a {
    display: block;
    padding: 15px 20px;
    color: #ccc;
    text-decoration: none;
    transition: background 0.3s;
}

.admin-nav a:hover,
.admin-nav a.active {
    background: #006c35;
    color: white;
}

.admin-main {
    flex: 1;
    padding: 30px;
    background: #f5f7fa;
}

.admin-header h1 {
    margin: 0 0 25px;
    color: #333;
}

.stats-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 25px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.stat-card .stat-number {
    display: block;
    font-size: 2rem;
    font-weight: bold;
}

.stat-card .stat-label {
    color: #666;
}

.stat-card.pending .stat-number { color: #f39c12; }
.stat-card.approved .stat-number { color: #27ae60; }
.stat-card.rejected .stat-number { color: #e74c3c; }

.filters-bar {
    display: flex;
    gap: 10px;
    margin-bottom: 25px;
    flex-wrap: wrap;
}

.filter-btn {
    padding: 10px 20px;
    background: white;
    border: 2px solid #e0e0e0;
    border-radius: 25px;
    color: #666;
    text-decoration: none;
    transition: all 0.3s;
}

.filter-btn:hover,
.filter-btn.active {
    background: #006c35;
    border-color: #006c35;
    color: white;
}

.reviews-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.review-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border-right: 4px solid #ccc;
}

.review-card.status-pending { border-right-color: #f39c12; }
.review-card.status-approved { border-right-color: #27ae60; }
.review-card.status-rejected { border-right-color: #e74c3c; }

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.reviewer-info strong {
    display: block;
    color: #333;
}

.reviewer-info .email {
    color: #666;
    font-size: 0.9rem;
}

.review-meta {
    text-align: left;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: bold;
}

.status-badge.status-pending { background: #fef5e7; color: #f39c12; }
.status-badge.status-approved { background: #e8f8ef; color: #27ae60; }
.status-badge.status-rejected { background: #fde8e8; color: #e74c3c; }

.review-meta .date {
    display: block;
    color: #999;
    font-size: 0.85rem;
    margin-top: 5px;
}

.book-info {
    margin-bottom: 12px;
}

.book-info a {
    color: #006c35;
    text-decoration: none;
}

.book-info a:hover {
    text-decoration: underline;
}

.rating-display {
    margin-bottom: 12px;
    font-size: 1.1rem;
}

.rating-display span {
    color: #666;
    font-size: 0.9rem;
    margin-right: 10px;
}

.review-text {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    line-height: 1.7;
    color: #444;
}

.review-actions {
    display: flex;
    gap: 10px;
}

.review-actions .btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: background 0.3s;
}

.btn-approve {
    background: #e8f8ef;
    color: #27ae60;
}

.btn-approve:hover {
    background: #27ae60;
    color: white;
}

.btn-reject {
    background: #fde8e8;
    color: #e74c3c;
}

.btn-reject:hover {
    background: #e74c3c;
    color: white;
}

.btn-delete {
    background: #f8f9fa;
    color: #666;
}

.btn-delete:hover {
    background: #e74c3c;
    color: white;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 30px;
}

.pagination a {
    padding: 8px 15px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 5px;
    color: #333;
    text-decoration: none;
}

.pagination a.active,
.pagination a:hover {
    background: #006c35;
    border-color: #006c35;
    color: white;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
}

.empty-icon {
    font-size: 4rem;
    display: block;
    margin-bottom: 20px;
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
}

@media (max-width: 768px) {
    .admin-layout {
        flex-direction: column;
    }
    
    .admin-sidebar {
        width: 100%;
    }
    
    .stats-row {
        grid-template-columns: 1fr;
    }
    
    .review-header {
        flex-direction: column;
        gap: 10px;
    }
    
    .review-actions {
        flex-wrap: wrap;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>
