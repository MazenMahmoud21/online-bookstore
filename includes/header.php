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
    <meta name="theme-color" content="#0071e3">
    <meta name="color-scheme" content="light dark">
    <title><?php echo htmlspecialchars($pageTitle); ?> | المكتبة الإلكترونية</title>
    
    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://unpkg.com">
    
    <!-- Google Fonts: Inter for Latin, Cairo for Arabic -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web@2.0.3"></script>
    
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
</head>
<body>
    <!-- Skip to main content for accessibility -->
    <a href="#main-content" class="skip-link">تخطي إلى المحتوى الرئيسي</a>
    
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="<?php echo url('index.php'); ?>">
                        <span class="logo-icon">
                            <i class="ph-duotone ph-books"></i>
                        </span>
                        <span class="logo-text">المكتبة الإلكترونية</span>
                    </a>
                </div>
                
                <nav class="main-nav" id="main-nav" role="navigation" aria-label="التنقل الرئيسي">
                    <ul>
                        <li><a href="<?php echo url('index.php'); ?>"><i class="ph ph-house"></i> الرئيسية</a></li>
                        <li><a href="<?php echo url('books.php'); ?>"><i class="ph ph-book-open"></i> الكتب</a></li>
                        <?php if (isLoggedIn()): ?>
                            <?php if (isAdmin()): ?>
                                <li><a href="<?php echo url('admin/dashboard.php'); ?>"><i class="ph ph-gauge"></i> لوحة التحكم</a></li>
                            <?php else: ?>
                                <li><a href="<?php echo url('customer/cart.php'); ?>"><i class="ph ph-shopping-cart"></i> السلة</a></li>
                                <li><a href="<?php echo url('customer/wishlist.php'); ?>"><i class="ph ph-heart"></i> المفضلة</a></li>
                                <li><a href="<?php echo url('customer/orders.php'); ?>"><i class="ph ph-package"></i> طلباتي</a></li>
                                <li><a href="<?php echo url('customer/profile.php'); ?>"><i class="ph ph-user-circle"></i> حسابي</a></li>
                            <?php endif; ?>
                            <li><a href="<?php echo url('logout.php'); ?>" class="nav-logout"><i class="ph ph-sign-out"></i> تسجيل الخروج</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo url('login.php'); ?>"><i class="ph ph-sign-in"></i> تسجيل الدخول</a></li>
                            <li><a href="<?php echo url('signup.php'); ?>" class="btn btn-primary btn-nav"><i class="ph ph-user-plus"></i> حساب جديد</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                
                <div class="header-actions">
                    <?php if (isLoggedIn() && !isAdmin()): ?>
                        <a href="<?php echo url('customer/cart.php'); ?>" class="cart-icon" aria-label="عربة التسوق">
                            <i class="ph ph-shopping-cart-simple"></i>
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
                        <div class="user-dropdown">
                            <button class="user-avatar-btn" aria-label="قائمة المستخدم">
                                <i class="ph-duotone ph-user-circle"></i>
                                <span class="user-name"><?php echo htmlspecialchars(getCurrentUserName()); ?></span>
                                <i class="ph ph-caret-down"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="فتح القائمة" aria-expanded="false" aria-controls="main-nav">
                        <i class="ph ph-list"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Search Bar (Expandable) -->
        <div class="header-search" id="header-search">
            <div class="container">
                <form action="<?php echo url('search.php'); ?>" method="GET" class="search-form-header">
                    <i class="ph ph-magnifying-glass search-icon"></i>
                    <input type="search" name="q" placeholder="ابحث عن كتاب، مؤلف، أو ناشر..." autocomplete="off" id="search-input">
                    <button type="submit" class="btn btn-primary">
                        <i class="ph ph-magnifying-glass"></i>
                        <span>بحث</span>
                    </button>
                </form>
                <div class="search-suggestions" id="search-suggestions"></div>
            </div>
        </div>
    </header>
    
    <main class="main-content" id="main-content">
        <div class="container">
