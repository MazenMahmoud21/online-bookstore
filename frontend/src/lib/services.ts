import api from './api';
import type {
  ApiResponse,
  AuthResponse,
  LoginData,
  RegisterData,
  User,
  Book,
  BookSearchParams,
  PagedResult,
  Category,
  Author,
  Publisher,
  Cart,
  CustomerOrder,
  CheckoutData,
  PublisherOrder,
  MonthlySalesReport,
  DailySalesReport,
  TopCustomer,
  TopSellingBook,
  BookReorderStats,
} from '@/types';

// Auth Services
export const authService = {
  login: async (data: LoginData): Promise<ApiResponse<AuthResponse>> => {
    const response = await api.post('/auth/login', data);
    return response.data;
  },

  register: async (data: RegisterData): Promise<ApiResponse<AuthResponse>> => {
    const response = await api.post('/auth/register', data);
    return response.data;
  },

  getProfile: async (): Promise<ApiResponse<User>> => {
    const response = await api.get('/auth/profile');
    return response.data;
  },

  updateProfile: async (data: Partial<User>): Promise<ApiResponse<User>> => {
    const response = await api.put('/auth/profile', data);
    return response.data;
  },

  changePassword: async (currentPassword: string, newPassword: string): Promise<ApiResponse<null>> => {
    const response = await api.post('/auth/change-password', { currentPassword, newPassword });
    return response.data;
  },
};

// Book Services
export const bookService = {
  getBooks: async (params?: BookSearchParams): Promise<ApiResponse<PagedResult<Book>>> => {
    const response = await api.get('/books', { params });
    return response.data;
  },

  getBook: async (isbn: string): Promise<ApiResponse<Book>> => {
    const response = await api.get(`/books/${isbn}`);
    return response.data;
  },

  createBook: async (data: Partial<Book> & { authorIds?: number[] }): Promise<ApiResponse<Book>> => {
    const response = await api.post('/books', data);
    return response.data;
  },

  updateBook: async (isbn: string, data: Partial<Book> & { authorIds?: number[] }): Promise<ApiResponse<Book>> => {
    const response = await api.put(`/books/${isbn}`, data);
    return response.data;
  },

  deleteBook: async (isbn: string): Promise<ApiResponse<null>> => {
    const response = await api.delete(`/books/${isbn}`);
    return response.data;
  },

  getCategories: async (): Promise<ApiResponse<Category[]>> => {
    const response = await api.get('/books/categories');
    return response.data;
  },

  getAuthors: async (): Promise<ApiResponse<Author[]>> => {
    const response = await api.get('/books/authors');
    return response.data;
  },

  createAuthor: async (name: string): Promise<ApiResponse<Author>> => {
    const response = await api.post('/books/authors', { name });
    return response.data;
  },

  deleteAuthor: async (authorId: number): Promise<ApiResponse<null>> => {
    const response = await api.delete(`/books/authors/${authorId}`);
    return response.data;
  },
};

// Publisher Services
export const publisherService = {
  getPublishers: async (): Promise<ApiResponse<Publisher[]>> => {
    const response = await api.get('/publishers');
    return response.data;
  },

  getPublisher: async (id: number): Promise<ApiResponse<Publisher>> => {
    const response = await api.get(`/publishers/${id}`);
    return response.data;
  },

  createPublisher: async (data: Partial<Publisher>): Promise<ApiResponse<Publisher>> => {
    const response = await api.post('/publishers', data);
    return response.data;
  },

  updatePublisher: async (id: number, data: Partial<Publisher>): Promise<ApiResponse<Publisher>> => {
    const response = await api.put(`/publishers/${id}`, data);
    return response.data;
  },

  deletePublisher: async (id: number): Promise<ApiResponse<null>> => {
    const response = await api.delete(`/publishers/${id}`);
    return response.data;
  },
};

// Publisher Order Services
export const publisherOrderService = {
  getOrders: async (status?: string): Promise<ApiResponse<PublisherOrder[]>> => {
    const response = await api.get('/publisherorders', { params: { status } });
    return response.data;
  },

  getPendingOrders: async (): Promise<ApiResponse<PublisherOrder[]>> => {
    const response = await api.get('/publisherorders/pending');
    return response.data;
  },

  getOrder: async (id: number): Promise<ApiResponse<PublisherOrder>> => {
    const response = await api.get(`/publisherorders/${id}`);
    return response.data;
  },

  createOrder: async (data: {
    publisherID: number;
    notes?: string;
    items: { isbn: string; quantity: number; unitPrice: number }[];
  }): Promise<ApiResponse<PublisherOrder>> => {
    const response = await api.post('/publisherorders', data);
    return response.data;
  },

  confirmOrder: async (id: number): Promise<ApiResponse<null>> => {
    const response = await api.post(`/publisherorders/${id}/confirm`);
    return response.data;
  },

  cancelOrder: async (id: number): Promise<ApiResponse<null>> => {
    const response = await api.post(`/publisherorders/${id}/cancel`);
    return response.data;
  },
};

// Cart Services
export const cartService = {
  getCart: async (): Promise<ApiResponse<Cart>> => {
    const response = await api.get('/cart');
    return response.data;
  },

  addToCart: async (isbn: string, quantity: number): Promise<ApiResponse<Cart>> => {
    const response = await api.post('/cart/items', { isbn, quantity });
    return response.data;
  },

  updateCartItem: async (cartItemId: number, quantity: number): Promise<ApiResponse<Cart>> => {
    const response = await api.put(`/cart/items/${cartItemId}`, { quantity });
    return response.data;
  },

  removeFromCart: async (cartItemId: number): Promise<ApiResponse<null>> => {
    const response = await api.delete(`/cart/items/${cartItemId}`);
    return response.data;
  },

  clearCart: async (): Promise<ApiResponse<null>> => {
    const response = await api.delete('/cart');
    return response.data;
  },
};

// Order Services
export const orderService = {
  checkout: async (data: CheckoutData): Promise<ApiResponse<CustomerOrder>> => {
    const response = await api.post('/orders/checkout', data);
    return response.data;
  },

  getMyOrders: async (): Promise<ApiResponse<CustomerOrder[]>> => {
    const response = await api.get('/orders');
    return response.data;
  },

  getOrder: async (orderId: number): Promise<ApiResponse<CustomerOrder>> => {
    const response = await api.get(`/orders/${orderId}`);
    return response.data;
  },

  getAllOrders: async (): Promise<ApiResponse<CustomerOrder[]>> => {
    const response = await api.get('/orders/all');
    return response.data;
  },

  updateOrderStatus: async (orderId: number, status: string): Promise<ApiResponse<null>> => {
    const response = await api.put(`/orders/${orderId}/status`, { status });
    return response.data;
  },
};

// Report Services
export const reportService = {
  getMonthlySales: async (year: number, month: number): Promise<ApiResponse<MonthlySalesReport>> => {
    const response = await api.get('/reports/sales/monthly', { params: { year, month } });
    return response.data;
  },

  getDailySales: async (date: string): Promise<ApiResponse<DailySalesReport>> => {
    const response = await api.get('/reports/sales/daily', { params: { date } });
    return response.data;
  },

  getTopCustomers: async (months?: number, topN?: number): Promise<ApiResponse<TopCustomer[]>> => {
    const response = await api.get('/reports/top-customers', { params: { months, topN } });
    return response.data;
  },

  getTopSellingBooks: async (months?: number, topN?: number): Promise<ApiResponse<TopSellingBook[]>> => {
    const response = await api.get('/reports/top-books', { params: { months, topN } });
    return response.data;
  },

  getBookRestockStats: async (isbn: string): Promise<ApiResponse<BookReorderStats>> => {
    const response = await api.get(`/reports/restock/${isbn}`);
    return response.data;
  },

  getAllBooksRestockStats: async (): Promise<ApiResponse<BookReorderStats[]>> => {
    const response = await api.get('/reports/restock');
    return response.data;
  },
};
