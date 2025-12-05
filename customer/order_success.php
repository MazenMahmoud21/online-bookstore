<?php
/**
 * Order Success Page - صفحة نجاح الطلب
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

if (!isset($_SESSION['order_success'])) {
    header('Location: /customer/orders.php');
    exit;
}

$orderId = $_SESSION['order_success'];
unset($_SESSION['order_success']);

$order = dbQuerySingle(
    "SELECT s.*, c.first_name, c.last_name, c.email, c.phone, c.address 
     FROM sales s 
     JOIN customers c ON s.customer_id = c.id 
     WHERE s.id = ?",
    [$orderId]
);

if (!$order) {
    header('Location: /customer/orders.php');
    exit;
}

$orderItems = dbQuery(
    "SELECT si.*, b.title, b.authors 
     FROM sales_items si 
     JOIN books b ON si.book_isbn = b.isbn 
     WHERE si.sale_id = ?",
    [$orderId]
);

$pageTitle = 'تم الطلب بنجاح';
require_once '../includes/header.php';
?>

<div style="text-align: center; padding: 40px 0;">
    <div style="font-size: 5rem; margin-bottom: 20px;">✅</div>
    <h1 style="color: var(--success-color); margin-bottom: 15px;">تم الطلب بنجاح!</h1>
    <p style="font-size: 1.2rem; color: var(--text-light);">شكراً لك، تم استلام طلبك وسيتم التوصيل قريباً</p>
    <p style="font-size: 1.1rem; margin-top: 10px;">رقم الطلب: <strong>#<?php echo $orderId; ?></strong></p>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header">
        <h3>تفاصيل الطلب</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <strong>تاريخ الطلب:</strong><br>
                <?php echo date('Y/m/d H:i', strtotime($order['date'])); ?>
            </div>
            <div>
                <strong>عنوان التوصيل:</strong><br>
                <?php echo htmlspecialchars($order['address']); ?>
            </div>
        </div>
        
        <div class="table-responsive">
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
                                <strong><?php echo htmlspecialchars($item['title']); ?></strong><br>
                                <small><?php echo htmlspecialchars($item['authors']); ?></small>
                            </td>
                            <td><?php echo $item['qty']; ?></td>
                            <td><?php echo number_format($item['price'], 2); ?> ريال</td>
                            <td><?php echo number_format($item['qty'] * $item['price'], 2); ?> ريال</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: left;"><strong>الإجمالي</strong></td>
                        <td><strong><?php echo number_format($order['total_amount'], 2); ?> ريال سعودي</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div style="text-align: center; margin-top: 30px;">
    <a href="/customer/orders.php" class="btn btn-primary">عرض جميع الطلبات</a>
    <a href="/books.php" class="btn btn-secondary">متابعة التسوق</a>
</div>

<?php require_once '../includes/footer.php'; ?>
