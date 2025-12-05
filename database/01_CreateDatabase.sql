-- =============================================
-- Online Bookstore Database Creation Script
-- =============================================

-- Create the database
IF NOT EXISTS (SELECT name FROM sys.databases WHERE name = 'OnlineBookstore')
BEGIN
    CREATE DATABASE OnlineBookstore;
END
GO

USE OnlineBookstore;
GO

-- =============================================
-- Drop existing tables if they exist (for clean setup)
-- =============================================
IF OBJECT_ID('dbo.CartItems', 'U') IS NOT NULL DROP TABLE dbo.CartItems;
IF OBJECT_ID('dbo.ShoppingCarts', 'U') IS NOT NULL DROP TABLE dbo.ShoppingCarts;
IF OBJECT_ID('dbo.CustomerOrderItems', 'U') IS NOT NULL DROP TABLE dbo.CustomerOrderItems;
IF OBJECT_ID('dbo.CustomerOrders', 'U') IS NOT NULL DROP TABLE dbo.CustomerOrders;
IF OBJECT_ID('dbo.PublisherOrderItems', 'U') IS NOT NULL DROP TABLE dbo.PublisherOrderItems;
IF OBJECT_ID('dbo.PublisherOrders', 'U') IS NOT NULL DROP TABLE dbo.PublisherOrders;
IF OBJECT_ID('dbo.BookAuthors', 'U') IS NOT NULL DROP TABLE dbo.BookAuthors;
IF OBJECT_ID('dbo.Books', 'U') IS NOT NULL DROP TABLE dbo.Books;
IF OBJECT_ID('dbo.Authors', 'U') IS NOT NULL DROP TABLE dbo.Authors;
IF OBJECT_ID('dbo.Categories', 'U') IS NOT NULL DROP TABLE dbo.Categories;
IF OBJECT_ID('dbo.Publishers', 'U') IS NOT NULL DROP TABLE dbo.Publishers;
IF OBJECT_ID('dbo.Users', 'U') IS NOT NULL DROP TABLE dbo.Users;
GO

PRINT 'Database OnlineBookstore created/verified successfully.';
GO
