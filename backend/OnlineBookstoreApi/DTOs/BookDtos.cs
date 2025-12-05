using System.ComponentModel.DataAnnotations;

namespace OnlineBookstoreApi.DTOs;

// Book DTOs
public class BookDto
{
    public string ISBN { get; set; } = string.Empty;
    public string Title { get; set; } = string.Empty;
    public string? TitleAr { get; set; }
    public int PublisherID { get; set; }
    public string? PublisherName { get; set; }
    public int? PublicationYear { get; set; }
    public decimal SellingPrice { get; set; }
    public int CategoryID { get; set; }
    public string? CategoryName { get; set; }
    public string? CategoryNameAr { get; set; }
    public int QuantityInStock { get; set; }
    public int ReorderThreshold { get; set; }
    public string? Description { get; set; }
    public string? DescriptionAr { get; set; }
    public string? ImageUrl { get; set; }
    public List<AuthorDto> Authors { get; set; } = new();
    public DateTime CreatedAt { get; set; }
}

public class CreateBookDto
{
    [Required]
    [MaxLength(20)]
    public string ISBN { get; set; } = string.Empty;

    [Required]
    [MaxLength(300)]
    public string Title { get; set; } = string.Empty;

    [MaxLength(300)]
    public string? TitleAr { get; set; }

    [Required]
    public int PublisherID { get; set; }

    public int? PublicationYear { get; set; }

    [Required]
    [Range(0, double.MaxValue)]
    public decimal SellingPrice { get; set; }

    [Required]
    public int CategoryID { get; set; }

    [Range(0, int.MaxValue)]
    public int QuantityInStock { get; set; } = 0;

    [Range(0, int.MaxValue)]
    public int ReorderThreshold { get; set; } = 10;

    public string? Description { get; set; }

    public string? DescriptionAr { get; set; }

    [MaxLength(500)]
    public string? ImageUrl { get; set; }

    public List<int> AuthorIds { get; set; } = new();
}

public class UpdateBookDto
{
    [MaxLength(300)]
    public string? Title { get; set; }

    [MaxLength(300)]
    public string? TitleAr { get; set; }

    public int? PublisherID { get; set; }

    public int? PublicationYear { get; set; }

    [Range(0, double.MaxValue)]
    public decimal? SellingPrice { get; set; }

    public int? CategoryID { get; set; }

    [Range(0, int.MaxValue)]
    public int? QuantityInStock { get; set; }

    [Range(0, int.MaxValue)]
    public int? ReorderThreshold { get; set; }

    public string? Description { get; set; }

    public string? DescriptionAr { get; set; }

    [MaxLength(500)]
    public string? ImageUrl { get; set; }

    public List<int>? AuthorIds { get; set; }
}

public class BookSearchDto
{
    public string? ISBN { get; set; }
    public string? Title { get; set; }
    public string? Author { get; set; }
    public string? Publisher { get; set; }
    public int? CategoryID { get; set; }
    public decimal? MinPrice { get; set; }
    public decimal? MaxPrice { get; set; }
    public bool? InStock { get; set; }
    public int Page { get; set; } = 1;
    public int PageSize { get; set; } = 10;
}

// Author DTOs
public class AuthorDto
{
    public int AuthorID { get; set; }
    public string Name { get; set; } = string.Empty;
}

public class CreateAuthorDto
{
    [Required]
    [MaxLength(200)]
    public string Name { get; set; } = string.Empty;
}

// Category DTOs
public class CategoryDto
{
    public int CategoryID { get; set; }
    public string CategoryName { get; set; } = string.Empty;
    public string CategoryNameAr { get; set; } = string.Empty;
}

// Publisher DTOs
public class PublisherDto
{
    public int PublisherID { get; set; }
    public string Name { get; set; } = string.Empty;
    public string? Address { get; set; }
    public string? Phone { get; set; }
}

public class CreatePublisherDto
{
    [Required]
    [MaxLength(200)]
    public string Name { get; set; } = string.Empty;

    [MaxLength(500)]
    public string? Address { get; set; }

    [MaxLength(20)]
    public string? Phone { get; set; }
}

public class UpdatePublisherDto
{
    [MaxLength(200)]
    public string? Name { get; set; }

    [MaxLength(500)]
    public string? Address { get; set; }

    [MaxLength(20)]
    public string? Phone { get; set; }
}
