-- =============================================
-- Online Bookstore - Create Tables Script
-- =============================================

USE OnlineBookstore;
GO

-- =============================================
-- Users Table
-- =============================================
CREATE TABLE Users (
    UserID INT IDENTITY(1,1) PRIMARY KEY,
    Username NVARCHAR(50) NOT NULL UNIQUE,
    PasswordHash NVARCHAR(255) NOT NULL,
    Role NVARCHAR(20) NOT NULL CHECK (Role IN ('Admin', 'Customer')),
    FirstName NVARCHAR(100) NOT NULL,
    LastName NVARCHAR(100) NOT NULL,
    Email NVARCHAR(255) NOT NULL UNIQUE,
    Phone NVARCHAR(20),
    ShippingAddress NVARCHAR(500),
    CreatedAt DATETIME2 DEFAULT GETDATE()
);
GO

-- =============================================
-- Publishers Table
-- =============================================
CREATE TABLE Publishers (
    PublisherID INT IDENTITY(1,1) PRIMARY KEY,
    Name NVARCHAR(200) NOT NULL,
    Address NVARCHAR(500),
    Phone NVARCHAR(20)
);
GO

-- =============================================
-- Categories Table
-- =============================================
CREATE TABLE Categories (
    CategoryID INT IDENTITY(1,1) PRIMARY KEY,
    CategoryName NVARCHAR(100) NOT NULL UNIQUE,
    CategoryNameAr NVARCHAR(100) NOT NULL
);
GO

-- =============================================
-- Authors Table
-- =============================================
CREATE TABLE Authors (
    AuthorID INT IDENTITY(1,1) PRIMARY KEY,
    Name NVARCHAR(200) NOT NULL
);
GO

-- =============================================
-- Books Table
-- =============================================
CREATE TABLE Books (
    ISBN NVARCHAR(20) PRIMARY KEY,
    Title NVARCHAR(300) NOT NULL,
    TitleAr NVARCHAR(300),
    PublisherID INT NOT NULL,
    PublicationYear INT,
    SellingPrice DECIMAL(10, 2) NOT NULL CHECK (SellingPrice >= 0),
    CategoryID INT NOT NULL,
    QuantityInStock INT NOT NULL DEFAULT 0 CHECK (QuantityInStock >= 0),
    ReorderThreshold INT NOT NULL DEFAULT 10 CHECK (ReorderThreshold >= 0),
    Description NVARCHAR(MAX),
    DescriptionAr NVARCHAR(MAX),
    ImageUrl NVARCHAR(500),
    CreatedAt DATETIME2 DEFAULT GETDATE(),
    CONSTRAINT FK_Books_Publishers FOREIGN KEY (PublisherID) REFERENCES Publishers(PublisherID),
    CONSTRAINT FK_Books_Categories FOREIGN KEY (CategoryID) REFERENCES Categories(CategoryID)
);
GO

-- =============================================
-- BookAuthors Table (Many-to-Many)
-- =============================================
CREATE TABLE BookAuthors (
    ISBN NVARCHAR(20) NOT NULL,
    AuthorID INT NOT NULL,
    PRIMARY KEY (ISBN, AuthorID),
    CONSTRAINT FK_BookAuthors_Books FOREIGN KEY (ISBN) REFERENCES Books(ISBN) ON DELETE CASCADE,
    CONSTRAINT FK_BookAuthors_Authors FOREIGN KEY (AuthorID) REFERENCES Authors(AuthorID) ON DELETE CASCADE
);
GO

-- =============================================
-- PublisherOrders Table (Orders to publishers for restocking)
-- =============================================
CREATE TABLE PublisherOrders (
    PubOrderID INT IDENTITY(1,1) PRIMARY KEY,
    PublisherID INT NOT NULL,
    OrderDate DATETIME2 DEFAULT GETDATE(),
    Status NVARCHAR(20) NOT NULL DEFAULT 'Pending' CHECK (Status IN ('Pending', 'Confirmed', 'Cancelled')),
    TotalAmount DECIMAL(10, 2) DEFAULT 0,
    Notes NVARCHAR(500),
    ConfirmedAt DATETIME2,
    CONSTRAINT FK_PublisherOrders_Publishers FOREIGN KEY (PublisherID) REFERENCES Publishers(PublisherID)
);
GO

-- =============================================
-- PublisherOrderItems Table
-- =============================================
CREATE TABLE PublisherOrderItems (
    PubOrderItemID INT IDENTITY(1,1) PRIMARY KEY,
    PubOrderID INT NOT NULL,
    ISBN NVARCHAR(20) NOT NULL,
    Quantity INT NOT NULL CHECK (Quantity > 0),
    UnitPrice DECIMAL(10, 2) NOT NULL CHECK (UnitPrice >= 0),
    CONSTRAINT FK_PublisherOrderItems_PublisherOrders FOREIGN KEY (PubOrderID) REFERENCES PublisherOrders(PubOrderID) ON DELETE CASCADE,
    CONSTRAINT FK_PublisherOrderItems_Books FOREIGN KEY (ISBN) REFERENCES Books(ISBN)
);
GO

-- =============================================
-- CustomerOrders Table (Sales)
-- =============================================
CREATE TABLE CustomerOrders (
    CustOrderID INT IDENTITY(1,1) PRIMARY KEY,
    UserID INT NOT NULL,
    OrderDate DATETIME2 DEFAULT GETDATE(),
    TotalAmount DECIMAL(10, 2) NOT NULL DEFAULT 0,
    CreditCardNumber NVARCHAR(20),
    CreditCardExpiry DATE,
    Status NVARCHAR(20) NOT NULL DEFAULT 'Pending' CHECK (Status IN ('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled')),
    ShippingAddress NVARCHAR(500),
    Notes NVARCHAR(500),
    CONSTRAINT FK_CustomerOrders_Users FOREIGN KEY (UserID) REFERENCES Users(UserID)
);
GO

-- =============================================
-- CustomerOrderItems Table
-- =============================================
CREATE TABLE CustomerOrderItems (
    CustOrderItemID INT IDENTITY(1,1) PRIMARY KEY,
    CustOrderID INT NOT NULL,
    ISBN NVARCHAR(20) NOT NULL,
    Quantity INT NOT NULL CHECK (Quantity > 0),
    UnitPrice DECIMAL(10, 2) NOT NULL CHECK (UnitPrice >= 0),
    CONSTRAINT FK_CustomerOrderItems_CustomerOrders FOREIGN KEY (CustOrderID) REFERENCES CustomerOrders(CustOrderID) ON DELETE CASCADE,
    CONSTRAINT FK_CustomerOrderItems_Books FOREIGN KEY (ISBN) REFERENCES Books(ISBN)
);
GO

-- =============================================
-- ShoppingCarts Table
-- =============================================
CREATE TABLE ShoppingCarts (
    CartID INT IDENTITY(1,1) PRIMARY KEY,
    UserID INT NOT NULL UNIQUE,
    CreatedAt DATETIME2 DEFAULT GETDATE(),
    UpdatedAt DATETIME2 DEFAULT GETDATE(),
    CONSTRAINT FK_ShoppingCarts_Users FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
);
GO

-- =============================================
-- CartItems Table
-- =============================================
CREATE TABLE CartItems (
    CartItemID INT IDENTITY(1,1) PRIMARY KEY,
    CartID INT NOT NULL,
    ISBN NVARCHAR(20) NOT NULL,
    Quantity INT NOT NULL DEFAULT 1 CHECK (Quantity > 0),
    AddedAt DATETIME2 DEFAULT GETDATE(),
    CONSTRAINT FK_CartItems_ShoppingCarts FOREIGN KEY (CartID) REFERENCES ShoppingCarts(CartID) ON DELETE CASCADE,
    CONSTRAINT FK_CartItems_Books FOREIGN KEY (ISBN) REFERENCES Books(ISBN),
    CONSTRAINT UQ_CartItems_CartBook UNIQUE (CartID, ISBN)
);
GO

-- =============================================
-- Create Indexes for Performance
-- =============================================
CREATE INDEX IX_Books_Title ON Books(Title);
CREATE INDEX IX_Books_CategoryID ON Books(CategoryID);
CREATE INDEX IX_Books_PublisherID ON Books(PublisherID);
CREATE INDEX IX_BookAuthors_AuthorID ON BookAuthors(AuthorID);
CREATE INDEX IX_CustomerOrders_OrderDate ON CustomerOrders(OrderDate);
CREATE INDEX IX_CustomerOrders_UserID ON CustomerOrders(UserID);
CREATE INDEX IX_CustomerOrderItems_ISBN ON CustomerOrderItems(ISBN);
CREATE INDEX IX_PublisherOrders_Status ON PublisherOrders(Status);
CREATE INDEX IX_PublisherOrders_PublisherID ON PublisherOrders(PublisherID);
GO

PRINT 'All tables and indexes created successfully.';
GO
