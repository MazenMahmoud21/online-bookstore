<?php
/**
 * Admin Coupons Management
 * إدارة الكوبونات - لوحة المدير
 */

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Require admin
requireAdmin();

$pageTitle = 'إدارة الكوبونات';
$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'خطأ في التحقق.';
        $messageType = 'error';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'add' || $action === 'edit') {
            $couponId = intval($_POST['coupon_id'] ?? 0);
            $code = strtoupper(sanitizeInput($_POST['code'] ?? ''));
            $description = sanitizeInput($_POST['description'] ?? '');
            $discountType = $_POST['discount_type'] ?? 'percentage';
            $discountValue = floatval($_POST['discount_value'] ?? 0);
            $minOrderAmount = floatval($_POST['min_order_amount'] ?? 0);
            $maxDiscount = !empty($_POST['max_discount']) ? floatval($_POST['max_discount']) : null;
            $usageLimit = !empty($_POST['usage_limit']) ? intval($_POST['usage_limit']) : null;
            $startDate = $_POST['start_date'] ?? date('Y-m-d');
            $endDate = $_POST['end_date'] ?? date('Y-m-d', strtotime('+1 month'));
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            
            // Validation
            $errors = [];
            if (empty($code) || strlen($code) < 3) {
                $errors[] = 'كود الكوبون مطلوب (3 أحرف على الأقل)';
            }
            if ($discountValue <= 0) {
                $errors[] = 'قيمة الخصم يجب أن تكون أكبر من صفر';
            }
            if ($discountType === 'percentage' && $discountValue > 100) {
                $errors[] = 'نسبة الخصم لا يمكن أن تتجاوز 100%';
            }
            if (strtotime($endDate) < strtotime($startDate)) {
                $errors[] = 'تاريخ الانتهاء يجب أن يكون بعد تاريخ البدء';
            }
            
            // Check for duplicate code
            $existing = dbQuerySingle(
                "SELECT id FROM coupons WHERE code = ? AND id != ?",
                [$code, $couponId]
            );
            if ($existing) {
                $errors[] = 'هذا الكود مستخدم بالفعل';
            }
            
            if (empty($errors)) {
                try {
                    if ($action === 'add') {
                        dbExecute(
                            "INSERT INTO coupons (code, description, discount_type, discount_value, min_order_amount, max_discount, usage_limit, start_date, end_date, is_active)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                            [$code, $description, $discountType, $discountValue, $minOrderAmount, $maxDiscount, $usageLimit, $startDate, $endDate, $isActive]
                        );
                        $message = 'تم إضافة الكوبون بنجاح.';
                    } else {
                        dbExecute(
                            "UPDATE coupons SET code = ?, description = ?, discount_type = ?, discount_value = ?, min_order_amount = ?, max_discount = ?, usage_limit = ?, start_date = ?, end_date = ?, is_active = ?
                             WHERE id = ?",
                            [$code, $description, $discountType, $discountValue, $minOrderAmount, $maxDiscount, $usageLimit, $startDate, $endDate, $isActive, $couponId]
                        );
                        $message = 'تم تحديث الكوبون بنجاح.';
                    }
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'حدث خطأ: ' . $e->getMessage();
                    $messageType = 'error';
                }
            } else {
                $message = implode('<br>', $errors);
                $messageType = 'error';
            }
        } elseif ($action === 'delete') {
            $couponId = intval($_POST['coupon_id'] ?? 0);
            if ($couponId > 0) {
                try {
                    dbExecute("DELETE FROM coupons WHERE id = ?", [$couponId]);
                    $message = 'تم حذف الكوبون.';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'حدث خطأ: ' . $e->getMessage();
                    $messageType = 'error';
                }
            }
        } elseif ($action === 'toggle') {
            $couponId = intval($_POST['coupon_id'] ?? 0);
            if ($couponId > 0) {
                try {
                    dbExecute("UPDATE coupons SET is_active = NOT is_active WHERE id = ?", [$couponId]);
                    $message = 'تم تحديث حالة الكوبون.';
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = 'حدث خطأ: ' . $e->getMessage();
                    $messageType = 'error';
                }
            }
        }
    }
}

// Create table if not exists
try {
    $tableExists = dbQuery("SHOW TABLES LIKE 'coupons'");
    if (count($tableExists) === 0) {
        dbExecute("
            CREATE TABLE IF NOT EXISTS coupons (
                id INT AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(50) NOT NULL UNIQUE,
                description VARCHAR(255),
                discount_type ENUM('percentage', 'fixed') NOT NULL,
                discount_value DECIMAL(10, 2) NOT NULL,
                min_order_amount DECIMAL(10, 2) DEFAULT 0,
                max_discount DECIMAL(10, 2) DEFAULT NULL,
                usage_limit INT DEFAULT NULL,
                used_count INT DEFAULT 0,
                start_date DATE NOT NULL,
                end_date DATE NOT NULL,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
} catch (Exception $e) {}

// Get coupons
$coupons = dbQuery("SELECT * FROM coupons ORDER BY created_at DESC");

// Get coupon for edit
$editCoupon = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $editCoupon = dbQuerySingle("SELECT * FROM coupons WHERE id = ?", [$editId]);
}

require_once '../includes/header.php';
?>

<div class="admin-layout">
    <?php include '../includes/admin_sidebar.php'; ?>
    
    <main class="admin-main">
        <div class="admin-header">
            <h1>🎟️ إدارة الكوبونات</h1>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="content-grid">
            <!-- Add/Edit Form -->
            <div class="form-section">
                <h2><?php echo $editCoupon ? 'تعديل كوبون' : 'إضافة كوبون جديد'; ?></h2>
                
                <form method="POST" class="coupon-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="<?php echo $editCoupon ? 'edit' : 'add'; ?>">
                    <?php if ($editCoupon): ?>
                        <input type="hidden" name="coupon_id" value="<?php echo $editCoupon['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="code">كود الكوبون *</label>
                        <input type="text" id="code" name="code" required
                               value="<?php echo htmlspecialchars($editCoupon['code'] ?? ''); ?>"
                               placeholder="مثال: SAVE20" style="text-transform: uppercase;">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">الوصف</label>
                        <input type="text" id="description" name="description"
                               value="<?php echo htmlspecialchars($editCoupon['description'] ?? ''); ?>"
                               placeholder="خصم 20% على جميع الكتب">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="discount_type">نوع الخصم *</label>
                            <select id="discount_type" name="discount_type" required>
                                <option value="percentage" <?php echo ($editCoupon['discount_type'] ?? '') === 'percentage' ? 'selected' : ''; ?>>نسبة مئوية (%)</option>
                                <option value="fixed" <?php echo ($editCoupon['discount_type'] ?? '') === 'fixed' ? 'selected' : ''; ?>>مبلغ ثابت (ر.س)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="discount_value">قيمة الخصم *</label>
                            <input type="number" id="discount_value" name="discount_value" required
                                   step="0.01" min="0"
                                   value="<?php echo $editCoupon['discount_value'] ?? ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="min_order_amount">الحد الأدنى للطلب</label>
                            <input type="number" id="min_order_amount" name="min_order_amount"
                                   step="0.01" min="0"
                                   value="<?php echo $editCoupon['min_order_amount'] ?? '0'; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="max_discount">الحد الأقصى للخصم</label>
                            <input type="number" id="max_discount" name="max_discount"
                                   step="0.01" min="0"
                                   value="<?php echo $editCoupon['max_discount'] ?? ''; ?>"
                                   placeholder="اتركه فارغاً للامحدود">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="usage_limit">عدد مرات الاستخدام</label>
                        <input type="number" id="usage_limit" name="usage_limit"
                               min="1"
                               value="<?php echo $editCoupon['usage_limit'] ?? ''; ?>"
                               placeholder="اتركه فارغاً للامحدود">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="start_date">تاريخ البدء *</label>
                            <input type="date" id="start_date" name="start_date" required
                                   value="<?php echo $editCoupon['start_date'] ?? date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="end_date">تاريخ الانتهاء *</label>
                            <input type="date" id="end_date" name="end_date" required
                                   value="<?php echo $editCoupon['end_date'] ?? date('Y-m-d', strtotime('+1 month')); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" name="is_active" value="1"
                                   <?php echo (!$editCoupon || $editCoupon['is_active']) ? 'checked' : ''; ?>>
                            الكوبون نشط
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $editCoupon ? 'تحديث الكوبون' : 'إضافة الكوبون'; ?>
                        </button>
                        <?php if ($editCoupon): ?>
                            <a href="coupons.php" class="btn btn-secondary">إلغاء</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <!-- Coupons List -->
            <div class="list-section">
                <h2>قائمة الكوبونات</h2>
                
                <?php if (empty($coupons)): ?>
                    <div class="empty-state">
                        <span class="empty-icon">🎟️</span>
                        <p>لا توجد كوبونات حتى الآن.</p>
                    </div>
                <?php else: ?>
                    <div class="coupons-list">
                        <?php foreach ($coupons as $coupon): 
                            $isExpired = strtotime($coupon['end_date']) < time();
                            $isNotStarted = strtotime($coupon['start_date']) > time();
                            $isLimitReached = $coupon['usage_limit'] && $coupon['used_count'] >= $coupon['usage_limit'];
                        ?>
                            <div class="coupon-card <?php echo !$coupon['is_active'] ? 'inactive' : ($isExpired ? 'expired' : ''); ?>">
                                <div class="coupon-header">
                                    <span class="coupon-code"><?php echo htmlspecialchars($coupon['code']); ?></span>
                                    <div class="coupon-badges">
                                        <?php if (!$coupon['is_active']): ?>
                                            <span class="badge inactive">غير نشط</span>
                                        <?php elseif ($isExpired): ?>
                                            <span class="badge expired">منتهي</span>
                                        <?php elseif ($isNotStarted): ?>
                                            <span class="badge pending">لم يبدأ</span>
                                        <?php elseif ($isLimitReached): ?>
                                            <span class="badge used">استنفد</span>
                                        <?php else: ?>
                                            <span class="badge active">نشط</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if ($coupon['description']): ?>
                                    <p class="coupon-description"><?php echo htmlspecialchars($coupon['description']); ?></p>
                                <?php endif; ?>
                                
                                <div class="coupon-details">
                                    <div class="detail">
                                        <span class="label">الخصم:</span>
                                        <span class="value">
                                            <?php 
                                            if ($coupon['discount_type'] === 'percentage') {
                                                echo $coupon['discount_value'] . '%';
                                            } else {
                                                echo number_format($coupon['discount_value'], 2) . ' ر.س';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    <div class="detail">
                                        <span class="label">الحد الأدنى:</span>
                                        <span class="value"><?php echo number_format($coupon['min_order_amount'], 2); ?> ر.س</span>
                                    </div>
                                    <div class="detail">
                                        <span class="label">الاستخدام:</span>
                                        <span class="value">
                                            <?php echo $coupon['used_count']; ?>
                                            <?php if ($coupon['usage_limit']): ?>
                                                / <?php echo $coupon['usage_limit']; ?>
                                            <?php else: ?>
                                                (غير محدود)
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="detail">
                                        <span class="label">الصلاحية:</span>
                                        <span class="value">
                                            <?php echo date('d/m/Y', strtotime($coupon['start_date'])); ?>
                                            -
                                            <?php echo date('d/m/Y', strtotime($coupon['end_date'])); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="coupon-actions">
                                    <a href="?edit=<?php echo $coupon['id']; ?>" class="btn btn-edit">✏️ تعديل</a>
                                    
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="coupon_id" value="<?php echo $coupon['id']; ?>">
                                        <button type="submit" class="btn btn-toggle">
                                            <?php echo $coupon['is_active'] ? '⏸️ تعطيل' : '▶️ تفعيل'; ?>
                                        </button>
                                    </form>
                                    
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="coupon_id" value="<?php echo $coupon['id']; ?>">
                                        <button type="submit" class="btn btn-delete">🗑️ حذف</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<style>
.admin-layout {
    display: flex;
    min-height: calc(100vh - 60px);
}

.admin-sidebar {
    width: 250px;
    background: #1a1a2e;
    color: white;
}

.admin-nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.admin-nav a {
    display: block;
    padding: 15px 20px;
    color: #ccc;
    text-decoration: none;
    transition: background 0.3s;
}

.admin-nav a:hover,
.admin-nav a.active {
    background: #006c35;
    color: white;
}

.admin-main {
    flex: 1;
    padding: 30px;
    background: #f5f7fa;
}

.admin-header h1 {
    margin: 0 0 25px;
    color: #333;
}

.content-grid {
    display: grid;
    grid-template-columns: 400px 1fr;
    gap: 30px;
}

.form-section,
.list-section {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.form-section h2,
.list-section h2 {
    margin: 0 0 20px;
    color: #333;
    font-size: 1.2rem;
}

.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #444;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 0.95rem;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #006c35;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.checkbox-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.checkbox-group input[type="checkbox"] {
    width: auto;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.95rem;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    transition: all 0.3s;
}

.btn-primary {
    background: linear-gradient(135deg, #006c35, #00a651);
    color: white;
}

.btn-secondary {
    background: #f0f0f0;
    color: #333;
}

.coupons-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
    max-height: 600px;
    overflow-y: auto;
}

.coupon-card {
    border: 2px solid #e8e8e8;
    border-radius: 10px;
    padding: 15px;
    transition: border-color 0.3s;
}

.coupon-card.inactive,
.coupon-card.expired {
    opacity: 0.6;
}

.coupon-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.coupon-code {
    font-size: 1.3rem;
    font-weight: bold;
    color: #006c35;
    font-family: monospace;
}

.badge {
    padding: 3px 10px;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: bold;
}

.badge.active { background: #d4edda; color: #155724; }
.badge.inactive { background: #f0f0f0; color: #666; }
.badge.expired { background: #fde8e8; color: #721c24; }
.badge.pending { background: #fff3cd; color: #856404; }
.badge.used { background: #cce5ff; color: #004085; }

.coupon-description {
    color: #666;
    margin: 0 0 12px;
    font-size: 0.9rem;
}

.coupon-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-bottom: 15px;
}

.detail {
    font-size: 0.85rem;
}

.detail .label {
    color: #888;
}

.detail .value {
    color: #333;
    font-weight: 500;
}

.coupon-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.coupon-actions .btn {
    padding: 6px 12px;
    font-size: 0.85rem;
}

.btn-edit { background: #e3f2fd; color: #1565c0; }
.btn-toggle { background: #fff3e0; color: #e65100; }
.btn-delete { background: #ffebee; color: #c62828; }

.btn-edit:hover { background: #1565c0; color: white; }
.btn-toggle:hover { background: #e65100; color: white; }
.btn-delete:hover { background: #c62828; color: white; }

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #666;
}

.empty-icon {
    font-size: 3rem;
    display: block;
    margin-bottom: 15px;
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
}

@media (max-width: 992px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .admin-layout {
        flex-direction: column;
    }
    
    .admin-sidebar {
        width: 100%;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>
