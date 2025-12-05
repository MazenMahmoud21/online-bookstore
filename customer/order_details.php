<?php
/**
 * Order Details Page - صفحة تفاصيل الطلب
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();
if (isAdmin()) {
    header('Location: ' . url('admin/dashboard.php'));
    exit;
}

$customerId = getCurrentUserId();
$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get order
$order = dbQuerySingle(
    "SELECT * FROM sales WHERE id = ? AND customer_id = ?",
    [$orderId, $customerId]
);

if (!$order) {
    header('Location: ' . url('customer/orders.php'));
    exit;
}

// Get order items
$orderItems = dbQuery(
    "SELECT si.*, b.title, b.authors, b.isbn 
     FROM sales_items si 
     JOIN books b ON si.book_isbn = b.isbn 
     WHERE si.sale_id = ?",
    [$orderId]
);

$pageTitle = 'تفاصيل الطلب #' . $orderId;
require_once '../includes/header.php';
?>

<div class="breadcrumb">
    <a href="/index.php">الرئيسية</a> &raquo;
    <a href="/customer/orders.php">طلباتي</a> &raquo;
    <span>طلب #<?php echo $orderId; ?></span>
</div>

<div class="page-header">
    <h1>📦 تفاصيل الطلب #<?php echo $orderId; ?></h1>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
    <div class="card">
        <div class="card-header">
            <h3>الكتب المطلوبة</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>الكتاب</th>
                        <th>الكمية</th>
                        <th>السعر</th>
                        <th>المجموع</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td>
                                <a href="/book.php?isbn=<?php echo urlencode($item['isbn']); ?>">
                                    <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                                </a><br>
                                <small><?php echo htmlspecialchars($item['authors']); ?></small>
                            </td>
                            <td><?php echo $item['qty']; ?></td>
                            <td><?php echo number_format($item['price'], 2); ?> ريال</td>
                            <td><?php echo number_format($item['qty'] * $item['price'], 2); ?> ريال</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div>
        <div class="card">
            <div class="card-header">
                <h3>ملخص الطلب</h3>
            </div>
            <div class="card-body">
                <div style="margin-bottom: 15px;">
                    <strong>رقم الطلب:</strong><br>
                    #<?php echo $order['id']; ?>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <strong>تاريخ الطلب:</strong><br>
                    <?php echo date('Y/m/d H:i', strtotime($order['date'])); ?>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <strong>عدد الكتب:</strong><br>
                    <?php echo count($orderItems); ?> كتب
                </div>
                
                <div style="padding-top: 15px; border-top: 2px solid var(--border-color);">
                    <strong style="font-size: 1.2rem;">الإجمالي:</strong><br>
                    <span style="font-size: 1.5rem; color: var(--primary-color); font-weight: bold;">
                        <?php echo number_format($order['total_amount'], 2); ?> ريال
                    </span>
                </div>
            </div>
        </div>
        
        <a href="/customer/orders.php" class="btn btn-secondary btn-block" style="margin-top: 15px;">
            ← العودة للطلبات
        </a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
