using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace OnlineBookstoreApi.Models;

public class PublisherOrder
{
    [Key]
    public int PubOrderID { get; set; }

    [Required]
    public int PublisherID { get; set; }

    public DateTime OrderDate { get; set; } = DateTime.UtcNow;

    [Required]
    [MaxLength(20)]
    public string Status { get; set; } = "Pending";

    [Column(TypeName = "decimal(10, 2)")]
    public decimal TotalAmount { get; set; } = 0;

    [MaxLength(500)]
    public string? Notes { get; set; }

    public DateTime? ConfirmedAt { get; set; }

    // Navigation properties
    [ForeignKey("PublisherID")]
    public virtual Publisher? Publisher { get; set; }

    public virtual ICollection<PublisherOrderItem> PublisherOrderItems { get; set; } = new List<PublisherOrderItem>();
}
