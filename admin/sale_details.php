<?php
/**
 * Admin Sale Details - تفاصيل البيع
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$saleId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($saleId <= 0) {
    header('Location: ' . url('admin/sales.php'));
    exit;
}

// Get sale
$sale = dbQuerySingle("
    SELECT s.*, CONCAT(c.first_name, ' ', c.last_name) as customer_name, 
           c.email, c.phone, c.address
    FROM sales s 
    JOIN customers c ON s.customer_id = c.id 
    WHERE s.id = ?
", [$saleId]);

if (!$sale) {
    header('Location: ' . url('admin/sales.php'));
    exit;
}

// Get sale items
$saleItems = dbQuery("
    SELECT si.*, b.title, b.authors 
    FROM sales_items si 
    JOIN books b ON si.book_isbn = b.isbn 
    WHERE si.sale_id = ?
", [$saleId]);

$pageTitle = 'تفاصيل البيع #' . $saleId;
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
        <div class="breadcrumb">
            <a href="/admin/dashboard.php">لوحة التحكم</a> &raquo;
            <a href="/admin/sales.php">المبيعات</a> &raquo;
            <span>طلب #<?php echo $saleId; ?></span>
        </div>
        
        <div class="page-header">
            <h1>📄 تفاصيل الطلب #<?php echo $saleId; ?></h1>
        </div>
        
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
            <!-- Order Items -->
            <div class="card">
                <div class="card-header">
                    <h3>الكتب المطلوبة</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>الكتاب</th>
                                <th>ISBN</th>
                                <th>الكمية</th>
                                <th>السعر</th>
                                <th>المجموع</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($saleItems as $item): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item['title']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($item['authors']); ?></small>
                                    </td>
                                    <td><small><?php echo htmlspecialchars($item['book_isbn']); ?></small></td>
                                    <td><?php echo $item['qty']; ?></td>
                                    <td><?php echo number_format($item['price'], 2); ?> ريال</td>
                                    <td><strong><?php echo number_format($item['qty'] * $item['price'], 2); ?> ريال</strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4"><strong>الإجمالي</strong></td>
                                <td><strong style="font-size: 1.2rem;"><?php echo number_format($sale['total_amount'], 2); ?> ريال</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <!-- Customer & Order Info -->
            <div>
                <div class="card" style="margin-bottom: 20px;">
                    <div class="card-header">
                        <h3>معلومات الطلب</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>رقم الطلب:</strong> #<?php echo $sale['id']; ?></p>
                        <p><strong>التاريخ:</strong> <?php echo date('Y/m/d H:i', strtotime($sale['date'])); ?></p>
                        <p><strong>المبلغ:</strong> <?php echo number_format($sale['total_amount'], 2); ?> ريال</p>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3>معلومات العميل</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>الاسم:</strong> <?php echo htmlspecialchars($sale['customer_name']); ?></p>
                        <p><strong>البريد:</strong> <?php echo htmlspecialchars($sale['email']); ?></p>
                        <p><strong>الهاتف:</strong> <?php echo htmlspecialchars($sale['phone'] ?: '-'); ?></p>
                        <p><strong>العنوان:</strong> <?php echo htmlspecialchars($sale['address'] ?: '-'); ?></p>
                    </div>
                </div>
                
                <a href="/admin/sales.php" class="btn btn-secondary btn-block" style="margin-top: 20px;">
                    ← العودة للمبيعات
                </a>
            </div>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
