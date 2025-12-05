<?php
/**
 * Signup Page - صفحة إنشاء حساب جديد
 */

require_once 'includes/db.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /index.php');
    exit;
}

$error = '';
$success = '';
$formData = [
    'username' => '',
    'email' => '',
    'first_name' => '',
    'last_name' => '',
    'phone' => '',
    'address' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'username' => sanitize($_POST['username'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'first_name' => sanitize($_POST['first_name'] ?? ''),
        'last_name' => sanitize($_POST['last_name'] ?? ''),
        'phone' => sanitize($_POST['phone'] ?? ''),
        'address' => sanitize($_POST['address'] ?? '')
    ];
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    // Validation
    if (empty($formData['username']) || empty($formData['email']) || 
        empty($formData['first_name']) || empty($formData['last_name']) || empty($password)) {
        $error = 'الرجاء ملء جميع الحقول المطلوبة';
    } elseif (strlen($formData['username']) < 3) {
        $error = 'اسم المستخدم يجب أن يكون 3 أحرف على الأقل';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'البريد الإلكتروني غير صالح';
    } elseif (strlen($password) < 6) {
        $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    } elseif ($password !== $password_confirm) {
        $error = 'كلمات المرور غير متطابقة';
    } else {
        // Check if username exists
        $existing = dbQuerySingle(
            "SELECT id FROM customers WHERE username = ?",
            [$formData['username']]
        );
        
        if ($existing) {
            $error = 'اسم المستخدم مستخدم بالفعل';
        } else {
            // Check if email exists
            $existing = dbQuerySingle(
                "SELECT id FROM customers WHERE email = ?",
                [$formData['email']]
            );
            
            if ($existing) {
                $error = 'البريد الإلكتروني مستخدم بالفعل';
            } else {
                // Create account
                try {
                    dbExecute(
                        "INSERT INTO customers (username, password_hash, first_name, last_name, email, phone, address) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)",
                        [
                            $formData['username'],
                            hashPassword($password),
                            $formData['first_name'],
                            $formData['last_name'],
                            $formData['email'],
                            $formData['phone'],
                            $formData['address']
                        ]
                    );
                    
                    $success = 'تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول.';
                    $formData = [
                        'username' => '',
                        'email' => '',
                        'first_name' => '',
                        'last_name' => '',
                        'phone' => '',
                        'address' => ''
                    ];
                } catch (PDOException $e) {
                    $error = 'حدث خطأ أثناء إنشاء الحساب. الرجاء المحاولة مرة أخرى.';
                }
            }
        }
    }
}

$pageTitle = 'إنشاء حساب جديد';
require_once 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>📝 إنشاء حساب جديد</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
                <br><a href="/login.php">تسجيل الدخول الآن</a>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="signupForm" data-validate>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label for="first_name">الاسم الأول *</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" 
                           placeholder="أدخل الاسم الأول" required
                           value="<?php echo htmlspecialchars($formData['first_name']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="last_name">اسم العائلة *</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" 
                           placeholder="أدخل اسم العائلة" required
                           value="<?php echo htmlspecialchars($formData['last_name']); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="username">اسم المستخدم *</label>
                <input type="text" id="username" name="username" class="form-control" 
                       placeholder="أدخل اسم المستخدم (3 أحرف على الأقل)" required
                       value="<?php echo htmlspecialchars($formData['username']); ?>">
            </div>
            
            <div class="form-group">
                <label for="email">البريد الإلكتروني *</label>
                <input type="email" id="email" name="email" class="form-control" 
                       placeholder="example@email.com" required
                       value="<?php echo htmlspecialchars($formData['email']); ?>">
            </div>
            
            <div class="form-group">
                <label for="phone">رقم الجوال</label>
                <input type="tel" id="phone" name="phone" class="form-control" 
                       placeholder="05XXXXXXXX"
                       value="<?php echo htmlspecialchars($formData['phone']); ?>">
            </div>
            
            <div class="form-group">
                <label for="address">العنوان</label>
                <textarea id="address" name="address" class="form-control" 
                          placeholder="المدينة، الحي، الشارع"><?php echo htmlspecialchars($formData['address']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="password">كلمة المرور *</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="6 أحرف على الأقل" required>
            </div>
            
            <div class="form-group">
                <label for="password_confirm">تأكيد كلمة المرور *</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-control" 
                       placeholder="أعد إدخال كلمة المرور" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block btn-lg">إنشاء الحساب</button>
        </form>
        
        <div class="auth-footer">
            <p>لديك حساب بالفعل؟ <a href="/login.php">تسجيل الدخول</a></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
