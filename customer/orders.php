<?php
/**
 * Customer Orders Page - صفحة طلبات العميل
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();
if (isAdmin()) {
    header('Location: ' . url('admin/dashboard.php'));
    exit;
}

$customerId = getCurrentUserId();

// Get customer orders
$orders = dbQuery(
    "SELECT * FROM sales WHERE customer_id = ? ORDER BY date DESC",
    [$customerId]
);

$pageTitle = 'طلباتي';
require_once '../includes/header.php';
?>

<div class="page-header">
    <h1><i class="ph-duotone ph-package"></i> طلباتي</h1>
    <p>عرض جميع طلباتك السابقة</p>
</div>

<?php if (empty($orders)): ?>
    <div class="empty-state">
        <div class="empty-state-icon"><i class="ph-duotone ph-package"></i></div>
        <h3>لا توجد طلبات</h3>
        <p>لم تقم بأي طلبات بعد</p>
        <a href="/books.php" class="btn btn-primary">تصفح الكتب</a>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>رقم الطلب</th>
                        <th>التاريخ</th>
                        <th>المجموع</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong>#<?php echo $order['id']; ?></strong></td>
                            <td><?php echo date('Y/m/d H:i', strtotime($order['date'])); ?></td>
                            <td><strong><?php echo number_format($order['total_amount'], 2); ?> ريال</strong></td>
                            <td>
                                <a href="/customer/order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary btn-sm">
                                    عرض التفاصيل
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
