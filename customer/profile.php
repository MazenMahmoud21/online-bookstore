<?php
/**
 * Customer Profile Page - صفحة الملف الشخصي
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();
if (isAdmin()) {
    header('Location: ' . url('admin/dashboard.php'));
    exit;
}

$customerId = getCurrentUserId();
$success = '';
$error = '';

// Get customer data
$customer = dbQuerySingle("SELECT * FROM customers WHERE id = ?", [$customerId]);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $firstName = sanitize($_POST['first_name'] ?? '');
        $lastName = sanitize($_POST['last_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        
        if (empty($firstName) || empty($lastName) || empty($email)) {
            $error = 'الرجاء ملء جميع الحقول المطلوبة';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'البريد الإلكتروني غير صالح';
        } else {
            // Check if email is used by another customer
            $existingEmail = dbQuerySingle(
                "SELECT id FROM customers WHERE email = ? AND id != ?",
                [$email, $customerId]
            );
            
            if ($existingEmail) {
                $error = 'البريد الإلكتروني مستخدم بالفعل';
            } else {
                dbExecute(
                    "UPDATE customers SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ? WHERE id = ?",
                    [$firstName, $lastName, $email, $phone, $address, $customerId]
                );
                
                $_SESSION['first_name'] = $firstName;
                $_SESSION['last_name'] = $lastName;
                
                $success = 'تم تحديث البيانات بنجاح';
                $customer = dbQuerySingle("SELECT * FROM customers WHERE id = ?", [$customerId]);
            }
        }
    } elseif ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'الرجاء ملء جميع الحقول';
        } elseif (!verifyPassword($currentPassword, $customer['password_hash'])) {
            $error = 'كلمة المرور الحالية غير صحيحة';
        } elseif (strlen($newPassword) < 6) {
            $error = 'كلمة المرور الجديدة يجب أن تكون 6 أحرف على الأقل';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'كلمات المرور غير متطابقة';
        } else {
            dbExecute(
                "UPDATE customers SET password_hash = ? WHERE id = ?",
                [hashPassword($newPassword), $customerId]
            );
            $success = 'تم تغيير كلمة المرور بنجاح';
        }
    }
}

$pageTitle = 'الملف الشخصي';
require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>👤 الملف الشخصي</h1>
    <p>إدارة بيانات حسابك</p>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
    <!-- Profile Info -->
    <div class="card">
        <div class="card-header">
            <h3><i class="ph ph-user-circle"></i> البيانات الشخصية</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_profile">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="first_name">الاسم الأول *</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" required
                               value="<?php echo htmlspecialchars($customer['first_name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">اسم العائلة *</label>
                        <input type="text" id="last_name" name="last_name" class="form-control" required
                               value="<?php echo htmlspecialchars($customer['last_name']); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="username">اسم المستخدم</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($customer['username']); ?>" readonly>
                    <small class="form-hint">لا يمكن تغيير اسم المستخدم</small>
                </div>
                
                <div class="form-group">
                    <label for="email">البريد الإلكتروني *</label>
                    <input type="email" id="email" name="email" class="form-control" required
                           value="<?php echo htmlspecialchars($customer['email']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">رقم الجوال</label>
                    <input type="tel" id="phone" name="phone" class="form-control"
                           value="<?php echo htmlspecialchars($customer['phone']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="address">العنوان</label>
                    <textarea id="address" name="address" class="form-control"><?php echo htmlspecialchars($customer['address']); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
            </form>
        </div>
    </div>
    
    <!-- Change Password -->
    <div class="card">
        <div class="card-header">
            <h3>🔒 تغيير كلمة المرور</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <input type="hidden" name="action" value="change_password">
                
                <div class="form-group">
                    <label for="current_password">كلمة المرور الحالية *</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">كلمة المرور الجديدة *</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                    <small class="form-hint">6 أحرف على الأقل</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">تأكيد كلمة المرور الجديدة *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary">تغيير كلمة المرور</button>
            </form>
        </div>
    </div>
</div>

<!-- Account Stats -->
<div class="card" style="margin-top: 30px;">
    <div class="card-header">
        <h3>📊 إحصائيات الحساب</h3>
    </div>
    <div class="card-body">
        <?php
        $stats = dbQuerySingle(
            "SELECT 
                COUNT(*) as order_count,
                COALESCE(SUM(total_amount), 0) as total_spent
             FROM sales WHERE customer_id = ?",
            [$customerId]
        );
        ?>
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="icon"><i class="ph-duotone ph-package"></i></div>
                <div class="value"><?php echo $stats['order_count']; ?></div>
                <div class="label">طلب مكتمل</div>
            </div>
            <div class="stat-card">
                <div class="icon">💰</div>
                <div class="value"><?php echo number_format($stats['total_spent'], 2); ?></div>
                <div class="label">إجمالي المشتريات (ريال)</div>
            </div>
            <div class="stat-card">
                <div class="icon">📅</div>
                <div class="value"><?php echo date('Y/m/d', strtotime($customer['created_at'])); ?></div>
                <div class="label">تاريخ التسجيل</div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
