using Microsoft.EntityFrameworkCore;
using OnlineBookstoreApi.Data;
using OnlineBookstoreApi.DTOs;
using OnlineBookstoreApi.Models;

namespace OnlineBookstoreApi.Services;

public interface IOrderService
{
    Task<CustomerOrderDto?> CheckoutAsync(int userId, CheckoutDto dto);
    Task<List<CustomerOrderDto>> GetCustomerOrdersAsync(int userId);
    Task<CustomerOrderDto?> GetCustomerOrderByIdAsync(int userId, int orderId);
    Task<List<CustomerOrderDto>> GetAllOrdersAsync(); // Admin only
    Task<bool> UpdateOrderStatusAsync(int orderId, string status);
}

public class OrderService : IOrderService
{
    private readonly BookstoreDbContext _context;

    public OrderService(BookstoreDbContext context)
    {
        _context = context;
    }

    public async Task<CustomerOrderDto?> CheckoutAsync(int userId, CheckoutDto dto)
    {
        using var transaction = await _context.Database.BeginTransactionAsync();

        try
        {
            // Validate card expiry
            if (dto.CreditCardExpiry.Date < DateTime.UtcNow.Date)
            {
                throw new InvalidOperationException("Credit card has expired.");
            }

            // Get user's cart
            var cart = await _context.ShoppingCarts
                .Include(c => c.CartItems)
                    .ThenInclude(ci => ci.Book)
                .FirstOrDefaultAsync(c => c.UserID == userId);

            if (cart == null || !cart.CartItems.Any())
            {
                throw new InvalidOperationException("Shopping cart is empty.");
            }

            // Check stock availability
            foreach (var item in cart.CartItems)
            {
                if (item.Book == null || item.Quantity > item.Book.QuantityInStock)
                {
                    throw new InvalidOperationException($"Insufficient stock for book: {item.Book?.Title ?? item.ISBN}");
                }
            }

            // Calculate total
            decimal totalAmount = cart.CartItems.Sum(ci => ci.Quantity * (ci.Book?.SellingPrice ?? 0));

            // Get shipping address
            var shippingAddress = dto.ShippingAddress;
            if (string.IsNullOrEmpty(shippingAddress))
            {
                var user = await _context.Users.FindAsync(userId);
                shippingAddress = user?.ShippingAddress;
            }

            // Create order
            var order = new CustomerOrder
            {
                UserID = userId,
                OrderDate = DateTime.UtcNow,
                TotalAmount = totalAmount,
                CreditCardNumber = dto.CreditCardNumber.Length >= 4 
                    ? dto.CreditCardNumber.Substring(dto.CreditCardNumber.Length - 4) 
                    : dto.CreditCardNumber,
                CreditCardExpiry = dto.CreditCardExpiry,
                Status = "Processing",
                ShippingAddress = shippingAddress,
                Notes = dto.Notes
            };

            _context.CustomerOrders.Add(order);
            await _context.SaveChangesAsync();

            // Create order items and deduct stock
            foreach (var cartItem in cart.CartItems)
            {
                _context.CustomerOrderItems.Add(new CustomerOrderItem
                {
                    CustOrderID = order.CustOrderID,
                    ISBN = cartItem.ISBN,
                    Quantity = cartItem.Quantity,
                    UnitPrice = cartItem.Book?.SellingPrice ?? 0
                });

                // Deduct stock
                if (cartItem.Book != null)
                {
                    cartItem.Book.QuantityInStock -= cartItem.Quantity;
                }
            }

            // Clear cart
            _context.CartItems.RemoveRange(cart.CartItems);

            await _context.SaveChangesAsync();
            await transaction.CommitAsync();

            return await GetCustomerOrderByIdAsync(userId, order.CustOrderID);
        }
        catch
        {
            await transaction.RollbackAsync();
            throw;
        }
    }

    public async Task<List<CustomerOrderDto>> GetCustomerOrdersAsync(int userId)
    {
        var orders = await _context.CustomerOrders
            .Include(o => o.User)
            .Include(o => o.CustomerOrderItems)
                .ThenInclude(oi => oi.Book)
            .Where(o => o.UserID == userId)
            .OrderByDescending(o => o.OrderDate)
            .ToListAsync();

        return orders.Select(MapToDto).ToList();
    }

    public async Task<CustomerOrderDto?> GetCustomerOrderByIdAsync(int userId, int orderId)
    {
        var order = await _context.CustomerOrders
            .Include(o => o.User)
            .Include(o => o.CustomerOrderItems)
                .ThenInclude(oi => oi.Book)
            .FirstOrDefaultAsync(o => o.CustOrderID == orderId && o.UserID == userId);

        return order == null ? null : MapToDto(order);
    }

    public async Task<List<CustomerOrderDto>> GetAllOrdersAsync()
    {
        var orders = await _context.CustomerOrders
            .Include(o => o.User)
            .Include(o => o.CustomerOrderItems)
                .ThenInclude(oi => oi.Book)
            .OrderByDescending(o => o.OrderDate)
            .ToListAsync();

        return orders.Select(MapToDto).ToList();
    }

    public async Task<bool> UpdateOrderStatusAsync(int orderId, string status)
    {
        var order = await _context.CustomerOrders.FindAsync(orderId);
        if (order == null) return false;

        var validStatuses = new[] { "Pending", "Processing", "Shipped", "Delivered", "Cancelled" };
        if (!validStatuses.Contains(status)) return false;

        order.Status = status;
        await _context.SaveChangesAsync();
        return true;
    }

    private static CustomerOrderDto MapToDto(CustomerOrder order)
    {
        return new CustomerOrderDto
        {
            CustOrderID = order.CustOrderID,
            UserID = order.UserID,
            CustomerName = order.User != null ? $"{order.User.FirstName} {order.User.LastName}" : null,
            OrderDate = order.OrderDate,
            TotalAmount = order.TotalAmount,
            CreditCardLast4 = order.CreditCardNumber,
            Status = order.Status,
            ShippingAddress = order.ShippingAddress,
            Notes = order.Notes,
            Items = order.CustomerOrderItems.Select(item => new CustomerOrderItemDto
            {
                CustOrderItemID = item.CustOrderItemID,
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
