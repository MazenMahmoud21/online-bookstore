using System.ComponentModel.DataAnnotations;

namespace OnlineBookstoreApi.Models;

public class Publisher
{
    [Key]
    public int PublisherID { get; set; }

    [Required]
    [MaxLength(200)]
    public string Name { get; set; } = string.Empty;

    [MaxLength(500)]
    public string? Address { get; set; }

    [MaxLength(20)]
    public string? Phone { get; set; }

    // Navigation properties
    public virtual ICollection<Book> Books { get; set; } = new List<Book>();
    public virtual ICollection<PublisherOrder> PublisherOrders { get; set; } = new List<PublisherOrder>();
}
