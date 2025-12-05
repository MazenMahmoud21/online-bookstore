<?php
/**
 * Homepage
 */

$pageTitle = 'Home';
require_once 'includes/db.php';
require_once 'includes/header.php';

// Get featured books (latest 8)
$featuredBooks = dbQuery(
    "SELECT b.*, p.name as publisher_name 
     FROM books b 
     LEFT JOIN publishers p ON b.publisher_id = p.id 
     ORDER BY b.created_at DESC 
     LIMIT 8"
);

// Get categories
$categories = dbQuery("SELECT DISTINCT category FROM books ORDER BY category");

// Get statistics
$stats = dbQuerySingle(
    "SELECT 
        (SELECT COUNT(*) FROM books) as book_count,
        (SELECT COUNT(*) FROM customers WHERE is_admin = 0) as customer_count,
        (SELECT COUNT(*) FROM publishers) as publisher_count"
);
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-content">
        <div class="hero-text">
            <h1 class="hero-title">Discover Your Next Great Read</h1>
            <p class="hero-subtitle">Egypt's premier online bookstore with thousands of titles at your fingertips</p>
            
            <form class="hero-search-box" action="<?php echo url('search.php'); ?>" method="GET">
                <div class="search-input-wrapper">
                    <i data-feather="search"></i>
                    <input type="text" name="q" placeholder="Search for books, authors, or ISBN..." required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <span>Search</span>
                    <i data-feather="arrow-right"></i>
                </button>
            </form>
            
            <div class="hero-features">
                <div class="hero-feature">
                    <i data-feather="truck"></i>
                    <span>Free Shipping</span>
                </div>
                <div class="hero-feature">
                    <i data-feather="check-circle"></i>
                    <span>Secure Payment</span>
                </div>
                <div class="hero-feature">
                    <i data-feather="rotate-cw"></i>
                    <span>Easy Returns</span>
                </div>
            </div>
        </div>
        
        <div class="hero-image">
            <svg viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="bookGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#1a4d2e;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#4caf50;stop-opacity:1" />
                    </linearGradient>
                </defs>
                <rect x="100" y="100" width="120" height="180" fill="url(#bookGradient)" rx="5"/>
                <rect x="105" y="105" width="110" height="20" fill="rgba(255,255,255,0.3)" rx="3"/>
                <rect x="105" y="130" width="80" height="3" fill="rgba(255,255,255,0.5)" rx="2"/>
                <rect x="105" y="140" width="90" height="3" fill="rgba(255,255,255,0.5)" rx="2"/>
                <rect x="240" y="120" width="120" height="180" fill="#2e7d4e" rx="5"/>
                <rect x="245" y="125" width="110" height="20" fill="rgba(255,255,255,0.3)" rx="3"/>
                <rect x="180" y="140" width="120" height="180" fill="#5fa778" rx="5"/>
                <rect x="185" y="145" width="110" height="20" fill="rgba(255,255,255,0.3)" rx="3"/>
            </svg>
        </div>
    </div>
</section>

<!-- Statistics -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i data-feather="book-open"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['book_count']); ?>+</div>
                <div class="stat-label">Books Available</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i data-feather="users"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['customer_count']); ?>+</div>
                <div class="stat-label">Happy Customers</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i data-feather="briefcase"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['publisher_count']); ?>+</div>
                <div class="stat-label">Publishers</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i data-feather="truck"></i>
                </div>
                <div class="stat-value">Free</div>
                <div class="stat-label">Delivery in Cairo</div>
            </div>
        </div>
    </div>
</section>

<!-- Categories -->
<section class="categories-section">
    <div class="container">
        <div class="section-header">
            <h2><i data-feather="grid"></i> Browse by Category</h2>
            <p>Explore our wide selection of books across different genres</p>
        </div>
        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
                <a href="<?php echo url('books.php?category=' . urlencode($cat['category'])); ?>" class="category-card">
                    <i data-feather="bookmark"></i>
                    <span><?php echo htmlspecialchars($cat['category']); ?></span>
                </a>
            <?php endforeach; ?>
            <a href="<?php echo url('books.php'); ?>" class="category-card view-all">
                <i data-feather="arrow-right"></i>
                <span>View All</span>
            </a>
        </div>
    </div>
</section>

<!-- Featured Books -->
<section class="featured-books-section">
    <div class="container">
        <div class="section-header">
            <h2><i data-feather="star"></i> Latest Releases</h2>
            <p>Discover the newest additions to our collection</p>
        </div>
        
        <?php if (empty($featuredBooks)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i data-feather="inbox"></i></div>
                <h3>No Books Available</h3>
                <p>New books will be added soon</p>
            </div>
        <?php else: ?>
            <div class="books-grid">
                <?php foreach ($featuredBooks as $book): ?>
                    <div class="book-card">
                        <div class="book-card-image">
                            <svg viewBox="0 0 200 280" xmlns="http://www.w3.org/2000/svg">
                                <rect width="200" height="280" fill="#f0f0f0" rx="4"/>
                                <rect x="20" y="30" width="160" height="40" fill="#1a4d2e" opacity="0.1" rx="3"/>
                                <rect x="20" y="90" width="120" height="8" fill="#1a4d2e" opacity="0.05" rx="2"/>
                                <rect x="20" y="110" width="140" height="8" fill="#1a4d2e" opacity="0.05" rx="2"/>
                                <rect x="20" y="130" width="100" height="8" fill="#1a4d2e" opacity="0.05" rx="2"/>
                            </svg>
                        </div>
                        <div class="book-card-content">
                            <span class="book-card-category"><?php echo htmlspecialchars($book['category']); ?></span>
                            <h3 class="book-card-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                            <p class="book-card-author"><?php echo htmlspecialchars($book['authors']); ?></p>
                            <div class="book-card-price">EGP <?php echo number_format($book['price'], 2); ?></div>
                            <div class="book-card-stock <?php echo $book['stock'] <= 0 ? 'out' : ($book['stock'] < $book['threshold'] ? 'low' : ''); ?>">
                                <i data-feather="<?php echo $book['stock'] > 0 ? 'check-circle' : 'x-circle'; ?>"></i>
                                <?php 
                                if ($book['stock'] <= 0) {
                                    echo 'Out of Stock';
                                } elseif ($book['stock'] < $book['threshold']) {
                                    echo 'Limited Stock (' . $book['stock'] . ')';
                                } else {
                                    echo 'In Stock';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="book-card-actions">
                            <a href="<?php echo url('book.php?isbn=' . urlencode($book['isbn'])); ?>" class="btn btn-secondary btn-sm">
                                <i data-feather="eye"></i>
                                <span>View Details</span>
                            </a>
                            <?php if (isLoggedIn() && !isAdmin() && $book['stock'] > 0): ?>
                                <button onclick="addToCart('<?php echo $book['isbn']; ?>')" class="btn btn-primary btn-sm">
                                    <i data-feather="shopping-cart"></i>
                                    <span>Add to Cart</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="section-footer">
                <a href="<?php echo url('books.php'); ?>" class="btn btn-primary btn-lg">
                    <span>View All Books</span>
                    <i data-feather="arrow-right"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
    // Initialize Feather Icons
    feather.replace();
</script>

<?php require_once 'includes/footer.php'; ?>
