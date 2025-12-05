using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OnlineBookstoreApi.Models;

public class CustomerOrder
{
    [Key]
    public int CustOrderID { get; set; }

    [Required]
    public int UserID { get; set; }

    public DateTime OrderDate { get; set; } = DateTime.UtcNow;

    [Required]
    [Column(TypeName = "decimal(10, 2)")]
    public decimal TotalAmount { get; set; } = 0;

    [MaxLength(20)]
    public string? CreditCardNumber { get; set; }

    public DateTime? CreditCardExpiry { get; set; }

    [Required]
    [MaxLength(20)]
    public string Status { get; set; } = "Pending";

    [MaxLength(500)]
    public string? ShippingAddress { get; set; }

    [MaxLength(500)]
    public string? Notes { get; set; }

    // Navigation properties
    [ForeignKey("UserID")]
    public virtual User? User { get; set; }

    public virtual ICollection<CustomerOrderItem> CustomerOrderItems { get; set; } = new List<CustomerOrderItem>();
}
