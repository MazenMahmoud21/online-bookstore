using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using OnlineBookstoreApi.DTOs;
using OnlineBookstoreApi.Services;

namespace OnlineBookstoreApi.Controllers;

[ApiController]
[Route("api/[controller]")]
public class BooksController : ControllerBase
{
    private readonly IBookService _bookService;

    public BooksController(IBookService bookService)
    {
        _bookService = bookService;
    }

    /// <summary>
    /// Get all books with optional search/filter
    /// </summary>
    [HttpGet]
    public async Task<ActionResult<ApiResponse<PagedResultDto<BookDto>>>> GetBooks([FromQuery] BookSearchDto search)
    {
        var result = await _bookService.GetBooksAsync(search);
        return Ok(ApiResponse<PagedResultDto<BookDto>>.SuccessResponse(result));
    }

    /// <summary>
    /// Get a book by ISBN
    /// </summary>
    [HttpGet("{isbn}")]
    public async Task<ActionResult<ApiResponse<BookDto>>> GetBook(string isbn)
    {
        var book = await _bookService.GetBookByIsbnAsync(isbn);
        if (book == null)
        {
            return NotFound(ApiResponse<BookDto>.ErrorResponse(
                "Book not found",
                "الكتاب غير موجود"));
        }

        return Ok(ApiResponse<BookDto>.SuccessResponse(book));
    }

    /// <summary>
    /// Create a new book (Admin only)
    /// </summary>
    [HttpPost]
    [Authorize(Roles = "Admin")]
    public async Task<ActionResult<ApiResponse<BookDto>>> CreateBook([FromBody] CreateBookDto dto)
    {
        if (!ModelState.IsValid)
        {
            return BadRequest(ApiResponse<BookDto>.ErrorResponse(
                "Invalid book data",
                "بيانات الكتاب غير صحيحة"));
        }

        var book = await _bookService.CreateBookAsync(dto);
        if (book == null)
        {
            return BadRequest(ApiResponse<BookDto>.ErrorResponse(
                "Book with this ISBN already exists",
                "يوجد كتاب بهذا الرقم المعياري الدولي"));
        }

        return CreatedAtAction(nameof(GetBook), new { isbn = book.ISBN }, 
            ApiResponse<BookDto>.SuccessResponse(
                book,
                "Book created successfully",
                "تم إنشاء الكتاب بنجاح"));
    }

    /// <summary>
    /// Update a book (Admin only)
    /// </summary>
    [HttpPut("{isbn}")]
    [Authorize(Roles = "Admin")]
    public async Task<ActionResult<ApiResponse<BookDto>>> UpdateBook(string isbn, [FromBody] UpdateBookDto dto)
    {
        var book = await _bookService.UpdateBookAsync(isbn, dto);
        if (book == null)
        {
            return NotFound(ApiResponse<BookDto>.ErrorResponse(
                "Book not found",
                "الكتاب غير موجود"));
        }

        return Ok(ApiResponse<BookDto>.SuccessResponse(
            book,
            "Book updated successfully",
            "تم تحديث الكتاب بنجاح"));
    }

    /// <summary>
    /// Delete a book (Admin only)
    /// </summary>
    [HttpDelete("{isbn}")]
    [Authorize(Roles = "Admin")]
    public async Task<ActionResult<ApiResponse<object>>> DeleteBook(string isbn)
    {
        var success = await _bookService.DeleteBookAsync(isbn);
        if (!success)
        {
            return NotFound(ApiResponse<object>.ErrorResponse(
                "Book not found",
                "الكتاب غير موجود"));
        }

        return Ok(ApiResponse<object>.SuccessResponse(
            null!,
            "Book deleted successfully",
            "تم حذف الكتاب بنجاح"));
    }

    /// <summary>
    /// Get all categories
    /// </summary>
    [HttpGet("categories")]
    public async Task<ActionResult<ApiResponse<List<CategoryDto>>>> GetCategories()
    {
        var categories = await _bookService.GetCategoriesAsync();
        return Ok(ApiResponse<List<CategoryDto>>.SuccessResponse(categories));
    }

    /// <summary>
    /// Get all authors
    /// </summary>
    [HttpGet("authors")]
    public async Task<ActionResult<ApiResponse<List<AuthorDto>>>> GetAuthors()
    {
        var authors = await _bookService.GetAuthorsAsync();
        return Ok(ApiResponse<List<AuthorDto>>.SuccessResponse(authors));
    }

    /// <summary>
    /// Create a new author (Admin only)
    /// </summary>
    [HttpPost("authors")]
    [Authorize(Roles = "Admin")]
    public async Task<ActionResult<ApiResponse<AuthorDto>>> CreateAuthor([FromBody] CreateAuthorDto dto)
    {
        if (!ModelState.IsValid)
        {
            return BadRequest(ApiResponse<AuthorDto>.ErrorResponse(
                "Invalid author data",
                "بيانات المؤلف غير صحيحة"));
        }

        var author = await _bookService.CreateAuthorAsync(dto);
        return CreatedAtAction(nameof(GetAuthors), new { }, 
            ApiResponse<AuthorDto>.SuccessResponse(
                author!,
                "Author created successfully",
                "تم إنشاء المؤلف بنجاح"));
    }

    /// <summary>
    /// Delete an author (Admin only)
    /// </summary>
    [HttpDelete("authors/{authorId}")]
    [Authorize(Roles = "Admin")]
    public async Task<ActionResult<ApiResponse<object>>> DeleteAuthor(int authorId)
    {
        var success = await _bookService.DeleteAuthorAsync(authorId);
        if (!success)
        {
            return NotFound(ApiResponse<object>.ErrorResponse(
                "Author not found",
                "المؤلف غير موجود"));
        }

        return Ok(ApiResponse<object>.SuccessResponse(
            null!,
            "Author deleted successfully",
            "تم حذف المؤلف بنجاح"));
    }
}
