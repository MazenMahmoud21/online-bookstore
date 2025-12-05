-- =============================================
-- Online Bookstore - Seed Data Script
-- =============================================

USE OnlineBookstore;
GO

-- =============================================
-- Insert Categories
-- =============================================
INSERT INTO Categories (CategoryName, CategoryNameAr) VALUES
('Science', N'علوم'),
('Art', N'فن'),
('Religion', N'دين'),
('History', N'تاريخ'),
('Geography', N'جغرافيا'),
('Literature', N'أدب'),
('Technology', N'تقنية'),
('Business', N'أعمال');
GO

PRINT 'Categories inserted successfully.';
GO

-- =============================================
-- Insert Publishers
-- =============================================
INSERT INTO Publishers (Name, Address, Phone) VALUES
(N'دار العلم للملايين', N'بيروت، لبنان', '+961-1-123456'),
(N'مكتبة جرير', N'الرياض، المملكة العربية السعودية', '+966-11-4650000'),
(N'دار الشروق', N'القاهرة، مصر', '+20-2-12345678'),
(N'المكتبة العربية', N'دبي، الإمارات العربية المتحدة', '+971-4-1234567'),
(N'دار النشر الحديثة', N'جدة، المملكة العربية السعودية', '+966-12-6789012');
GO

PRINT 'Publishers inserted successfully.';
GO

-- =============================================
-- Insert Authors
-- =============================================
INSERT INTO Authors (Name) VALUES
(N'أحمد خالد توفيق'),
(N'نجيب محفوظ'),
(N'طه حسين'),
(N'عباس محمود العقاد'),
(N'مصطفى صادق الرافعي'),
(N'غازي عبد الرحمن القصيبي'),
(N'محمد حسن علوان'),
(N'أحلام مستغانمي'),
(N'يوسف إدريس'),
(N'توفيق الحكيم');
GO

PRINT 'Authors inserted successfully.';
GO

-- =============================================
-- Insert Books
-- =============================================
INSERT INTO Books (ISBN, Title, TitleAr, PublisherID, PublicationYear, SellingPrice, CategoryID, QuantityInStock, ReorderThreshold, Description, DescriptionAr, ImageUrl) VALUES
-- Science Books
('978-0-13-110362-7', 'Introduction to Algorithms', N'مقدمة في الخوارزميات', 2, 2022, 150.00, 1, 25, 10, 'A comprehensive book on algorithms and data structures', N'كتاب شامل عن الخوارزميات وهياكل البيانات', '/images/books/algorithms.jpg'),
('978-0-262-03384-8', 'Artificial Intelligence', N'الذكاء الاصطناعي', 2, 2023, 200.00, 1, 15, 10, 'Modern approaches to AI', N'مناهج حديثة في الذكاء الاصطناعي', '/images/books/ai.jpg'),
('978-0-07-013151-4', 'Physics for Scientists', N'الفيزياء للعلماء', 1, 2021, 180.00, 1, 30, 10, 'Comprehensive physics textbook', N'كتاب فيزياء شامل', '/images/books/physics.jpg'),

-- Art Books
('978-0-7148-3930-6', 'Story of Art', N'قصة الفن', 3, 2020, 120.00, 2, 20, 10, 'The history of art from ancient times', N'تاريخ الفن من العصور القديمة', '/images/books/art-story.jpg'),
('978-0-500-20271-2', 'Islamic Art', N'الفن الإسلامي', 4, 2019, 95.00, 2, 18, 10, 'A beautiful exploration of Islamic art', N'استكشاف جميل للفن الإسلامي', '/images/books/islamic-art.jpg'),

-- Religion Books
('978-977-08-3245-8', 'Quran Sciences', N'علوم القرآن', 3, 2022, 75.00, 3, 50, 15, 'Introduction to Quranic sciences', N'مقدمة في علوم القرآن', '/images/books/quran-sciences.jpg'),
('978-9771-3456-7', 'Islamic History', N'التاريخ الإسلامي', 1, 2021, 85.00, 3, 40, 12, 'Comprehensive Islamic history', N'تاريخ إسلامي شامل', '/images/books/islamic-history.jpg'),
('978-9771-8888-1', 'Fiqh Essentials', N'أساسيات الفقه', 3, 2020, 65.00, 3, 35, 10, 'Basic Islamic jurisprudence', N'أساسيات الفقه الإسلامي', '/images/books/fiqh.jpg'),

-- History Books
('978-0-19-285259-6', 'History of the Arab Peoples', N'تاريخ الشعوب العربية', 4, 2018, 110.00, 4, 25, 10, 'Comprehensive Arab history', N'تاريخ عربي شامل', '/images/books/arab-history.jpg'),
('978-977-82-0167-0', 'Egypt Ancient History', N'تاريخ مصر القديم', 3, 2017, 95.00, 4, 22, 10, 'Ancient Egyptian civilization', N'الحضارة المصرية القديمة', '/images/books/egypt-history.jpg'),

-- Geography Books
('978-0-06-093564-6', 'World Geography', N'جغرافية العالم', 2, 2022, 130.00, 5, 28, 10, 'Comprehensive world geography', N'جغرافيا العالم الشاملة', '/images/books/world-geo.jpg'),
('978-9771-5555-3', 'Arabian Peninsula', N'شبه الجزيرة العربية', 5, 2021, 88.00, 5, 30, 10, 'Geography of Arabian Peninsula', N'جغرافيا شبه الجزيرة العربية', '/images/books/arabian-geo.jpg'),

-- Literature Books
('978-977-06-1423-8', N'أولاد حارتنا', N'أولاد حارتنا', 3, 2015, 55.00, 6, 45, 15, N'رواية نجيب محفوظ الشهيرة', N'رواية نجيب محفوظ الشهيرة', '/images/books/awlad-haratna.jpg'),
('978-977-06-1424-5', N'الأيام', N'الأيام', 3, 2016, 45.00, 6, 38, 12, N'سيرة ذاتية لطه حسين', N'سيرة ذاتية لطه حسين', '/images/books/al-ayyam.jpg'),
('978-9953-21-426-0', N'ذاكرة الجسد', N'ذاكرة الجسد', 1, 2018, 60.00, 6, 42, 15, N'رواية أحلام مستغانمي', N'رواية أحلام مستغانمي', '/images/books/dhakira.jpg'),

-- Technology Books
('978-1-491-95038-8', 'Python Programming', N'برمجة بايثون', 2, 2023, 165.00, 7, 35, 12, 'Learn Python programming', N'تعلم برمجة بايثون', '/images/books/python.jpg'),
('978-1-449-37321-8', 'JavaScript Guide', N'دليل جافاسكريبت', 2, 2022, 145.00, 7, 32, 12, 'Complete JavaScript reference', N'مرجع جافاسكريبت الكامل', '/images/books/javascript.jpg'),
('978-0-596-51774-8', 'Web Development', N'تطوير الويب', 4, 2023, 175.00, 7, 28, 10, 'Modern web development techniques', N'تقنيات تطوير الويب الحديثة', '/images/books/webdev.jpg'),

-- Business Books
('978-0-06-229348-0', 'Rich Dad Poor Dad', N'الأب الغني والأب الفقير', 2, 2017, 70.00, 8, 50, 15, 'Financial education classic', N'كلاسيكية التعليم المالي', '/images/books/rich-dad.jpg'),
('978-0-06-231609-7', 'Think and Grow Rich', N'فكر تصبح غنياً', 4, 2019, 65.00, 8, 45, 15, 'Success and wealth mindset', N'عقلية النجاح والثروة', '/images/books/think-rich.jpg');
GO

PRINT 'Books inserted successfully.';
GO

-- =============================================
-- Insert BookAuthors (Many-to-Many relationships)
-- =============================================
INSERT INTO BookAuthors (ISBN, AuthorID) VALUES
-- Science books
('978-0-13-110362-7', 1),
('978-0-262-03384-8', 1),
('978-0-07-013151-4', 4),

-- Art books
('978-0-7148-3930-6', 3),
('978-0-500-20271-2', 5),

-- Religion books
('978-977-08-3245-8', 4),
('978-9771-3456-7', 3),
('978-9771-8888-1', 5),

-- History books
('978-0-19-285259-6', 4),
('978-977-82-0167-0', 3),

-- Geography books
('978-0-06-093564-6', 4),
('978-9771-5555-3', 6),

-- Literature books
('978-977-06-1423-8', 2),  -- نجيب محفوظ
('978-977-06-1424-5', 3),  -- طه حسين
('978-9953-21-426-0', 8),  -- أحلام مستغانمي

-- Technology books
('978-1-491-95038-8', 1),
('978-1-449-37321-8', 1),
('978-0-596-51774-8', 7),

-- Business books
('978-0-06-229348-0', 6),
('978-0-06-231609-7', 6);
GO

PRINT 'BookAuthors relationships inserted successfully.';
GO

-- =============================================
-- Insert Users (Admin and Customers)
-- Password hash is for 'Password123!' using BCrypt
-- =============================================
-- Note: These are sample BCrypt hashes. In production, generate proper hashes.
DECLARE @AdminPasswordHash NVARCHAR(255) = '$2a$11$8uQCH5nKhRn.LXtQlJAcVOTHUdJtPnmjPZVQEm/KkjQHK5LGlvK/a';
DECLARE @CustomerPasswordHash NVARCHAR(255) = '$2a$11$8uQCH5nKhRn.LXtQlJAcVOTHUdJtPnmjPZVQEm/KkjQHK5LGlvK/a';

INSERT INTO Users (Username, PasswordHash, Role, FirstName, LastName, Email, Phone, ShippingAddress) VALUES
-- Admin users
('admin', @AdminPasswordHash, 'Admin', N'محمد', N'الإداري', 'admin@bookstore.sa', '+966-50-1234567', N'الرياض، حي العليا، شارع العروبة'),
('admin2', @AdminPasswordHash, 'Admin', N'أحمد', N'المدير', 'admin2@bookstore.sa', '+966-50-7654321', N'جدة، حي الروضة، شارع الملك فهد'),

-- Customer users
('customer1', @CustomerPasswordHash, 'Customer', N'فاطمة', N'العلي', 'fatima@email.com', '+966-55-1111111', N'الرياض، حي النخيل، شارع الأمير سلطان'),
('customer2', @CustomerPasswordHash, 'Customer', N'عبدالله', N'السعيد', 'abdullah@email.com', '+966-55-2222222', N'جدة، حي الصفا، شارع فلسطين'),
('customer3', @CustomerPasswordHash, 'Customer', N'نورة', N'القحطاني', 'noura@email.com', '+966-55-3333333', N'الدمام، حي الفيصلية، شارع الملك سعود'),
('customer4', @CustomerPasswordHash, 'Customer', N'خالد', N'العتيبي', 'khaled@email.com', '+966-55-4444444', N'مكة المكرمة، حي العزيزية'),
('customer5', @CustomerPasswordHash, 'Customer', N'سارة', N'الدوسري', 'sara@email.com', '+966-55-5555555', N'المدينة المنورة، حي قباء');
GO

PRINT 'Users inserted successfully.';
GO

-- =============================================
-- Create Shopping Carts for all customers
-- =============================================
INSERT INTO ShoppingCarts (UserID)
SELECT UserID FROM Users WHERE Role = 'Customer';
GO

PRINT 'Shopping carts created for all customers.';
GO

-- =============================================
-- Insert Sample Cart Items (for testing)
-- =============================================
-- Add items to customer1's cart
DECLARE @CartID1 INT;
SELECT @CartID1 = CartID FROM ShoppingCarts sc INNER JOIN Users u ON sc.UserID = u.UserID WHERE u.Username = 'customer1';

INSERT INTO CartItems (CartID, ISBN, Quantity) VALUES
(@CartID1, '978-0-13-110362-7', 1),
(@CartID1, '978-977-06-1423-8', 2);
GO

-- Add items to customer2's cart
DECLARE @CartID2 INT;
SELECT @CartID2 = CartID FROM ShoppingCarts sc INNER JOIN Users u ON sc.UserID = u.UserID WHERE u.Username = 'customer2';

INSERT INTO CartItems (CartID, ISBN, Quantity) VALUES
(@CartID2, '978-1-491-95038-8', 1),
(@CartID2, '978-0-06-229348-0', 1);
GO

PRINT 'Sample cart items added.';
GO

-- =============================================
-- Insert Sample Customer Orders (for reports testing)
-- =============================================
-- Create some historical orders for reporting
DECLARE @Customer3ID INT, @Customer4ID INT, @Customer5ID INT;
SELECT @Customer3ID = UserID FROM Users WHERE Username = 'customer3';
SELECT @Customer4ID = UserID FROM Users WHERE Username = 'customer4';
SELECT @Customer5ID = UserID FROM Users WHERE Username = 'customer5';

-- Orders from last 3 months
INSERT INTO CustomerOrders (UserID, OrderDate, TotalAmount, CreditCardNumber, CreditCardExpiry, Status, ShippingAddress)
VALUES
(@Customer3ID, DATEADD(DAY, -60, GETDATE()), 335.00, '1234', '2025-12-31', 'Delivered', N'الدمام، حي الفيصلية'),
(@Customer3ID, DATEADD(DAY, -30, GETDATE()), 220.00, '1234', '2025-12-31', 'Delivered', N'الدمام، حي الفيصلية'),
(@Customer4ID, DATEADD(DAY, -45, GETDATE()), 450.00, '5678', '2026-06-30', 'Delivered', N'مكة المكرمة، حي العزيزية'),
(@Customer4ID, DATEADD(DAY, -15, GETDATE()), 175.00, '5678', '2026-06-30', 'Shipped', N'مكة المكرمة، حي العزيزية'),
(@Customer5ID, DATEADD(DAY, -20, GETDATE()), 280.00, '9012', '2025-09-30', 'Delivered', N'المدينة المنورة، حي قباء');

-- Get order IDs
DECLARE @Order1ID INT, @Order2ID INT, @Order3ID INT, @Order4ID INT, @Order5ID INT;
SELECT @Order1ID = MIN(CustOrderID) FROM CustomerOrders WHERE UserID = @Customer3ID;
SELECT @Order2ID = MAX(CustOrderID) FROM CustomerOrders WHERE UserID = @Customer3ID;
SELECT @Order3ID = MIN(CustOrderID) FROM CustomerOrders WHERE UserID = @Customer4ID;
SELECT @Order4ID = MAX(CustOrderID) FROM CustomerOrders WHERE UserID = @Customer4ID;
SELECT @Order5ID = CustOrderID FROM CustomerOrders WHERE UserID = @Customer5ID;

-- Insert order items
INSERT INTO CustomerOrderItems (CustOrderID, ISBN, Quantity, UnitPrice)
VALUES
-- Order 1
(@Order1ID, '978-0-13-110362-7', 1, 150.00),
(@Order1ID, '978-0-262-03384-8', 1, 200.00),
-- Order 2
(@Order2ID, '978-977-06-1423-8', 2, 55.00),
(@Order2ID, '978-0-06-093564-6', 1, 130.00),
-- Order 3
(@Order3ID, '978-0-07-013151-4', 1, 180.00),
(@Order3ID, '978-1-491-95038-8', 1, 165.00),
(@Order3ID, '978-977-08-3245-8', 1, 75.00),
-- Order 4
(@Order4ID, '978-0-596-51774-8', 1, 175.00),
-- Order 5
(@Order5ID, '978-0-19-285259-6', 1, 110.00),
(@Order5ID, '978-0-06-229348-0', 2, 70.00);
GO

PRINT 'Sample customer orders inserted.';
GO

-- =============================================
-- Insert Sample Publisher Orders (for admin testing)
-- =============================================
-- Create some pending publisher orders
INSERT INTO PublisherOrders (PublisherID, OrderDate, Status, TotalAmount, Notes)
VALUES
(2, DATEADD(DAY, -5, GETDATE()), 'Pending', 4500.00, N'طلب إعادة تخزين - كتب التقنية'),
(3, DATEADD(DAY, -3, GETDATE()), 'Pending', 2000.00, N'طلب إعادة تخزين - كتب الأدب');

DECLARE @PubOrder1ID INT, @PubOrder2ID INT;
SELECT @PubOrder1ID = MIN(PubOrderID) FROM PublisherOrders WHERE Status = 'Pending';
SELECT @PubOrder2ID = MAX(PubOrderID) FROM PublisherOrders WHERE Status = 'Pending';

INSERT INTO PublisherOrderItems (PubOrderID, ISBN, Quantity, UnitPrice)
VALUES
(@PubOrder1ID, '978-1-491-95038-8', 50, 90.00),
(@PubOrder2ID, '978-977-06-1423-8', 50, 40.00);
GO

-- Create a confirmed publisher order (historical)
INSERT INTO PublisherOrders (PublisherID, OrderDate, Status, TotalAmount, Notes, ConfirmedAt)
VALUES
(1, DATEADD(DAY, -30, GETDATE()), 'Confirmed', 3000.00, N'طلب مكتمل', DATEADD(DAY, -25, GETDATE()));

DECLARE @ConfirmedPubOrderID INT;
SELECT @ConfirmedPubOrderID = PubOrderID FROM PublisherOrders WHERE Status = 'Confirmed';

INSERT INTO PublisherOrderItems (PubOrderID, ISBN, Quantity, UnitPrice)
VALUES
(@ConfirmedPubOrderID, '978-9953-21-426-0', 50, 60.00);
GO

PRINT 'Sample publisher orders inserted.';
GO

PRINT '========================================';
PRINT 'Seed data insertion completed successfully!';
PRINT '========================================';
PRINT 'Sample Admin Credentials:';
PRINT '  Username: admin';
PRINT '  Password: Password123!';
PRINT '';
PRINT 'Sample Customer Credentials:';
PRINT '  Username: customer1';
PRINT '  Password: Password123!';
PRINT '========================================';
GO
