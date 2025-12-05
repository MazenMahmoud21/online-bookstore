using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using OnlineBookstoreApi.DTOs;
using OnlineBookstoreApi.Services;

namespace OnlineBookstoreApi.Controllers;

[ApiController]
[Route("api/[controller]")]
[Authorize(Roles = "Admin")]
public class PublishersController : ControllerBase
{
    private readonly IPublisherService _publisherService;

    public PublishersController(IPublisherService publisherService)
    {
        _publisherService = publisherService;
    }

    /// <summary>
    /// Get all publishers
    /// </summary>
    [HttpGet]
    [AllowAnonymous]
    public async Task<ActionResult<ApiResponse<List<PublisherDto>>>> GetPublishers()
    {
        var publishers = await _publisherService.GetPublishersAsync();
        return Ok(ApiResponse<List<PublisherDto>>.SuccessResponse(publishers));
    }

    /// <summary>
    /// Get a publisher by ID
    /// </summary>
    [HttpGet("{id}")]
    [AllowAnonymous]
    public async Task<ActionResult<ApiResponse<PublisherDto>>> GetPublisher(int id)
    {
        var publisher = await _publisherService.GetPublisherByIdAsync(id);
        if (publisher == null)
        {
            return NotFound(ApiResponse<PublisherDto>.ErrorResponse(
                "Publisher not found",
                "الناشر غير موجود"));
        }

        return Ok(ApiResponse<PublisherDto>.SuccessResponse(publisher));
    }

    /// <summary>
    /// Create a new publisher (Admin only)
    /// </summary>
    [HttpPost]
    public async Task<ActionResult<ApiResponse<PublisherDto>>> CreatePublisher([FromBody] CreatePublisherDto dto)
    {
        if (!ModelState.IsValid)
        {
            return BadRequest(ApiResponse<PublisherDto>.ErrorResponse(
                "Invalid publisher data",
                "بيانات الناشر غير صحيحة"));
        }

        var publisher = await _publisherService.CreatePublisherAsync(dto);
        return CreatedAtAction(nameof(GetPublisher), new { id = publisher!.PublisherID },
            ApiResponse<PublisherDto>.SuccessResponse(
                publisher,
                "Publisher created successfully",
                "تم إنشاء الناشر بنجاح"));
    }

    /// <summary>
    /// Update a publisher (Admin only)
    /// </summary>
    [HttpPut("{id}")]
    public async Task<ActionResult<ApiResponse<PublisherDto>>> UpdatePublisher(int id, [FromBody] UpdatePublisherDto dto)
    {
        var publisher = await _publisherService.UpdatePublisherAsync(id, dto);
        if (publisher == null)
        {
            return NotFound(ApiResponse<PublisherDto>.ErrorResponse(
                "Publisher not found",
                "الناشر غير موجود"));
        }

        return Ok(ApiResponse<PublisherDto>.SuccessResponse(
            publisher,
            "Publisher updated successfully",
            "تم تحديث الناشر بنجاح"));
    }

    /// <summary>
    /// Delete a publisher (Admin only)
    /// </summary>
    [HttpDelete("{id}")]
    public async Task<ActionResult<ApiResponse<object>>> DeletePublisher(int id)
    {
        var success = await _publisherService.DeletePublisherAsync(id);
        if (!success)
        {
            return NotFound(ApiResponse<object>.ErrorResponse(
                "Publisher not found",
                "الناشر غير موجود"));
        }

        return Ok(ApiResponse<object>.SuccessResponse(
            null!,
            "Publisher deleted successfully",
            "تم حذف الناشر بنجاح"));
    }
}
