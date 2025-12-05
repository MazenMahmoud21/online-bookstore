<?php
/**
 * Admin Reports - التقارير
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$reportType = sanitize($_GET['report'] ?? 'monthly_sales');
$selectedDate = sanitize($_GET['date'] ?? date('Y-m-d'));

$reportData = [];
$reportTitle = '';

switch ($reportType) {
    case 'monthly_sales':
        $reportTitle = 'مبيعات الشهر الماضي';
        $reportData = callProcedure('get_sales_last_month');
        break;
        
    case 'daily_sales':
        $reportTitle = 'مبيعات يوم ' . $selectedDate;
        $reportData = callProcedure('get_sales_on_day', [$selectedDate]);
        break;
        
    case 'top_customers':
        $reportTitle = 'أفضل 5 عملاء في آخر 3 أشهر';
        $reportData = callProcedure('get_top_customers');
        break;
        
    case 'top_books':
        $reportTitle = 'أفضل 10 كتب مبيعاً في آخر 3 أشهر';
        $reportData = callProcedure('get_top_selling_books');
        break;
        
    case 'reorder_stats':
        $reportTitle = 'إحصائيات إعادة الطلب';
        $reportData = callProcedure('get_all_books_reorder_stats');
        break;
}

$pageTitle = 'التقارير';
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
            <li><a href="/admin/sales.php">💰 المبيعات</a></li>
            <li><a href="/admin/reports.php" class="active">📈 التقارير</a></li>
        </ul>
    </aside>
    
    <main>
        <div class="page-header">
            <h1>📈 التقارير</h1>
            <p>تحليل بيانات المبيعات والعملاء</p>
        </div>
        
        <!-- Report Selection -->
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-body">
                <form method="GET" action="" class="report-filters">
                    <div class="form-group">
                        <label for="report">نوع التقرير</label>
                        <select name="report" id="report" class="form-control" onchange="toggleDateField()">
                            <option value="monthly_sales" <?php echo $reportType === 'monthly_sales' ? 'selected' : ''; ?>>
                                مبيعات الشهر الماضي
                            </option>
                            <option value="daily_sales" <?php echo $reportType === 'daily_sales' ? 'selected' : ''; ?>>
                                مبيعات يوم محدد
                            </option>
                            <option value="top_customers" <?php echo $reportType === 'top_customers' ? 'selected' : ''; ?>>
                                أفضل 5 عملاء
                            </option>
                            <option value="top_books" <?php echo $reportType === 'top_books' ? 'selected' : ''; ?>>
                                أفضل 10 كتب مبيعاً
                            </option>
                            <option value="reorder_stats" <?php echo $reportType === 'reorder_stats' ? 'selected' : ''; ?>>
                                إحصائيات إعادة الطلب
                            </option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="dateField" style="<?php echo $reportType === 'daily_sales' ? '' : 'display: none;'; ?>">
                        <label for="date">التاريخ</label>
                        <input type="date" name="date" id="date" class="form-control" 
                               value="<?php echo $selectedDate; ?>" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">عرض التقرير</button>
                    
                    <?php if (!empty($reportData)): ?>
                        <button type="button" onclick="printReport('reportContent')" class="btn btn-secondary">🖨️ طباعة</button>
                        <button type="button" onclick="exportToCSV('reportTable', 'report_<?php echo $reportType; ?>')" class="btn btn-secondary">📥 تصدير CSV</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <!-- Report Content -->
        <div class="card" id="reportContent">
            <div class="card-header">
                <h3><?php echo $reportTitle; ?></h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (empty($reportData)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📊</div>
                        <h3>لا توجد بيانات</h3>
                        <p>لم نجد أي بيانات لهذا التقرير</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table" id="reportTable">
                            <?php if ($reportType === 'monthly_sales' || $reportType === 'daily_sales'): ?>
                                <thead>
                                    <tr>
                                        <th>رقم الطلب</th>
                                        <th>العميل</th>
                                        <th>التاريخ</th>
                                        <th>المبلغ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $totalAmount = 0;
                                    foreach ($reportData as $row): 
                                        $totalAmount += $row['total_amount'];
                                    ?>
                                        <tr>
                                            <td>#<?php echo $row['sale_id']; ?></td>
                                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                            <td><?php echo date('Y/m/d H:i', strtotime($row['date'])); ?></td>
                                            <td><?php echo number_format($row['total_amount'], 2); ?> ريال</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3"><strong>الإجمالي</strong></td>
                                        <td><strong><?php echo number_format($totalAmount, 2); ?> ريال</strong></td>
                                    </tr>
                                </tfoot>
                            <?php elseif ($reportType === 'top_customers'): ?>
                                <thead>
                                    <tr>
                                        <th>المرتبة</th>
                                        <th>العميل</th>
                                        <th>البريد الإلكتروني</th>
                                        <th>عدد الطلبات</th>
                                        <th>إجمالي المشتريات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $rank = 1; foreach ($reportData as $row): ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                echo $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : $rank)); 
                                                $rank++;
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td><?php echo $row['order_count']; ?></td>
                                            <td><?php echo number_format($row['total_spent'], 2); ?> ريال</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            <?php elseif ($reportType === 'top_books'): ?>
                                <thead>
                                    <tr>
                                        <th>المرتبة</th>
                                        <th>الكتاب</th>
                                        <th>المؤلف</th>
                                        <th>الكمية المباعة</th>
                                        <th>الإيرادات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $rank = 1; foreach ($reportData as $row): ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                echo $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : $rank)); 
                                                $rank++;
                                                ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                                                <small>ISBN: <?php echo htmlspecialchars($row['isbn']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['authors']); ?></td>
                                            <td><?php echo $row['total_sold']; ?></td>
                                            <td><?php echo number_format($row['total_revenue'], 2); ?> ريال</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            <?php elseif ($reportType === 'reorder_stats'): ?>
                                <thead>
                                    <tr>
                                        <th>ISBN</th>
                                        <th>الكتاب</th>
                                        <th>عدد مرات إعادة الطلب</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reportData as $row): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['isbn']); ?></td>
                                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                                            <td>
                                                <?php if ($row['reorder_count'] > 0): ?>
                                                    <span class="badge badge-confirmed"><?php echo $row['reorder_count']; ?> مرة</span>
                                                <?php else: ?>
                                                    <span class="badge badge-pending">لم يُطلب</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            <?php endif; ?>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script>
function toggleDateField() {
    const reportType = document.getElementById('report').value;
    const dateField = document.getElementById('dateField');
    dateField.style.display = reportType === 'daily_sales' ? 'block' : 'none';
}
</script>

<?php require_once '../includes/footer.php'; ?>
