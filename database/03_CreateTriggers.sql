-- =============================================
-- Online Bookstore - Triggers Script
-- =============================================

USE OnlineBookstore;
GO

-- =============================================
-- Trigger: Prevent Negative Stock
-- Prevents QuantityInStock from going below 0
-- =============================================
IF OBJECT_ID('dbo.trg_PreventNegativeStock', 'TR') IS NOT NULL
    DROP TRIGGER dbo.trg_PreventNegativeStock;
GO

CREATE TRIGGER trg_PreventNegativeStock
ON Books
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    
    IF EXISTS (SELECT 1 FROM inserted WHERE QuantityInStock < 0)
    BEGIN
        RAISERROR ('Cannot update book stock: Quantity in stock cannot be negative.', 16, 1);
        ROLLBACK TRANSACTION;
        RETURN;
    END
END;
GO

PRINT 'Trigger trg_PreventNegativeStock created successfully.';
GO

-- =============================================
-- Trigger: Auto Reorder
-- When stock drops below threshold after an update,
-- automatically create a PublisherOrder for restocking
-- =============================================
IF OBJECT_ID('dbo.trg_AutoReorder', 'TR') IS NOT NULL
    DROP TRIGGER dbo.trg_AutoReorder;
GO

CREATE TRIGGER trg_AutoReorder
ON Books
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @ReorderQuantity INT = 50; -- Default reorder quantity
    
    -- Find books that need reordering (stock dropped below threshold)
    -- and don't already have a pending order
    DECLARE @BooksToReorder TABLE (
        ISBN NVARCHAR(20),
        PublisherID INT,
        SellingPrice DECIMAL(10,2),
        QuantityToOrder INT
    );
    
    INSERT INTO @BooksToReorder (ISBN, PublisherID, SellingPrice, QuantityToOrder)
    SELECT 
        i.ISBN,
        i.PublisherID,
        i.SellingPrice,
        @ReorderQuantity
    FROM inserted i
    INNER JOIN deleted d ON i.ISBN = d.ISBN
    WHERE i.QuantityInStock < i.ReorderThreshold
      AND i.QuantityInStock >= 0
      -- Check if stock actually decreased (to avoid triggering on other updates)
      AND i.QuantityInStock < d.QuantityInStock
      -- Check there's no pending order for this book already
      AND NOT EXISTS (
          SELECT 1 
          FROM PublisherOrders po
          INNER JOIN PublisherOrderItems poi ON po.PubOrderID = poi.PubOrderID
          WHERE poi.ISBN = i.ISBN 
            AND po.Status = 'Pending'
      );
    
    -- Create publisher orders for each book that needs restocking
    IF EXISTS (SELECT 1 FROM @BooksToReorder)
    BEGIN
        DECLARE @ISBN NVARCHAR(20), @PublisherID INT, @SellingPrice DECIMAL(10,2), @QuantityToOrder INT;
        DECLARE @NewOrderID INT;
        
        DECLARE reorder_cursor CURSOR FOR 
            SELECT ISBN, PublisherID, SellingPrice, QuantityToOrder 
            FROM @BooksToReorder;
        
        OPEN reorder_cursor;
        FETCH NEXT FROM reorder_cursor INTO @ISBN, @PublisherID, @SellingPrice, @QuantityToOrder;
        
        WHILE @@FETCH_STATUS = 0
        BEGIN
            -- Create a new publisher order
            INSERT INTO PublisherOrders (PublisherID, Status, TotalAmount, Notes)
            VALUES (@PublisherID, 'Pending', @QuantityToOrder * @SellingPrice * 0.6, 
                    N'طلب إعادة تخزين تلقائي - Auto restock order');
            
            SET @NewOrderID = SCOPE_IDENTITY();
            
            -- Add the order item
            INSERT INTO PublisherOrderItems (PubOrderID, ISBN, Quantity, UnitPrice)
            VALUES (@NewOrderID, @ISBN, @QuantityToOrder, @SellingPrice * 0.6);
            
            FETCH NEXT FROM reorder_cursor INTO @ISBN, @PublisherID, @SellingPrice, @QuantityToOrder;
        END
        
        CLOSE reorder_cursor;
        DEALLOCATE reorder_cursor;
    END
END;
GO

PRINT 'Trigger trg_AutoReorder created successfully.';
GO
