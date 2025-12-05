using System.IdentityModel.Tokens.Jwt;
using System.Security.Claims;
using System.Text;
using Microsoft.EntityFrameworkCore;
using Microsoft.IdentityModel.Tokens;
using OnlineBookstoreApi.Data;
using OnlineBookstoreApi.DTOs;
using OnlineBookstoreApi.Models;

namespace OnlineBookstoreApi.Services;

public interface IAuthService
{
    Task<AuthResponseDto?> LoginAsync(LoginDto dto);
    Task<AuthResponseDto?> RegisterAsync(RegisterDto dto);
    Task<UserDto?> GetUserByIdAsync(int userId);
    Task<bool> UpdateUserAsync(int userId, UpdateUserDto dto);
    Task<bool> ChangePasswordAsync(int userId, ChangePasswordDto dto);
}

public class AuthService : IAuthService
{
    private readonly BookstoreDbContext _context;
    private readonly IConfiguration _configuration;

    public AuthService(BookstoreDbContext context, IConfiguration configuration)
    {
        _context = context;
        _configuration = configuration;
    }

    public async Task<AuthResponseDto?> LoginAsync(LoginDto dto)
    {
        var user = await _context.Users
            .FirstOrDefaultAsync(u => u.Username == dto.Username);

        if (user == null || !BCrypt.Net.BCrypt.Verify(dto.Password, user.PasswordHash))
        {
            return null;
        }

        var token = GenerateJwtToken(user);
        var expiresAt = DateTime.UtcNow.AddHours(24);

        return new AuthResponseDto
        {
            UserID = user.UserID,
            Username = user.Username,
            Email = user.Email,
            Role = user.Role,
            Token = token,
            ExpiresAt = expiresAt
        };
    }

    public async Task<AuthResponseDto?> RegisterAsync(RegisterDto dto)
    {
        // Check if username or email already exists
        if (await _context.Users.AnyAsync(u => u.Username == dto.Username))
        {
            return null;
        }

        if (await _context.Users.AnyAsync(u => u.Email == dto.Email))
        {
            return null;
        }

        var user = new User
        {
            Username = dto.Username,
            PasswordHash = BCrypt.Net.BCrypt.HashPassword(dto.Password),
            FirstName = dto.FirstName,
            LastName = dto.LastName,
            Email = dto.Email,
            Phone = dto.Phone,
            ShippingAddress = dto.ShippingAddress,
            Role = "Customer",
            CreatedAt = DateTime.UtcNow
        };

        _context.Users.Add(user);
        await _context.SaveChangesAsync();

        // Create shopping cart for the new user
        var cart = new ShoppingCart
        {
            UserID = user.UserID,
            CreatedAt = DateTime.UtcNow,
            UpdatedAt = DateTime.UtcNow
        };
        _context.ShoppingCarts.Add(cart);
        await _context.SaveChangesAsync();

        var token = GenerateJwtToken(user);
        var expiresAt = DateTime.UtcNow.AddHours(24);

        return new AuthResponseDto
        {
            UserID = user.UserID,
            Username = user.Username,
            Email = user.Email,
            Role = user.Role,
            Token = token,
            ExpiresAt = expiresAt
        };
    }

    public async Task<UserDto?> GetUserByIdAsync(int userId)
    {
        var user = await _context.Users.FindAsync(userId);
        if (user == null) return null;

        return new UserDto
        {
            UserID = user.UserID,
            Username = user.Username,
            FirstName = user.FirstName,
            LastName = user.LastName,
            Email = user.Email,
            Phone = user.Phone,
            ShippingAddress = user.ShippingAddress,
            Role = user.Role,
            CreatedAt = user.CreatedAt
        };
    }

    public async Task<bool> UpdateUserAsync(int userId, UpdateUserDto dto)
    {
        var user = await _context.Users.FindAsync(userId);
        if (user == null) return false;

        if (!string.IsNullOrEmpty(dto.FirstName))
            user.FirstName = dto.FirstName;
        if (!string.IsNullOrEmpty(dto.LastName))
            user.LastName = dto.LastName;
        if (!string.IsNullOrEmpty(dto.Email))
        {
            // Check if email is already taken by another user
            if (await _context.Users.AnyAsync(u => u.Email == dto.Email && u.UserID != userId))
                return false;
            user.Email = dto.Email;
        }
        if (dto.Phone != null)
            user.Phone = dto.Phone;
        if (dto.ShippingAddress != null)
            user.ShippingAddress = dto.ShippingAddress;

        await _context.SaveChangesAsync();
        return true;
    }

    public async Task<bool> ChangePasswordAsync(int userId, ChangePasswordDto dto)
    {
        var user = await _context.Users.FindAsync(userId);
        if (user == null) return false;

        if (!BCrypt.Net.BCrypt.Verify(dto.CurrentPassword, user.PasswordHash))
            return false;

        user.PasswordHash = BCrypt.Net.BCrypt.HashPassword(dto.NewPassword);
        await _context.SaveChangesAsync();
        return true;
    }

    private string GenerateJwtToken(User user)
    {
        var jwtKey = _configuration["Jwt:Key"] ?? throw new InvalidOperationException("JWT Key not configured");
        var key = new SymmetricSecurityKey(Encoding.UTF8.GetBytes(jwtKey));
        var credentials = new SigningCredentials(key, SecurityAlgorithms.HmacSha256);

        var claims = new[]
        {
            new Claim(ClaimTypes.NameIdentifier, user.UserID.ToString()),
            new Claim(ClaimTypes.Name, user.Username),
            new Claim(ClaimTypes.Email, user.Email),
            new Claim(ClaimTypes.Role, user.Role)
        };

        var token = new JwtSecurityToken(
            issuer: _configuration["Jwt:Issuer"],
            audience: _configuration["Jwt:Audience"],
            claims: claims,
            expires: DateTime.UtcNow.AddHours(24),
            signingCredentials: credentials
        );

        return new JwtSecurityTokenHandler().WriteToken(token);
    }
}
