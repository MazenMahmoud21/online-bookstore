<?php
/**
 * Admin Dashboard - لوحة تحكم المدير
 */

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

// Get statistics
$stats = dbQuerySingle("
    SELECT 
        (SELECT COUNT(*) FROM books) as book_count,
        (SELECT COUNT(*) FROM customers WHERE is_admin = 0) as customer_count,
        (SELECT COUNT(*) FROM sales) as sales_count,
        (SELECT COALESCE(SUM(total_amount), 0) FROM sales WHERE date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) as monthly_revenue,
        (SELECT COUNT(*) FROM orders_from_publishers WHERE status = 'pending') as pending_orders
");

// Recent sales
$recentSales = dbQuery("
    SELECT s.*, CONCAT(c.first_name, ' ', c.last_name) as customer_name 
    FROM sales s 
    JOIN customers c ON s.customer_id = c.id 
    ORDER BY s.date DESC 
    LIMIT 5
");

// Low stock books
$lowStockBooks = dbQuery("
    SELECT isbn, title, stock, threshold 
    FROM books 
    WHERE stock <= threshold 
    ORDER BY stock ASC 
    LIMIT 5
");

$pageTitle = 'Dashboard';
require_once '../includes/header.php';
?>

<div class="admin-layout">
    <?php require_once '../includes/admin_sidebar.php'; ?>
    
    <!-- Main Content -->
    <main>
        <div class="page-header">
            <h1>
                <span style="vertical-align: middle; margin-right: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#006c35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                </span>
                Dashboard
            </h1>
            <p>Welcome, <?php echo htmlspecialchars(getCurrentUserName()); ?></p>
        </div>
    
            <!-- Main Content -->
            <main>
                <div class="page-header">
                    <h1><i data-feather="bar-chart-2"></i> Dashboard</h1>
                    <p>Welcome, <?php echo htmlspecialchars(getCurrentUserName()); ?></p>
                </div>
        
        <!-- Stats Grid -->
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#006c35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                </div>
                <div class="value"><?php echo number_format($stats['book_count']); ?></div>
                <div class="label">Books</div>
            </div>
            <div class="stat-card">
                <div class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#006c35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <div class="value"><?php echo number_format($stats['customer_count']); ?></div>
                <div class="label">Customers</div>
            </div>
            <div class="stat-card">
                <div class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#006c35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                </div>
                <div class="value"><?php echo number_format($stats['sales_count']); ?></div>
                <div class="label">Sales</div>
            </div>
            <div class="stat-card">
                <div class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#006c35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
                <div class="value">EGP <?php echo number_format($stats['monthly_revenue'], 2); ?></div>
                <div class="label">Monthly Revenue</div>
            </div>
            <div class="stat-card">
                <div class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#006c35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                </div>
                <div class="value"><?php echo number_format($stats['pending_orders']); ?></div>
                <div class="label">Pending Supply Orders</div>
            </div>
        </div>
            
        <!-- Low Stock Alert -->
        <div class="card" style="margin-top: 30px;">
            <div class="card-header" style="background-color: var(--warning-color);">
                <h3>
                    <span style="vertical-align: middle; margin-right: 8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12" y2="17"></line></svg>
                    </span>
                    Low Stock Alert
                </h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (empty($lowStockBooks)): ?>
                    <p style="padding: 20px; text-align: center; color: var(--success-color);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#27ae60" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        All books have sufficient stock
                    </p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Book</th>
                                <th>Stock</th>
                                <th>Threshold</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lowStockBooks as $book): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                                    <td style="color: <?php echo $book['stock'] <= 0 ? 'var(--error-color)' : 'var(--warning-color)'; ?>;">
                                        <?php echo $book['stock']; ?>
                                    </td>
                                    <td><?php echo $book['threshold']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <a href="/admin/view_orders.php">View Supply Orders →</a>
            </div>
        </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card" style="margin-top: 30px;">
            <div class="card-header">
                <h3>
                    <span style="vertical-align: middle; margin-right: 8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#006c35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                    </span>
                    Quick Actions
                </h3>
            </div>
            <div class="card-body">
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <a href="/admin/add_book.php" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 6px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                        Add New Book
                    </a>
                    <a href="/admin/view_orders.php" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 6px;"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                        View Supply Orders
                    </a>
                    <a href="/admin/reports.php" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 6px;"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                        View Reports
                    </a>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
