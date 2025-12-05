using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using OnlineBookstoreApi.DTOs;
using OnlineBookstoreApi.Services;

namespace OnlineBookstoreApi.Controllers;

[ApiController]
[Route("api/[controller]")]
public class AuthController : ControllerBase
{
    private readonly IAuthService _authService;

    public AuthController(IAuthService authService)
    {
        _authService = authService;
    }

    /// <summary>
    /// Login with username and password
    /// </summary>
    [HttpPost("login")]
    public async Task<ActionResult<ApiResponse<AuthResponseDto>>> Login([FromBody] LoginDto dto)
    {
        var result = await _authService.LoginAsync(dto);
        if (result == null)
        {
            return Unauthorized(ApiResponse<AuthResponseDto>.ErrorResponse(
                "Invalid username or password",
                "اسم المستخدم أو كلمة المرور غير صحيحة"));
        }

        return Ok(ApiResponse<AuthResponseDto>.SuccessResponse(
            result,
            "Login successful",
            "تم تسجيل الدخول بنجاح"));
    }

    /// <summary>
    /// Register a new customer account
    /// </summary>
    [HttpPost("register")]
    public async Task<ActionResult<ApiResponse<AuthResponseDto>>> Register([FromBody] RegisterDto dto)
    {
        if (!ModelState.IsValid)
        {
            return BadRequest(ApiResponse<AuthResponseDto>.ErrorResponse(
                "Invalid registration data",
                "بيانات التسجيل غير صحيحة"));
        }

        var result = await _authService.RegisterAsync(dto);
        if (result == null)
        {
            return BadRequest(ApiResponse<AuthResponseDto>.ErrorResponse(
                "Username or email already exists",
                "اسم المستخدم أو البريد الإلكتروني مستخدم بالفعل"));
        }

        return CreatedAtAction(nameof(GetProfile), new { }, ApiResponse<AuthResponseDto>.SuccessResponse(
            result,
            "Registration successful",
            "تم التسجيل بنجاح"));
    }

    /// <summary>
    /// Get current user profile
    /// </summary>
    [HttpGet("profile")]
    [Authorize]
    public async Task<ActionResult<ApiResponse<UserDto>>> GetProfile()
    {
        var userId = GetUserId();
        if (userId == null)
        {
            return Unauthorized(ApiResponse<UserDto>.ErrorResponse(
                "User not authenticated",
                "المستخدم غير مصرح له"));
        }

        var user = await _authService.GetUserByIdAsync(userId.Value);
        if (user == null)
        {
            return NotFound(ApiResponse<UserDto>.ErrorResponse(
                "User not found",
                "المستخدم غير موجود"));
        }

        return Ok(ApiResponse<UserDto>.SuccessResponse(user));
    }

    /// <summary>
    /// Update current user profile
    /// </summary>
    [HttpPut("profile")]
    [Authorize]
    public async Task<ActionResult<ApiResponse<UserDto>>> UpdateProfile([FromBody] UpdateUserDto dto)
    {
        var userId = GetUserId();
        if (userId == null)
        {
            return Unauthorized(ApiResponse<UserDto>.ErrorResponse(
                "User not authenticated",
                "المستخدم غير مصرح له"));
        }

        var success = await _authService.UpdateUserAsync(userId.Value, dto);
        if (!success)
        {
            return BadRequest(ApiResponse<UserDto>.ErrorResponse(
                "Failed to update profile",
                "فشل في تحديث الملف الشخصي"));
        }

        var user = await _authService.GetUserByIdAsync(userId.Value);
        return Ok(ApiResponse<UserDto>.SuccessResponse(
            user!,
            "Profile updated successfully",
            "تم تحديث الملف الشخصي بنجاح"));
    }

    /// <summary>
    /// Change password
    /// </summary>
    [HttpPost("change-password")]
    [Authorize]
    public async Task<ActionResult<ApiResponse<object>>> ChangePassword([FromBody] ChangePasswordDto dto)
    {
        var userId = GetUserId();
        if (userId == null)
        {
            return Unauthorized(ApiResponse<object>.ErrorResponse(
                "User not authenticated",
                "المستخدم غير مصرح له"));
        }

        var success = await _authService.ChangePasswordAsync(userId.Value, dto);
        if (!success)
        {
            return BadRequest(ApiResponse<object>.ErrorResponse(
                "Invalid current password",
                "كلمة المرور الحالية غير صحيحة"));
        }

        return Ok(ApiResponse<object>.SuccessResponse(
            null!,
            "Password changed successfully",
            "تم تغيير كلمة المرور بنجاح"));
    }

    private int? GetUserId()
    {
        var userIdClaim = User.FindFirst(System.Security.Claims.ClaimTypes.NameIdentifier);
        if (userIdClaim != null && int.TryParse(userIdClaim.Value, out int userId))
        {
            return userId;
        }
        return null;
    }
}
