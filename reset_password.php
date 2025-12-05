<?php
/**
 * Reset Password Page
 * صفحة إعادة تعيين كلمة المرور
 */

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . url('index.php'));
    exit;
}

$pageTitle = 'إعادة تعيين كلمة المرور';
$message = '';
$messageType = '';
$validToken = false;
$token = $_GET['token'] ?? $_POST['token'] ?? '';

// Validate token
if (!empty($token)) {
    $hashedToken = hash('sha256', $token);
    
    try {
        // Check if password_resets table exists
        $tableExists = dbQuery("SHOW TABLES LIKE 'password_resets'");
        
        if (count($tableExists) > 0) {
            // Check token in database
            $resetRequest = dbQuerySingle(
                "SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = 0",
                [$hashedToken]
            );
            
            if ($resetRequest) {
                $validToken = true;
                $resetEmail = $resetRequest['email'];
            }
        } else {
            // Check in session (fallback for demo)
            foreach ($_SESSION as $key => $value) {
                if (strpos($key, 'password_reset_') === 0 && is_array($value)) {
                    if ($value['token'] === $hashedToken && strtotime($value['expires_at']) > time()) {
                        $validToken = true;
                        $resetEmail = str_replace('password_reset_', '', $key);
                        break;
                    }
                }
            }
        }
    } catch (Exception $e) {
        error_log("Reset password error: " . $e->getMessage());
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'خطأ في التحقق. يرجى المحاولة مرة أخرى.';
        $messageType = 'error';
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate password
        $passwordValidation = validatePasswordStrength($password);
        
        if (!$passwordValidation['valid']) {
            $message = implode('<br>', $passwordValidation['errors']);
            $messageType = 'error';
        } elseif ($password !== $confirmPassword) {
            $message = 'كلمتا المرور غير متطابقتين.';
            $messageType = 'error';
        } else {
            // Update password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            try {
                // Update customer password
                dbExecute(
                    "UPDATE customers SET password = ? WHERE email = ?",
                    [$hashedPassword, $resetEmail]
                );
                
                // Mark token as used
                $tableExists = dbQuery("SHOW TABLES LIKE 'password_resets'");
                if (count($tableExists) > 0) {
                    dbExecute(
                        "UPDATE password_resets SET used = 1 WHERE token = ?",
                        [$hashedToken]
                    );
                } else {
                    // Remove from session
                    unset($_SESSION['password_reset_' . $resetEmail]);
                }
                
                $message = 'تم تغيير كلمة المرور بنجاح! يمكنك الآن تسجيل الدخول.';
                $messageType = 'success';
                $validToken = false; // Prevent form from showing again
                
            } catch (Exception $e) {
                $message = 'حدث خطأ أثناء تغيير كلمة المرور. يرجى المحاولة مرة أخرى.';
                $messageType = 'error';
                error_log("Password update error: " . $e->getMessage());
            }
        }
    }
}

require_once 'includes/header.php';
?>

<main class="reset-password-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <span class="auth-icon">🔑</span>
                <h1>إعادة تعيين كلمة المرور</h1>
                <?php if ($validToken): ?>
                    <p>أدخل كلمة المرور الجديدة</p>
                <?php else: ?>
                    <p>إنشاء كلمة مرور جديدة لحسابك</p>
                <?php endif; ?>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($validToken): ?>
            <form method="POST" action="" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="form-group">
                    <label for="password">كلمة المرور الجديدة</label>
                    <input type="password" id="password" name="password" required 
                           minlength="8" placeholder="••••••••">
                    <small class="hint">يجب أن تحتوي على 8 أحرف على الأقل، حرف كبير، رقم، ورمز خاص</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">تأكيد كلمة المرور</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           minlength="8" placeholder="••••••••">
                </div>
                
                <div class="password-strength" id="passwordStrength">
                    <div class="strength-bar">
                        <div class="strength-fill" id="strengthFill"></div>
                    </div>
                    <span class="strength-text" id="strengthText">قوة كلمة المرور</span>
                </div>
                
                <button type="submit" class="btn btn-submit">
                    حفظ كلمة المرور الجديدة
                </button>
            </form>
            
            <?php elseif (empty($token)): ?>
                <div class="invalid-token">
                    <span class="error-icon">⚠️</span>
                    <h2>رابط غير صالح</h2>
                    <p>يرجى استخدام الرابط المرسل إلى بريدك الإلكتروني.</p>
                    <a href="<?php echo url('forgot_password.php'); ?>" class="btn btn-link">
                        طلب رابط جديد
                    </a>
                </div>
            <?php else: ?>
                <div class="invalid-token">
                    <span class="error-icon">⏰</span>
                    <h2>انتهت صلاحية الرابط</h2>
                    <p>رابط إعادة تعيين كلمة المرور غير صالح أو انتهت صلاحيته.</p>
                    <a href="<?php echo url('forgot_password.php'); ?>" class="btn btn-link">
                        طلب رابط جديد
                    </a>
                </div>
            <?php endif; ?>
            
            <div class="auth-footer">
                <p>تذكرت كلمة المرور؟ <a href="<?php echo url('login.php'); ?>">تسجيل الدخول</a></p>
            </div>
        </div>
    </div>
</main>

<style>
.reset-password-page {
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

.hint {
    display: block;
    margin-top: 8px;
    color: #666;
    font-size: 0.85rem;
}

.password-strength {
    margin-bottom: 25px;
}

.strength-bar {
    height: 6px;
    background: #e0e0e0;
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 8px;
}

.strength-fill {
    height: 100%;
    width: 0;
    background: #e74c3c;
    transition: width 0.3s, background 0.3s;
}

.strength-text {
    font-size: 0.85rem;
    color: #666;
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

.invalid-token {
    padding: 40px 30px;
    text-align: center;
}

.error-icon {
    font-size: 4rem;
    display: block;
    margin-bottom: 20px;
}

.invalid-token h2 {
    color: #333;
    margin-bottom: 15px;
}

.invalid-token p {
    color: #666;
    margin-bottom: 25px;
}

.btn-link {
    display: inline-block;
    padding: 12px 30px;
    background: linear-gradient(135deg, #006c35, #00a651);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: bold;
    transition: transform 0.3s;
}

.btn-link:hover {
    transform: translateY(-2px);
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
    margin: 0;
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

<script>
// Password strength checker
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');
    
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            let text = '';
            let color = '';
            
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            switch (true) {
                case (strength <= 2):
                    text = 'ضعيفة';
                    color = '#e74c3c';
                    break;
                case (strength <= 4):
                    text = 'متوسطة';
                    color = '#f39c12';
                    break;
                case (strength <= 5):
                    text = 'جيدة';
                    color = '#3498db';
                    break;
                default:
                    text = 'قوية';
                    color = '#27ae60';
            }
            
            const percentage = (strength / 6) * 100;
            strengthFill.style.width = percentage + '%';
            strengthFill.style.background = color;
            strengthText.textContent = text;
            strengthText.style.color = color;
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
