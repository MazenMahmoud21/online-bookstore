<?php
/**
 * Add Book - إضافة كتاب جديد
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$error = '';
$formData = [
    'isbn' => '',
    'title' => '',
    'authors' => '',
    'publisher_id' => '',
    'year' => date('Y'),
    'price' => '',
    'category' => '',
    'stock' => 0,
    'threshold' => 5,
    'description' => ''
];

// Get publishers
$publishers = dbQuery("SELECT id, name FROM publishers ORDER BY name");

// Get existing categories
$categories = dbQuery("SELECT DISTINCT category FROM books ORDER BY category");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'isbn' => sanitize($_POST['isbn'] ?? ''),
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
    if (empty($formData['isbn']) || empty($formData['title']) || empty($formData['authors'])) {
        $error = 'Please fill in all required fields';
    } elseif ($formData['price'] <= 0) {
        $error = 'Price must be greater than zero';
    } else {
        // Check if ISBN exists
        $existing = dbQuerySingle("SELECT isbn FROM books WHERE isbn = ?", [$formData['isbn']]);
        
        if ($existing) {
            $error = 'This ISBN already exists';
        } else {
            try {
                dbExecute(
                    "INSERT INTO books (isbn, title, authors, publisher_id, year, price, category, stock, threshold, description) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $formData['isbn'],
                        $formData['title'],
                        $formData['authors'],
                        $formData['publisher_id'] ?: null,
                        $formData['year'],
                        $formData['price'],
                        $formData['category'],
                        $formData['stock'],
                        $formData['threshold'],
                        $formData['description']
                    ]
                );
                
                header('Location: ' . url('admin/books.php?added=1'));
                exit;
            } catch (PDOException $e) {
                $error = 'An error occurred while adding the book';
            }
        }
    }
}

$pageTitle = 'Add New Book';
require_once '../includes/header.php';
?>

<div class="admin-layout">
    <?php require_once '../includes/admin_sidebar.php'; ?>
    
    <main>
        <div class="page-header">
            <h1>
                <span style="vertical-align: middle; margin-right: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#006c35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                </span>
                Add New Book
            </h1>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <form method="POST" action="">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label for="isbn">ISBN *</label>
                            <input type="text" id="isbn" name="isbn" class="form-control" required
                                   placeholder="978-XXXX-XX-XXX"
                                   value="<?php echo htmlspecialchars($formData['isbn']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="title">Book Title *</label>
                            <input type="text" id="title" name="title" class="form-control" required
                                   value="<?php echo htmlspecialchars($formData['title']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="authors">Author *</label>
                            <input type="text" id="authors" name="authors" class="form-control" required
                                   value="<?php echo htmlspecialchars($formData['authors']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="publisher_id">Publisher</label>
                            <select id="publisher_id" name="publisher_id" class="form-control">
                                <option value="">-- Select Publisher --</option>
                                <?php foreach ($publishers as $pub): ?>
                                    <option value="<?php echo $pub['id']; ?>" 
                                            <?php echo $formData['publisher_id'] == $pub['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($pub['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="category">Category</label>
                            <input type="text" id="category" name="category" class="form-control" list="categories"
                                   value="<?php echo htmlspecialchars($formData['category']); ?>">
                            <datalist id="categories">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['category']); ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        
                        <div class="form-group">
                            <label for="year">Publication Year</label>
                            <input type="number" id="year" name="year" class="form-control" 
                                   min="1900" max="<?php echo date('Y'); ?>"
                                   value="<?php echo $formData['year']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Price (EGP) *</label>
                            <input type="number" id="price" name="price" class="form-control" required
                                   min="0" step="0.01"
                                   value="<?php echo $formData['price']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="stock">Available Quantity</label>
                            <input type="number" id="stock" name="stock" class="form-control" min="0"
                                   value="<?php echo $formData['stock']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="threshold">Minimum Stock Level</label>
                            <input type="number" id="threshold" name="threshold" class="form-control" min="1"
                                   value="<?php echo $formData['threshold']; ?>">
                            <small class="form-hint">A supply order will be automatically created when stock falls below this level</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Book Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($formData['description']); ?></textarea>
                    </div>
                    
                    <div style="display: flex; gap: 15px;">
                        <button type="submit" class="btn btn-primary btn-lg">Save Book</button>
                        <a href="/admin/books.php" class="btn btn-secondary btn-lg">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
