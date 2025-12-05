<?php
/**
 * Login Page - صفحة تسجيل الدخول
 */

require_once 'includes/db.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . url('index.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'خطأ في التحقق. الرجاء المحاولة مرة أخرى.';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'الرجاء إدخال اسم المستخدم وكلمة المرور';
        } else {
            $customer = dbQuerySingle(
                "SELECT * FROM customers WHERE username = ? OR email = ?",
                [$username, $username]
            );
            
            if ($customer && verifyPassword($password, $customer['password_hash'])) {
                loginUser($customer);
                
                // Redirect based on role
                if ($customer['is_admin']) {
                    header('Location: ' . url('admin/dashboard.php'));
                } else {
                    header('Location: ' . url('index.php'));
                }
                exit;
            } else {
                $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
            }
        }
    }
}

$pageTitle = 'تسجيل الدخول';
require_once 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <h2><i class="ph-duotone ph-lock-key"></i> تسجيل الدخول</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" id="loginForm" data-validate>
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div class="form-group">
                <label for="username">اسم المستخدم أو البريد الإلكتروني</label>
                <input type="text" id="username" name="username" class="form-control" 
                       placeholder="أدخل اسم المستخدم أو البريد الإلكتروني" required
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="أدخل كلمة المرور" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block btn-lg">تسجيل الدخول</button>
        </form>
        
        <div class="auth-footer">
            <p><a href="<?php echo url('forgot_password.php'); ?>">نسيت كلمة المرور؟</a></p>
            <p>ليس لديك حساب؟ <a href="<?php echo url('signup.php'); ?>">إنشاء حساب جديد</a></p>
        </div>
    </div>
    
    <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 8px;">
        <strong><i class="ph ph-info"></i> بيانات تجريبية للدخول:</strong>
        <p style="margin: 10px 0 5px;">
            <strong>مدير:</strong> admin / password
        </p>
        <p style="margin: 0;">
            <strong>عميل:</strong> mohammed / password
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
