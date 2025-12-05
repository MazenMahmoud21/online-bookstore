<?php
/**
 * Authentication Functions
 * دوال المصادقة
 */

require_once __DIR__ . '/config.php';
session_start();

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['customer_id']);
}

/**
 * Check if user is admin
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

/**
 * Require login - redirect to login page if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . url('login.php'));
        exit;
    }
}

/**
 * Require admin - redirect if not admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . url('index.php'));
        exit;
    }
}

/**
 * Login user
 * @param array $customer
 */
function loginUser($customer) {
    $_SESSION['customer_id'] = $customer['id'];
    $_SESSION['username'] = $customer['username'];
    $_SESSION['first_name'] = $customer['first_name'];
    $_SESSION['last_name'] = $customer['last_name'];
    $_SESSION['is_admin'] = $customer['is_admin'];
}

/**
 * Logout user
 */
function logoutUser() {
    // Clear cart on logout
    if (isset($_SESSION['customer_id'])) {
        require_once __DIR__ . '/db.php';
        dbExecute("DELETE FROM shopping_cart WHERE customer_id = ?", [$_SESSION['customer_id']]);
    }
    
    session_destroy();
    header('Location: ' . url('index.php'));
    exit;
}

/**
 * Get current user ID
 * @return int|null
 */
function getCurrentUserId() {
    return $_SESSION['customer_id'] ?? null;
}

/**
 * Get current user display name
 * @return string
 */
function getCurrentUserName() {
    if (isLoggedIn()) {
        return $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
    }
    return 'زائر';
}

/**
 * Hash password
 * @param string $password
 * @return string
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 * @param string $password
 * @param string $hash
 * @return bool
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Validate credit card (simple validation)
 * @param string $cardNumber
 * @param string $expiry
 * @param string $cvv
 * @return array ['valid' => bool, 'message' => string]
 */
function validateCreditCard($cardNumber, $expiry, $cvv) {
    // Remove spaces and dashes
    $cardNumber = preg_replace('/[\s-]/', '', $cardNumber);
    
    // Check if card number is numeric and 16 digits
    if (!preg_match('/^\d{16}$/', $cardNumber)) {
        return ['valid' => false, 'message' => 'رقم البطاقة يجب أن يكون 16 رقماً'];
    }
    
    // Luhn algorithm validation
    $sum = 0;
    $length = strlen($cardNumber);
    for ($i = 0; $i < $length; $i++) {
        $digit = (int)$cardNumber[$length - $i - 1];
        if ($i % 2 == 1) {
            $digit *= 2;
            if ($digit > 9) {
                $digit -= 9;
            }
        }
        $sum += $digit;
    }
    
    if ($sum % 10 !== 0) {
        return ['valid' => false, 'message' => 'رقم البطاقة غير صالح'];
    }
    
    // Check expiry format MM/YY
    if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiry)) {
        return ['valid' => false, 'message' => 'تاريخ الانتهاء غير صالح (MM/YY)'];
    }
    
    // Check if card is not expired
    list($month, $year) = explode('/', $expiry);
    $expYear = 2000 + (int)$year;
    $expMonth = (int)$month;
    $currentYear = (int)date('Y');
    $currentMonth = (int)date('m');
    
    if ($expYear < $currentYear || ($expYear == $currentYear && $expMonth < $currentMonth)) {
        return ['valid' => false, 'message' => 'البطاقة منتهية الصلاحية'];
    }
    
    // Check CVV (3 digits)
    if (!preg_match('/^\d{3,4}$/', $cvv)) {
        return ['valid' => false, 'message' => 'رمز CVV غير صالح'];
    }
    
    return ['valid' => true, 'message' => 'البطاقة صالحة'];
}

/**
 * Sanitize input
 * @param string $data
 * @return string
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 * @return string
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * @param string $token
 * @return bool
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
