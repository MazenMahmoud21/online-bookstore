using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OnlineBookstoreApi.Models;

public class CartItem
{
    [Key]
    public int CartItemID { get; set; }

    [Required]
    public int CartID { get; set; }

    [Required]
    [MaxLength(20)]
    public string ISBN { get; set; } = string.Empty;

    [Required]
    [Range(1, int.MaxValue)]
    public int Quantity { get; set; } = 1;

    public DateTime AddedAt { get; set; } = DateTime.UtcNow;

    // Navigation properties
    [ForeignKey("CartID")]
    public virtual ShoppingCart? ShoppingCart { get; set; }

    [ForeignKey("ISBN")]
    public virtual Book? Book { get; set; }
}
