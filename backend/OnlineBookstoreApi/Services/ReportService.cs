using Microsoft.EntityFrameworkCore;
using OnlineBookstoreApi.Data;
using OnlineBookstoreApi.DTOs;

namespace OnlineBookstoreApi.Services;

public interface IReportService
{
    Task<MonthlySalesReportDto> GetTotalSalesByMonthAsync(int year, int month);
    Task<DailySalesReportDto> GetTotalSalesByDateAsync(DateTime date);
    Task<List<TopCustomerDto>> GetTopCustomersAsync(int months = 3, int topN = 5);
    Task<List<TopSellingBookDto>> GetTopSellingBooksAsync(int months = 3, int topN = 10);
    Task<BookReorderStatsDto?> GetTimesBookReorderedAsync(string isbn);
    Task<List<BookReorderStatsDto>> GetAllBooksReorderStatsAsync();
}

public class ReportService : IReportService
{
    private readonly BookstoreDbContext _context;

    public ReportService(BookstoreDbContext context)
    {
        _context = context;
    }

    public async Task<MonthlySalesReportDto> GetTotalSalesByMonthAsync(int year, int month)
    {
        var orders = await _context.CustomerOrders
            .Where(o => o.OrderDate.Year == year && 
                        o.OrderDate.Month == month &&
                        o.Status != "Cancelled")
            .ToListAsync();

        return new MonthlySalesReportDto
        {
            Year = year,
            Month = month,
            TotalOrders = orders.Count,
            TotalSales = orders.Sum(o => o.TotalAmount),
            UniqueCustomers = orders.Select(o => o.UserID).Distinct().Count()
        };
    }

    public async Task<DailySalesReportDto> GetTotalSalesByDateAsync(DateTime date)
    {
        var orders = await _context.CustomerOrders
            .Where(o => o.OrderDate.Date == date.Date &&
                        o.Status != "Cancelled")
            .ToListAsync();

        return new DailySalesReportDto
        {
            Date = date.Date,
            TotalOrders = orders.Count,
            TotalSales = orders.Sum(o => o.TotalAmount),
            UniqueCustomers = orders.Select(o => o.UserID).Distinct().Count()
        };
    }

    public async Task<List<TopCustomerDto>> GetTopCustomersAsync(int months = 3, int topN = 5)
    {
        var startDate = DateTime.UtcNow.AddMonths(-months);

        var topCustomers = await _context.CustomerOrders
            .Include(o => o.User)
            .Where(o => o.OrderDate >= startDate && o.Status != "Cancelled")
            .GroupBy(o => new { o.UserID, o.User!.Username, o.User.FirstName, o.User.LastName, o.User.Email })
            .Select(g => new TopCustomerDto
            {
                UserID = g.Key.UserID,
                Username = g.Key.Username,
                FirstName = g.Key.FirstName,
                LastName = g.Key.LastName,
                Email = g.Key.Email,
                OrderCount = g.Count(),
                TotalSpent = g.Sum(o => o.TotalAmount)
            })
            .OrderByDescending(c => c.TotalSpent)
            .Take(topN)
            .ToListAsync();

        return topCustomers;
    }

    public async Task<List<TopSellingBookDto>> GetTopSellingBooksAsync(int months = 3, int topN = 10)
    {
        var startDate = DateTime.UtcNow.AddMonths(-months);

        var topBooks = await _context.CustomerOrderItems
            .Include(oi => oi.CustomerOrder)
            .Include(oi => oi.Book)
                .ThenInclude(b => b!.Publisher)
            .Include(oi => oi.Book)
                .ThenInclude(b => b!.Category)
            .Where(oi => oi.CustomerOrder!.OrderDate >= startDate && 
                         oi.CustomerOrder.Status != "Cancelled")
            .GroupBy(oi => new 
            { 
                oi.ISBN, 
                oi.Book!.Title, 
                oi.Book.TitleAr,
                PublisherName = oi.Book.Publisher!.Name,
                oi.Book.Category!.CategoryName,
                oi.Book.Category.CategoryNameAr
            })
            .Select(g => new TopSellingBookDto
            {
                ISBN = g.Key.ISBN,
                Title = g.Key.Title,
                TitleAr = g.Key.TitleAr,
                PublisherName = g.Key.PublisherName,
                CategoryName = g.Key.CategoryName,
                CategoryNameAr = g.Key.CategoryNameAr,
                TotalQuantitySold = g.Sum(oi => oi.Quantity),
                TotalRevenue = g.Sum(oi => oi.Quantity * oi.UnitPrice)
            })
            .OrderByDescending(b => b.TotalQuantitySold)
            .Take(topN)
            .ToListAsync();

        return topBooks;
    }

    public async Task<BookReorderStatsDto?> GetTimesBookReorderedAsync(string isbn)
    {
        var book = await _context.Books.FindAsync(isbn);
        if (book == null) return null;

        var confirmedOrders = await _context.PublisherOrderItems
            .Include(poi => poi.PublisherOrder)
            .Where(poi => poi.ISBN == isbn && poi.PublisherOrder!.Status == "Confirmed")
            .ToListAsync();

        return new BookReorderStatsDto
        {
            ISBN = book.ISBN,
            Title = book.Title,
            TitleAr = book.TitleAr,
            QuantityInStock = book.QuantityInStock,
            ReorderThreshold = book.ReorderThreshold,
            TimesReordered = confirmedOrders.Count,
            TotalQuantityOrdered = confirmedOrders.Sum(poi => poi.Quantity),
            LastReorderDate = confirmedOrders
                .OrderByDescending(poi => poi.PublisherOrder!.ConfirmedAt)
                .Select(poi => poi.PublisherOrder!.ConfirmedAt)
                .FirstOrDefault()
        };
    }

    public async Task<List<BookReorderStatsDto>> GetAllBooksReorderStatsAsync()
    {
        var books = await _context.Books
            .Include(b => b.PublisherOrderItems)
                .ThenInclude(poi => poi.PublisherOrder)
            .ToListAsync();

        return books.Select(book =>
        {
            var confirmedOrders = book.PublisherOrderItems
                .Where(poi => poi.PublisherOrder?.Status == "Confirmed")
                .ToList();

            return new BookReorderStatsDto
            {
                ISBN = book.ISBN,
                Title = book.Title,
                TitleAr = book.TitleAr,
                QuantityInStock = book.QuantityInStock,
                ReorderThreshold = book.ReorderThreshold,
                TimesReordered = confirmedOrders.Count,
                TotalQuantityOrdered = confirmedOrders.Sum(poi => poi.Quantity),
                LastReorderDate = confirmedOrders
                    .OrderByDescending(poi => poi.PublisherOrder!.ConfirmedAt)
                    .Select(poi => poi.PublisherOrder!.ConfirmedAt)
                    .FirstOrDefault()
            };
        })
        .OrderByDescending(b => b.TimesReordered)
        .ToList();
    }
}
