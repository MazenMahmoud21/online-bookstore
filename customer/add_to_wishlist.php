<?php
/**
 * Add to Wishlist API
 * إضافة إلى قائمة الأمنيات
 */

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

// Require login
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'يرجى تسجيل الدخول أولاً'
    ]);
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

// Get JSON input or POST data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$isbn = sanitizeInput($input['isbn'] ?? '');
$action = $input['action'] ?? 'toggle';

if (empty($isbn)) {
    echo json_encode([
        'success' => false,
        'message' => 'ISBN مطلوب'
    ]);
    exit;
}

$customerId = getCurrentUserId();

try {
    // Check if book exists
    $book = dbQuerySingle("SELECT isbn, title FROM books WHERE isbn = ?", [$isbn]);
    
    if (!$book) {
        echo json_encode([
            'success' => false,
            'message' => 'الكتاب غير موجود'
        ]);
        exit;
    }
    
    // Check if wishlists table exists
    $tableExists = dbQuery("SHOW TABLES LIKE 'wishlists'");
    if (count($tableExists) === 0) {
        // Create table if not exists
        dbExecute("
            CREATE TABLE IF NOT EXISTS wishlists (
                id INT AUTO_INCREMENT PRIMARY KEY,
                customer_id INT NOT NULL,
                book_isbn VARCHAR(20) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_wishlist_item (customer_id, book_isbn)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    
    // Check if already in wishlist
    $existing = dbQuerySingle(
        "SELECT id FROM wishlists WHERE customer_id = ? AND book_isbn = ?",
        [$customerId, $isbn]
    );
    
    if ($action === 'add' || ($action === 'toggle' && !$existing)) {
        if ($existing) {
            echo json_encode([
                'success' => true,
                'action' => 'already_exists',
                'message' => 'الكتاب موجود في القائمة بالفعل',
                'in_wishlist' => true
            ]);
        } else {
            dbExecute(
                "INSERT INTO wishlists (customer_id, book_isbn) VALUES (?, ?)",
                [$customerId, $isbn]
            );
            echo json_encode([
                'success' => true,
                'action' => 'added',
                'message' => 'تمت إضافة الكتاب إلى قائمة الأمنيات',
                'in_wishlist' => true
            ]);
        }
    } elseif ($action === 'remove' || ($action === 'toggle' && $existing)) {
        if ($existing) {
            dbExecute(
                "DELETE FROM wishlists WHERE customer_id = ? AND book_isbn = ?",
                [$customerId, $isbn]
            );
            echo json_encode([
                'success' => true,
                'action' => 'removed',
                'message' => 'تمت إزالة الكتاب من قائمة الأمنيات',
                'in_wishlist' => false
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'action' => 'not_found',
                'message' => 'الكتاب غير موجود في القائمة',
                'in_wishlist' => false
            ]);
        }
    } elseif ($action === 'check') {
        echo json_encode([
            'success' => true,
            'in_wishlist' => $existing ? true : false
        ]);
    }
    
} catch (Exception $e) {
    error_log("Wishlist error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'حدث خطأ. يرجى المحاولة مرة أخرى.'
    ]);
}
