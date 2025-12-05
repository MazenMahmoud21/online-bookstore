-- =============================================
-- Sample Data - Test/Demo Data Only
-- Online Bookstore Order Processing System
-- =============================================

USE online_bookstore;

-- =============================================
-- Clear existing data (in reverse order due to foreign keys)
-- =============================================
DELETE FROM orders_from_publishers;
DELETE FROM sales_items;
DELETE FROM sales;
DELETE FROM reviews;
DELETE FROM wishlist;
DELETE FROM cart;
DELETE FROM books;
DELETE FROM customers;
DELETE FROM publishers;
DELETE FROM coupons;

-- Reset auto-increment counters
ALTER TABLE publishers AUTO_INCREMENT = 1;
ALTER TABLE customers AUTO_INCREMENT = 1;
ALTER TABLE sales AUTO_INCREMENT = 1;
ALTER TABLE orders_from_publishers AUTO_INCREMENT = 1;

-- =============================================
-- Publishers Data - Example Publishers
-- =============================================
INSERT INTO publishers (name, address, phone) VALUES
('TechBooks Publishing', '123 Main Street, City A', '0111111111'),
('Knowledge Press', '456 Oak Avenue, City B', '0122222222'),
('Modern Literature House', '789 Pine Road, City C', '0133333333'),
('Academic Publishers Inc', '321 Elm Street, City D', '0144444444'),
('Digital Media Books', '654 Maple Drive, City E', '0155555555');

-- =============================================
-- Books Data - Example Books
-- =============================================
INSERT INTO books (isbn, title, authors, publisher_id, year, price, category, stock, threshold, description) VALUES
('978-1234-00-001', 'Introduction to Computer Science', 'John Smith', 1, 2022, 85.00, 'Technology', 25, 5, 'A comprehensive guide to computer science fundamentals for beginners'),
('978-1234-00-002', 'Python Programming Basics', 'Jane Doe', 1, 2023, 120.00, 'Programming', 15, 5, 'Learn programming from scratch using Python language'),
('978-1234-00-003', 'World History Volume 1', 'Robert Johnson', 2, 2021, 75.00, 'History', 30, 5, 'A comprehensive overview of world history'),
('978-1234-00-004', 'Modern Literature Studies', 'Emily Williams', 2, 2020, 65.00, 'Literature', 20, 5, 'An in-depth study of contemporary literature'),
('978-1234-00-005', 'Digital Marketing Essentials', 'Michael Brown', 3, 2023, 95.00, 'Business', 18, 5, 'Complete guide to marketing in the digital age'),
('978-1234-00-006', 'Artificial Intelligence for Beginners', 'David Lee', 1, 2023, 150.00, 'Technology', 12, 5, 'Introduction to artificial intelligence and its applications'),
('978-1234-00-007', 'Art History Fundamentals', 'Sarah Taylor', 4, 2019, 55.00, 'Arts', 40, 5, 'Learn the foundations of art history and styles'),
('978-1234-00-008', 'Cooking Around the World', 'Chef Alex Martin', 4, 2022, 80.00, 'Cooking', 35, 5, 'Traditional recipes from various cuisines'),
('978-1234-00-009', 'Business Mathematics', 'Professor James Wilson', 5, 2021, 110.00, 'Education', 22, 5, 'Applied mathematics for business professionals'),
('978-1234-00-010', 'Professional Project Management', 'Lisa Anderson', 3, 2023, 130.00, 'Management', 8, 5, 'Comprehensive guide to successful project management'),
('978-1234-00-011', 'Web Application Development', 'Tom Davis', 1, 2023, 140.00, 'Programming', 10, 5, 'Building professional web applications'),
('978-1234-00-012', 'Health and Nutrition Guide', 'Dr. Mary Thompson', 5, 2022, 70.00, 'Health', 28, 5, 'Health and nutrition tips for everyday life');

-- =============================================
-- Customers Data - Example Users
-- Password: 123456 (hashed with password_hash)
-- =============================================
INSERT INTO customers (username, password_hash, first_name, last_name, email, phone, address, is_admin) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin@example.com', '0501111111', '123 Admin Street, City A', 1),
('user1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'First', 'Customer', 'user1@example.com', '0552222222', '456 Customer Ave, City B', 0),
('user2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Second', 'Buyer', 'user2@example.com', '0563333333', '789 Buyer Road, City C', 0),
('user3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Third', 'Reader', 'user3@example.com', '0574444444', '321 Reader Lane, City D', 0),
('user4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Fourth', 'Shopper', 'user4@example.com', '0585555555', '654 Shopper Drive, City E', 0);

-- =============================================
-- Sales Data - Example Sales
-- =============================================
INSERT INTO sales (customer_id, date, total_amount) VALUES
(2, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 205.00),
(3, DATE_SUB(CURDATE(), INTERVAL 10 DAY), 120.00),
(4, DATE_SUB(CURDATE(), INTERVAL 15 DAY), 280.00),
(2, DATE_SUB(CURDATE(), INTERVAL 20 DAY), 95.00),
(5, DATE_SUB(CURDATE(), INTERVAL 25 DAY), 150.00),
(3, DATE_SUB(CURDATE(), INTERVAL 30 DAY), 185.00),
(4, DATE_SUB(CURDATE(), INTERVAL 45 DAY), 320.00),
(2, DATE_SUB(CURDATE(), INTERVAL 60 DAY), 175.00);

-- =============================================
-- Sales Items Data - Example Order Items
-- =============================================
INSERT INTO sales_items (sale_id, book_isbn, qty, price) VALUES
(1, '978-1234-00-001', 1, 85.00),
(1, '978-1234-00-002', 1, 120.00),
(2, '978-1234-00-002', 1, 120.00),
(3, '978-1234-00-006', 1, 150.00),
(3, '978-1234-00-011', 1, 130.00),
(4, '978-1234-00-005', 1, 95.00),
(5, '978-1234-00-006', 1, 150.00),
(6, '978-1234-00-001', 1, 85.00),
(6, '978-1234-00-003', 1, 75.00),
(6, '978-1234-00-007', 1, 55.00),
(7, '978-1234-00-009', 2, 110.00),
(7, '978-1234-00-012', 1, 70.00),
(8, '978-1234-00-004', 2, 65.00),
(8, '978-1234-00-008', 1, 80.00);

-- =============================================
-- Publisher Orders Data - Example Orders from Publishers
-- =============================================
INSERT INTO orders_from_publishers (book_isbn, qty, status) VALUES
('978-1234-00-010', 20, 'pending'),
('978-1234-00-011', 15, 'confirmed'),
('978-1234-00-006', 25, 'pending');

