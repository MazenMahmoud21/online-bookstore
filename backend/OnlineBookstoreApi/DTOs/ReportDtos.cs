namespace OnlineBookstoreApi.DTOs;

// Report DTOs
public class MonthlySalesReportDto
{
    public int Year { get; set; }
    public int Month { get; set; }
    public int TotalOrders { get; set; }
    public decimal TotalSales { get; set; }
    public int UniqueCustomers { get; set; }
}

public class DailySalesReportDto
{
    public DateTime Date { get; set; }
    public int TotalOrders { get; set; }
    public decimal TotalSales { get; set; }
    public int UniqueCustomers { get; set; }
}

public class TopCustomerDto
{
    public int UserID { get; set; }
    public string Username { get; set; } = string.Empty;
    public string FirstName { get; set; } = string.Empty;
    public string LastName { get; set; } = string.Empty;
    public string Email { get; set; } = string.Empty;
    public int OrderCount { get; set; }
    public decimal TotalSpent { get; set; }
}

public class TopSellingBookDto
{
    public string ISBN { get; set; } = string.Empty;
    public string Title { get; set; } = string.Empty;
    public string? TitleAr { get; set; }
    public string? PublisherName { get; set; }
    public string? CategoryName { get; set; }
    public string? CategoryNameAr { get; set; }
    public int TotalQuantitySold { get; set; }
    public decimal TotalRevenue { get; set; }
}

public class BookReorderStatsDto
{
    public string ISBN { get; set; } = string.Empty;
    public string Title { get; set; } = string.Empty;
    public string? TitleAr { get; set; }
    public int QuantityInStock { get; set; }
    public int ReorderThreshold { get; set; }
    public int TimesReordered { get; set; }
    public int TotalQuantityOrdered { get; set; }
    public DateTime? LastReorderDate { get; set; }
}

// Pagination DTOs
public class PagedResultDto<T>
{
    public List<T> Items { get; set; } = new();
    public int TotalCount { get; set; }
    public int Page { get; set; }
    public int PageSize { get; set; }
    public int TotalPages { get; set; }
    public bool HasPreviousPage => Page > 1;
    public bool HasNextPage => Page < TotalPages;
}

// API Response DTOs
public class ApiResponse<T>
{
    public bool Success { get; set; }
    public string? Message { get; set; }
    public string? MessageAr { get; set; }
    public T? Data { get; set; }
    public List<string>? Errors { get; set; }

    public static ApiResponse<T> SuccessResponse(T data, string? message = null, string? messageAr = null)
    {
        return new ApiResponse<T>
        {
            Success = true,
            Message = message,
            MessageAr = messageAr,
            Data = data
        };
    }

    public static ApiResponse<T> ErrorResponse(string message, string? messageAr = null, List<string>? errors = null)
    {
        return new ApiResponse<T>
        {
            Success = false,
            Message = message,
            MessageAr = messageAr,
            Errors = errors
        };
    }
}
