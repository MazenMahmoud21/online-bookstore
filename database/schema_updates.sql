
USE online_bookstore;

-- =============================================
-- إعادة تعيين كلمة المرور - Password Resets Table
-- =============================================
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_email (email),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- محاولات تسجيل الدخول - Login Attempts Table (Rate Limiting)
-- =============================================
CREATE TABLE IF NOT EXISTS rate_limit_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action_type VARCHAR(50) NOT NULL,
    identifier VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    success TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_action_identifier (action_type, identifier),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- قائمة الأمنيات - Wishlists Table
-- =============================================
CREATE TABLE IF NOT EXISTS wishlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    book_isbn VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (book_isbn) REFERENCES books(isbn) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist_item (customer_id, book_isbn)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- تقييمات الكتب - Book Reviews Table
-- =============================================
CREATE TABLE IF NOT EXISTS book_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_isbn VARCHAR(20) NOT NULL,
    customer_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (book_isbn) REFERENCES books(isbn) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_customer_review (book_isbn, customer_id),
    INDEX idx_book_rating (book_isbn, rating),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- كوبونات الخصم - Coupons Table
-- =============================================
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_active_dates (is_active, start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- استخدام الكوبونات - Coupon Usage Table
-- =============================================
CREATE TABLE IF NOT EXISTS coupon_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    coupon_id INT NOT NULL,
    customer_id INT NOT NULL,
    sale_id INT NOT NULL,
    discount_amount DECIMAL(10, 2) NOT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    UNIQUE KEY unique_coupon_sale (coupon_id, sale_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- رسائل الاتصال - Contact Messages Table
-- =============================================
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied', 'closed') DEFAULT 'new',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- تتبع الطلبات - Order Tracking Table
-- =============================================
CREATE TABLE IF NOT EXISTS order_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    INDEX idx_sale_status (sale_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- إشعارات المستخدمين - User Notifications Table
-- =============================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('order', 'promo', 'system', 'review') DEFAULT 'system',
    is_read TINYINT(1) DEFAULT 0,
    link VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_customer_read (customer_id, is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- سجل الأنشطة - Activity Log Table
-- =============================================
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    user_type ENUM('customer', 'admin', 'system') DEFAULT 'customer',
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id VARCHAR(50),
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id, user_type),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- إضافة عمود status للمبيعات إذا لم يكن موجوداً
-- Add status column to sales if not exists
-- =============================================
ALTER TABLE sales 
ADD COLUMN IF NOT EXISTS status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
ADD COLUMN IF NOT EXISTS shipping_address TEXT,
ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) DEFAULT 'cash_on_delivery',
ADD COLUMN IF NOT EXISTS notes TEXT,
ADD COLUMN IF NOT EXISTS coupon_id INT DEFAULT NULL,
ADD COLUMN IF NOT EXISTS discount_amount DECIMAL(10, 2) DEFAULT 0;

-- =============================================
-- إضافة عمود الصور للكتب
-- Add more columns to books if not exists
-- =============================================
ALTER TABLE books
ADD COLUMN IF NOT EXISTS average_rating DECIMAL(3, 2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS review_count INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS is_featured TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS is_new TINYINT(1) DEFAULT 0;

-- =============================================
-- إضافة أعمدة للعملاء
-- Add more columns to customers if not exists
-- =============================================
ALTER TABLE customers
ADD COLUMN IF NOT EXISTS city VARCHAR(100),
ADD COLUMN IF NOT EXISTS postal_code VARCHAR(20),
ADD COLUMN IF NOT EXISTS last_login DATETIME,
ADD COLUMN IF NOT EXISTS email_verified TINYINT(1) DEFAULT 0;

-- =============================================
-- STORED PROCEDURES الجديدة
-- New Stored Procedures
-- =============================================

-- Procedure: Update book average rating
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS update_book_rating(IN book_isbn_param VARCHAR(20))
BEGIN
    UPDATE books SET 
        average_rating = (
            SELECT COALESCE(AVG(rating), 0) 
            FROM book_reviews 
            WHERE book_isbn = book_isbn_param AND status = 'approved'
        ),
        review_count = (
            SELECT COUNT(*) 
            FROM book_reviews 
            WHERE book_isbn = book_isbn_param AND status = 'approved'
        )
    WHERE isbn = book_isbn_param;
END //
DELIMITER ;

-- Procedure: Validate and apply coupon
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS validate_coupon(
    IN coupon_code VARCHAR(50),
    IN order_amount DECIMAL(10, 2),
    IN customer_id_param INT,
    OUT is_valid BOOLEAN,
    OUT discount DECIMAL(10, 2),
    OUT error_message VARCHAR(255)
)
BEGIN
    DECLARE coupon_rec_id INT;
    DECLARE coupon_discount_type VARCHAR(20);
    DECLARE coupon_discount_value DECIMAL(10, 2);
    DECLARE coupon_min_amount DECIMAL(10, 2);
    DECLARE coupon_max_discount DECIMAL(10, 2);
    DECLARE coupon_usage_limit INT;
    DECLARE coupon_used_count INT;
    DECLARE customer_used BOOLEAN;
    
    SET is_valid = FALSE;
    SET discount = 0;
    SET error_message = '';
    
    -- Get coupon details
    SELECT id, discount_type, discount_value, min_order_amount, max_discount, usage_limit, used_count
    INTO coupon_rec_id, coupon_discount_type, coupon_discount_value, coupon_min_amount, coupon_max_discount, coupon_usage_limit, coupon_used_count
    FROM coupons
    WHERE code = coupon_code 
        AND is_active = 1 
        AND CURDATE() BETWEEN start_date AND end_date
    LIMIT 1;
    
    IF coupon_rec_id IS NULL THEN
        SET error_message = 'كوبون غير صالح أو منتهي الصلاحية';
    ELSEIF order_amount < coupon_min_amount THEN
        SET error_message = CONCAT('الحد الأدنى للطلب ', coupon_min_amount, ' ر.س');
    ELSEIF coupon_usage_limit IS NOT NULL AND coupon_used_count >= coupon_usage_limit THEN
        SET error_message = 'تم استنفاد هذا الكوبون';
    ELSE
        -- Check if customer already used this coupon
        SELECT COUNT(*) > 0 INTO customer_used
        FROM coupon_usage
        WHERE coupon_id = coupon_rec_id AND customer_id = customer_id_param;
        
        IF customer_used THEN
            SET error_message = 'لقد استخدمت هذا الكوبون من قبل';
        ELSE
            SET is_valid = TRUE;
            
            IF coupon_discount_type = 'percentage' THEN
                SET discount = order_amount * (coupon_discount_value / 100);
                IF coupon_max_discount IS NOT NULL AND discount > coupon_max_discount THEN
                    SET discount = coupon_max_discount;
                END IF;
            ELSE
                SET discount = coupon_discount_value;
            END IF;
        END IF;
    END IF;
END //
DELIMITER ;

-- Procedure: Get customer order history with tracking
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS get_customer_orders(IN customer_id_param INT)
BEGIN
    SELECT 
        s.id AS order_id,
        s.date AS order_date,
        s.total_amount,
        s.status,
        s.discount_amount,
        (s.total_amount - s.discount_amount) AS final_amount,
        COUNT(si.id) AS item_count,
        (SELECT status FROM order_tracking WHERE sale_id = s.id ORDER BY created_at DESC LIMIT 1) AS latest_tracking
    FROM sales s
    LEFT JOIN sales_items si ON s.id = si.sale_id
    WHERE s.customer_id = customer_id_param
    GROUP BY s.id
    ORDER BY s.date DESC;
END //
DELIMITER ;

-- =============================================
-- TRIGGERS الجديدة
-- New Triggers
-- =============================================

-- Trigger: Auto-update book rating when review is approved
DELIMITER //
CREATE TRIGGER IF NOT EXISTS update_rating_on_review
AFTER UPDATE ON book_reviews
FOR EACH ROW
BEGIN
    IF NEW.status = 'approved' AND (OLD.status != 'approved' OR OLD.rating != NEW.rating) THEN
        CALL update_book_rating(NEW.book_isbn);
    END IF;
END //
DELIMITER ;

-- Trigger: Create initial tracking entry when sale is created
DELIMITER //
CREATE TRIGGER IF NOT EXISTS create_order_tracking
AFTER INSERT ON sales
FOR EACH ROW
BEGIN
    INSERT INTO order_tracking (sale_id, status, notes)
    VALUES (NEW.id, 'pending', 'تم إنشاء الطلب');
END //
DELIMITER ;

-- =============================================
-- بيانات تجريبية للكوبونات
-- Sample Data for Coupons
-- =============================================
INSERT IGNORE INTO coupons (code, description, discount_type, discount_value, min_order_amount, max_discount, start_date, end_date) VALUES
('WELCOME10', 'خصم 10% للعملاء الجدد', 'percentage', 10.00, 50.00, 30.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR)),
('SAVE20', 'خصم 20 ريال على الطلبات فوق 100 ريال', 'fixed', 20.00, 100.00, NULL, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 6 MONTH)),
('BOOKS25', 'خصم 25% على جميع الكتب', 'percentage', 25.00, 75.00, 50.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 MONTH));


