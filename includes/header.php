<?php
/**
 * Header Template
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$pageTitle = $pageTitle ?? 'Egyptian Online Bookstore';
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a4d2e">
    <meta name="color-scheme" content="light dark">
    <base href="<?php echo rtrim(url(''), '/') . '/'; ?>">
    <title><?php echo htmlspecialchars($pageTitle); ?> | Egyptian Bookstore</title>
    
    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Google Fonts: Inter & Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>
    
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Skip to main content for accessibility -->
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="<?php echo url('index.php'); ?>" class="logo-link">
                        <div class="logo-icon-wrapper">
                            <svg class="logo-icon" width="36" height="36" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke="url(#gradient1)" stroke-width="2" stroke-linecap="round"/>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" stroke="url(#gradient1)" stroke-width="2" stroke-linecap="round"/>
                                <circle cx="12" cy="9" r="1.5" fill="url(#gradient2)"/>
                                <circle cx="15" cy="12" r="1.5" fill="url(#gradient2)"/>
                                <circle cx="9" cy="14" r="1.5" fill="url(#gradient2)"/>
                                <defs>
                                    <linearGradient id="gradient1" x1="4" y1="2" x2="20" y2="22" gradientUnits="userSpaceOnUse">
                                        <stop offset="0%" stop-color="#006c35"/>
                                        <stop offset="100%" stop-color="#00a651"/>
                                    </linearGradient>
                                    <linearGradient id="gradient2" x1="9" y1="9" x2="15" y2="14" gradientUnits="userSpaceOnUse">
                                        <stop offset="0%" stop-color="#00a651"/>
                                        <stop offset="100%" stop-color="#006c35"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>
                        <div class="logo-text-wrapper">
                            <span class="logo-text-main">Online Bookstore</span>
                            <span class="logo-text-sub">Egyptian Collection</span>
                        </div>
                    </a>
                </div>
                
                <nav class="main-nav" id="main-nav" role="navigation" aria-label="Main Navigation">
                    <ul>
                        <li><a href="<?php echo url('index.php'); ?>"><i data-feather="home"></i> Home</a></li>
                        <li><a href="<?php echo url('books.php'); ?>"><i data-feather="book-open"></i> Books</a></li>
                        <?php if (isLoggedIn()): ?>
                            <?php if (isAdmin()): ?>
                                <li><a href="<?php echo url('admin/dashboard.php'); ?>"><i data-feather="bar-chart-2"></i> Dashboard</a></li>
                            <?php else: ?>
                                <li><a href="<?php echo url('customer/cart.php'); ?>"><i data-feather="shopping-cart"></i> Cart</a></li>
                                <li><a href="<?php echo url('customer/wishlist.php'); ?>"><i data-feather="heart"></i> Wishlist</a></li>
                                <li><a href="<?php echo url('customer/orders.php'); ?>"><i data-feather="package"></i> Orders</a></li>
                                <li><a href="<?php echo url('customer/profile.php'); ?>"><i data-feather="user"></i> Profile</a></li>
                            <?php endif; ?>
                            <li><a href="<?php echo url('logout.php'); ?>" class="nav-logout"><i data-feather="log-out"></i> Logout</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo url('login.php'); ?>"><i data-feather="log-in"></i> Login</a></li>
                            <li><a href="<?php echo url('signup.php'); ?>" class="btn btn-primary btn-nav"><i data-feather="user-plus"></i> Sign Up</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                
                <div class="header-actions">
                    <?php if (isLoggedIn() && !isAdmin()): ?>
                        <a href="<?php echo url('customer/cart.php'); ?>" class="cart-icon" aria-label="Shopping Cart">
                            <i data-feather="shopping-bag"></i>
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
                            <button class="user-avatar-btn" aria-label="User Menu">
                                <i data-feather="user-circle"></i>
                                <span class="user-name"><?php echo htmlspecialchars(getCurrentUserName()); ?></span>
                                <i data-feather="chevron-down"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="Open Menu" aria-expanded="false" aria-controls="main-nav">
                        <i data-feather="menu"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Search Bar -->
        <div class="header-search" id="header-search">
            <div class="container">
                <form action="<?php echo url('search.php'); ?>" method="GET" class="search-form-header">
                    <i data-feather="search" class="search-icon"></i>
                    <input type="search" name="q" placeholder="Search for books, authors, or publishers..." autocomplete="off" id="search-input">
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="search"></i>
                        <span>Search</span>
                    </button>
                </form>
                <div class="search-suggestions" id="search-suggestions"></div>
            </div>
        </div>
    </header>
    
    <script>
        // Initialize Feather Icons
        document.addEventListener('DOMContentLoaded', function() {
            feather.replace();
        });
    </script>
    
    <main class="main-content" id="main-content">
        <div class="container">
