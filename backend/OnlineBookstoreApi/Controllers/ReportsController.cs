using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using OnlineBookstoreApi.DTOs;
using OnlineBookstoreApi.Services;

namespace OnlineBookstoreApi.Controllers;

[ApiController]
[Route("api/[controller]")]
[Authorize(Roles = "Admin")]
public class ReportsController : ControllerBase
{
    private readonly IReportService _reportService;

    public ReportsController(IReportService reportService)
    {
        _reportService = reportService;
    }

    /// <summary>
    /// Get total sales by month
    /// </summary>
    [HttpGet("sales/monthly")]
    public async Task<ActionResult<ApiResponse<MonthlySalesReportDto>>> GetMonthlySales([FromQuery] int year, [FromQuery] int month)
    {
        if (month < 1 || month > 12)
        {
            return BadRequest(ApiResponse<MonthlySalesReportDto>.ErrorResponse(
                "Invalid month",
                "الشهر غير صحيح"));
        }

        var report = await _reportService.GetTotalSalesByMonthAsync(year, month);
        return Ok(ApiResponse<MonthlySalesReportDto>.SuccessResponse(report));
    }

    /// <summary>
    /// Get total sales by date
    /// </summary>
    [HttpGet("sales/daily")]
    public async Task<ActionResult<ApiResponse<DailySalesReportDto>>> GetDailySales([FromQuery] DateTime date)
    {
        var report = await _reportService.GetTotalSalesByDateAsync(date);
        return Ok(ApiResponse<DailySalesReportDto>.SuccessResponse(report));
    }

    /// <summary>
    /// Get top customers by spending
    /// </summary>
    [HttpGet("top-customers")]
    public async Task<ActionResult<ApiResponse<List<TopCustomerDto>>>> GetTopCustomers(
        [FromQuery] int months = 3, 
        [FromQuery] int topN = 5)
    {
        if (months < 1 || months > 24)
        {
            return BadRequest(ApiResponse<List<TopCustomerDto>>.ErrorResponse(
                "Months must be between 1 and 24",
                "يجب أن يكون عدد الأشهر بين 1 و 24"));
        }

        if (topN < 1 || topN > 100)
        {
            return BadRequest(ApiResponse<List<TopCustomerDto>>.ErrorResponse(
                "TopN must be between 1 and 100",
                "يجب أن يكون العدد المطلوب بين 1 و 100"));
        }

        var report = await _reportService.GetTopCustomersAsync(months, topN);
        return Ok(ApiResponse<List<TopCustomerDto>>.SuccessResponse(report));
    }

    /// <summary>
    /// Get top selling books
    /// </summary>
    [HttpGet("top-books")]
    public async Task<ActionResult<ApiResponse<List<TopSellingBookDto>>>> GetTopSellingBooks(
        [FromQuery] int months = 3, 
        [FromQuery] int topN = 10)
    {
        if (months < 1 || months > 24)
        {
            return BadRequest(ApiResponse<List<TopSellingBookDto>>.ErrorResponse(
                "Months must be between 1 and 24",
                "يجب أن يكون عدد الأشهر بين 1 و 24"));
        }

        if (topN < 1 || topN > 100)
        {
            return BadRequest(ApiResponse<List<TopSellingBookDto>>.ErrorResponse(
                "TopN must be between 1 and 100",
                "يجب أن يكون العدد المطلوب بين 1 و 100"));
        }

        var report = await _reportService.GetTopSellingBooksAsync(months, topN);
        return Ok(ApiResponse<List<TopSellingBookDto>>.SuccessResponse(report));
    }

    /// <summary>
    /// Get restock statistics for a specific book
    /// </summary>
    [HttpGet("restock/{isbn}")]
    public async Task<ActionResult<ApiResponse<BookReorderStatsDto>>> GetBookRestockStats(string isbn)
    {
        var report = await _reportService.GetTimesBookReorderedAsync(isbn);
        if (report == null)
        {
            return NotFound(ApiResponse<BookReorderStatsDto>.ErrorResponse(
                "Book not found",
                "الكتاب غير موجود"));
        }

        return Ok(ApiResponse<BookReorderStatsDto>.SuccessResponse(report));
    }

    /// <summary>
    /// Get restock statistics for all books
    /// </summary>
    [HttpGet("restock")]
    public async Task<ActionResult<ApiResponse<List<BookReorderStatsDto>>>> GetAllBooksRestockStats()
    {
        var report = await _reportService.GetAllBooksReorderStatsAsync();
        return Ok(ApiResponse<List<BookReorderStatsDto>>.SuccessResponse(report));
    }
}
