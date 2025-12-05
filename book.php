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
    <a href="<?php echo url('index.php'); ?>">الرئيسية</a> &raquo;
    <a href="<?php echo url('books.php'); ?>">الكتب</a> &raquo;
    <a href="<?php echo url('books.php?category=' . urlencode($book['category'])); ?>"><?php echo htmlspecialchars($book['category']); ?></a> &raquo;
    <span><?php echo htmlspecialchars($book['title']); ?></span>
</div>

<div class="book-details">
    <div class="book-image-large">
        <i class="ph-duotone ph-book"></i>
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
                echo $i <= $rating ? '⭐' : '☆';
            }
            ?>
            <span class="rating-text"><?php echo $reviewStats['average']; ?>/5 (<?php echo $reviewStats['count']; ?> تقييم)</span>
        </div>
        <?php endif; ?>
        
        <div class="book-meta">
            <p><strong>المؤلف:</strong> <?php echo htmlspecialchars($book['authors']); ?></p>
            <?php if ($book['publisher_name']): ?>
                <p><strong>دار النشر:</strong> <?php echo htmlspecialchars($book['publisher_name']); ?></p>
            <?php endif; ?>
            <p><strong>سنة النشر:</strong> <?php echo $book['year']; ?></p>
            <p><strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?></p>
        </div>
        
        <div class="book-price-large"><?php echo number_format($book['price'], 2); ?> ريال سعودي</div>
        
        <div class="book-card-stock <?php echo $book['stock'] <= 0 ? 'out' : ($book['stock'] < $book['threshold'] ? 'low' : ''); ?>" style="font-size: 1.1rem; margin-bottom: 20px;">
            <?php 
            if ($book['stock'] <= 0) {
                echo '<i class="ph ph-x-circle"></i> غير متوفر حالياً';
            } elseif ($book['stock'] < $book['threshold']) {
                echo '<i class="ph ph-warning-circle"></i> كمية محدودة (' . $book['stock'] . ' متبقية)';
            } else {
                echo '<i class="ph ph-check-circle"></i> متوفر في المخزون';
            }
            ?>
        </div>
        
        <?php if ($book['description']): ?>
            <div class="book-description">
                <h3>عن الكتاب</h3>
                <p><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (isLoggedIn() && !isAdmin() && $book['stock'] > 0): ?>
            <form id="addToCartForm" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="quantity">الكمية:</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" 
                           max="<?php echo $book['stock']; ?>" class="form-control" style="width: 80px;">
                </div>
                <button type="button" onclick="addToCart('<?php echo $book['isbn']; ?>', document.getElementById('quantity').value)" 
                        class="btn btn-primary btn-lg">
                    <i class="ph ph-shopping-cart"></i> أضف إلى السلة
                </button>
                <button type="button" onclick="toggleWishlist('<?php echo $book['isbn']; ?>')" 
                        class="btn btn-wishlist <?php echo $inWishlist ? 'active' : ''; ?>" id="wishlistBtn">
                    <?php echo $inWishlist ? '❤️ في المفضلة' : '🤍 أضف للمفضلة'; ?>
                </button>
            </form>
        <?php elseif (isLoggedIn() && !isAdmin()): ?>
            <div class="action-buttons">
                <button type="button" onclick="toggleWishlist('<?php echo $book['isbn']; ?>')" 
                        class="btn btn-wishlist <?php echo $inWishlist ? 'active' : ''; ?>" id="wishlistBtn">
                    <?php echo $inWishlist ? '❤️ في المفضلة' : '🤍 أضف للمفضلة'; ?>
                </button>
            </div>
        <?php elseif (!isLoggedIn()): ?>
            <div class="alert alert-info">
                <a href="<?php echo url('login.php'); ?>">سجل الدخول</a> لإضافة الكتاب إلى السلة
            </div>
        <?php elseif ($book['stock'] <= 0): ?>
            <div class="alert alert-warning">
                هذا الكتاب غير متوفر حالياً. يرجى المحاولة لاحقاً.
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border-color);">
            <a href="<?php echo url('books.php'); ?>" class="btn btn-secondary">← العودة إلى الكتب</a>
        </div>
    </div>
</div>

<!-- Reviews Section -->
<div class="reviews-section">
    <h2>⭐ التقييمات والمراجعات</h2>
    
    <?php if (isLoggedIn() && !isAdmin()): ?>
        <!-- Review Form -->
        <div class="review-form-container">
            <h3><?php echo $userReview ? 'تعديل تقييمك' : 'أضف تقييمك'; ?></h3>
            <form id="reviewForm" class="review-form">
                <input type="hidden" name="isbn" value="<?php echo htmlspecialchars($isbn); ?>">
                
                <div class="rating-input">
                    <label>تقييمك:</label>
                    <div class="star-rating" id="starRating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?php echo ($userReview && $i <= $userReview['rating']) ? 'active' : ''; ?>" data-rating="<?php echo $i; ?>">
                                <?php echo ($userReview && $i <= $userReview['rating']) ? '⭐' : '☆'; ?>
                            </span>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating" id="ratingInput" value="<?php echo $userReview['rating'] ?? '0'; ?>">
                </div>
                
                <div class="form-group">
                    <label for="reviewText">رأيك في الكتاب (اختياري):</label>
                    <textarea id="reviewText" name="review_text" rows="4" placeholder="شاركنا رأيك في هذا الكتاب..."><?php echo htmlspecialchars($userReview['review_text'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <?php echo $userReview ? 'تحديث التقييم' : 'إرسال التقييم'; ?>
                </button>
                
                <?php if ($userReview): ?>
                    <span class="review-status">
                        <?php
                        switch ($userReview['status']) {
                            case 'pending': echo '⏳ قيد المراجعة'; break;
                            case 'approved': echo '✅ تم النشر'; break;
                            case 'rejected': echo '❌ تم الرفض'; break;
                        }
                        ?>
                    </span>
                <?php endif; ?>
            </form>
            <div id="reviewMessage" class="review-message"></div>
        </div>
    <?php elseif (!isLoggedIn()): ?>
        <div class="alert alert-info">
            <a href="<?php echo url('login.php'); ?>">سجل الدخول</a> لإضافة تقييمك
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
