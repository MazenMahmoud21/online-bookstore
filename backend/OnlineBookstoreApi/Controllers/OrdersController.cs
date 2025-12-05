using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using OnlineBookstoreApi.DTOs;
using OnlineBookstoreApi.Services;

namespace OnlineBookstoreApi.Controllers;

[ApiController]
[Route("api/[controller]")]
[Authorize]
public class OrdersController : ControllerBase
{
    private readonly IOrderService _orderService;

    public OrdersController(IOrderService orderService)
    {
        _orderService = orderService;
    }

    /// <summary>
    /// Checkout - Create an order from the shopping cart
    /// </summary>
    [HttpPost("checkout")]
    public async Task<ActionResult<ApiResponse<CustomerOrderDto>>> Checkout([FromBody] CheckoutDto dto)
    {
        var userId = GetUserId();
        if (userId == null)
        {
            return Unauthorized(ApiResponse<CustomerOrderDto>.ErrorResponse(
                "User not authenticated",
                "المستخدم غير مصرح له"));
        }

        if (!ModelState.IsValid)
        {
            return BadRequest(ApiResponse<CustomerOrderDto>.ErrorResponse(
                "Invalid checkout data",
                "بيانات الدفع غير صحيحة"));
        }

        try
        {
            var order = await _orderService.CheckoutAsync(userId.Value, dto);
            if (order == null)
            {
                return BadRequest(ApiResponse<CustomerOrderDto>.ErrorResponse(
                    "Checkout failed",
                    "فشل إتمام الطلب"));
            }

            return Ok(ApiResponse<CustomerOrderDto>.SuccessResponse(
                order,
                "Order placed successfully",
                "تم إتمام الطلب بنجاح"));
        }
        catch (InvalidOperationException ex)
        {
            return BadRequest(ApiResponse<CustomerOrderDto>.ErrorResponse(
                ex.Message,
                GetArabicErrorMessage(ex.Message)));
        }
    }

    /// <summary>
    /// Get current user's order history
    /// </summary>
    [HttpGet]
    public async Task<ActionResult<ApiResponse<List<CustomerOrderDto>>>> GetMyOrders()
    {
        var userId = GetUserId();
        if (userId == null)
        {
            return Unauthorized(ApiResponse<List<CustomerOrderDto>>.ErrorResponse(
                "User not authenticated",
                "المستخدم غير مصرح له"));
        }

        var orders = await _orderService.GetCustomerOrdersAsync(userId.Value);
        return Ok(ApiResponse<List<CustomerOrderDto>>.SuccessResponse(orders));
    }

    /// <summary>
    /// Get a specific order by ID
    /// </summary>
    [HttpGet("{orderId}")]
    public async Task<ActionResult<ApiResponse<CustomerOrderDto>>> GetOrder(int orderId)
    {
        var userId = GetUserId();
        var userRole = GetUserRole();
        
        if (userId == null)
        {
            return Unauthorized(ApiResponse<CustomerOrderDto>.ErrorResponse(
                "User not authenticated",
                "المستخدم غير مصرح له"));
        }

        var order = await _orderService.GetCustomerOrderByIdAsync(userId.Value, orderId);
        
        // Admin can view any order
        if (order == null && userRole == "Admin")
        {
            var allOrders = await _orderService.GetAllOrdersAsync();
            order = allOrders.FirstOrDefault(o => o.CustOrderID == orderId);
        }

        if (order == null)
        {
            return NotFound(ApiResponse<CustomerOrderDto>.ErrorResponse(
                "Order not found",
                "الطلب غير موجود"));
        }

        return Ok(ApiResponse<CustomerOrderDto>.SuccessResponse(order));
    }

    /// <summary>
    /// Get all orders (Admin only)
    /// </summary>
    [HttpGet("all")]
    [Authorize(Roles = "Admin")]
    public async Task<ActionResult<ApiResponse<List<CustomerOrderDto>>>> GetAllOrders()
    {
        var orders = await _orderService.GetAllOrdersAsync();
        return Ok(ApiResponse<List<CustomerOrderDto>>.SuccessResponse(orders));
    }

    /// <summary>
    /// Update order status (Admin only)
    /// </summary>
    [HttpPut("{orderId}/status")]
    [Authorize(Roles = "Admin")]
    public async Task<ActionResult<ApiResponse<object>>> UpdateOrderStatus(int orderId, [FromBody] UpdateOrderStatusDto dto)
    {
        var success = await _orderService.UpdateOrderStatusAsync(orderId, dto.Status);
        if (!success)
        {
            return BadRequest(ApiResponse<object>.ErrorResponse(
                "Failed to update order status",
                "فشل في تحديث حالة الطلب"));
        }

        return Ok(ApiResponse<object>.SuccessResponse(
            null!,
            "Order status updated successfully",
            "تم تحديث حالة الطلب بنجاح"));
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

    private string? GetUserRole()
    {
        return User.FindFirst(System.Security.Claims.ClaimTypes.Role)?.Value;
    }

    private static string GetArabicErrorMessage(string englishMessage)
    {
        return englishMessage switch
        {
            "Credit card has expired." => "بطاقة الائتمان منتهية الصلاحية.",
            "Shopping cart is empty." => "سلة التسوق فارغة.",
            _ when englishMessage.Contains("Insufficient stock") => "المخزون غير كافٍ لأحد الكتب.",
            _ => "حدث خطأ أثناء إتمام الطلب."
        };
    }
}

public class UpdateOrderStatusDto
{
    public string Status { get; set; } = string.Empty;
}
