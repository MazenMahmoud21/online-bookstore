<?php
/**
 * Forgot Password Page
 * صفحة نسيت كلمة المرور
 */

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/email.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . url('index.php'));
    exit;
}

$pageTitle = 'نسيت كلمة المرور';
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'خطأ في التحقق. يرجى المحاولة مرة أخرى.';
        $messageType = 'error';
    } else {
        $email = sanitizeInput($_POST['email'] ?? '');
        
        // Rate limiting
        if (!checkRateLimit('password_reset', $email, 3, 3600)) {
            $message = 'تم تجاوز عدد المحاولات المسموح بها. يرجى الانتظار ساعة ثم المحاولة مرة أخرى.';
            $messageType = 'error';
        } elseif (empty($email) || !validateEmailFormat($email)) {
            $message = 'يرجى إدخال بريد إلكتروني صحيح.';
            $messageType = 'error';
        } else {
            // Check if email exists
            $customer = dbQuerySingle(
                "SELECT customer_id, first_name, email FROM customers WHERE email = ?",
                [$email]
            );
            
            // Always show success message to prevent email enumeration
            $message = 'إذا كان البريد الإلكتروني مسجلاً لدينا، سيتم إرسال رابط استعادة كلمة المرور خلال دقائق.';
            $messageType = 'success';
            
            if ($customer) {
                // Generate secure token
                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Check if password_resets table exists
                try {
                    $tableExists = dbQuery("SHOW TABLES LIKE 'password_resets'");
                    
                    if (count($tableExists) > 0) {
                        // Delete any existing tokens for this email
                        dbExecute("DELETE FROM password_resets WHERE email = ?", [$email]);
                        
                        // Insert new token
                        dbExecute(
                            "INSERT INTO password_resets (email, token, expires_at, created_at) VALUES (?, ?, ?, NOW())",
                            [$email, hash('sha256', $token), $expiresAt]
                        );
                        
                        // Send email
                        sendPasswordResetEmail($email, $customer['first_name'], $token);
                    } else {
                        // Table doesn't exist, store in session temporarily (for demo)
                        $_SESSION['password_reset_' . $email] = [
                            'token' => hash('sha256', $token),
                            'expires_at' => $expiresAt
                        ];
                        
                        // Send email
                        sendPasswordResetEmail($email, $customer['first_name'], $token);
                    }
                } catch (Exception $e) {
                    // Log error but don't expose to user
                    error_log("Password reset error: " . $e->getMessage());
                }
            }
            
            // Record the attempt
            recordLoginAttempt($email, 'password_reset', true);
        }
    }
}

require_once 'includes/header.php';
?>

<main class="forgot-password-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <span class="auth-icon">🔐</span>
                <h1>نسيت كلمة المرور</h1>
                <p>أدخل بريدك الإلكتروني وسنرسل لك رابط استعادة كلمة المرور</p>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($messageType !== 'success'): ?>
            <form method="POST" action="" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="email">البريد الإلكتروني</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="example@email.com"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <button type="submit" class="btn btn-submit">
                    إرسال رابط الاستعادة
                </button>
            </form>
            <?php endif; ?>
            
            <div class="auth-footer">
                <p>تذكرت كلمة المرور؟ <a href="<?php echo url('login.php'); ?>">تسجيل الدخول</a></p>
                <p>ليس لديك حساب؟ <a href="<?php echo url('signup.php'); ?>">إنشاء حساب جديد</a></p>
            </div>
        </div>
    </div>
</main>

<style>
.forgot-password-page {
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    background: linear-gradient(135deg, #f5f7fa, #e4e8ec);
}

.auth-container {
    width: 100%;
    max-width: 450px;
}

.auth-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    overflow: hidden;
}

.auth-header {
    background: linear-gradient(135deg, #006c35, #00a651);
    color: white;
    padding: 40px 30px;
    text-align: center;
}

.auth-icon {
    font-size: 3rem;
    display: block;
    margin-bottom: 15px;
}

.auth-header h1 {
    margin: 0 0 10px;
    font-size: 1.8rem;
}

.auth-header p {
    margin: 0;
    opacity: 0.9;
    font-size: 0.95rem;
    line-height: 1.6;
}

.auth-form {
    padding: 30px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.form-group input {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 1rem;
    transition: border-color 0.3s, box-shadow 0.3s;
}

.form-group input:focus {
    outline: none;
    border-color: #006c35;
    box-shadow: 0 0 0 4px rgba(0,108,53,0.1);
}

.btn-submit {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #006c35, #00a651);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 1.1rem;
    font-weight: bold;
    cursor: pointer;
    transition: transform 0.3s, box-shadow 0.3s;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(0,108,53,0.3);
}

.alert {
    margin: 20px 30px 0;
    padding: 15px;
    border-radius: 10px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.auth-footer {
    padding: 20px 30px;
    text-align: center;
    background: #f8f9fa;
    border-top: 1px solid #eee;
}

.auth-footer p {
    margin: 8px 0;
    color: #666;
}

.auth-footer a {
    color: #006c35;
    text-decoration: none;
    font-weight: 600;
}

.auth-footer a:hover {
    text-decoration: underline;
}
</style>

<?php require_once 'includes/footer.php'; ?>
