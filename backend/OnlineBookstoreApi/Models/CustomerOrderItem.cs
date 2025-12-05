using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OnlineBookstoreApi.Models;

public class CustomerOrderItem
{
    [Key]
    public int CustOrderItemID { get; set; }

    [Required]
    public int CustOrderID { get; set; }

    [Required]
    [MaxLength(20)]
    public string ISBN { get; set; } = string.Empty;

    [Required]
    [Range(1, int.MaxValue)]
    public int Quantity { get; set; }

    [Required]
    [Column(TypeName = "decimal(10, 2)")]
    [Range(0, double.MaxValue)]
    public decimal UnitPrice { get; set; }

    // Navigation properties
    [ForeignKey("CustOrderID")]
    public virtual CustomerOrder? CustomerOrder { get; set; }

    [ForeignKey("ISBN")]
    public virtual Book? Book { get; set; }
}
