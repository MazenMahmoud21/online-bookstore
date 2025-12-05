<?php
/**
 * Header Template
 * قالب الرأس
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$pageTitle = $pageTitle ?? 'المكتبة الإلكترونية';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> | المكتبة الإلكترونية</title>
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="<?php echo url('index.php'); ?>">
                        <span class="logo-icon">📚</span>
                        <span class="logo-text">المكتبة الإلكترونية</span>
                    </a>
                </div>
                
                <nav class="main-nav">
                    <ul>
                        <li><a href="<?php echo url('index.php'); ?>">الرئيسية</a></li>
                        <li><a href="<?php echo url('books.php'); ?>">الكتب</a></li>
                        <?php if (isLoggedIn()): ?>
                            <?php if (isAdmin()): ?>
                                <li><a href="<?php echo url('admin/dashboard.php'); ?>">لوحة التحكم</a></li>
                            <?php else: ?>
                                <li><a href="<?php echo url('customer/cart.php'); ?>">السلة</a></li>
                                <li><a href="<?php echo url('customer/orders.php'); ?>">طلباتي</a></li>
                                <li><a href="<?php echo url('customer/profile.php'); ?>">حسابي</a></li>
                            <?php endif; ?>
                            <li><a href="<?php echo url('logout.php'); ?>">تسجيل الخروج</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo url('login.php'); ?>">تسجيل الدخول</a></li>
                            <li><a href="<?php echo url('signup.php'); ?>">حساب جديد</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                
                <div class="header-actions">
                    <?php if (isLoggedIn() && !isAdmin()): ?>
                        <a href="<?php echo url('customer/cart.php'); ?>" class="cart-icon">
                            🛒
                            <?php
                            require_once __DIR__ . '/db.php';
                            $cartCount = dbQuerySingle(
                                "SELECT COALESCE(SUM(ci.qty), 0) as count 
                                 FROM shopping_cart sc 
                                 LEFT JOIN cart_items ci ON sc.id = ci.cart_id 
                                 WHERE sc.customer_id = ?",
                                [getCurrentUserId()]
                            );
                            if ($cartCount && $cartCount['count'] > 0):
                            ?>
                                <span class="cart-count"><?php echo $cartCount['count']; ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (isLoggedIn()): ?>
                        <span class="user-welcome">مرحباً، <?php echo htmlspecialchars(getCurrentUserName()); ?></span>
                    <?php endif; ?>
                </div>
                
                <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">☰</button>
            </div>
        </div>
    </header>
    
    <main class="main-content">
        <div class="container">
