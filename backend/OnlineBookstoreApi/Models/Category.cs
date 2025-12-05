using System.ComponentModel.DataAnnotations;

namespace OnlineBookstoreApi.Models;

public class Category
{
    [Key]
    public int CategoryID { get; set; }

    [Required]
    [MaxLength(100)]
    public string CategoryName { get; set; } = string.Empty;

    [Required]
    [MaxLength(100)]
    public string CategoryNameAr { get; set; } = string.Empty;

    // Navigation properties
    public virtual ICollection<Book> Books { get; set; } = new List<Book>();
}
