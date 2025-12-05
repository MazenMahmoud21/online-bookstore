using Microsoft.EntityFrameworkCore;
using OnlineBookstoreApi.Data;
using OnlineBookstoreApi.DTOs;
using OnlineBookstoreApi.Models;

namespace OnlineBookstoreApi.Services;

public interface IPublisherService
{
    Task<List<PublisherDto>> GetPublishersAsync();
    Task<PublisherDto?> GetPublisherByIdAsync(int id);
    Task<PublisherDto?> CreatePublisherAsync(CreatePublisherDto dto);
    Task<PublisherDto?> UpdatePublisherAsync(int id, UpdatePublisherDto dto);
    Task<bool> DeletePublisherAsync(int id);
}

public class PublisherService : IPublisherService
{
    private readonly BookstoreDbContext _context;

    public PublisherService(BookstoreDbContext context)
    {
        _context = context;
    }

    public async Task<List<PublisherDto>> GetPublishersAsync()
    {
        return await _context.Publishers
            .Select(p => new PublisherDto
            {
                PublisherID = p.PublisherID,
                Name = p.Name,
                Address = p.Address,
                Phone = p.Phone
            })
            .ToListAsync();
    }

    public async Task<PublisherDto?> GetPublisherByIdAsync(int id)
    {
        var publisher = await _context.Publishers.FindAsync(id);
        if (publisher == null) return null;

        return new PublisherDto
        {
            PublisherID = publisher.PublisherID,
            Name = publisher.Name,
            Address = publisher.Address,
            Phone = publisher.Phone
        };
    }

    public async Task<PublisherDto?> CreatePublisherAsync(CreatePublisherDto dto)
    {
        var publisher = new Publisher
        {
            Name = dto.Name,
            Address = dto.Address,
            Phone = dto.Phone
        };

        _context.Publishers.Add(publisher);
        await _context.SaveChangesAsync();

        return new PublisherDto
        {
            PublisherID = publisher.PublisherID,
            Name = publisher.Name,
            Address = publisher.Address,
            Phone = publisher.Phone
        };
    }

    public async Task<PublisherDto?> UpdatePublisherAsync(int id, UpdatePublisherDto dto)
    {
        var publisher = await _context.Publishers.FindAsync(id);
        if (publisher == null) return null;

        if (!string.IsNullOrEmpty(dto.Name))
            publisher.Name = dto.Name;
        if (dto.Address != null)
            publisher.Address = dto.Address;
        if (dto.Phone != null)
            publisher.Phone = dto.Phone;

        await _context.SaveChangesAsync();

        return new PublisherDto
        {
            PublisherID = publisher.PublisherID,
            Name = publisher.Name,
            Address = publisher.Address,
            Phone = publisher.Phone
        };
    }

    public async Task<bool> DeletePublisherAsync(int id)
    {
        var publisher = await _context.Publishers.FindAsync(id);
        if (publisher == null) return false;

        _context.Publishers.Remove(publisher);
        await _context.SaveChangesAsync();
        return true;
    }
}
