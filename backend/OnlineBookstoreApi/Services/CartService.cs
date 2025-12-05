using Microsoft.EntityFrameworkCore;
using OnlineBookstoreApi.Data;
using OnlineBookstoreApi.DTOs;
using OnlineBookstoreApi.Models;

namespace OnlineBookstoreApi.Services;

public interface ICartService
{
    Task<CartDto?> GetCartAsync(int userId);
    Task<CartDto?> AddToCartAsync(int userId, AddToCartDto dto);
    Task<CartDto?> UpdateCartItemAsync(int userId, int cartItemId, UpdateCartItemDto dto);
    Task<bool> RemoveFromCartAsync(int userId, int cartItemId);
    Task<bool> ClearCartAsync(int userId);
}

public class CartService : ICartService
{
    private readonly BookstoreDbContext _context;

    public CartService(BookstoreDbContext context)
    {
        _context = context;
    }

    public async Task<CartDto?> GetCartAsync(int userId)
    {
        var cart = await GetOrCreateCartAsync(userId);
        if (cart == null) return null;

        var cartItems = await _context.CartItems
            .Include(ci => ci.Book)
            .Where(ci => ci.CartID == cart.CartID)
            .ToListAsync();

        var items = cartItems.Select(ci => new CartItemDto
        {
            CartItemID = ci.CartItemID,
            ISBN = ci.ISBN,
            BookTitle = ci.Book?.Title ?? "",
            BookTitleAr = ci.Book?.TitleAr,
            UnitPrice = ci.Book?.SellingPrice ?? 0,
            Quantity = ci.Quantity,
            AvailableStock = ci.Book?.QuantityInStock ?? 0,
            ImageUrl = ci.Book?.ImageUrl,
            Subtotal = ci.Quantity * (ci.Book?.SellingPrice ?? 0),
            AddedAt = ci.AddedAt
        }).ToList();

        return new CartDto
        {
            CartID = cart.CartID,
            UserID = userId,
            Items = items,
            TotalAmount = items.Sum(i => i.Subtotal),
            TotalItems = items.Sum(i => i.Quantity),
            UpdatedAt = cart.UpdatedAt
        };
    }

    public async Task<CartDto?> AddToCartAsync(int userId, AddToCartDto dto)
    {
        var cart = await GetOrCreateCartAsync(userId);
        if (cart == null) return null;

        // Check if book exists
        var book = await _context.Books.FindAsync(dto.ISBN);
        if (book == null) return null;

        // Check if item already exists in cart
        var existingItem = await _context.CartItems
            .FirstOrDefaultAsync(ci => ci.CartID == cart.CartID && ci.ISBN == dto.ISBN);

        if (existingItem != null)
        {
            existingItem.Quantity += dto.Quantity;
        }
        else
        {
            _context.CartItems.Add(new CartItem
            {
                CartID = cart.CartID,
                ISBN = dto.ISBN,
                Quantity = dto.Quantity,
                AddedAt = DateTime.UtcNow
            });
        }

        cart.UpdatedAt = DateTime.UtcNow;
        await _context.SaveChangesAsync();

        return await GetCartAsync(userId);
    }

    public async Task<CartDto?> UpdateCartItemAsync(int userId, int cartItemId, UpdateCartItemDto dto)
    {
        var cart = await GetOrCreateCartAsync(userId);
        if (cart == null) return null;

        var cartItem = await _context.CartItems
            .FirstOrDefaultAsync(ci => ci.CartItemID == cartItemId && ci.CartID == cart.CartID);

        if (cartItem == null) return null;

        cartItem.Quantity = dto.Quantity;
        cart.UpdatedAt = DateTime.UtcNow;
        await _context.SaveChangesAsync();

        return await GetCartAsync(userId);
    }

    public async Task<bool> RemoveFromCartAsync(int userId, int cartItemId)
    {
        var cart = await _context.ShoppingCarts
            .FirstOrDefaultAsync(c => c.UserID == userId);

        if (cart == null) return false;

        var cartItem = await _context.CartItems
            .FirstOrDefaultAsync(ci => ci.CartItemID == cartItemId && ci.CartID == cart.CartID);

        if (cartItem == null) return false;

        _context.CartItems.Remove(cartItem);
        cart.UpdatedAt = DateTime.UtcNow;
        await _context.SaveChangesAsync();

        return true;
    }

    public async Task<bool> ClearCartAsync(int userId)
    {
        var cart = await _context.ShoppingCarts
            .FirstOrDefaultAsync(c => c.UserID == userId);

        if (cart == null) return false;

        var cartItems = await _context.CartItems
            .Where(ci => ci.CartID == cart.CartID)
            .ToListAsync();

        _context.CartItems.RemoveRange(cartItems);
        cart.UpdatedAt = DateTime.UtcNow;
        await _context.SaveChangesAsync();

        return true;
    }

    private async Task<ShoppingCart?> GetOrCreateCartAsync(int userId)
    {
        var cart = await _context.ShoppingCarts
            .FirstOrDefaultAsync(c => c.UserID == userId);

        if (cart == null)
        {
            cart = new ShoppingCart
            {
                UserID = userId,
                CreatedAt = DateTime.UtcNow,
                UpdatedAt = DateTime.UtcNow
            };
            _context.ShoppingCarts.Add(cart);
            await _context.SaveChangesAsync();
        }

        return cart;
    }
}
