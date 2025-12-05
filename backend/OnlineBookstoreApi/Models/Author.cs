using System.ComponentModel.DataAnnotations;

namespace OnlineBookstoreApi.Models;

public class Author
{
    [Key]
    public int AuthorID { get; set; }

    [Required]
    [MaxLength(200)]
    public string Name { get; set; } = string.Empty;

    // Navigation properties
    public virtual ICollection<BookAuthor> BookAuthors { get; set; } = new List<BookAuthor>();
}
