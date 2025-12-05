using Microsoft.EntityFrameworkCore;
using OnlineBookstoreApi.Models;

namespace OnlineBookstoreApi.Data;

public class BookstoreDbContext : DbContext
{
    public BookstoreDbContext(DbContextOptions<BookstoreDbContext> options)
        : base(options)
    {
    }

    public DbSet<User> Users { get; set; } = null!;
    public DbSet<Publisher> Publishers { get; set; } = null!;
    public DbSet<Category> Categories { get; set; } = null!;
    public DbSet<Author> Authors { get; set; } = null!;
    public DbSet<Book> Books { get; set; } = null!;
    public DbSet<BookAuthor> BookAuthors { get; set; } = null!;
    public DbSet<PublisherOrder> PublisherOrders { get; set; } = null!;
    public DbSet<PublisherOrderItem> PublisherOrderItems { get; set; } = null!;
    public DbSet<CustomerOrder> CustomerOrders { get; set; } = null!;
    public DbSet<CustomerOrderItem> CustomerOrderItems { get; set; } = null!;
    public DbSet<ShoppingCart> ShoppingCarts { get; set; } = null!;
    public DbSet<CartItem> CartItems { get; set; } = null!;

    protected override void OnModelCreating(ModelBuilder modelBuilder)
    {
        base.OnModelCreating(modelBuilder);

        // User configuration
        modelBuilder.Entity<User>(entity =>
        {
            entity.HasIndex(e => e.Username).IsUnique();
            entity.HasIndex(e => e.Email).IsUnique();
            entity.Property(e => e.Role)
                .HasDefaultValue("Customer");
        });

        // Book configuration
        modelBuilder.Entity<Book>(entity =>
        {
            entity.HasKey(e => e.ISBN);
            entity.Property(e => e.SellingPrice)
                .HasColumnType("decimal(10, 2)");
            entity.Property(e => e.QuantityInStock)
                .HasDefaultValue(0);
            entity.Property(e => e.ReorderThreshold)
                .HasDefaultValue(10);
            entity.HasIndex(e => e.Title);
        });

        // BookAuthor configuration (Many-to-Many)
        modelBuilder.Entity<BookAuthor>(entity =>
        {
            entity.HasKey(e => new { e.ISBN, e.AuthorID });

            entity.HasOne(e => e.Book)
                .WithMany(b => b.BookAuthors)
                .HasForeignKey(e => e.ISBN)
                .OnDelete(DeleteBehavior.Cascade);

            entity.HasOne(e => e.Author)
                .WithMany(a => a.BookAuthors)
                .HasForeignKey(e => e.AuthorID)
                .OnDelete(DeleteBehavior.Cascade);

            entity.HasIndex(e => e.AuthorID);
        });

        // PublisherOrder configuration
        modelBuilder.Entity<PublisherOrder>(entity =>
        {
            entity.Property(e => e.Status)
                .HasDefaultValue("Pending");
            entity.Property(e => e.TotalAmount)
                .HasColumnType("decimal(10, 2)")
                .HasDefaultValue(0);
            entity.HasIndex(e => e.Status);
            entity.HasIndex(e => e.PublisherID);
        });

        // PublisherOrderItem configuration
        modelBuilder.Entity<PublisherOrderItem>(entity =>
        {
            entity.Property(e => e.UnitPrice)
                .HasColumnType("decimal(10, 2)");
        });

        // CustomerOrder configuration
        modelBuilder.Entity<CustomerOrder>(entity =>
        {
            entity.Property(e => e.TotalAmount)
                .HasColumnType("decimal(10, 2)")
                .HasDefaultValue(0);
            entity.Property(e => e.Status)
                .HasDefaultValue("Pending");
            entity.HasIndex(e => e.OrderDate);
            entity.HasIndex(e => e.UserID);
        });

        // CustomerOrderItem configuration
        modelBuilder.Entity<CustomerOrderItem>(entity =>
        {
            entity.Property(e => e.UnitPrice)
                .HasColumnType("decimal(10, 2)");
            entity.HasIndex(e => e.ISBN);
        });

        // ShoppingCart configuration
        modelBuilder.Entity<ShoppingCart>(entity =>
        {
            entity.HasIndex(e => e.UserID).IsUnique();
        });

        // CartItem configuration
        modelBuilder.Entity<CartItem>(entity =>
        {
            entity.Property(e => e.Quantity)
                .HasDefaultValue(1);
            entity.HasIndex(e => new { e.CartID, e.ISBN }).IsUnique();
        });
    }
}
