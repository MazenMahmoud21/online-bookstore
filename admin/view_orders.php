<?php
/**
 * View Publisher Orders - عرض طلبات التوريد
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

// Get filter
$status = sanitize($_GET['status'] ?? '');

$where = '';
$params = [];
if ($status) {
    $where = "WHERE o.status = ?";
    $params = [$status];
}

// Get orders
$orders = dbQuery(
    "SELECT o.*, b.title, b.authors, p.name as publisher_name 
     FROM orders_from_publishers o 
     JOIN books b ON o.book_isbn = b.isbn 
     LEFT JOIN publishers p ON b.publisher_id = p.id 
     $where 
     ORDER BY o.date DESC",
    $params
);

$pageTitle = 'طلبات التوريد';
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
            <li><a href="/admin/view_orders.php" class="active">📦 طلبات التوريد</a></li>
            <li><a href="/admin/customers.php">👥 العملاء</a></li>
            <li><a href="/admin/sales.php">💰 المبيعات</a></li>
            <li><a href="/admin/reports.php">📈 التقارير</a></li>
        </ul>
    </aside>
    
    <main>
        <div class="page-header">
            <h1>📦 طلبات التوريد من الناشرين</h1>
            <p>إدارة طلبات التوريد وتأكيدها</p>
        </div>
        
        <?php if (isset($_GET['confirmed'])): ?>
            <div class="alert alert-success">تم تأكيد الطلب بنجاح وتحديث المخزون</div>
        <?php endif; ?>
        
        <!-- Filters -->
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-body">
                <form method="GET" action="" style="display: flex; gap: 15px; align-items: center;">
                    <label>تصفية حسب الحالة:</label>
                    <select name="status" class="form-control" style="width: auto;">
                        <option value="">الكل</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>معلق</option>
                        <option value="confirmed" <?php echo $status === 'confirmed' ? 'selected' : ''; ?>>مؤكد</option>
                        <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>ملغي</option>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">تطبيق</button>
                    <?php if ($status): ?>
                        <a href="/admin/view_orders.php" class="btn btn-secondary btn-sm">إعادة تعيين</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <!-- Orders Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الكتاب</th>
                            <th>دار النشر</th>
                            <th>الكمية</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px;">
                                    لا توجد طلبات توريد
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?php echo $order['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($order['title']); ?></strong><br>
                                        <small>ISBN: <?php echo htmlspecialchars($order['book_isbn']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($order['publisher_name'] ?? 'غير محدد'); ?></td>
                                    <td><strong><?php echo $order['qty']; ?></strong> نسخة</td>
                                    <td><?php echo date('Y/m/d', strtotime($order['date'])); ?></td>
                                    <td>
                                        <?php
                                        $statusClass = match($order['status']) {
                                            'pending' => 'badge-pending',
                                            'confirmed' => 'badge-confirmed',
                                            'cancelled' => 'badge-cancelled',
                                            default => ''
                                        };
                                        $statusText = match($order['status']) {
                                            'pending' => 'معلق',
                                            'confirmed' => 'مؤكد',
                                            'cancelled' => 'ملغي',
                                            default => $order['status']
                                        };
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                    </td>
                                    <td>
                                        <?php if ($order['status'] === 'pending'): ?>
                                            <a href="/admin/confirm_order.php?id=<?php echo $order['id']; ?>" 
                                               class="btn btn-success btn-sm"
                                               onclick="return confirm('هل تريد تأكيد هذا الطلب؟ سيتم تحديث المخزون تلقائياً.');">
                                                ✓ تأكيد
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="alert alert-info" style="margin-top: 20px;">
            <strong>ملاحظة:</strong> عند تأكيد طلب التوريد، سيتم تحديث مخزون الكتاب تلقائياً.
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
