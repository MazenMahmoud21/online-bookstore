<?php
/**
 * Authentication Functions
 * دوال المصادقة
 * 
 * Enhanced with security features:
 * - Secure session configuration
 * - Rate limiting
 * - Input validation
 * - Session regeneration
 */

require_once __DIR__ . '/config.php';

// Configure secure session before starting
configureSecureSession();

/**
 * Configure secure session settings
 */
function configureSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        // For HTTPS environments
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            ini_set('session.cookie_secure', 1);
        }
        
        // Set session timeout (1 hour)
        ini_set('session.gc_maxlifetime', 3600);
        
        session_start();
        
        // Check session timeout
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
            session_unset();
            session_destroy();
            session_start();
        }
        $_SESSION['last_activity'] = time();
    }
}

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
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
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
 * Login user with session regeneration
 * @param array $customer
 */
function loginUser($customer) {
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
    
    $_SESSION['customer_id'] = $customer['id'];
    $_SESSION['username'] = $customer['username'];
    $_SESSION['first_name'] = $customer['first_name'];
    $_SESSION['last_name'] = $customer['last_name'];
    $_SESSION['is_admin'] = $customer['is_admin'];
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    
    // Clear rate limit on successful login
    clearRateLimit('login', getClientIP());
}

/**
 * Logout user
 */
function logoutUser() {
    // Clear cart on logout
    if (isset($_SESSION['customer_id'])) {
        try {
            require_once __DIR__ . '/db.php';
            dbExecute("DELETE FROM shopping_cart WHERE customer_id = ?", [$_SESSION['customer_id']]);
        } catch (Exception $e) {
            // Continue logout even if cart clearing fails
            error_log("Error clearing cart on logout: " . $e->getMessage());
        }
    }
    
    // Clear session data
    $_SESSION = array();
    
    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
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
 * Get current user email
 * @return string|null
 */
function getCurrentUserEmail() {
    return $_SESSION['email'] ?? null;
}

// =============================================
// PASSWORD FUNCTIONS
// =============================================

/**
 * Hash password
 * @param string $password
 * @return string
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
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
 * Validate password strength
 * @param string $password
 * @return array ['valid' => bool, 'message' => string, 'strength' => int]
 */
function validatePasswordStrength($password) {
    $strength = 0;
    $messages = [];
    
    if (strlen($password) < 8) {
        $messages[] = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل';
    } else {
        $strength++;
    }
    
    if (strlen($password) >= 12) {
        $strength++;
    }
    
    if (preg_match('/[a-z]/', $password)) {
        $strength++;
    } else {
        $messages[] = 'يجب أن تحتوي على حروف صغيرة';
    }
    
    if (preg_match('/[A-Z]/', $password)) {
        $strength++;
    }
    
    if (preg_match('/[0-9]/', $password)) {
        $strength++;
    } else {
        $messages[] = 'يجب أن تحتوي على أرقام';
    }
    
    if (preg_match('/[^a-zA-Z0-9]/', $password)) {
        $strength++;
    }
    
    $valid = strlen($password) >= 8 && preg_match('/[a-z]/', $password) && preg_match('/[0-9]/', $password);
    
    return [
        'valid' => $valid,
        'message' => $valid ? 'كلمة المرور قوية' : implode('، ', $messages),
        'strength' => min($strength, 5)
    ];
}

// =============================================
// RATE LIMITING FUNCTIONS
// =============================================

/**
 * Check rate limit for an action
 * @param string $action (login, signup, api)
 * @param string $identifier (IP or user ID)
 * @param int $maxAttempts
 * @param int $timeWindow in seconds
 * @return array ['allowed' => bool, 'remaining' => int, 'reset_time' => int]
 */
function checkRateLimit($action, $identifier, $maxAttempts = 5, $timeWindow = 300) {
    $key = "rate_limit_{$action}_{$identifier}";
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [
            'attempts' => 0,
            'first_attempt' => time()
        ];
    }
    
    $data = &$_SESSION[$key];
    
    // Reset if time window has passed
    if (time() - $data['first_attempt'] > $timeWindow) {
        $data['attempts'] = 0;
        $data['first_attempt'] = time();
    }
    
    $remaining = max(0, $maxAttempts - $data['attempts']);
    $resetTime = $data['first_attempt'] + $timeWindow;
    
    return [
        'allowed' => $data['attempts'] < $maxAttempts,
        'remaining' => $remaining,
        'reset_time' => $resetTime,
        'wait_seconds' => max(0, $resetTime - time())
    ];
}

/**
 * Record an attempt for rate limiting
 * @param string $action
 * @param string $identifier
 */
function recordRateLimitAttempt($action, $identifier) {
    $key = "rate_limit_{$action}_{$identifier}";
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [
            'attempts' => 0,
            'first_attempt' => time()
        ];
    }
    
    $_SESSION[$key]['attempts']++;
}

/**
 * Clear rate limit for an action
 * @param string $action
 * @param string $identifier
 */
function clearRateLimit($action, $identifier) {
    $key = "rate_limit_{$action}_{$identifier}";
    unset($_SESSION[$key]);
}

/**
 * Get client IP address
 * @return string
 */
function getClientIP() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    }
    
    return filter_var($ip, FILTER_VALIDATE_IP) ?: '0.0.0.0';
}

// =============================================
// INPUT VALIDATION FUNCTIONS
// =============================================

/**
 * Sanitize input
 * @param string $data
 * @return string
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize for database (less aggressive)
 * @param string $data
 * @return string
 */
function sanitizeForDB($data) {
    return trim($data);
}

/**
 * Validate email
 * @param string $email
 * @return array ['valid' => bool, 'message' => string]
 */
function validateEmail($email) {
    $email = trim($email);
    
    if (empty($email)) {
        return ['valid' => false, 'message' => 'البريد الإلكتروني مطلوب'];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['valid' => false, 'message' => 'البريد الإلكتروني غير صالح'];
    }
    
    // Check for common disposable email domains
    $domain = strtolower(substr(strrchr($email, "@"), 1));
    $disposable = ['tempmail.com', 'throwaway.com', 'guerrillamail.com'];
    
    if (in_array($domain, $disposable)) {
        return ['valid' => false, 'message' => 'لا يمكن استخدام بريد مؤقت'];
    }
    
    return ['valid' => true, 'message' => 'البريد الإلكتروني صالح'];
}

/**
 * Validate username
 * @param string $username
 * @return array ['valid' => bool, 'message' => string]
 */
function validateUsername($username) {
    $username = trim($username);
    
    if (empty($username)) {
        return ['valid' => false, 'message' => 'اسم المستخدم مطلوب'];
    }
    
    if (strlen($username) < 3) {
        return ['valid' => false, 'message' => 'اسم المستخدم يجب أن يكون 3 أحرف على الأقل'];
    }
    
    if (strlen($username) > 50) {
        return ['valid' => false, 'message' => 'اسم المستخدم طويل جداً'];
    }
    
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        return ['valid' => false, 'message' => 'اسم المستخدم يجب أن يحتوي على حروف وأرقام فقط'];
    }
    
    return ['valid' => true, 'message' => 'اسم المستخدم صالح'];
}

/**
 * Validate phone number (Saudi format)
 * @param string $phone
 * @return array ['valid' => bool, 'message' => string, 'formatted' => string]
 */
function validatePhone($phone) {
    // Remove spaces and dashes
    $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
    
    if (empty($phone)) {
        return ['valid' => true, 'message' => 'رقم الهاتف اختياري', 'formatted' => ''];
    }
    
    // Saudi mobile: 05xxxxxxxx or +9665xxxxxxxx or 9665xxxxxxxx
    $patterns = [
        '/^05\d{8}$/',           // 05xxxxxxxx
        '/^\+9665\d{8}$/',       // +9665xxxxxxxx
        '/^9665\d{8}$/',         // 9665xxxxxxxx
        '/^5\d{8}$/',            // 5xxxxxxxx
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $phone)) {
            // Format to standard
            $formatted = preg_replace('/^\+?966/', '0', $phone);
            if (strlen($formatted) === 9) {
                $formatted = '0' . $formatted;
            }
            return ['valid' => true, 'message' => 'رقم الهاتف صالح', 'formatted' => $formatted];
        }
    }
    
    return ['valid' => false, 'message' => 'رقم الهاتف غير صالح (مثال: 05xxxxxxxx)', 'formatted' => $phone];
}

/**
 * Validate ISBN (ISBN-10 or ISBN-13)
 * @param string $isbn
 * @return array ['valid' => bool, 'message' => string, 'type' => string]
 */
function validateISBN($isbn) {
    // Remove dashes and spaces
    $isbn = preg_replace('/[\s\-]/', '', $isbn);
    
    if (empty($isbn)) {
        return ['valid' => false, 'message' => 'رقم ISBN مطلوب', 'type' => ''];
    }
    
    // ISBN-10
    if (strlen($isbn) === 10) {
        if (!preg_match('/^\d{9}[\dX]$/', $isbn)) {
            return ['valid' => false, 'message' => 'تنسيق ISBN-10 غير صالح', 'type' => 'ISBN-10'];
        }
        
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += ($i + 1) * (int)$isbn[$i];
        }
        $check = $isbn[9] === 'X' ? 10 : (int)$isbn[9];
        $sum += 10 * $check;
        
        if ($sum % 11 !== 0) {
            return ['valid' => false, 'message' => 'رقم ISBN-10 غير صالح', 'type' => 'ISBN-10'];
        }
        
        return ['valid' => true, 'message' => 'ISBN-10 صالح', 'type' => 'ISBN-10'];
    }
    
    // ISBN-13
    if (strlen($isbn) === 13) {
        if (!preg_match('/^\d{13}$/', $isbn)) {
            return ['valid' => false, 'message' => 'تنسيق ISBN-13 غير صالح', 'type' => 'ISBN-13'];
        }
        
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$isbn[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        $check = (10 - ($sum % 10)) % 10;
        
        if ($check !== (int)$isbn[12]) {
            return ['valid' => false, 'message' => 'رقم ISBN-13 غير صالح', 'type' => 'ISBN-13'];
        }
        
        return ['valid' => true, 'message' => 'ISBN-13 صالح', 'type' => 'ISBN-13'];
    }
    
    return ['valid' => false, 'message' => 'ISBN يجب أن يكون 10 أو 13 رقم', 'type' => ''];
}

/**
 * Validate price
 * @param mixed $price
 * @return array ['valid' => bool, 'message' => string, 'value' => float]
 */
function validatePrice($price) {
    $price = floatval($price);
    
    if ($price < 0) {
        return ['valid' => false, 'message' => 'السعر لا يمكن أن يكون سالباً', 'value' => 0];
    }
    
    if ($price > 99999.99) {
        return ['valid' => false, 'message' => 'السعر مرتفع جداً', 'value' => 0];
    }
    
    return ['valid' => true, 'message' => 'السعر صالح', 'value' => round($price, 2)];
}

/**
 * Validate quantity/stock
 * @param mixed $quantity
 * @param int $max
 * @return array ['valid' => bool, 'message' => string, 'value' => int]
 */
function validateQuantity($quantity, $max = 9999) {
    $quantity = intval($quantity);
    
    if ($quantity < 0) {
        return ['valid' => false, 'message' => 'الكمية لا يمكن أن تكون سالبة', 'value' => 0];
    }
    
    if ($quantity > $max) {
        return ['valid' => false, 'message' => "الكمية لا يمكن أن تتجاوز {$max}", 'value' => $max];
    }
    
    return ['valid' => true, 'message' => 'الكمية صالحة', 'value' => $quantity];
}

// =============================================
// CREDIT CARD VALIDATION
// =============================================

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
    
    // Check CVV (3-4 digits)
    if (!preg_match('/^\d{3,4}$/', $cvv)) {
        return ['valid' => false, 'message' => 'رمز CVV غير صالح'];
    }
    
    return ['valid' => true, 'message' => 'البطاقة صالحة'];
}

/**
 * Detect card type
 * @param string $cardNumber
 * @return string
 */
function detectCardType($cardNumber) {
    $cardNumber = preg_replace('/[\s-]/', '', $cardNumber);
    
    $patterns = [
        'visa' => '/^4[0-9]{12}(?:[0-9]{3})?$/',
        'mastercard' => '/^5[1-5][0-9]{14}$/',
        'amex' => '/^3[47][0-9]{13}$/',
        'mada' => '/^(4[0-9]{15}|5[0-9]{15})$/', // Saudi Mada cards
    ];
    
    foreach ($patterns as $type => $pattern) {
        if (preg_match($pattern, $cardNumber)) {
            return $type;
        }
    }
    
    return 'unknown';
}

// =============================================
// CSRF PROTECTION
// =============================================

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
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Regenerate CSRF token (use after successful form submission)
 * @return string
 */
function regenerateCSRFToken() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

/**
 * Output CSRF hidden input field
 * @return string
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

// =============================================
// ERROR HANDLING & LOGGING
// =============================================

/**
 * Log error to file
 * @param string $message
 * @param array $context
 */
function logError($message, $context = []) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/error_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : '';
    $ip = getClientIP();
    $user = getCurrentUserId() ?? 'guest';
    
    $logEntry = "[{$timestamp}] [{$ip}] [User:{$user}] {$message} {$contextStr}\n";
    
    error_log($logEntry, 3, $logFile);
}

/**
 * Log security event
 * @param string $event
 * @param array $context
 */
function logSecurityEvent($event, $context = []) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/security_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = getClientIP();
    $user = getCurrentUserId() ?? 'guest';
    $contextStr = json_encode($context, JSON_UNESCAPED_UNICODE);
    
    $logEntry = "[{$timestamp}] [{$ip}] [User:{$user}] {$event} {$contextStr}\n";
    
    error_log($logEntry, 3, $logFile);
}

// =============================================
// UTILITY FUNCTIONS
// =============================================

/**
 * Generate random token
 * @param int $length
 * @return string
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Check if request is AJAX
 * @return bool
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Send JSON response
 * @param array $data
 * @param int $statusCode
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Redirect with flash message
 * @param string $url
 * @param string $message
 * @param string $type (success, error, warning, info)
 */
function redirectWithMessage($url, $message, $type = 'info') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
    header('Location: ' . $url);
    exit;
}

/**
 * Get and clear flash message
 * @return array|null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Display flash message HTML
 * @return string
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if (!$flash) return '';
    
    $typeClass = [
        'success' => 'alert-success',
        'error' => 'alert-error',
        'warning' => 'alert-warning',
        'info' => 'alert-info'
    ];
    
    $class = $typeClass[$flash['type']] ?? 'alert-info';
    return '<div class="alert ' . $class . '">' . htmlspecialchars($flash['message']) . '</div>';
}
?>
