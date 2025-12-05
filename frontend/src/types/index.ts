// User Types
export interface User {
  userID: number;
  username: string;
  firstName: string;
  lastName: string;
  email: string;
  phone?: string;
  shippingAddress?: string;
  role: 'Admin' | 'Customer';
  createdAt: string;
}

export interface AuthResponse {
  userID: number;
  username: string;
  email: string;
  role: string;
  token: string;
  expiresAt: string;
}

export interface LoginData {
  username: string;
  password: string;
}

export interface RegisterData {
  username: string;
  password: string;
  firstName: string;
  lastName: string;
  email: string;
  phone?: string;
  shippingAddress?: string;
}

// Book Types
export interface Author {
  authorID: number;
  name: string;
}

export interface Category {
  categoryID: number;
  categoryName: string;
  categoryNameAr: string;
}

export interface Publisher {
  publisherID: number;
  name: string;
  address?: string;
  phone?: string;
}

export interface Book {
  isbn: string;
  title: string;
  titleAr?: string;
  publisherID: number;
  publisherName?: string;
  publicationYear?: number;
  sellingPrice: number;
  categoryID: number;
  categoryName?: string;
  categoryNameAr?: string;
  quantityInStock: number;
  reorderThreshold: number;
  description?: string;
  descriptionAr?: string;
  imageUrl?: string;
  authors: Author[];
  createdAt: string;
}

export interface BookSearchParams {
  isbn?: string;
  title?: string;
  author?: string;
  publisher?: string;
  categoryID?: number;
  minPrice?: number;
  maxPrice?: number;
  inStock?: boolean;
  page?: number;
  pageSize?: number;
}

// Cart Types
export interface CartItem {
  cartItemID: number;
  isbn: string;
  bookTitle: string;
  bookTitleAr?: string;
  unitPrice: number;
  quantity: number;
  availableStock: number;
  imageUrl?: string;
  subtotal: number;
  addedAt: string;
}

export interface Cart {
  cartID: number;
  userID: number;
  items: CartItem[];
  totalAmount: number;
  totalItems: number;
  updatedAt: string;
}

// Order Types
export interface CustomerOrderItem {
  custOrderItemID: number;
  isbn: string;
  bookTitle: string;
  bookTitleAr?: string;
  quantity: number;
  unitPrice: number;
  subtotal: number;
}

export interface CustomerOrder {
  custOrderID: number;
  userID: number;
  customerName?: string;
  orderDate: string;
  totalAmount: number;
  creditCardLast4?: string;
  status: 'Pending' | 'Processing' | 'Shipped' | 'Delivered' | 'Cancelled';
  shippingAddress?: string;
  notes?: string;
  items: CustomerOrderItem[];
}

export interface CheckoutData {
  creditCardNumber: string;
  creditCardExpiry: string;
  shippingAddress?: string;
  notes?: string;
}

// Publisher Order Types
export interface PublisherOrderItem {
  pubOrderItemID: number;
  isbn: string;
  bookTitle: string;
  bookTitleAr?: string;
  quantity: number;
  unitPrice: number;
  subtotal: number;
}

export interface PublisherOrder {
  pubOrderID: number;
  publisherID: number;
  publisherName?: string;
  orderDate: string;
  status: 'Pending' | 'Confirmed' | 'Cancelled';
  totalAmount: number;
  notes?: string;
  confirmedAt?: string;
  items: PublisherOrderItem[];
}

// Report Types
export interface MonthlySalesReport {
  year: number;
  month: number;
  totalOrders: number;
  totalSales: number;
  uniqueCustomers: number;
}

export interface DailySalesReport {
  date: string;
  totalOrders: number;
  totalSales: number;
  uniqueCustomers: number;
}

export interface TopCustomer {
  userID: number;
  username: string;
  firstName: string;
  lastName: string;
  email: string;
  orderCount: number;
  totalSpent: number;
}

export interface TopSellingBook {
  isbn: string;
  title: string;
  titleAr?: string;
  publisherName?: string;
  categoryName?: string;
  categoryNameAr?: string;
  totalQuantitySold: number;
  totalRevenue: number;
}

export interface BookReorderStats {
  isbn: string;
  title: string;
  titleAr?: string;
  quantityInStock: number;
  reorderThreshold: number;
  timesReordered: number;
  totalQuantityOrdered: number;
  lastReorderDate?: string;
}

// API Response Types
export interface ApiResponse<T> {
  success: boolean;
  message?: string;
  messageAr?: string;
  data?: T;
  errors?: string[];
}

export interface PagedResult<T> {
  items: T[];
  totalCount: number;
  page: number;
  pageSize: number;
  totalPages: number;
  hasPreviousPage: boolean;
  hasNextPage: boolean;
}
