-- =============================================
-- نظام معالجة طلبات المكتبة الإلكترونية
-- Online Bookstore Order Processing System
-- Database Schema for XAMPP/MySQL
-- =============================================

-- Create Database
CREATE DATABASE IF NOT EXISTS online_bookstore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE online_bookstore;

-- =============================================
-- الناشرين - Publishers Table
-- =============================================
CREATE TABLE publishers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- الكتب - Books Table
-- =============================================
CREATE TABLE books (
    isbn VARCHAR(20) PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    authors VARCHAR(500) NOT NULL,
    publisher_id INT,
    year INT,
    price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(100),
    stock INT DEFAULT 0,
    threshold INT DEFAULT 5,
    image_url VARCHAR(500) DEFAULT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (publisher_id) REFERENCES publishers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- طلبات من الناشرين - Orders from Publishers Table
-- =============================================
CREATE TABLE orders_from_publishers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_isbn VARCHAR(20) NOT NULL,
    qty INT NOT NULL,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    FOREIGN KEY (book_isbn) REFERENCES books(isbn) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- العملاء - Customers Table
-- =============================================
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- سلة التسوق - Shopping Cart Table
-- =============================================
CREATE TABLE shopping_cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- عناصر السلة - Cart Items Table
-- =============================================
CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    book_isbn VARCHAR(20) NOT NULL,
    qty INT NOT NULL DEFAULT 1,
    FOREIGN KEY (cart_id) REFERENCES shopping_cart(id) ON DELETE CASCADE,
    FOREIGN KEY (book_isbn) REFERENCES books(isbn) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_book (cart_id, book_isbn)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- المبيعات - Sales Table
-- =============================================
CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- عناصر المبيعات - Sales Items Table
-- =============================================
CREATE TABLE sales_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    book_isbn VARCHAR(20) NOT NULL,
    qty INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (book_isbn) REFERENCES books(isbn) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TRIGGERS
-- =============================================

-- TRIGGER 1: Prevent negative stock
DELIMITER //
CREATE TRIGGER prevent_negative_stock
BEFORE UPDATE ON books
FOR EACH ROW
BEGIN
    IF NEW.stock < 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'لا يمكن أن يكون المخزون سالباً';
    END IF;
END //
DELIMITER ;

-- TRIGGER 2: Auto-create restock order when stock drops below threshold
DELIMITER //
CREATE TRIGGER auto_restock_order
AFTER UPDATE ON books
FOR EACH ROW
BEGIN
    IF NEW.stock < NEW.threshold AND OLD.stock >= OLD.threshold THEN
        INSERT INTO orders_from_publishers (book_isbn, qty, status)
        VALUES (NEW.isbn, NEW.threshold * 2, 'pending');
    END IF;
END //
DELIMITER ;

-- TRIGGER 3: When admin confirms order, stock increases automatically
DELIMITER //
CREATE TRIGGER update_stock_on_confirm
AFTER UPDATE ON orders_from_publishers
FOR EACH ROW
BEGIN
    IF NEW.status = 'confirmed' AND OLD.status = 'pending' THEN
        UPDATE books 
        SET stock = stock + NEW.qty 
        WHERE isbn = NEW.book_isbn;
    END IF;
END //
DELIMITER ;

-- =============================================
-- STORED PROCEDURES
-- =============================================

-- Stored Procedure: Confirm order from publisher
DELIMITER //
CREATE PROCEDURE confirm_publisher_order(IN order_id INT)
BEGIN
    UPDATE orders_from_publishers 
    SET status = 'confirmed' 
    WHERE id = order_id AND status = 'pending';
END //
DELIMITER ;

-- Stored Procedure: Get sales report for last month
DELIMITER //
CREATE PROCEDURE get_sales_last_month()
BEGIN
    SELECT 
        s.id AS sale_id,
        CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
        s.date,
        s.total_amount
    FROM sales s
    JOIN customers c ON s.customer_id = c.id
    WHERE s.date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
    ORDER BY s.date DESC;
END //
DELIMITER ;

-- Stored Procedure: Get sales on a selected day
DELIMITER //
CREATE PROCEDURE get_sales_on_day(IN selected_date DATE)
BEGIN
    SELECT 
        s.id AS sale_id,
        CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
        s.date,
        s.total_amount
    FROM sales s
    JOIN customers c ON s.customer_id = c.id
    WHERE DATE(s.date) = selected_date
    ORDER BY s.date DESC;
END //
DELIMITER ;

-- Stored Procedure: Get top 5 customers in last 3 months
DELIMITER //
CREATE PROCEDURE get_top_customers()
BEGIN
    SELECT 
        c.id,
        CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
        c.email,
        COUNT(s.id) AS order_count,
        SUM(s.total_amount) AS total_spent
    FROM customers c
    JOIN sales s ON c.id = s.customer_id
    WHERE s.date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
    GROUP BY c.id
    ORDER BY total_spent DESC
    LIMIT 5;
END //
DELIMITER ;

-- Stored Procedure: Get top 10 selling books in last 3 months
DELIMITER //
CREATE PROCEDURE get_top_selling_books()
BEGIN
    SELECT 
        b.isbn,
        b.title,
        b.authors,
        SUM(si.qty) AS total_sold,
        SUM(si.qty * si.price) AS total_revenue
    FROM books b
    JOIN sales_items si ON b.isbn = si.book_isbn
    JOIN sales s ON si.sale_id = s.id
    WHERE s.date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
    GROUP BY b.isbn
    ORDER BY total_sold DESC
    LIMIT 10;
END //
DELIMITER ;

-- Stored Procedure: Get book reorder count
DELIMITER //
CREATE PROCEDURE get_book_reorder_count(IN book_isbn_param VARCHAR(20))
BEGIN
    SELECT 
        b.isbn,
        b.title,
        COUNT(o.id) AS reorder_count
    FROM books b
    LEFT JOIN orders_from_publishers o ON b.isbn = o.book_isbn
    WHERE b.isbn = book_isbn_param
    GROUP BY b.isbn;
END //
DELIMITER ;

-- Stored Procedure: Get all books reorder statistics
DELIMITER //
CREATE PROCEDURE get_all_books_reorder_stats()
BEGIN
    SELECT 
        b.isbn,
        b.title,
        COUNT(o.id) AS reorder_count
    FROM books b
    LEFT JOIN orders_from_publishers o ON b.isbn = o.book_isbn
    GROUP BY b.isbn
    ORDER BY reorder_count DESC;
END //
DELIMITER ;
