<?php
/**
 * Admin Sales - المبيعات
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Get total count
$totalCount = dbQuerySingle("SELECT COUNT(*) as count FROM sales")['count'];
$totalPages = ceil($totalCount / $perPage);

// Get sales
$sales = dbQuery("
    SELECT s.*, CONCAT(c.first_name, ' ', c.last_name) as customer_name, c.email
    FROM sales s 
    JOIN customers c ON s.customer_id = c.id 
    ORDER BY s.date DESC 
    LIMIT ? OFFSET ?
", [$perPage, $offset]);

$pageTitle = 'المبيعات';
require_once '../includes/header.php';
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <h3>⚙️ الإدارة</h3>
        <ul class="admin-nav">
            <li><a href="/admin/dashboard.php">📊 لوحة التحكم</a></li>
            <li><a href="/admin/books.php">📚 إدارة الكتب</a></li>
            <li><a href="/admin/add_book.php">➕ إضافة كتاب</a></li>
            <li><a href="/admin/publishers.php">🏢 الناشرين</a></li>
            <li><a href="/admin/view_orders.php">📦 طلبات التوريد</a></li>
            <li><a href="/admin/customers.php">👥 العملاء</a></li>
            <li><a href="/admin/sales.php" class="active">💰 المبيعات</a></li>
            <li><a href="/admin/reports.php">📈 التقارير</a></li>
        </ul>
    </aside>
    
    <main>
        <div class="page-header">
            <h1>💰 المبيعات</h1>
            <p>عرض جميع عمليات البيع</p>
        </div>
        
        <div class="card">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>العميل</th>
                            <th>البريد الإلكتروني</th>
                            <th>المبلغ</th>
                            <th>التاريخ</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sales)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px;">
                                    لا توجد مبيعات
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sales as $sale): ?>
                                <tr>
                                    <td><strong>#<?php echo $sale['id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($sale['email']); ?></td>
                                    <td><strong><?php echo number_format($sale['total_amount'], 2); ?> ريال</strong></td>
                                    <td><?php echo date('Y/m/d H:i', strtotime($sale['date'])); ?></td>
                                    <td>
                                        <a href="/admin/sale_details.php?id=<?php echo $sale['id']; ?>" class="btn btn-secondary btn-sm">
                                            عرض التفاصيل
                                        </a>
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
                    <a href="?page=<?php echo $page - 1; ?>">السابق</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>">التالي</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <p style="margin-top: 20px; color: var(--text-light);">
            إجمالي المبيعات: <?php echo $totalCount; ?>
        </p>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
