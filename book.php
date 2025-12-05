<?php
/**
 * Book Details Page - صفحة تفاصيل الكتاب
 */

require_once 'includes/db.php';
require_once 'includes/auth.php';

$isbn = isset($_GET['isbn']) ? sanitize($_GET['isbn']) : '';

if (empty($isbn)) {
    header('Location: ' . url('books.php'));
    exit;
}

$book = dbQuerySingle(
    "SELECT b.*, p.name as publisher_name, p.phone as publisher_phone 
     FROM books b 
     LEFT JOIN publishers p ON b.publisher_id = p.id 
     WHERE b.isbn = ?",
    [$isbn]
);

if (!$book) {
    header('Location: ' . url('books.php'));
    exit;
}

// Get related books (same category)
$relatedBooks = dbQuery(
    "SELECT * FROM books 
     WHERE category = ? AND isbn != ? AND stock > 0 
     ORDER BY RAND() 
     LIMIT 4",
    [$book['category'], $isbn]
);

// Check if book is in wishlist
$inWishlist = false;
if (isLoggedIn()) {
    try {
        $wishlistItem = dbQuerySingle(
            "SELECT id FROM wishlists WHERE customer_id = ? AND book_isbn = ?",
            [getCurrentUserId(), $isbn]
        );
        $inWishlist = $wishlistItem !== null;
    } catch (Exception $e) {
        // Table might not exist
    }
}

// Get reviews
$reviews = [];
$reviewStats = ['average' => 0, 'count' => 0];
try {
    $reviews = dbQuery(
        "SELECT r.*, CONCAT(c.first_name, ' ', c.last_name) as customer_name
         FROM book_reviews r
         JOIN customers c ON r.customer_id = c.id
         WHERE r.book_isbn = ? AND r.status = 'approved'
         ORDER BY r.created_at DESC
         LIMIT 10",
        [$isbn]
    );
    
    $stats = dbQuerySingle(
        "SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM book_reviews WHERE book_isbn = ? AND status = 'approved'",
        [$isbn]
    );
    $reviewStats = [
        'average' => round($stats['avg_rating'] ?? 0, 1),
        'count' => $stats['count'] ?? 0
    ];
} catch (Exception $e) {
    // Table might not exist
}

// Check if user has already reviewed
$userReview = null;
if (isLoggedIn()) {
    try {
        $userReview = dbQuerySingle(
            "SELECT * FROM book_reviews WHERE book_isbn = ? AND customer_id = ?",
            [$isbn, getCurrentUserId()]
        );
    } catch (Exception $e) {}
}

$pageTitle = $book['title'];
require_once 'includes/header.php';
?>

<div class="breadcrumb">
    <a href="<?php echo url('index.php'); ?>">Home</a> &raquo;
    <a href="<?php echo url('books.php'); ?>">Books</a> &raquo;
    <a href="<?php echo url('books.php?category=' . urlencode($book['category'])); ?>"><?php echo htmlspecialchars($book['category']); ?></a> &raquo;
    <span><?php echo htmlspecialchars($book['title']); ?></span>
</div>

<div class="book-details">
    <div class="book-image-section">
        <div class="book-image-large">
            <?php if (!empty($book['image_url'])): ?>
                <img src="<?php echo htmlspecialchars($book['image_url']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <svg style="display:none;" viewBox="0 0 200 280" xmlns="http://www.w3.org/2000/svg"><rect width="200" height="280" fill="#f0f0f0" rx="4"/><rect x="20" y="30" width="160" height="40" fill="#1a4d2e" opacity="0.1" rx="3"/><rect x="20" y="90" width="120" height="8" fill="#1a4d2e" opacity="0.05" rx="2"/><rect x="20" y="110" width="140" height="8" fill="#1a4d2e" opacity="0.05" rx="2"/><rect x="20" y="130" width="100" height="8" fill="#1a4d2e" opacity="0.05" rx="2"/><text x="100" y="200" text-anchor="middle" fill="#999" font-size="14">No Image</text></svg>
            <?php else: ?>
                <svg viewBox="0 0 200 280" xmlns="http://www.w3.org/2000/svg"><rect width="200" height="280" fill="#f0f0f0" rx="4"/><rect x="20" y="30" width="160" height="40" fill="#1a4d2e" opacity="0.1" rx="3"/><rect x="20" y="90" width="120" height="8" fill="#1a4d2e" opacity="0.05" rx="2"/><rect x="20" y="110" width="140" height="8" fill="#1a4d2e" opacity="0.05" rx="2"/><rect x="20" y="130" width="100" height="8" fill="#1a4d2e" opacity="0.05" rx="2"/><text x="100" y="200" text-anchor="middle" fill="#999" font-size="14">No Image</text></svg>
            <?php endif; ?>
        </div>
        
        <!-- Social Share Buttons -->
        <div class="social-share">
            <p class="share-label"><i data-feather="share-2"></i> Share this book:</p>
            <div class="share-buttons">
                <button onclick="shareOnFacebook()" class="share-btn facebook" title="Share on Facebook">
                    <i data-feather="facebook"></i>
                </button>
                <button onclick="shareOnTwitter()" class="share-btn twitter" title="Share on Twitter">
                    <i data-feather="twitter"></i>
                </button>
                <button onclick="shareOnWhatsApp()" class="share-btn whatsapp" title="Share on WhatsApp">
                    <i data-feather="message-circle"></i>
                </button>
                <button onclick="copyLink()" class="share-btn link" title="Copy Link">
                    <i data-feather="link"></i>
                </button>
            </div>
        </div>
    </div>
    
    <div class="book-info">
        <span class="book-card-category"><?php echo htmlspecialchars($book['category']); ?></span>
        
        <h1><?php echo htmlspecialchars($book['title']); ?></h1>
        
        <!-- Rating Display -->
        <?php if ($reviewStats['count'] > 0): ?>
        <div class="book-rating-display">
            <?php
            $rating = round($reviewStats['average']);
            for ($i = 1; $i <= 5; $i++) {
                echo $i <= $rating ? '<i data-feather="star"></i>' : '<i data-feather="star"></i>';
            }
            ?>
            <span class="rating-text"><?php echo $reviewStats['average']; ?>/5 (<?php echo $reviewStats['count']; ?> reviews)</span>
        </div>
        <?php endif; ?>
        
        <div class="book-meta">
            <p><strong>Author:</strong> <?php echo htmlspecialchars($book['authors']); ?></p>
            <?php if ($book['publisher_name']): ?>
                <p><strong>Publisher:</strong> <?php echo htmlspecialchars($book['publisher_name']); ?></p>
            <?php endif; ?>
            <p><strong>Year:</strong> <?php echo $book['year']; ?></p>
            <p><strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?></p>
        </div>
        
        <div class="book-price-large">EGP <?php echo number_format($book['price'], 2); ?></div>
        
        <div class="book-card-stock <?php echo $book['stock'] <= 0 ? 'out' : ($book['stock'] < $book['threshold'] ? 'low' : ''); ?>" style="font-size: 1.1rem; margin-bottom: 20px;">
            <?php 
            if ($book['stock'] <= 0) {
                echo '<i data-feather="x-circle"></i> Out of Stock';
            } elseif ($book['stock'] < $book['threshold']) {
                echo '<i data-feather="alert-triangle"></i> Limited Stock (' . $book['stock'] . ' left)';
            } else {
                echo '<i data-feather="check-circle"></i> In Stock';
            }
            ?>
        </div>
        
        <?php if ($book['description']): ?>
            <div class="book-description">
                <h3>About this Book</h3>
                <p><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (isLoggedIn() && !isAdmin() && $book['stock'] > 0): ?>
            <form id="addToCartForm" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" 
                           max="<?php echo $book['stock']; ?>" class="form-control" style="width: 80px;">
                </div>
                <button type="button" onclick="addToCart('<?php echo $book['isbn']; ?>', document.getElementById('quantity').value)" 
                        class="btn btn-primary btn-lg">
                    <i data-feather="shopping-cart"></i> Add to Cart
                </button>
                <button type="button" onclick="toggleWishlist('<?php echo $book['isbn']; ?>')" 
                        class="btn btn-wishlist <?php echo $inWishlist ? 'active' : ''; ?>" id="wishlistBtn">
                    <?php echo $inWishlist ? '<i data-feather="heart"></i> In Wishlist' : '<i data-feather="heart"></i> Add to Wishlist'; ?>
                </button>
            </form>
        <?php elseif (isLoggedIn() && !isAdmin()): ?>
            <div class="action-buttons">
                <button type="button" onclick="toggleWishlist('<?php echo $book['isbn']; ?>')" 
                        class="btn btn-wishlist <?php echo $inWishlist ? 'active' : ''; ?>" id="wishlistBtn">
                    <?php echo $inWishlist ? '<i data-feather="heart"></i> In Wishlist' : '<i data-feather="heart"></i> Add to Wishlist'; ?>
                </button>
            </div>
        <?php elseif (!isLoggedIn()): ?>
            <div class="alert alert-info">
                <a href="<?php echo url('login.php'); ?>">Login</a> to add this book to your cart
            </div>
        <?php elseif ($book['stock'] <= 0): ?>
            <div class="alert alert-warning">
                This book is currently out of stock. Please try again later.
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border-color);">
            <a href="<?php echo url('books.php'); ?>" class="btn btn-secondary">&larr; Back to Books</a>
        </div>
    </div>
</div>

<!-- Related Books Section -->
<?php if (!empty($relatedBooks)): ?>
<div class="related-books-section">
    <h2><i data-feather="book-open"></i> You May Also Like</h2>
    <div class="books-grid">
        <?php foreach ($relatedBooks as $relatedBook): ?>
            <div class="book-card">
                <a href="<?php echo url('book.php?isbn=' . urlencode($relatedBook['isbn'])); ?>" class="book-link">
                    <div class="book-image">
                        <?php if (!empty($relatedBook['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($relatedBook['image_url']); ?>" alt="<?php echo htmlspecialchars($relatedBook['title']); ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <svg style="display:none;" viewBox="0 0 200 280" xmlns="http://www.w3.org/2000/svg"><rect width="200" height="280" fill="#f0f0f0" rx="4"/><rect x="20" y="30" width="160" height="40" fill="#1a4d2e" opacity="0.1" rx="3"/><rect x="20" y="90" width="120" height="8" fill="#1a4d2e" opacity="0.05" rx="2"/></svg>
                        <?php else: ?>
                            <svg viewBox="0 0 200 280" xmlns="http://www.w3.org/2000/svg"><rect width="200" height="280" fill="#f0f0f0" rx="4"/><rect x="20" y="30" width="160" height="40" fill="#1a4d2e" opacity="0.1" rx="3"/><rect x="20" y="90" width="120" height="8" fill="#1a4d2e" opacity="0.05" rx="2"/></svg>
                        <?php endif; ?>
                        <span class="book-card-category"><?php echo htmlspecialchars($relatedBook['category']); ?></span>
                    </div>
                    <div class="book-card-content">
                        <h3><?php echo htmlspecialchars($relatedBook['title']); ?></h3>
                        <p class="book-card-author"><?php echo htmlspecialchars($relatedBook['authors']); ?></p>
                        <div class="book-card-footer">
                            <span class="book-card-price">EGP <?php echo number_format($relatedBook['price'], 2); ?></span>
                            <span class="book-card-stock <?php echo $relatedBook['stock'] <= 0 ? 'out' : ($relatedBook['stock'] < $relatedBook['threshold'] ? 'low' : ''); ?>">
                                <?php 
                                if ($relatedBook['stock'] <= 0) {
                                    echo '<i data-feather="x-circle"></i> Out';
                                } elseif ($relatedBook['stock'] < $relatedBook['threshold']) {
                                    echo '<i data-feather="alert-triangle"></i> Low';
                                } else {
                                    echo '<i data-feather="check-circle"></i> Stock';
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Reviews Section -->
<div class="reviews-section">
    <h2><i data-feather="star"></i> Ratings & Reviews</h2>
    
    <?php if (isLoggedIn() && !isAdmin()): ?>
        <!-- Review Form -->
        <div class="review-form-container">
            <h3><?php echo $userReview ? 'Edit Your Review' : 'Add Your Review'; ?></h3>
            <form id="reviewForm" class="review-form">
                <input type="hidden" name="isbn" value="<?php echo htmlspecialchars($isbn); ?>">
                
                <div class="rating-input">
                    <label>Your Rating:</label>
                    <div class="star-rating" id="starRating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?php echo ($userReview && $i <= $userReview['rating']) ? 'active' : ''; ?>" data-rating="<?php echo $i; ?>">
                                <i data-feather="star"></i>
                            </span>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating" id="ratingInput" value="<?php echo $userReview['rating'] ?? '0'; ?>">
                </div>
                
                <div class="form-group">
                    <label for="reviewText">Your Review (optional):</label>
                    <textarea id="reviewText" name="review_text" rows="4" placeholder="Share your thoughts about this book..."><?php echo htmlspecialchars($userReview['review_text'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <?php echo $userReview ? 'Update Review' : 'Submit Review'; ?>
                </button>
                
                <?php if ($userReview): ?>
                    <span class="review-status">
                        <?php
                        switch ($userReview['status']) {
                            case 'pending': echo 'Pending Review'; break;
                            case 'approved': echo 'Published'; break;
                            case 'rejected': echo 'Rejected'; break;
                        }
                        ?>
                    </span>
                <?php endif; ?>
            </form>
            <div id="reviewMessage" class="review-message"></div>
        </div>
    <?php elseif (!isLoggedIn()): ?>
        <div class="alert alert-info">
            <a href="<?php echo url('login.php'); ?>">Login</a> to add your review
        </div>
    <?php endif; ?>
    
    <!-- Reviews List -->
    <?php if (!empty($reviews)): ?>
        <div class="reviews-list">
            <?php foreach ($reviews as $review): ?>
                <div class="review-item">
                    <div class="review-header">
                        <span class="reviewer-name">👤 <?php echo htmlspecialchars($review['customer_name']); ?></span>
                        <span class="review-date"><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></span>
                    </div>
                    <div class="review-rating">
                        <?php
                        for ($i = 1; $i <= 5; $i++) {
                            echo $i <= $review['rating'] ? '⭐' : '☆';
                        }
                        ?>
                    </div>
                    <?php if ($review['review_text']): ?>
                        <p class="review-text"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="no-reviews">لا توجد تقييمات لهذا الكتاب بعد. كن أول من يقيم!</p>
    <?php endif; ?>
</div>

<style>
.book-details {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 40px;
    margin-bottom: 50px;
    background: white;
    padding: 30px;
    border-radius: 16px;
    box-shadow: var(--shadow-md);
}

.book-image-section {
    position: sticky;
    top: 20px;
    height: fit-content;
}

.book-image-large {
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--shadow-lg);
    transition: transform 0.3s ease;
}

.book-image-large:hover {
    transform: scale(1.02);
}

.book-image-large img {
    width: 100%;
    height: auto;
    display: block;
}

.book-image-large svg {
    width: 100%;
    height: auto;
    display: block;
}

.social-share {
    margin-top: 25px;
    padding: 20px;
    background: var(--background-color);
    border-radius: 12px;
}

.share-label {
    font-weight: 600;
    margin-bottom: 12px;
    color: var(--text-color);
    display: flex;
    align-items: center;
    gap: 8px;
}

.share-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.share-btn {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    box-shadow: var(--shadow-sm);
}

.share-btn:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
}

.share-btn.facebook {
    background: #1877f2;
    color: white;
}

.share-btn.twitter {
    background: #1da1f2;
    color: white;
}

.share-btn.whatsapp {
    background: #25d366;
    color: white;
}

.share-btn.link {
    background: #6c757d;
    color: white;
}

.book-info h1 {
    font-size: 2.5rem;
    color: var(--primary-color);
    margin-bottom: 15px;
    line-height: 1.2;
}

.book-description {
    margin: 25px 0;
    padding: 20px;
    background: var(--background-accent);
    border-left: 4px solid var(--accent-color);
    border-radius: 8px;
}

.book-description h3 {
    color: var(--primary-color);
    margin-bottom: 12px;
    font-size: 1.3rem;
}

.book-description p {
    color: var(--text-secondary);
    line-height: 1.8;
}

.related-books-section {
    margin: 60px 0;
    padding: 40px;
    background: var(--background-color);
    border-radius: 16px;
}

.related-books-section h2 {
    color: var(--primary-color);
    margin-bottom: 30px;
    font-size: 2rem;
    display: flex;
    align-items: center;
    gap: 12px;
}

@media (max-width: 968px) {
    .book-details {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .book-image-section {
        position: relative;
        top: 0;
    }
    
    .book-image-large {
        max-width: 300px;
    }
    
    .book-info h1 {
        font-size: 2rem;
    }
}

@media (max-width: 576px) {
    .book-details {
        padding: 20px;
    }
    
    .related-books-section {
        padding: 20px;
    }
    
    .book-info h1 {
        font-size: 1.5rem;
    }
}

.book-rating-display {
    margin: 10px 0 15px;
    font-size: 1.1rem;
}

.rating-text {
    color: #666;
    margin-right: 10px;
}

.btn-wishlist {
    background: #f8f9fa;
    border: 2px solid #e74c3c;
    color: #e74c3c;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-wishlist.active {
    background: #fde8e8;
}

.btn-wishlist:hover {
    background: #e74c3c;
    color: white;
}

.reviews-section {
    margin-top: 50px;
    padding-top: 30px;
    border-top: 2px solid var(--border-color);
}

.reviews-section h2 {
    color: var(--primary-color);
    margin-bottom: 25px;
}

.review-form-container {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 30px;
}

.review-form-container h3 {
    margin: 0 0 20px;
    color: #333;
}

.rating-input {
    margin-bottom: 20px;
}

.rating-input label {
    display: block;
    margin-bottom: 10px;
    font-weight: bold;
}

.star-rating {
    display: flex;
    gap: 5px;
}

.star-rating .star {
    font-size: 2rem;
    cursor: pointer;
    transition: transform 0.2s;
}

.star-rating .star:hover {
    transform: scale(1.2);
}

.review-form textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    resize: vertical;
    font-size: 1rem;
}

.review-form textarea:focus {
    outline: none;
    border-color: var(--primary-color);
}

.review-status {
    margin-right: 15px;
    font-size: 0.9rem;
}

.review-message {
    margin-top: 15px;
    padding: 10px;
    border-radius: 8px;
    display: none;
}

.review-message.success {
    display: block;
    background: #d4edda;
    color: #155724;
}

.review-message.error {
    display: block;
    background: #f8d7da;
    color: #721c24;
}

.reviews-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.review-item {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.review-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.reviewer-name {
    font-weight: bold;
    color: #333;
}

.review-date {
    color: #888;
    font-size: 0.9rem;
}

.review-rating {
    margin-bottom: 10px;
}

.review-text {
    color: #555;
    line-height: 1.7;
}

.no-reviews {
    text-align: center;
    color: #888;
    padding: 30px;
    background: #f8f9fa;
    border-radius: 10px;
}
</style>

<script>
// Social sharing functions
function shareOnFacebook() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.title);
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=600,height=400');
}

function shareOnTwitter() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.title);
    window.open(`https://twitter.com/intent/tweet?url=${url}&text=${title}`, '_blank', 'width=600,height=400');
}

function shareOnWhatsApp() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.title);
    window.open(`https://wa.me/?text=${title}%20${url}`, '_blank');
}

function copyLink() {
    const url = window.location.href;
    
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(() => {
            showNotification('Link copied to clipboard!', 'success');
        }).catch(err => {
            fallbackCopyLink(url);
        });
    } else {
        fallbackCopyLink(url);
    }
}

function fallbackCopyLink(url) {
    const textArea = document.createElement('textarea');
    textArea.value = url;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    document.body.appendChild(textArea);
    textArea.select();
    
    try {
        document.execCommand('copy');
        showNotification('Link copied to clipboard!', 'success');
    } catch (err) {
        showNotification('Failed to copy link', 'error');
    }
    
    document.body.removeChild(textArea);
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        background: ${type === 'success' ? '#06d6a0' : type === 'error' ? '#ef233c' : '#4cc9f0'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        animation: slideInRight 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Star rating interaction
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star-rating .star');
    const ratingInput = document.getElementById('ratingInput');
    
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = this.dataset.rating;
            ratingInput.value = rating;
            
            stars.forEach((s, index) => {
                if (index < rating) {
                    s.textContent = '⭐';
                    s.classList.add('active');
                } else {
                    s.textContent = '☆';
                    s.classList.remove('active');
                }
            });
        });
        
        star.addEventListener('mouseenter', function() {
            const rating = this.dataset.rating;
            stars.forEach((s, index) => {
                if (index < rating) {
                    s.textContent = '⭐';
                } else {
                    s.textContent = '☆';
                }
            });
        });
    });
    
    document.querySelector('.star-rating').addEventListener('mouseleave', function() {
        const currentRating = ratingInput.value;
        stars.forEach((s, index) => {
            if (index < currentRating) {
                s.textContent = '⭐';
            } else {
                s.textContent = '☆';
            }
        });
    });
    
    // Review form submission
    const reviewForm = document.getElementById('reviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const rating = ratingInput.value;
            if (rating < 1) {
                alert('يرجى اختيار تقييم');
                return;
            }
            
            const formData = new FormData(this);
            
            fetch('<?php echo url("customer/submit_review.php"); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById('reviewMessage');
                messageDiv.textContent = data.message;
                messageDiv.className = 'review-message ' + (data.success ? 'success' : 'error');
                
                if (data.success) {
                    setTimeout(() => location.reload(), 2000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }
});

// Wishlist toggle
function toggleWishlist(isbn) {
    fetch('<?php echo url("customer/add_to_wishlist.php"); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ isbn: isbn, action: 'toggle' })
    })
    .then(response => response.json())
    .then(data => {
        const btn = document.getElementById('wishlistBtn');
        if (data.success) {
            if (data.in_wishlist) {
                btn.innerHTML = '❤️ في المفضلة';
                btn.classList.add('active');
            } else {
                btn.innerHTML = '🤍 أضف للمفضلة';
                btn.classList.remove('active');
            }
        }
        alert(data.message);
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ. يرجى المحاولة مرة أخرى.');
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
