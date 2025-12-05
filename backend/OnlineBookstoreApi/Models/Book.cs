using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OnlineBookstoreApi.Models;

public class Book
{
    [Key]
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
    [Column(TypeName = "decimal(10, 2)")]
    [Range(0, double.MaxValue)]
    public decimal SellingPrice { get; set; }

    [Required]
    public int CategoryID { get; set; }

    [Required]
    [Range(0, int.MaxValue)]
    public int QuantityInStock { get; set; } = 0;

    [Required]
    [Range(0, int.MaxValue)]
    public int ReorderThreshold { get; set; } = 10;

    public string? Description { get; set; }

    public string? DescriptionAr { get; set; }

    [MaxLength(500)]
    public string? ImageUrl { get; set; }

    public DateTime CreatedAt { get; set; } = DateTime.UtcNow;

    // Navigation properties
    [ForeignKey("PublisherID")]
    public virtual Publisher? Publisher { get; set; }

    [ForeignKey("CategoryID")]
    public virtual Category? Category { get; set; }

    public virtual ICollection<BookAuthor> BookAuthors { get; set; } = new List<BookAuthor>();
    public virtual ICollection<CustomerOrderItem> CustomerOrderItems { get; set; } = new List<CustomerOrderItem>();
    public virtual ICollection<PublisherOrderItem> PublisherOrderItems { get; set; } = new List<PublisherOrderItem>();
    public virtual ICollection<CartItem> CartItems { get; set; } = new List<CartItem>();
}
