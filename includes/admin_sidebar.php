<?php
/**
 * Admin Sidebar Navigation
 * شريط التنقل الجانبي للمدير
 */

// Get current page for active state
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="admin-sidebar">
    <nav class="admin-nav">
        <ul>
            <li>
                <a href="<?php echo url('admin/dashboard.php'); ?>" class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="ph ph-gauge"></i> لوحة التحكم
                </a>
            </li>
            <li>
                <a href="<?php echo url('admin/books.php'); ?>" class="<?php echo $currentPage === 'books.php' ? 'active' : ''; ?>">
                    <i class="ph ph-books"></i> إدارة الكتب
                </a>
            </li>
            <li>
                <a href="<?php echo url('admin/add_book.php'); ?>" class="<?php echo $currentPage === 'add_book.php' ? 'active' : ''; ?>">
                    <i class="ph ph-plus-circle"></i> إضافة كتاب
                </a>
            </li>
            <li>
                <a href="<?php echo url('admin/publishers.php'); ?>" class="<?php echo $currentPage === 'publishers.php' ? 'active' : ''; ?>">
                    <i class="ph ph-buildings"></i> الناشرين
                </a>
            </li>
            <li>
                <a href="<?php echo url('admin/view_orders.php'); ?>" class="<?php echo $currentPage === 'view_orders.php' ? 'active' : ''; ?>">
                    <i class="ph ph-package"></i> طلبات التوريد
                </a>
            </li>
            <li>
                <a href="<?php echo url('admin/customers.php'); ?>" class="<?php echo $currentPage === 'customers.php' ? 'active' : ''; ?>">
                    <i class="ph ph-users"></i> العملاء
                </a>
            </li>
            <li>
                <a href="<?php echo url('admin/sales.php'); ?>" class="<?php echo $currentPage === 'sales.php' ? 'active' : ''; ?>">
                    <i class="ph ph-currency-circle-dollar"></i> المبيعات
                </a>
            </li>
            <li>
                <a href="<?php echo url('admin/reports.php'); ?>" class="<?php echo $currentPage === 'reports.php' ? 'active' : ''; ?>">
                    <i class="ph ph-chart-line"></i> التقارير
                </a>
            </li>
            <li>
                <a href="<?php echo url('admin/coupons.php'); ?>" class="<?php echo $currentPage === 'coupons.php' ? 'active' : ''; ?>">
                    <i class="ph ph-ticket"></i> الكوبونات
                </a>
            </li>
            <li>
                <a href="<?php echo url('admin/reviews.php'); ?>" class="<?php echo $currentPage === 'reviews.php' ? 'active' : ''; ?>">
                    ⭐ التقييمات
                </a>
            </li>
        </ul>
    </nav>
</aside>
