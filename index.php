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
    <div class="hero-background-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>
    
    <div class="hero-content">
        <div class="hero-text">
            <div class="hero-badge">
                <i data-feather="zap"></i>
                <span>Your Literary Journey Starts Here</span>
            </div>
            
            <h1 class="hero-title">
                Discover Your Next
                <span class="gradient-text">Great Read</span>
            </h1>
            
            <p class="hero-subtitle">
                Egypt's premier online bookstore with thousands of titles at your fingertips. 
                From bestsellers to rare finds, explore our curated collection.
            </p>
            
            <form class="hero-search-box" action="<?php echo url('search.php'); ?>" method="GET">
                <div class="search-input-wrapper">
                    <i data-feather="search"></i>
                    <input type="text" name="q" placeholder="Search for books, authors, or ISBN..." required>
                    <div class="search-suggestions">
                        <i data-feather="trending-up"></i>
                        <span>Try: "Python", "Fiction", "History"</span>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-hero">
                    <span>Search</span>
                    <i data-feather="arrow-right"></i>
                </button>
            </form>
            
            <div class="hero-features">
                <div class="hero-feature">
                    <div class="feature-icon">
                        <i data-feather="truck"></i>
                    </div>
                    <div class="feature-text">
                        <strong>Free Shipping</strong>
                        <span>On orders over EGP 200</span>
                    </div>
                </div>
                <div class="hero-feature">
                    <div class="feature-icon">
                        <i data-feather="shield"></i>
                    </div>
                    <div class="feature-text">
                        <strong>Secure Payment</strong>
                        <span>100% safe & encrypted</span>
                    </div>
                </div>
                <div class="hero-feature">
                    <div class="feature-icon">
                        <i data-feather="rotate-cw"></i>
                    </div>
                    <div class="feature-text">
                        <strong>Easy Returns</strong>
                        <span>30-day return policy</span>
                    </div>
                </div>
            </div>
            
            <div class="hero-cta-group">
                <a href="<?php echo url('books.php'); ?>" class="btn btn-primary btn-lg">
                    <i data-feather="book-open"></i>
                    <span>Browse Collection</span>
                </a>
                <?php if (!isLoggedIn()): ?>
                    <a href="<?php echo url('signup.php'); ?>" class="btn btn-outline btn-lg">
                        <i data-feather="user-plus"></i>
                        <span>Sign Up Free</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="hero-image">
            <div class="hero-book-stack">
                <div class="book-3d book-1">
                    <div class="book-front"></div>
                    <div class="book-spine"></div>
                    <div class="book-top"></div>
                </div>
                <div class="book-3d book-2">
                    <div class="book-front"></div>
                    <div class="book-spine"></div>
                    <div class="book-top"></div>
                </div>
                <div class="book-3d book-3">
                    <div class="book-front"></div>
                    <div class="book-spine"></div>
                    <div class="book-top"></div>
                </div>
            </div>
            
            <div class="floating-elements">
                <div class="floating-element star-1">⭐</div>
                <div class="floating-element star-2">📚</div>
                <div class="floating-element star-3">✨</div>
                <div class="floating-element star-4">📖</div>
            </div>
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
