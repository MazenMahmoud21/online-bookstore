'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { ShoppingCart, Trash2, Plus, Minus, ArrowRight, Loader2 } from 'lucide-react';
import toast from 'react-hot-toast';
import { cartService } from '@/lib/services';
import { useAuthStore } from '@/lib/store';
import type { Cart, CartItem } from '@/types';
import { formatPrice, getBookTitle } from '@/lib/utils';
import { PageLoading } from '@/components/LoadingSpinner';

export default function CartPage() {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading } = useAuthStore();
  const [cart, setCart] = useState<Cart | null>(null);
  const [loading, setLoading] = useState(true);
  const [updatingItems, setUpdatingItems] = useState<Set<number>>(new Set());

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push('/login');
      return;
    }

    const fetchCart = async () => {
      try {
        const response = await cartService.getCart();
        if (response.success && response.data) {
          setCart(response.data);
        }
      } catch (error) {
        console.error('Error fetching cart:', error);
      } finally {
        setLoading(false);
      }
    };

    if (isAuthenticated) {
      fetchCart();
    }
  }, [isAuthenticated, authLoading, router]);

  const updateQuantity = async (item: CartItem, newQuantity: number) => {
    if (newQuantity < 1 || newQuantity > item.availableStock) return;

    setUpdatingItems((prev) => new Set(prev).add(item.cartItemID));

    try {
      const response = await cartService.updateCartItem(item.cartItemID, newQuantity);
      if (response.success && response.data) {
        setCart(response.data);
      }
    } catch {
      toast.error('فشل في تحديث الكمية');
    } finally {
      setUpdatingItems((prev) => {
        const newSet = new Set(prev);
        newSet.delete(item.cartItemID);
        return newSet;
      });
    }
  };

  const removeItem = async (cartItemId: number) => {
    setUpdatingItems((prev) => new Set(prev).add(cartItemId));

    try {
      const response = await cartService.removeFromCart(cartItemId);
      if (response.success) {
        const newCart = await cartService.getCart();
        if (newCart.success && newCart.data) {
          setCart(newCart.data);
        }
        toast.success('تم إزالة الكتاب من السلة');
      }
    } catch {
      toast.error('فشل في إزالة الكتاب');
    } finally {
      setUpdatingItems((prev) => {
        const newSet = new Set(prev);
        newSet.delete(cartItemId);
        return newSet;
      });
    }
  };

  const clearCart = async () => {
    try {
      const response = await cartService.clearCart();
      if (response.success) {
        setCart(null);
        const newCart = await cartService.getCart();
        if (newCart.success && newCart.data) {
          setCart(newCart.data);
        }
        toast.success('تم تفريغ السلة');
      }
    } catch {
      toast.error('فشل في تفريغ السلة');
    }
  };

  if (authLoading || loading) {
    return <PageLoading />;
  }

  if (!cart || cart.items.length === 0) {
    return (
      <div className="container mx-auto px-4 py-16 text-center">
        <ShoppingCart className="w-24 h-24 text-gray-300 mx-auto mb-6" />
        <h1 className="text-2xl font-bold text-gray-900 mb-4">سلة التسوق فارغة</h1>
        <p className="text-gray-600 mb-8">لم تقم بإضافة أي كتب إلى سلة التسوق بعد</p>
        <Link
          href="/books"
          className="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 text-white font-medium rounded-xl hover:bg-emerald-700 transition-colors"
        >
          تصفح الكتب
        </Link>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-3xl font-bold text-gray-900 mb-8">سلة التسوق</h1>

      <div className="grid lg:grid-cols-3 gap-8">
        {/* Cart Items */}
        <div className="lg:col-span-2 space-y-4">
          <div className="flex items-center justify-between mb-4">
            <span className="text-gray-600">{cart.totalItems} كتاب في السلة</span>
            <button
              onClick={clearCart}
              className="text-red-600 hover:text-red-700 text-sm font-medium"
            >
              تفريغ السلة
            </button>
          </div>

          {cart.items.map((item) => (
            <div
              key={item.cartItemID}
              className="bg-white rounded-xl shadow-md p-4 flex gap-4"
            >
              {/* Book Image */}
              <div className="w-24 h-32 bg-emerald-50 rounded-lg flex-shrink-0 flex items-center justify-center">
                {item.imageUrl ? (
                  <img
                    src={item.imageUrl}
                    alt={item.bookTitle}
                    className="w-full h-full object-cover rounded-lg"
                  />
                ) : (
                  <ShoppingCart className="w-8 h-8 text-emerald-300" />
                )}
              </div>

              {/* Book Details */}
              <div className="flex-1">
                <Link
                  href={`/books/${item.isbn}`}
                  className="text-lg font-semibold text-gray-900 hover:text-emerald-600 transition-colors"
                >
                  {getBookTitle(item.bookTitle, item.bookTitleAr)}
                </Link>

                <p className="text-emerald-600 font-bold mt-1">
                  {formatPrice(item.unitPrice)}
                </p>

                <p className="text-sm text-gray-500 mt-1">
                  المتاح: {item.availableStock} قطعة
                </p>

                {/* Quantity Controls */}
                <div className="flex items-center gap-4 mt-3">
                  <div className="flex items-center gap-2">
                    <button
                      onClick={() => updateQuantity(item, item.quantity - 1)}
                      disabled={item.quantity <= 1 || updatingItems.has(item.cartItemID)}
                      className="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50"
                    >
                      <Minus className="w-4 h-4" />
                    </button>
                    <span className="w-8 text-center font-semibold">
                      {updatingItems.has(item.cartItemID) ? (
                        <Loader2 className="w-4 h-4 animate-spin mx-auto" />
                      ) : (
                        item.quantity
                      )}
                    </span>
                    <button
                      onClick={() => updateQuantity(item, item.quantity + 1)}
                      disabled={item.quantity >= item.availableStock || updatingItems.has(item.cartItemID)}
                      className="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50"
                    >
                      <Plus className="w-4 h-4" />
                    </button>
                  </div>

                  <button
                    onClick={() => removeItem(item.cartItemID)}
                    disabled={updatingItems.has(item.cartItemID)}
                    className="text-red-600 hover:text-red-700 p-2"
                  >
                    <Trash2 className="w-5 h-5" />
                  </button>
                </div>
              </div>

              {/* Subtotal */}
              <div className="text-left">
                <p className="text-lg font-bold text-gray-900">
                  {formatPrice(item.subtotal)}
                </p>
              </div>
            </div>
          ))}
        </div>

        {/* Order Summary */}
        <div className="lg:col-span-1">
          <div className="bg-white rounded-xl shadow-md p-6 sticky top-24">
            <h2 className="text-xl font-semibold text-gray-900 mb-6">ملخص الطلب</h2>

            <div className="space-y-4 mb-6">
              <div className="flex justify-between">
                <span className="text-gray-600">المجموع الفرعي</span>
                <span className="font-semibold">{formatPrice(cart.totalAmount)}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">الشحن</span>
                <span className="text-green-600 font-medium">مجاني</span>
              </div>
              <div className="border-t pt-4 flex justify-between">
                <span className="text-lg font-semibold">الإجمالي</span>
                <span className="text-xl font-bold text-emerald-600">
                  {formatPrice(cart.totalAmount)}
                </span>
              </div>
            </div>

            <Link
              href="/checkout"
              className="w-full block text-center py-3 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 transition-colors"
            >
              إتمام الشراء
            </Link>

            <Link
              href="/books"
              className="w-full block text-center py-3 mt-3 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors"
            >
              متابعة التسوق
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
