using Microsoft.EntityFrameworkCore;
using OnlineBookstoreApi.Data;
using OnlineBookstoreApi.DTOs;
using OnlineBookstoreApi.Models;

namespace OnlineBookstoreApi.Services;

public interface IPublisherOrderService
{
    Task<List<PublisherOrderDto>> GetPublisherOrdersAsync(string? status = null);
    Task<PublisherOrderDto?> GetPublisherOrderByIdAsync(int id);
    Task<PublisherOrderDto?> CreatePublisherOrderAsync(CreatePublisherOrderDto dto);
    Task<bool> ConfirmPublisherOrderAsync(int id);
    Task<bool> CancelPublisherOrderAsync(int id);
}

public class PublisherOrderService : IPublisherOrderService
{
    private readonly BookstoreDbContext _context;

    public PublisherOrderService(BookstoreDbContext context)
    {
        _context = context;
    }

    public async Task<List<PublisherOrderDto>> GetPublisherOrdersAsync(string? status = null)
    {
        var query = _context.PublisherOrders
            .Include(po => po.Publisher)
            .Include(po => po.PublisherOrderItems)
                .ThenInclude(poi => poi.Book)
            .AsQueryable();

        if (!string.IsNullOrEmpty(status))
        {
            query = query.Where(po => po.Status == status);
        }

        var orders = await query
            .OrderByDescending(po => po.OrderDate)
            .ToListAsync();

        return orders.Select(MapToDto).ToList();
    }

    public async Task<PublisherOrderDto?> GetPublisherOrderByIdAsync(int id)
    {
        var order = await _context.PublisherOrders
            .Include(po => po.Publisher)
            .Include(po => po.PublisherOrderItems)
                .ThenInclude(poi => poi.Book)
            .FirstOrDefaultAsync(po => po.PubOrderID == id);

        return order == null ? null : MapToDto(order);
    }

    public async Task<PublisherOrderDto?> CreatePublisherOrderAsync(CreatePublisherOrderDto dto)
    {
        using var transaction = await _context.Database.BeginTransactionAsync();

        try
        {
            var order = new PublisherOrder
            {
                PublisherID = dto.PublisherID,
                OrderDate = DateTime.UtcNow,
                Status = "Pending",
                Notes = dto.Notes
            };

            decimal totalAmount = 0;

            foreach (var item in dto.Items)
            {
                var book = await _context.Books.FindAsync(item.ISBN);
                if (book == null)
                {
                    await transaction.RollbackAsync();
                    return null;
                }

                order.PublisherOrderItems.Add(new PublisherOrderItem
                {
                    ISBN = item.ISBN,
                    Quantity = item.Quantity,
                    UnitPrice = item.UnitPrice
                });

                totalAmount += item.Quantity * item.UnitPrice;
            }

            order.TotalAmount = totalAmount;

            _context.PublisherOrders.Add(order);
            await _context.SaveChangesAsync();
            await transaction.CommitAsync();

            return await GetPublisherOrderByIdAsync(order.PubOrderID);
        }
        catch
        {
            await transaction.RollbackAsync();
            throw;
        }
    }

    public async Task<bool> ConfirmPublisherOrderAsync(int id)
    {
        using var transaction = await _context.Database.BeginTransactionAsync();

        try
        {
            var order = await _context.PublisherOrders
                .Include(po => po.PublisherOrderItems)
                .FirstOrDefaultAsync(po => po.PubOrderID == id);

            if (order == null || order.Status != "Pending")
            {
                return false;
            }

            // Add quantities to book stock
            foreach (var item in order.PublisherOrderItems)
            {
                var book = await _context.Books.FindAsync(item.ISBN);
                if (book != null)
                {
                    book.QuantityInStock += item.Quantity;
                }
            }

            // Update order status
            order.Status = "Confirmed";
            order.ConfirmedAt = DateTime.UtcNow;

            await _context.SaveChangesAsync();
            await transaction.CommitAsync();

            return true;
        }
        catch
        {
            await transaction.RollbackAsync();
            throw;
        }
    }

    public async Task<bool> CancelPublisherOrderAsync(int id)
    {
        var order = await _context.PublisherOrders.FindAsync(id);
        if (order == null || order.Status != "Pending")
        {
            return false;
        }

        order.Status = "Cancelled";
        await _context.SaveChangesAsync();
        return true;
    }

    private static PublisherOrderDto MapToDto(PublisherOrder order)
    {
        return new PublisherOrderDto
        {
            PubOrderID = order.PubOrderID,
            PublisherID = order.PublisherID,
            PublisherName = order.Publisher?.Name,
            OrderDate = order.OrderDate,
            Status = order.Status,
            TotalAmount = order.TotalAmount,
            Notes = order.Notes,
            ConfirmedAt = order.ConfirmedAt,
            Items = order.PublisherOrderItems.Select(item => new PublisherOrderItemDto
            {
                PubOrderItemID = item.PubOrderItemID,
                ISBN = item.ISBN,
                BookTitle = item.Book?.Title ?? "",
                BookTitleAr = item.Book?.TitleAr,
                Quantity = item.Quantity,
                UnitPrice = item.UnitPrice,
                Subtotal = item.Quantity * item.UnitPrice
            }).ToList()
        };
    }
}
