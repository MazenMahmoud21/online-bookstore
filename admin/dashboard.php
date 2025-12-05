<?php
/**
 * Admin Dashboard - لوحة تحكم المدير
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

// Get statistics
$stats = dbQuerySingle("
    SELECT 
        (SELECT COUNT(*) FROM books) as book_count,
        (SELECT COUNT(*) FROM customers WHERE is_admin = 0) as customer_count,
        (SELECT COUNT(*) FROM sales) as sales_count,
        (SELECT COALESCE(SUM(total_amount), 0) FROM sales WHERE date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) as monthly_revenue,
        (SELECT COUNT(*) FROM orders_from_publishers WHERE status = 'pending') as pending_orders
");

// Recent sales
$recentSales = dbQuery("
    SELECT s.*, CONCAT(c.first_name, ' ', c.last_name) as customer_name 
    FROM sales s 
    JOIN customers c ON s.customer_id = c.id 
    ORDER BY s.date DESC 
    LIMIT 5
");

// Low stock books
$lowStockBooks = dbQuery("
    SELECT isbn, title, stock, threshold 
    FROM books 
    WHERE stock <= threshold 
    ORDER BY stock ASC 
    LIMIT 5
");

$pageTitle = 'لوحة التحكم';
require_once '../includes/header.php';
?>

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <h3>⚙️ الإدارة</h3>
        <ul class="admin-nav">
            <li><a href="/admin/dashboard.php" class="active">📊 لوحة التحكم</a></li>
            <li><a href="/admin/books.php">📚 إدارة الكتب</a></li>
            <li><a href="/admin/add_book.php">➕ إضافة كتاب</a></li>
            <li><a href="/admin/publishers.php">🏢 الناشرين</a></li>
            <li><a href="/admin/view_orders.php">📦 طلبات التوريد</a></li>
            <li><a href="/admin/customers.php">👥 العملاء</a></li>
            <li><a href="/admin/sales.php">💰 المبيعات</a></li>
            <li><a href="/admin/reports.php">📈 التقارير</a></li>
        </ul>
    </aside>
    
    <!-- Main Content -->
    <main>
        <div class="page-header">
            <h1><i class="ph-duotone ph-gauge"></i> لوحة التحكم</h1>
            <p>مرحباً <?php echo htmlspecialchars(getCurrentUserName()); ?></p>
        </div>
        
        <!-- Stats Grid -->
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="icon"><i class="ph-duotone ph-books"></i></div>
                <div class="value"><?php echo number_format($stats['book_count']); ?></div>
                <div class="label">كتاب</div>
            </div>
            <div class="stat-card">
                <div class="icon"><i class="ph-duotone ph-users"></i></div>
                <div class="value"><?php echo number_format($stats['customer_count']); ?></div>
                <div class="label">عميل</div>
            </div>
            <div class="stat-card">
                <div class="icon">🛒</div>
                <div class="value"><?php echo number_format($stats['sales_count']); ?></div>
                <div class="label">طلب</div>
            </div>
            <div class="stat-card">
                <div class="icon"><i class="ph-duotone ph-currency-circle-dollar"></i></div>
                <div class="value"><?php echo number_format($stats['monthly_revenue'], 2); ?></div>
                <div class="label">إيرادات الشهر (ريال)</div>
            </div>
            <div class="stat-card">
                <div class="icon"><i class="ph-duotone ph-package"></i></div>
                <div class="value"><?php echo $stats['pending_orders']; ?></div>
                <div class="label">طلب توريد معلق</div>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 30px;">
            <!-- Recent Sales -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="ph ph-currency-circle-dollar"></i> آخر المبيعات</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <?php if (empty($recentSales)): ?>
                        <p style="padding: 20px; text-align: center; color: var(--text-light);">لا توجد مبيعات</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>العميل</th>
                                    <th>المبلغ</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentSales as $sale): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                                        <td><?php echo number_format($sale['total_amount'], 2); ?> ريال</td>
                                        <td><?php echo date('m/d H:i', strtotime($sale['date'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <a href="/admin/sales.php">عرض الكل ←</a>
                </div>
            </div>
            
            <!-- Low Stock Alert -->
            <div class="card">
                <div class="card-header" style="background-color: var(--warning-color);">
                    <h3><i class="ph ph-warning-circle"></i> تنبيه المخزون</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <?php if (empty($lowStockBooks)): ?>
                        <p style="padding: 20px; text-align: center; color: var(--success-color);">
                            ✓ جميع الكتب متوفرة بكميات كافية
                        </p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>الكتاب</th>
                                    <th>المخزون</th>
                                    <th>الحد الأدنى</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lowStockBooks as $book): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                                        <td style="color: <?php echo $book['stock'] <= 0 ? 'var(--error-color)' : 'var(--warning-color)'; ?>;">
                                            <?php echo $book['stock']; ?>
                                        </td>
                                        <td><?php echo $book['threshold']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <a href="/admin/view_orders.php">طلبات التوريد ←</a>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card" style="margin-top: 30px;">
            <div class="card-header">
                <h3>⚡ إجراءات سريعة</h3>
            </div>
            <div class="card-body">
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <a href="/admin/add_book.php" class="btn btn-primary">➕ إضافة كتاب جديد</a>
                    <a href="/admin/view_orders.php" class="btn btn-secondary">📦 عرض طلبات التوريد</a>
                    <a href="/admin/reports.php" class="btn btn-secondary">📈 عرض التقارير</a>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
