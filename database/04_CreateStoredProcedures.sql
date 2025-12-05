-- =============================================
-- Online Bookstore - Stored Procedures Script
-- =============================================

USE OnlineBookstore;
GO

-- =============================================
-- Stored Procedure: ConfirmPublisherOrder
-- Confirms a publisher order and adds stock to books
-- =============================================
IF OBJECT_ID('dbo.sp_ConfirmPublisherOrder', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_ConfirmPublisherOrder;
GO

CREATE PROCEDURE sp_ConfirmPublisherOrder
    @PubOrderID INT
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;
    
    BEGIN TRY
        BEGIN TRANSACTION;
        
        -- Check if order exists and is pending
        IF NOT EXISTS (SELECT 1 FROM PublisherOrders WHERE PubOrderID = @PubOrderID)
        BEGIN
            RAISERROR ('Publisher order not found.', 16, 1);
            RETURN;
        END
        
        IF NOT EXISTS (SELECT 1 FROM PublisherOrders WHERE PubOrderID = @PubOrderID AND Status = 'Pending')
        BEGIN
            RAISERROR ('Cannot confirm order: Order is not in Pending status.', 16, 1);
            RETURN;
        END
        
        -- Disable the auto-reorder trigger temporarily to prevent issues
        DISABLE TRIGGER trg_AutoReorder ON Books;
        
        -- Add quantities to book stock
        UPDATE b
        SET b.QuantityInStock = b.QuantityInStock + poi.Quantity
        FROM Books b
        INNER JOIN PublisherOrderItems poi ON b.ISBN = poi.ISBN
        WHERE poi.PubOrderID = @PubOrderID;
        
        -- Re-enable the trigger
        ENABLE TRIGGER trg_AutoReorder ON Books;
        
        -- Update order status to Confirmed
        UPDATE PublisherOrders
        SET Status = 'Confirmed',
            ConfirmedAt = GETDATE()
        WHERE PubOrderID = @PubOrderID;
        
        COMMIT TRANSACTION;
        
        SELECT 'Publisher order confirmed successfully.' AS Message;
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;
        
        -- Re-enable trigger if it was disabled
        ENABLE TRIGGER trg_AutoReorder ON Books;
        
        THROW;
    END CATCH
END;
GO

PRINT 'Stored Procedure sp_ConfirmPublisherOrder created successfully.';
GO

-- =============================================
-- Stored Procedure: CheckoutCart
-- Processes customer checkout atomically
-- =============================================
IF OBJECT_ID('dbo.sp_CheckoutCart', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_CheckoutCart;
GO

CREATE PROCEDURE sp_CheckoutCart
    @UserID INT,
    @CardNumber NVARCHAR(20),
    @CardExpiry DATE,
    @ShippingAddress NVARCHAR(500) = NULL,
    @NewOrderID INT OUTPUT
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;
    
    BEGIN TRY
        BEGIN TRANSACTION;
        
        -- Validate card expiry (must be in the future)
        IF @CardExpiry < CAST(GETDATE() AS DATE)
        BEGIN
            RAISERROR ('Credit card has expired.', 16, 1);
            RETURN;
        END
        
        -- Get user's cart
        DECLARE @CartID INT;
        SELECT @CartID = CartID FROM ShoppingCarts WHERE UserID = @UserID;
        
        IF @CartID IS NULL
        BEGIN
            RAISERROR ('Shopping cart not found.', 16, 1);
            RETURN;
        END
        
        -- Check if cart has items
        IF NOT EXISTS (SELECT 1 FROM CartItems WHERE CartID = @CartID)
        BEGIN
            RAISERROR ('Shopping cart is empty.', 16, 1);
            RETURN;
        END
        
        -- Check stock availability for all items
        IF EXISTS (
            SELECT 1 
            FROM CartItems ci
            INNER JOIN Books b ON ci.ISBN = b.ISBN
            WHERE ci.CartID = @CartID AND ci.Quantity > b.QuantityInStock
        )
        BEGIN
            RAISERROR ('Insufficient stock for one or more items in cart.', 16, 1);
            RETURN;
        END
        
        -- Calculate total amount
        DECLARE @TotalAmount DECIMAL(10, 2);
        SELECT @TotalAmount = SUM(ci.Quantity * b.SellingPrice)
        FROM CartItems ci
        INNER JOIN Books b ON ci.ISBN = b.ISBN
        WHERE ci.CartID = @CartID;
        
        -- Get shipping address (use provided or user's default)
        IF @ShippingAddress IS NULL
        BEGIN
            SELECT @ShippingAddress = ShippingAddress FROM Users WHERE UserID = @UserID;
        END
        
        -- Create customer order
        INSERT INTO CustomerOrders (UserID, TotalAmount, CreditCardNumber, CreditCardExpiry, Status, ShippingAddress)
        VALUES (@UserID, @TotalAmount, RIGHT(@CardNumber, 4), @CardExpiry, 'Processing', @ShippingAddress);
        
        SET @NewOrderID = SCOPE_IDENTITY();
        
        -- Create order items from cart items
        INSERT INTO CustomerOrderItems (CustOrderID, ISBN, Quantity, UnitPrice)
        SELECT @NewOrderID, ci.ISBN, ci.Quantity, b.SellingPrice
        FROM CartItems ci
        INNER JOIN Books b ON ci.ISBN = b.ISBN
        WHERE ci.CartID = @CartID;
        
        -- Deduct stock from books
        UPDATE b
        SET b.QuantityInStock = b.QuantityInStock - ci.Quantity
        FROM Books b
        INNER JOIN CartItems ci ON b.ISBN = ci.ISBN
        WHERE ci.CartID = @CartID;
        
        -- Empty the cart
        DELETE FROM CartItems WHERE CartID = @CartID;
        
        COMMIT TRANSACTION;
        
        SELECT 'Checkout completed successfully.' AS Message, @NewOrderID AS OrderID, @TotalAmount AS TotalAmount;
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;
        
        THROW;
    END CATCH
END;
GO

PRINT 'Stored Procedure sp_CheckoutCart created successfully.';
GO

-- =============================================
-- Report Stored Procedures
-- =============================================

-- =============================================
-- GetTotalSalesByMonth
-- Returns total sales for a specific month
-- =============================================
IF OBJECT_ID('dbo.sp_GetTotalSalesByMonth', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_GetTotalSalesByMonth;
GO

CREATE PROCEDURE sp_GetTotalSalesByMonth
    @Year INT,
    @Month INT
AS
BEGIN
    SET NOCOUNT ON;
    
    SELECT 
        @Year AS Year,
        @Month AS Month,
        COUNT(DISTINCT CustOrderID) AS TotalOrders,
        SUM(TotalAmount) AS TotalSales,
        COUNT(DISTINCT UserID) AS UniqueCustomers
    FROM CustomerOrders
    WHERE YEAR(OrderDate) = @Year 
      AND MONTH(OrderDate) = @Month
      AND Status NOT IN ('Cancelled');
END;
GO

PRINT 'Stored Procedure sp_GetTotalSalesByMonth created successfully.';
GO

-- =============================================
-- GetTotalSalesByDate
-- Returns total sales for a specific date
-- =============================================
IF OBJECT_ID('dbo.sp_GetTotalSalesByDate', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_GetTotalSalesByDate;
GO

CREATE PROCEDURE sp_GetTotalSalesByDate
    @Date DATE
AS
BEGIN
    SET NOCOUNT ON;
    
    SELECT 
        @Date AS Date,
        COUNT(DISTINCT CustOrderID) AS TotalOrders,
        ISNULL(SUM(TotalAmount), 0) AS TotalSales,
        COUNT(DISTINCT UserID) AS UniqueCustomers
    FROM CustomerOrders
    WHERE CAST(OrderDate AS DATE) = @Date
      AND Status NOT IN ('Cancelled');
END;
GO

PRINT 'Stored Procedure sp_GetTotalSalesByDate created successfully.';
GO

-- =============================================
-- GetTopCustomers
-- Returns top N customers by spending in last X months
-- =============================================
IF OBJECT_ID('dbo.sp_GetTopCustomers', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_GetTopCustomers;
GO

CREATE PROCEDURE sp_GetTopCustomers
    @Months INT = 3,
    @TopN INT = 5
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @StartDate DATETIME2 = DATEADD(MONTH, -@Months, GETDATE());
    
    SELECT TOP (@TopN)
        u.UserID,
        u.Username,
        u.FirstName,
        u.LastName,
        u.Email,
        COUNT(co.CustOrderID) AS OrderCount,
        SUM(co.TotalAmount) AS TotalSpent
    FROM Users u
    INNER JOIN CustomerOrders co ON u.UserID = co.UserID
    WHERE co.OrderDate >= @StartDate
      AND co.Status NOT IN ('Cancelled')
    GROUP BY u.UserID, u.Username, u.FirstName, u.LastName, u.Email
    ORDER BY TotalSpent DESC;
END;
GO

PRINT 'Stored Procedure sp_GetTopCustomers created successfully.';
GO

-- =============================================
-- GetTopSellingBooks
-- Returns top N best-selling books in last X months
-- =============================================
IF OBJECT_ID('dbo.sp_GetTopSellingBooks', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_GetTopSellingBooks;
GO

CREATE PROCEDURE sp_GetTopSellingBooks
    @Months INT = 3,
    @TopN INT = 10
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @StartDate DATETIME2 = DATEADD(MONTH, -@Months, GETDATE());
    
    SELECT TOP (@TopN)
        b.ISBN,
        b.Title,
        b.TitleAr,
        p.Name AS PublisherName,
        c.CategoryName,
        c.CategoryNameAr,
        SUM(coi.Quantity) AS TotalQuantitySold,
        SUM(coi.Quantity * coi.UnitPrice) AS TotalRevenue
    FROM Books b
    INNER JOIN CustomerOrderItems coi ON b.ISBN = coi.ISBN
    INNER JOIN CustomerOrders co ON coi.CustOrderID = co.CustOrderID
    INNER JOIN Publishers p ON b.PublisherID = p.PublisherID
    INNER JOIN Categories c ON b.CategoryID = c.CategoryID
    WHERE co.OrderDate >= @StartDate
      AND co.Status NOT IN ('Cancelled')
    GROUP BY b.ISBN, b.Title, b.TitleAr, p.Name, c.CategoryName, c.CategoryNameAr
    ORDER BY TotalQuantitySold DESC;
END;
GO

PRINT 'Stored Procedure sp_GetTopSellingBooks created successfully.';
GO

-- =============================================
-- GetTimesBookReordered
-- Returns how many times a book was restocked
-- =============================================
IF OBJECT_ID('dbo.sp_GetTimesBookReordered', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_GetTimesBookReordered;
GO

CREATE PROCEDURE sp_GetTimesBookReordered
    @ISBN NVARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;
    
    SELECT 
        b.ISBN,
        b.Title,
        b.TitleAr,
        COUNT(po.PubOrderID) AS TimesReordered,
        ISNULL(SUM(poi.Quantity), 0) AS TotalQuantityOrdered,
        MAX(po.OrderDate) AS LastReorderDate
    FROM Books b
    LEFT JOIN PublisherOrderItems poi ON b.ISBN = poi.ISBN
    LEFT JOIN PublisherOrders po ON poi.PubOrderID = po.PubOrderID AND po.Status = 'Confirmed'
    WHERE b.ISBN = @ISBN
    GROUP BY b.ISBN, b.Title, b.TitleAr;
END;
GO

PRINT 'Stored Procedure sp_GetTimesBookReordered created successfully.';
GO

-- =============================================
-- GetAllBooksReorderStats
-- Returns restock statistics for all books
-- =============================================
IF OBJECT_ID('dbo.sp_GetAllBooksReorderStats', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_GetAllBooksReorderStats;
GO

CREATE PROCEDURE sp_GetAllBooksReorderStats
AS
BEGIN
    SET NOCOUNT ON;
    
    SELECT 
        b.ISBN,
        b.Title,
        b.TitleAr,
        b.QuantityInStock,
        b.ReorderThreshold,
        COUNT(po.PubOrderID) AS TimesReordered,
        ISNULL(SUM(poi.Quantity), 0) AS TotalQuantityOrdered,
        MAX(po.OrderDate) AS LastReorderDate
    FROM Books b
    LEFT JOIN PublisherOrderItems poi ON b.ISBN = poi.ISBN
    LEFT JOIN PublisherOrders po ON poi.PubOrderID = po.PubOrderID AND po.Status = 'Confirmed'
    GROUP BY b.ISBN, b.Title, b.TitleAr, b.QuantityInStock, b.ReorderThreshold
    ORDER BY TimesReordered DESC;
END;
GO

PRINT 'Stored Procedure sp_GetAllBooksReorderStats created successfully.';
GO
