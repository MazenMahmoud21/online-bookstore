using System.ComponentModel.DataAnnotations;

namespace OnlineBookstoreApi.DTOs;

// Shopping Cart DTOs
public class CartDto
{
    public int CartID { get; set; }
    public int UserID { get; set; }
    public List<CartItemDto> Items { get; set; } = new();
    public decimal TotalAmount { get; set; }
    public int TotalItems { get; set; }
    public DateTime UpdatedAt { get; set; }
}

public class CartItemDto
{
    public int CartItemID { get; set; }
    public string ISBN { get; set; } = string.Empty;
    public string BookTitle { get; set; } = string.Empty;
    public string? BookTitleAr { get; set; }
    public decimal UnitPrice { get; set; }
    public int Quantity { get; set; }
    public int AvailableStock { get; set; }
    public string? ImageUrl { get; set; }
    public decimal Subtotal { get; set; }
    public DateTime AddedAt { get; set; }
}

public class AddToCartDto
{
    [Required]
    public string ISBN { get; set; } = string.Empty;

    [Required]
    [Range(1, int.MaxValue)]
    public int Quantity { get; set; } = 1;
}

public class UpdateCartItemDto
{
    [Required]
    [Range(1, int.MaxValue)]
    public int Quantity { get; set; }
}

// Checkout DTOs
public class CheckoutDto
{
    [Required]
    [CreditCard]
    public string CreditCardNumber { get; set; } = string.Empty;

    [Required]
    public DateTime CreditCardExpiry { get; set; }

    public string? ShippingAddress { get; set; }

    public string? Notes { get; set; }
}

// Customer Order DTOs
public class CustomerOrderDto
{
    public int CustOrderID { get; set; }
    public int UserID { get; set; }
    public string? CustomerName { get; set; }
    public DateTime OrderDate { get; set; }
    public decimal TotalAmount { get; set; }
    public string? CreditCardLast4 { get; set; }
    public string Status { get; set; } = string.Empty;
    public string? ShippingAddress { get; set; }
    public string? Notes { get; set; }
    public List<CustomerOrderItemDto> Items { get; set; } = new();
}

public class CustomerOrderItemDto
{
    public int CustOrderItemID { get; set; }
    public string ISBN { get; set; } = string.Empty;
    public string BookTitle { get; set; } = string.Empty;
    public string? BookTitleAr { get; set; }
    public int Quantity { get; set; }
    public decimal UnitPrice { get; set; }
    public decimal Subtotal { get; set; }
}

// Publisher Order DTOs
public class PublisherOrderDto
{
    public int PubOrderID { get; set; }
    public int PublisherID { get; set; }
    public string? PublisherName { get; set; }
    public DateTime OrderDate { get; set; }
    public string Status { get; set; } = string.Empty;
    public decimal TotalAmount { get; set; }
    public string? Notes { get; set; }
    public DateTime? ConfirmedAt { get; set; }
    public List<PublisherOrderItemDto> Items { get; set; } = new();
}

public class PublisherOrderItemDto
{
    public int PubOrderItemID { get; set; }
    public string ISBN { get; set; } = string.Empty;
    public string BookTitle { get; set; } = string.Empty;
    public string? BookTitleAr { get; set; }
    public int Quantity { get; set; }
    public decimal UnitPrice { get; set; }
    public decimal Subtotal { get; set; }
}

public class CreatePublisherOrderDto
{
    [Required]
    public int PublisherID { get; set; }

    public string? Notes { get; set; }

    [Required]
    [MinLength(1)]
    public List<CreatePublisherOrderItemDto> Items { get; set; } = new();
}

public class CreatePublisherOrderItemDto
{
    [Required]
    public string ISBN { get; set; } = string.Empty;

    [Required]
    [Range(1, int.MaxValue)]
    public int Quantity { get; set; }

    [Required]
    [Range(0, double.MaxValue)]
    public decimal UnitPrice { get; set; }
}
