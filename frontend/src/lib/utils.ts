// Format price in Saudi Riyal
export function formatPrice(price: number): string {
  return `${price.toFixed(2)} ر.س`;
}

// Format date in Arabic format
export function formatDate(dateString: string): string {
  const date = new Date(dateString);
  return date.toLocaleDateString('ar-SA', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
}

// Format date with time
export function formatDateTime(dateString: string): string {
  const date = new Date(dateString);
  return date.toLocaleDateString('ar-SA', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

// Format short date
export function formatShortDate(dateString: string): string {
  const date = new Date(dateString);
  return date.toLocaleDateString('ar-SA', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  });
}

// Get order status in Arabic
export function getOrderStatusAr(status: string): string {
  const statusMap: Record<string, string> = {
    Pending: 'قيد الانتظار',
    Processing: 'قيد المعالجة',
    Shipped: 'تم الشحن',
    Delivered: 'تم التوصيل',
    Cancelled: 'ملغي',
    Confirmed: 'مؤكد',
  };
  return statusMap[status] || status;
}

// Get order status color
export function getOrderStatusColor(status: string): string {
  const colorMap: Record<string, string> = {
    Pending: 'bg-yellow-100 text-yellow-800',
    Processing: 'bg-blue-100 text-blue-800',
    Shipped: 'bg-purple-100 text-purple-800',
    Delivered: 'bg-green-100 text-green-800',
    Cancelled: 'bg-red-100 text-red-800',
    Confirmed: 'bg-green-100 text-green-800',
  };
  return colorMap[status] || 'bg-gray-100 text-gray-800';
}

// Validate Saudi phone number
export function isValidSaudiPhone(phone: string): boolean {
  // Saudi phone format: starts with 05, followed by 8 digits
  const phoneRegex = /^(05|5|9665|00966)(5|0|3|6|4|9|1|8|7)([0-9]{7})$/;
  return phoneRegex.test(phone.replace(/\s|-/g, ''));
}

// Truncate text
export function truncateText(text: string, maxLength: number): string {
  if (text.length <= maxLength) return text;
  return text.substring(0, maxLength) + '...';
}

// Get category name based on language preference (Arabic by default)
export function getCategoryName(categoryName?: string, categoryNameAr?: string): string {
  return categoryNameAr || categoryName || '';
}

// Get book title based on language preference (Arabic by default)
export function getBookTitle(title: string, titleAr?: string): string {
  return titleAr || title;
}

// Generate placeholder image URL
export function getBookImageUrl(imageUrl?: string): string {
  return imageUrl || '/images/book-placeholder.png';
}

// Format credit card number (show last 4 digits)
export function formatCreditCard(last4?: string): string {
  return last4 ? `**** **** **** ${last4}` : '';
}

// Calculate cart totals
export function calculateCartTotals(items: { quantity: number; unitPrice: number }[]): {
  totalItems: number;
  totalAmount: number;
} {
  return items.reduce(
    (acc, item) => ({
      totalItems: acc.totalItems + item.quantity,
      totalAmount: acc.totalAmount + item.quantity * item.unitPrice,
    }),
    { totalItems: 0, totalAmount: 0 }
  );
}
