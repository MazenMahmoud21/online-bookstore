<?php
/**
 * Submit Book Review API
 * إضافة تقييم للكتاب
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
$rating = intval($input['rating'] ?? 0);
$reviewText = sanitizeInput($input['review_text'] ?? '');

// Validation
$errors = [];

if (empty($isbn)) {
    $errors[] = 'ISBN مطلوب';
}

if ($rating < 1 || $rating > 5) {
    $errors[] = 'التقييم يجب أن يكون من 1 إلى 5';
}

if (strlen($reviewText) > 0 && mb_strlen($reviewText) < 10) {
    $errors[] = 'نص التقييم قصير جداً (10 أحرف على الأقل)';
}

if (mb_strlen($reviewText) > 1000) {
    $errors[] = 'نص التقييم طويل جداً (1000 حرف كحد أقصى)';
}

if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => implode(', ', $errors)
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
    
    // Check if customer has purchased this book (optional - remove if you want anyone to review)
    $hasPurchased = dbQuerySingle(
        "SELECT si.id FROM sales_items si
         JOIN sales s ON si.sale_id = s.id
         WHERE s.customer_id = ? AND si.book_isbn = ?",
        [$customerId, $isbn]
    );
    
    // Allow reviews even without purchase, but mark differently
    $verifiedPurchase = $hasPurchased ? 1 : 0;
    
    // Check if book_reviews table exists
    $tableExists = dbQuery("SHOW TABLES LIKE 'book_reviews'");
    if (count($tableExists) === 0) {
        // Create table if not exists
        dbExecute("
            CREATE TABLE IF NOT EXISTS book_reviews (
                id INT AUTO_INCREMENT PRIMARY KEY,
                book_isbn VARCHAR(20) NOT NULL,
                customer_id INT NOT NULL,
                rating TINYINT NOT NULL,
                review_text TEXT,
                verified_purchase TINYINT(1) DEFAULT 0,
                status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_customer_review (book_isbn, customer_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    
    // Check if already reviewed
    $existing = dbQuerySingle(
        "SELECT id, status FROM book_reviews WHERE customer_id = ? AND book_isbn = ?",
        [$customerId, $isbn]
    );
    
    if ($existing) {
        // Update existing review
        dbExecute(
            "UPDATE book_reviews SET rating = ?, review_text = ?, status = 'pending', updated_at = NOW() WHERE id = ?",
            [$rating, $reviewText, $existing['id']]
        );
        echo json_encode([
            'success' => true,
            'action' => 'updated',
            'message' => 'تم تحديث تقييمك بنجاح! سيظهر بعد المراجعة.'
        ]);
    } else {
        // Insert new review
        dbExecute(
            "INSERT INTO book_reviews (book_isbn, customer_id, rating, review_text, verified_purchase, status) VALUES (?, ?, ?, ?, ?, 'pending')",
            [$isbn, $customerId, $rating, $reviewText, $verifiedPurchase]
        );
        echo json_encode([
            'success' => true,
            'action' => 'created',
            'message' => 'شكراً لك! تم إرسال تقييمك وسيظهر بعد المراجعة.'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Review error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'حدث خطأ. يرجى المحاولة مرة أخرى.'
    ]);
}
