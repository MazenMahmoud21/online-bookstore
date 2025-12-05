using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OnlineBookstoreApi.Models;

public class ShoppingCart
{
    [Key]
    public int CartID { get; set; }

    [Required]
    public int UserID { get; set; }

    public DateTime CreatedAt { get; set; } = DateTime.UtcNow;

    public DateTime UpdatedAt { get; set; } = DateTime.UtcNow;

    // Navigation properties
    [ForeignKey("UserID")]
    public virtual User? User { get; set; }

    public virtual ICollection<CartItem> CartItems { get; set; } = new List<CartItem>();
}
