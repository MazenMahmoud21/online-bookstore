<?php
/**
 * Admin Customers - إدارة العملاء
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

// Get customers with order count
$customers = dbQuery("
    SELECT c.*, 
           COUNT(s.id) as order_count,
           COALESCE(SUM(s.total_amount), 0) as total_spent
    FROM customers c 
    LEFT JOIN sales s ON c.id = s.customer_id 
    WHERE c.is_admin = 0
    GROUP BY c.id 
    ORDER BY c.created_at DESC
");

$pageTitle = 'إدارة العملاء';
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
            <li><a href="/admin/customers.php" class="active">👥 العملاء</a></li>
            <li><a href="/admin/sales.php">💰 المبيعات</a></li>
            <li><a href="/admin/reports.php">📈 التقارير</a></li>
        </ul>
    </aside>
    
    <main>
        <div class="page-header">
            <h1>👥 إدارة العملاء</h1>
            <p>عرض جميع العملاء المسجلين</p>
        </div>
        
        <div class="card">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
                            <th>اسم المستخدم</th>
                            <th>البريد الإلكتروني</th>
                            <th>الهاتف</th>
                            <th>عدد الطلبات</th>
                            <th>إجمالي المشتريات</th>
                            <th>تاريخ التسجيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($customers)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px;">
                                    لا يوجد عملاء مسجلين
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td><?php echo $customer['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($customer['username']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['phone'] ?: '-'); ?></td>
                                    <td>
                                        <span class="badge badge-confirmed"><?php echo $customer['order_count']; ?></span>
                                    </td>
                                    <td><?php echo number_format($customer['total_spent'], 2); ?> ريال</td>
                                    <td><?php echo date('Y/m/d', strtotime($customer['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <p style="margin-top: 20px; color: var(--text-light);">
            إجمالي العملاء: <?php echo count($customers); ?>
        </p>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
