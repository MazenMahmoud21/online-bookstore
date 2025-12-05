<?php
/**
 * Admin Publishers - إدارة الناشرين
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$success = '';
$error = '';

// Handle add publisher
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = sanitize($_POST['name'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        
        if (empty($name)) {
            $error = 'الرجاء إدخال اسم دار النشر';
        } else {
            dbExecute(
                "INSERT INTO publishers (name, address, phone) VALUES (?, ?, ?)",
                [$name, $address, $phone]
            );
            $success = 'تمت إضافة دار النشر بنجاح';
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        dbExecute("DELETE FROM publishers WHERE id = ?", [$id]);
        $success = 'تم حذف دار النشر بنجاح';
    }
}

// Get publishers with book count
$publishers = dbQuery("
    SELECT p.*, COUNT(b.isbn) as book_count 
    FROM publishers p 
    LEFT JOIN books b ON p.id = b.publisher_id 
    GROUP BY p.id 
    ORDER BY p.name
");

$pageTitle = 'إدارة الناشرين';
require_once '../includes/header.php';
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <h3>⚙️ الإدارة</h3>
        <ul class="admin-nav">
            <li><a href="/admin/dashboard.php">📊 لوحة التحكم</a></li>
            <li><a href="/admin/books.php">📚 إدارة الكتب</a></li>
            <li><a href="/admin/add_book.php">➕ إضافة كتاب</a></li>
            <li><a href="/admin/publishers.php" class="active">🏢 الناشرين</a></li>
            <li><a href="/admin/view_orders.php">📦 طلبات التوريد</a></li>
            <li><a href="/admin/customers.php">👥 العملاء</a></li>
            <li><a href="/admin/sales.php">💰 المبيعات</a></li>
            <li><a href="/admin/reports.php">📈 التقارير</a></li>
        </ul>
    </aside>
    
    <main>
        <div class="page-header">
            <h1>🏢 إدارة الناشرين</h1>
            <p>إضافة وإدارة دور النشر</p>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px;">
            <!-- Publishers List -->
            <div class="card">
                <div class="card-header">
                    <h3>دور النشر (<?php echo count($publishers); ?>)</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>الاسم</th>
                                <th>العنوان</th>
                                <th>الهاتف</th>
                                <th>عدد الكتب</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($publishers)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 30px;">
                                        لا توجد دور نشر مسجلة
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($publishers as $pub): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($pub['name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($pub['address'] ?: '-'); ?></td>
                                        <td><?php echo htmlspecialchars($pub['phone'] ?: '-'); ?></td>
                                        <td><?php echo $pub['book_count']; ?></td>
                                        <td>
                                            <?php if ($pub['book_count'] == 0): ?>
                                                <form method="POST" action="" style="display: inline;" 
                                                      onsubmit="return confirm('هل تريد حذف هذه الدار؟');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $pub['id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                                                </form>
                                            <?php else: ?>
                                                <small style="color: var(--text-light);">لا يمكن الحذف</small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Add Publisher Form -->
            <div class="card">
                <div class="card-header">
                    <h3>➕ إضافة دار نشر</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-group">
                            <label for="name">اسم دار النشر *</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">العنوان</label>
                            <textarea id="address" name="address" class="form-control" rows="2"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">رقم الهاتف</label>
                            <input type="tel" id="phone" name="phone" class="form-control">
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">إضافة</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
