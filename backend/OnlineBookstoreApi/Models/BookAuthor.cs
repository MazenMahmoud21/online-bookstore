using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OnlineBookstoreApi.Models;

public class BookAuthor
{
    [Required]
    [MaxLength(20)]
    public string ISBN { get; set; } = string.Empty;

    [Required]
    public int AuthorID { get; set; }

    // Navigation properties
    [ForeignKey("ISBN")]
    public virtual Book? Book { get; set; }

    [ForeignKey("AuthorID")]
    public virtual Author? Author { get; set; }
}
