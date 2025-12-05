using Microsoft.EntityFrameworkCore;
using OnlineBookstoreApi.Data;
using OnlineBookstoreApi.DTOs;
using OnlineBookstoreApi.Models;

namespace OnlineBookstoreApi.Services;

public interface IBookService
{
    Task<PagedResultDto<BookDto>> GetBooksAsync(BookSearchDto search);
    Task<BookDto?> GetBookByIsbnAsync(string isbn);
    Task<BookDto?> CreateBookAsync(CreateBookDto dto);
    Task<BookDto?> UpdateBookAsync(string isbn, UpdateBookDto dto);
    Task<bool> DeleteBookAsync(string isbn);
    Task<List<CategoryDto>> GetCategoriesAsync();
    Task<List<AuthorDto>> GetAuthorsAsync();
    Task<AuthorDto?> CreateAuthorAsync(CreateAuthorDto dto);
    Task<bool> DeleteAuthorAsync(int authorId);
}

public class BookService : IBookService
{
    private readonly BookstoreDbContext _context;

    public BookService(BookstoreDbContext context)
    {
        _context = context;
    }

    public async Task<PagedResultDto<BookDto>> GetBooksAsync(BookSearchDto search)
    {
        var query = _context.Books
            .Include(b => b.Publisher)
            .Include(b => b.Category)
            .Include(b => b.BookAuthors)
                .ThenInclude(ba => ba.Author)
            .AsQueryable();

        // Apply filters
        if (!string.IsNullOrEmpty(search.ISBN))
        {
            query = query.Where(b => b.ISBN.Contains(search.ISBN));
        }

        if (!string.IsNullOrEmpty(search.Title))
        {
            query = query.Where(b => b.Title.Contains(search.Title) || 
                                     (b.TitleAr != null && b.TitleAr.Contains(search.Title)));
        }

        if (!string.IsNullOrEmpty(search.Author))
        {
            query = query.Where(b => b.BookAuthors.Any(ba => 
                ba.Author != null && ba.Author.Name.Contains(search.Author)));
        }

        if (!string.IsNullOrEmpty(search.Publisher))
        {
            query = query.Where(b => b.Publisher != null && 
                                     b.Publisher.Name.Contains(search.Publisher));
        }

        if (search.CategoryID.HasValue)
        {
            query = query.Where(b => b.CategoryID == search.CategoryID.Value);
        }

        if (search.MinPrice.HasValue)
        {
            query = query.Where(b => b.SellingPrice >= search.MinPrice.Value);
        }

        if (search.MaxPrice.HasValue)
        {
            query = query.Where(b => b.SellingPrice <= search.MaxPrice.Value);
        }

        if (search.InStock.HasValue && search.InStock.Value)
        {
            query = query.Where(b => b.QuantityInStock > 0);
        }

        // Get total count before pagination
        var totalCount = await query.CountAsync();

        // Apply pagination
        var books = await query
            .OrderByDescending(b => b.CreatedAt)
            .Skip((search.Page - 1) * search.PageSize)
            .Take(search.PageSize)
            .ToListAsync();

        var bookDtos = books.Select(MapToDto).ToList();

        return new PagedResultDto<BookDto>
        {
            Items = bookDtos,
            TotalCount = totalCount,
            Page = search.Page,
            PageSize = search.PageSize,
            TotalPages = (int)Math.Ceiling(totalCount / (double)search.PageSize)
        };
    }

    public async Task<BookDto?> GetBookByIsbnAsync(string isbn)
    {
        var book = await _context.Books
            .Include(b => b.Publisher)
            .Include(b => b.Category)
            .Include(b => b.BookAuthors)
                .ThenInclude(ba => ba.Author)
            .FirstOrDefaultAsync(b => b.ISBN == isbn);

        return book == null ? null : MapToDto(book);
    }

    public async Task<BookDto?> CreateBookAsync(CreateBookDto dto)
    {
        // Check if ISBN already exists
        if (await _context.Books.AnyAsync(b => b.ISBN == dto.ISBN))
        {
            return null;
        }

        var book = new Book
        {
            ISBN = dto.ISBN,
            Title = dto.Title,
            TitleAr = dto.TitleAr,
            PublisherID = dto.PublisherID,
            PublicationYear = dto.PublicationYear,
            SellingPrice = dto.SellingPrice,
            CategoryID = dto.CategoryID,
            QuantityInStock = dto.QuantityInStock,
            ReorderThreshold = dto.ReorderThreshold,
            Description = dto.Description,
            DescriptionAr = dto.DescriptionAr,
            ImageUrl = dto.ImageUrl,
            CreatedAt = DateTime.UtcNow
        };

        _context.Books.Add(book);

        // Add author associations
        foreach (var authorId in dto.AuthorIds)
        {
            _context.BookAuthors.Add(new BookAuthor
            {
                ISBN = dto.ISBN,
                AuthorID = authorId
            });
        }

        await _context.SaveChangesAsync();

        return await GetBookByIsbnAsync(dto.ISBN);
    }

    public async Task<BookDto?> UpdateBookAsync(string isbn, UpdateBookDto dto)
    {
        var book = await _context.Books
            .Include(b => b.BookAuthors)
            .FirstOrDefaultAsync(b => b.ISBN == isbn);

        if (book == null) return null;

        // Update properties if provided
        if (!string.IsNullOrEmpty(dto.Title))
            book.Title = dto.Title;
        if (dto.TitleAr != null)
            book.TitleAr = dto.TitleAr;
        if (dto.PublisherID.HasValue)
            book.PublisherID = dto.PublisherID.Value;
        if (dto.PublicationYear.HasValue)
            book.PublicationYear = dto.PublicationYear.Value;
        if (dto.SellingPrice.HasValue)
            book.SellingPrice = dto.SellingPrice.Value;
        if (dto.CategoryID.HasValue)
            book.CategoryID = dto.CategoryID.Value;
        if (dto.QuantityInStock.HasValue)
            book.QuantityInStock = dto.QuantityInStock.Value;
        if (dto.ReorderThreshold.HasValue)
            book.ReorderThreshold = dto.ReorderThreshold.Value;
        if (dto.Description != null)
            book.Description = dto.Description;
        if (dto.DescriptionAr != null)
            book.DescriptionAr = dto.DescriptionAr;
        if (dto.ImageUrl != null)
            book.ImageUrl = dto.ImageUrl;

        // Update authors if provided
        if (dto.AuthorIds != null)
        {
            // Remove existing authors
            _context.BookAuthors.RemoveRange(book.BookAuthors);

            // Add new authors
            foreach (var authorId in dto.AuthorIds)
            {
                _context.BookAuthors.Add(new BookAuthor
                {
                    ISBN = isbn,
                    AuthorID = authorId
                });
            }
        }

        await _context.SaveChangesAsync();
        return await GetBookByIsbnAsync(isbn);
    }

    public async Task<bool> DeleteBookAsync(string isbn)
    {
        var book = await _context.Books.FindAsync(isbn);
        if (book == null) return false;

        _context.Books.Remove(book);
        await _context.SaveChangesAsync();
        return true;
    }

    public async Task<List<CategoryDto>> GetCategoriesAsync()
    {
        return await _context.Categories
            .Select(c => new CategoryDto
            {
                CategoryID = c.CategoryID,
                CategoryName = c.CategoryName,
                CategoryNameAr = c.CategoryNameAr
            })
            .ToListAsync();
    }

    public async Task<List<AuthorDto>> GetAuthorsAsync()
    {
        return await _context.Authors
            .Select(a => new AuthorDto
            {
                AuthorID = a.AuthorID,
                Name = a.Name
            })
            .ToListAsync();
    }

    public async Task<AuthorDto?> CreateAuthorAsync(CreateAuthorDto dto)
    {
        var author = new Author { Name = dto.Name };
        _context.Authors.Add(author);
        await _context.SaveChangesAsync();

        return new AuthorDto
        {
            AuthorID = author.AuthorID,
            Name = author.Name
        };
    }

    public async Task<bool> DeleteAuthorAsync(int authorId)
    {
        var author = await _context.Authors.FindAsync(authorId);
        if (author == null) return false;

        _context.Authors.Remove(author);
        await _context.SaveChangesAsync();
        return true;
    }

    private static BookDto MapToDto(Book book)
    {
        return new BookDto
        {
            ISBN = book.ISBN,
            Title = book.Title,
            TitleAr = book.TitleAr,
            PublisherID = book.PublisherID,
            PublisherName = book.Publisher?.Name,
            PublicationYear = book.PublicationYear,
            SellingPrice = book.SellingPrice,
            CategoryID = book.CategoryID,
            CategoryName = book.Category?.CategoryName,
            CategoryNameAr = book.Category?.CategoryNameAr,
            QuantityInStock = book.QuantityInStock,
            ReorderThreshold = book.ReorderThreshold,
            Description = book.Description,
            DescriptionAr = book.DescriptionAr,
            ImageUrl = book.ImageUrl,
            Authors = book.BookAuthors
                .Where(ba => ba.Author != null)
                .Select(ba => new AuthorDto
                {
                    AuthorID = ba.AuthorID,
                    Name = ba.Author!.Name
                })
                .ToList(),
            CreatedAt = book.CreatedAt
        };
    }
}
