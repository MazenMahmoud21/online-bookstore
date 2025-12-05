# Online Bookstore - المكتبة العربية

A complete online bookstore website for Arabic/Saudi users with RTL support.

## Features

- **Arabic UI**: Full Arabic interface with RTL layout
- **Authentication**: JWT-based authentication with role-based access (Admin/Customer)
- **Book Management**: CRUD operations for books with search/filter functionality
- **Shopping Cart**: Add, update, remove items from cart
- **Checkout**: Simulated payment processing
- **Order History**: View past orders and their status
- **Admin Dashboard**: Reports, publisher order management, book/publisher management
- **Auto-Restock**: Automatic publisher orders when stock falls below threshold

## Tech Stack

### Backend
- ASP.NET Core 8.0 Web API
- Entity Framework Core
- Microsoft SQL Server
- JWT Authentication
- BCrypt password hashing

### Frontend
- Next.js 16 with App Router
- TypeScript
- Tailwind CSS
- Zustand (state management)
- Axios (API client)

### Database
- Microsoft SQL Server
- Stored Procedures
- Triggers for business logic

## Project Structure

```
online-bookstore/
├── backend/
│   └── OnlineBookstoreApi/
│       ├── Controllers/      # API endpoints
│       ├── Models/           # Entity models
│       ├── DTOs/             # Data transfer objects
│       ├── Services/         # Business logic
│       ├── Data/             # Database context
│       └── Program.cs        # Application setup
├── frontend/
│   └── src/
│       ├── app/              # Next.js pages
│       ├── components/       # Reusable components
│       ├── lib/              # Utilities and services
│       └── types/            # TypeScript types
└── database/
    ├── 01_CreateDatabase.sql
    ├── 02_CreateTables.sql
    ├── 03_CreateTriggers.sql
    ├── 04_CreateStoredProcedures.sql
    └── 05_SeedData.sql
```

## Setup Instructions

### Prerequisites

- .NET 8.0 SDK
- Node.js 18+
- Microsoft SQL Server (or SQL Server Management Studio)

### Database Setup

1. Open SQL Server Management Studio
2. Run the SQL scripts in order:
   ```
   01_CreateDatabase.sql
   02_CreateTables.sql
   03_CreateTriggers.sql
   04_CreateStoredProcedures.sql
   05_SeedData.sql
   ```

### Backend Setup

1. Navigate to the backend directory:
   ```bash
   cd backend/OnlineBookstoreApi
   ```

2. Update the connection string in `appsettings.json`:
   ```json
   {
     "ConnectionStrings": {
       "DefaultConnection": "Server=YOUR_SERVER;Database=OnlineBookstore;Trusted_Connection=True;TrustServerCertificate=True;"
     }
   }
   ```

3. Run the API:
   ```bash
   dotnet run
   ```

   The API will be available at `http://localhost:5000`

### Frontend Setup

1. Navigate to the frontend directory:
   ```bash
   cd frontend
   ```

2. Install dependencies:
   ```bash
   npm install
   ```

3. Create `.env.local` file:
   ```
   NEXT_PUBLIC_API_URL=http://localhost:5000/api
   ```

4. Run the development server:
   ```bash
   npm run dev
   ```

   The app will be available at `http://localhost:3000`

## Demo Credentials

### Admin Account
- **Username**: admin
- **Password**: Password123!

### Customer Account
- **Username**: customer1
- **Password**: Password123!

## API Endpoints

### Authentication
- `POST /api/auth/login` - Login
- `POST /api/auth/register` - Register new customer
- `GET /api/auth/profile` - Get current user profile
- `PUT /api/auth/profile` - Update profile
- `POST /api/auth/change-password` - Change password

### Books
- `GET /api/books` - List/search books
- `GET /api/books/{isbn}` - Get book details
- `POST /api/books` - Create book (Admin)
- `PUT /api/books/{isbn}` - Update book (Admin)
- `DELETE /api/books/{isbn}` - Delete book (Admin)
- `GET /api/books/categories` - List categories
- `GET /api/books/authors` - List authors

### Publishers (Admin)
- `GET /api/publishers` - List publishers
- `POST /api/publishers` - Create publisher
- `PUT /api/publishers/{id}` - Update publisher
- `DELETE /api/publishers/{id}` - Delete publisher

### Publisher Orders (Admin)
- `GET /api/publisherorders` - List orders
- `GET /api/publisherorders/pending` - List pending orders
- `POST /api/publisherorders` - Create order
- `POST /api/publisherorders/{id}/confirm` - Confirm order
- `POST /api/publisherorders/{id}/cancel` - Cancel order

### Shopping Cart
- `GET /api/cart` - Get cart
- `POST /api/cart/items` - Add item
- `PUT /api/cart/items/{id}` - Update quantity
- `DELETE /api/cart/items/{id}` - Remove item
- `DELETE /api/cart` - Clear cart

### Customer Orders
- `POST /api/orders/checkout` - Checkout
- `GET /api/orders` - Get my orders
- `GET /api/orders/{id}` - Get order details
- `GET /api/orders/all` - Get all orders (Admin)
- `PUT /api/orders/{id}/status` - Update order status (Admin)

### Reports (Admin)
- `GET /api/reports/sales/monthly` - Monthly sales
- `GET /api/reports/sales/daily` - Daily sales
- `GET /api/reports/top-customers` - Top customers
- `GET /api/reports/top-books` - Top selling books
- `GET /api/reports/restock` - Restock statistics
- `GET /api/reports/restock/{isbn}` - Book restock stats

## Database Features

### Triggers
- **trg_PreventNegativeStock**: Prevents book stock from going negative
- **trg_AutoReorder**: Automatically creates publisher orders when stock falls below threshold

### Stored Procedures
- **sp_ConfirmPublisherOrder**: Confirms order and adds stock
- **sp_CheckoutCart**: Atomic checkout process
- **sp_GetTotalSalesByMonth/Date**: Sales reports
- **sp_GetTopCustomers/Books**: Top performers reports
- **sp_GetTimesBookReordered**: Restock statistics

## Currency & Localization

- Currency: Saudi Riyal (ر.س / SAR)
- Language: Arabic (ar)
- Direction: Right-to-Left (RTL)
- Date Format: Arabic locale (DD/MM/YYYY)

## License

MIT