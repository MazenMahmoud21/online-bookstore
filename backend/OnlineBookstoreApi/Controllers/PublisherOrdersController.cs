using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using OnlineBookstoreApi.DTOs;
using OnlineBookstoreApi.Services;

namespace OnlineBookstoreApi.Controllers;

[ApiController]
[Route("api/[controller]")]
[Authorize(Roles = "Admin")]
public class PublisherOrdersController : ControllerBase
{
    private readonly IPublisherOrderService _publisherOrderService;

    public PublisherOrdersController(IPublisherOrderService publisherOrderService)
    {
        _publisherOrderService = publisherOrderService;
    }

    /// <summary>
    /// Get all publisher orders, optionally filtered by status
    /// </summary>
    [HttpGet]
    public async Task<ActionResult<ApiResponse<List<PublisherOrderDto>>>> GetPublisherOrders([FromQuery] string? status = null)
    {
        var orders = await _publisherOrderService.GetPublisherOrdersAsync(status);
        return Ok(ApiResponse<List<PublisherOrderDto>>.SuccessResponse(orders));
    }

    /// <summary>
    /// Get pending publisher orders
    /// </summary>
    [HttpGet("pending")]
    public async Task<ActionResult<ApiResponse<List<PublisherOrderDto>>>> GetPendingOrders()
    {
        var orders = await _publisherOrderService.GetPublisherOrdersAsync("Pending");
        return Ok(ApiResponse<List<PublisherOrderDto>>.SuccessResponse(orders));
    }

    /// <summary>
    /// Get a publisher order by ID
    /// </summary>
    [HttpGet("{id}")]
    public async Task<ActionResult<ApiResponse<PublisherOrderDto>>> GetPublisherOrder(int id)
    {
        var order = await _publisherOrderService.GetPublisherOrderByIdAsync(id);
        if (order == null)
        {
            return NotFound(ApiResponse<PublisherOrderDto>.ErrorResponse(
                "Publisher order not found",
                "طلب الناشر غير موجود"));
        }

        return Ok(ApiResponse<PublisherOrderDto>.SuccessResponse(order));
    }

    /// <summary>
    /// Create a new publisher order
    /// </summary>
    [HttpPost]
    public async Task<ActionResult<ApiResponse<PublisherOrderDto>>> CreatePublisherOrder([FromBody] CreatePublisherOrderDto dto)
    {
        if (!ModelState.IsValid)
        {
            return BadRequest(ApiResponse<PublisherOrderDto>.ErrorResponse(
                "Invalid order data",
                "بيانات الطلب غير صحيحة"));
        }

        var order = await _publisherOrderService.CreatePublisherOrderAsync(dto);
        if (order == null)
        {
            return BadRequest(ApiResponse<PublisherOrderDto>.ErrorResponse(
                "Failed to create order",
                "فشل في إنشاء الطلب"));
        }

        return CreatedAtAction(nameof(GetPublisherOrder), new { id = order.PubOrderID },
            ApiResponse<PublisherOrderDto>.SuccessResponse(
                order,
                "Publisher order created successfully",
                "تم إنشاء طلب الناشر بنجاح"));
    }

    /// <summary>
    /// Confirm a publisher order (adds stock to books)
    /// </summary>
    [HttpPost("{id}/confirm")]
    public async Task<ActionResult<ApiResponse<object>>> ConfirmOrder(int id)
    {
        var success = await _publisherOrderService.ConfirmPublisherOrderAsync(id);
        if (!success)
        {
            return BadRequest(ApiResponse<object>.ErrorResponse(
                "Cannot confirm order. Order not found or not in pending status.",
                "لا يمكن تأكيد الطلب. الطلب غير موجود أو ليس في حالة معلقة."));
        }

        return Ok(ApiResponse<object>.SuccessResponse(
            null!,
            "Publisher order confirmed successfully. Stock has been updated.",
            "تم تأكيد طلب الناشر بنجاح. تم تحديث المخزون."));
    }

    /// <summary>
    /// Cancel a publisher order
    /// </summary>
    [HttpPost("{id}/cancel")]
    public async Task<ActionResult<ApiResponse<object>>> CancelOrder(int id)
    {
        var success = await _publisherOrderService.CancelPublisherOrderAsync(id);
        if (!success)
        {
            return BadRequest(ApiResponse<object>.ErrorResponse(
                "Cannot cancel order. Order not found or not in pending status.",
                "لا يمكن إلغاء الطلب. الطلب غير موجود أو ليس في حالة معلقة."));
        }

        return Ok(ApiResponse<object>.SuccessResponse(
            null!,
            "Publisher order cancelled successfully.",
            "تم إلغاء طلب الناشر بنجاح."));
    }
}
