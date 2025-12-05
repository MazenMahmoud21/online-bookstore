<?php
/**
 * Update Book - تعديل كتاب
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$isbn = isset($_GET['isbn']) ? sanitize($_GET['isbn']) : '';

if (empty($isbn)) {
    header('Location: /admin/books.php');
    exit;
}

// Get book
$book = dbQuerySingle("SELECT * FROM books WHERE isbn = ?", [$isbn]);

if (!$book) {
    header('Location: /admin/books.php');
    exit;
}

$error = '';
$success = '';

// Get publishers
$publishers = dbQuery("SELECT id, name FROM publishers ORDER BY name");

// Get existing categories
$categories = dbQuery("SELECT DISTINCT category FROM books ORDER BY category");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'title' => sanitize($_POST['title'] ?? ''),
        'authors' => sanitize($_POST['authors'] ?? ''),
        'publisher_id' => intval($_POST['publisher_id'] ?? 0),
        'year' => intval($_POST['year'] ?? date('Y')),
        'price' => floatval($_POST['price'] ?? 0),
        'category' => sanitize($_POST['category'] ?? ''),
        'stock' => intval($_POST['stock'] ?? 0),
        'threshold' => intval($_POST['threshold'] ?? 5),
        'description' => sanitize($_POST['description'] ?? '')
    ];
    
    // Validation
    if (empty($formData['title']) || empty($formData['authors'])) {
        $error = 'الرجاء ملء جميع الحقول المطلوبة';
    } elseif ($formData['price'] <= 0) {
        $error = 'السعر يجب أن يكون أكبر من صفر';
    } else {
        try {
            dbExecute(
                "UPDATE books SET 
                    title = ?, 
                    authors = ?, 
                    publisher_id = ?, 
                    year = ?, 
                    price = ?, 
                    category = ?, 
                    stock = ?, 
                    threshold = ?, 
                    description = ? 
                 WHERE isbn = ?",
                [
                    $formData['title'],
                    $formData['authors'],
                    $formData['publisher_id'] ?: null,
                    $formData['year'],
                    $formData['price'],
                    $formData['category'],
                    $formData['stock'],
                    $formData['threshold'],
                    $formData['description'],
                    $isbn
                ]
            );
            
            header('Location: /admin/books.php?updated=1');
            exit;
        } catch (PDOException $e) {
            $error = 'حدث خطأ أثناء تحديث الكتاب';
        }
    }
    
    $book = array_merge($book, $formData);
}

$pageTitle = 'تعديل كتاب';
require_once '../includes/header.php';
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <h3>⚙️ الإدارة</h3>
        <ul class="admin-nav">
            <li><a href="/admin/dashboard.php">📊 لوحة التحكم</a></li>
            <li><a href="/admin/books.php" class="active">📚 إدارة الكتب</a></li>
            <li><a href="/admin/add_book.php">➕ إضافة كتاب</a></li>
            <li><a href="/admin/publishers.php">🏢 الناشرين</a></li>
            <li><a href="/admin/view_orders.php">📦 طلبات التوريد</a></li>
            <li><a href="/admin/customers.php">👥 العملاء</a></li>
            <li><a href="/admin/sales.php">💰 المبيعات</a></li>
            <li><a href="/admin/reports.php">📈 التقارير</a></li>
        </ul>
    </aside>
    
    <main>
        <div class="page-header">
            <h1>✏️ تعديل كتاب</h1>
            <p>ISBN: <?php echo htmlspecialchars($isbn); ?></p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <form method="POST" action="">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>ISBN</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($isbn); ?>" readonly>
                            <small class="form-hint">لا يمكن تغيير ISBN</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="title">عنوان الكتاب *</label>
                            <input type="text" id="title" name="title" class="form-control" required
                                   value="<?php echo htmlspecialchars($book['title']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="authors">المؤلف *</label>
                            <input type="text" id="authors" name="authors" class="form-control" required
                                   value="<?php echo htmlspecialchars($book['authors']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="publisher_id">دار النشر</label>
                            <select id="publisher_id" name="publisher_id" class="form-control">
                                <option value="">-- اختر دار النشر --</option>
                                <?php foreach ($publishers as $pub): ?>
                                    <option value="<?php echo $pub['id']; ?>" 
                                            <?php echo $book['publisher_id'] == $pub['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($pub['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="category">التصنيف</label>
                            <input type="text" id="category" name="category" class="form-control" list="categories"
                                   value="<?php echo htmlspecialchars($book['category']); ?>">
                            <datalist id="categories">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['category']); ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        
                        <div class="form-group">
                            <label for="year">سنة النشر</label>
                            <input type="number" id="year" name="year" class="form-control" 
                                   min="1900" max="<?php echo date('Y'); ?>"
                                   value="<?php echo $book['year']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="price">السعر (ريال) *</label>
                            <input type="number" id="price" name="price" class="form-control" required
                                   min="0" step="0.01"
                                   value="<?php echo $book['price']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="stock">الكمية المتوفرة</label>
                            <input type="number" id="stock" name="stock" class="form-control" min="0"
                                   value="<?php echo $book['stock']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="threshold">الحد الأدنى للمخزون</label>
                            <input type="number" id="threshold" name="threshold" class="form-control" min="1"
                                   value="<?php echo $book['threshold']; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">وصف الكتاب</label>
                        <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($book['description']); ?></textarea>
                    </div>
                    
                    <div style="display: flex; gap: 15px;">
                        <button type="submit" class="btn btn-primary btn-lg">حفظ التغييرات</button>
                        <a href="/admin/books.php" class="btn btn-secondary btn-lg">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
