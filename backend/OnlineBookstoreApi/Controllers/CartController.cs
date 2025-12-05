using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using OnlineBookstoreApi.DTOs;
using OnlineBookstoreApi.Services;

namespace OnlineBookstoreApi.Controllers;

[ApiController]
[Route("api/[controller]")]
[Authorize]
public class CartController : ControllerBase
{
    private readonly ICartService _cartService;

    public CartController(ICartService cartService)
    {
        _cartService = cartService;
    }

    /// <summary>
    /// Get current user's shopping cart
    /// </summary>
    [HttpGet]
    public async Task<ActionResult<ApiResponse<CartDto>>> GetCart()
    {
        var userId = GetUserId();
        if (userId == null)
        {
            return Unauthorized(ApiResponse<CartDto>.ErrorResponse(
                "User not authenticated",
                "المستخدم غير مصرح له"));
        }

        var cart = await _cartService.GetCartAsync(userId.Value);
        if (cart == null)
        {
            return NotFound(ApiResponse<CartDto>.ErrorResponse(
                "Cart not found",
                "السلة غير موجودة"));
        }

        return Ok(ApiResponse<CartDto>.SuccessResponse(cart));
    }

    /// <summary>
    /// Add an item to the cart
    /// </summary>
    [HttpPost("items")]
    public async Task<ActionResult<ApiResponse<CartDto>>> AddToCart([FromBody] AddToCartDto dto)
    {
        var userId = GetUserId();
        if (userId == null)
        {
            return Unauthorized(ApiResponse<CartDto>.ErrorResponse(
                "User not authenticated",
                "المستخدم غير مصرح له"));
        }

        if (!ModelState.IsValid)
        {
            return BadRequest(ApiResponse<CartDto>.ErrorResponse(
                "Invalid cart item data",
                "بيانات عنصر السلة غير صحيحة"));
        }

        var cart = await _cartService.AddToCartAsync(userId.Value, dto);
        if (cart == null)
        {
            return BadRequest(ApiResponse<CartDto>.ErrorResponse(
                "Book not found",
                "الكتاب غير موجود"));
        }

        return Ok(ApiResponse<CartDto>.SuccessResponse(
            cart,
            "Item added to cart",
            "تمت إضافة العنصر إلى السلة"));
    }

    /// <summary>
    /// Update cart item quantity
    /// </summary>
    [HttpPut("items/{cartItemId}")]
    public async Task<ActionResult<ApiResponse<CartDto>>> UpdateCartItem(int cartItemId, [FromBody] UpdateCartItemDto dto)
    {
        var userId = GetUserId();
        if (userId == null)
        {
            return Unauthorized(ApiResponse<CartDto>.ErrorResponse(
                "User not authenticated",
                "المستخدم غير مصرح له"));
        }

        if (!ModelState.IsValid)
        {
            return BadRequest(ApiResponse<CartDto>.ErrorResponse(
                "Invalid quantity",
                "الكمية غير صحيحة"));
        }

        var cart = await _cartService.UpdateCartItemAsync(userId.Value, cartItemId, dto);
        if (cart == null)
        {
            return NotFound(ApiResponse<CartDto>.ErrorResponse(
                "Cart item not found",
                "عنصر السلة غير موجود"));
        }

        return Ok(ApiResponse<CartDto>.SuccessResponse(
            cart,
            "Cart item updated",
            "تم تحديث عنصر السلة"));
    }

    /// <summary>
    /// Remove an item from the cart
    /// </summary>
    [HttpDelete("items/{cartItemId}")]
    public async Task<ActionResult<ApiResponse<object>>> RemoveFromCart(int cartItemId)
    {
        var userId = GetUserId();
        if (userId == null)
        {
            return Unauthorized(ApiResponse<object>.ErrorResponse(
                "User not authenticated",
                "المستخدم غير مصرح له"));
        }

        var success = await _cartService.RemoveFromCartAsync(userId.Value, cartItemId);
        if (!success)
        {
            return NotFound(ApiResponse<object>.ErrorResponse(
                "Cart item not found",
                "عنصر السلة غير موجود"));
        }

        return Ok(ApiResponse<object>.SuccessResponse(
            null!,
            "Item removed from cart",
            "تمت إزالة العنصر من السلة"));
    }

    /// <summary>
    /// Clear all items from the cart
    /// </summary>
    [HttpDelete]
    public async Task<ActionResult<ApiResponse<object>>> ClearCart()
    {
        var userId = GetUserId();
        if (userId == null)
        {
            return Unauthorized(ApiResponse<object>.ErrorResponse(
                "User not authenticated",
                "المستخدم غير مصرح له"));
        }

        await _cartService.ClearCartAsync(userId.Value);
        return Ok(ApiResponse<object>.SuccessResponse(
            null!,
            "Cart cleared",
            "تم تفريغ السلة"));
    }

    private int? GetUserId()
    {
        var userIdClaim = User.FindFirst(System.Security.Claims.ClaimTypes.NameIdentifier);
        if (userIdClaim != null && int.TryParse(userIdClaim.Value, out int userId))
        {
            return userId;
        }
        return null;
    }
}
