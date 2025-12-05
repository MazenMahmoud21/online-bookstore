'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { CreditCard, MapPin, Loader2, CheckCircle } from 'lucide-react';
import toast from 'react-hot-toast';
import { cartService, orderService, authService } from '@/lib/services';
import { useAuthStore } from '@/lib/store';
import type { Cart, User } from '@/types';
import { formatPrice, getBookTitle } from '@/lib/utils';
import { PageLoading } from '@/components/LoadingSpinner';

export default function CheckoutPage() {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading, user } = useAuthStore();
  const [cart, setCart] = useState<Cart | null>(null);
  const [profile, setProfile] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [orderComplete, setOrderComplete] = useState(false);
  const [orderId, setOrderId] = useState<number | null>(null);

  const [formData, setFormData] = useState({
    creditCardNumber: '',
    creditCardExpiry: '',
    shippingAddress: '',
    notes: '',
  });

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push('/login');
      return;
    }

    const fetchData = async () => {
      try {
        const [cartRes, profileRes] = await Promise.all([
          cartService.getCart(),
          authService.getProfile(),
        ]);

        if (cartRes.success && cartRes.data) {
          setCart(cartRes.data);
        }

        if (profileRes.success && profileRes.data) {
          setProfile(profileRes.data);
          setFormData((prev) => ({
            ...prev,
            shippingAddress: profileRes.data?.shippingAddress || '',
          }));
        }
      } catch (error) {
        console.error('Error fetching data:', error);
      } finally {
        setLoading(false);
      }
    };

    if (isAuthenticated) {
      fetchData();
    }
  }, [isAuthenticated, authLoading, router]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!cart || cart.items.length === 0) {
      toast.error('السلة فارغة');
      return;
    }

    // Validate expiry date
    const expiryDate = new Date(formData.creditCardExpiry + '-01');
    if (expiryDate < new Date()) {
      toast.error('بطاقة الائتمان منتهية الصلاحية');
      return;
    }

    setSubmitting(true);

    try {
      const response = await orderService.checkout({
        creditCardNumber: formData.creditCardNumber,
        creditCardExpiry: formData.creditCardExpiry + '-01',
        shippingAddress: formData.shippingAddress || undefined,
        notes: formData.notes || undefined,
      });

      if (response.success && response.data) {
        setOrderId(response.data.custOrderID);
        setOrderComplete(true);
        toast.success('تم إتمام الطلب بنجاح!');
      } else {
        toast.error(response.messageAr || 'فشل في إتمام الطلب');
      }
    } catch {
      toast.error('حدث خطأ أثناء إتمام الطلب');
    } finally {
      setSubmitting(false);
    }
  };

  if (authLoading || loading) {
    return <PageLoading />;
  }

  if (orderComplete) {
    return (
      <div className="container mx-auto px-4 py-16 text-center">
        <div className="max-w-md mx-auto">
          <CheckCircle className="w-24 h-24 text-emerald-500 mx-auto mb-6" />
          <h1 className="text-3xl font-bold text-gray-900 mb-4">تم الطلب بنجاح!</h1>
          <p className="text-gray-600 mb-2">شكراً لك على طلبك</p>
          <p className="text-gray-600 mb-8">
            رقم الطلب: <span className="font-bold text-emerald-600">#{orderId}</span>
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <button
              onClick={() => router.push(`/orders`)}
              className="px-6 py-3 bg-emerald-600 text-white font-medium rounded-xl hover:bg-emerald-700 transition-colors"
            >
              عرض طلباتي
            </button>
            <button
              onClick={() => router.push('/books')}
              className="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors"
            >
              متابعة التسوق
            </button>
          </div>
        </div>
      </div>
    );
  }

  if (!cart || cart.items.length === 0) {
    router.push('/cart');
    return null;
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-3xl font-bold text-gray-900 mb-8">إتمام الشراء</h1>

      <form onSubmit={handleSubmit}>
        <div className="grid lg:grid-cols-3 gap-8">
          {/* Checkout Form */}
          <div className="lg:col-span-2 space-y-6">
            {/* Shipping Address */}
            <div className="bg-white rounded-xl shadow-md p-6">
              <div className="flex items-center gap-3 mb-6">
                <MapPin className="w-6 h-6 text-emerald-600" />
                <h2 className="text-xl font-semibold">عنوان الشحن</h2>
              </div>

              <textarea
                value={formData.shippingAddress}
                onChange={(e) => setFormData({ ...formData, shippingAddress: e.target.value })}
                className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 resize-none"
                placeholder="أدخل عنوان الشحن الكامل (المدينة، الحي، الشارع، رقم المبنى)"
                rows={3}
                required
              />
            </div>

            {/* Payment Information */}
            <div className="bg-white rounded-xl shadow-md p-6">
              <div className="flex items-center gap-3 mb-6">
                <CreditCard className="w-6 h-6 text-emerald-600" />
                <h2 className="text-xl font-semibold">معلومات الدفع</h2>
              </div>

              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    رقم البطاقة *
                  </label>
                  <input
                    type="text"
                    value={formData.creditCardNumber}
                    onChange={(e) => setFormData({ ...formData, creditCardNumber: e.target.value.replace(/\D/g, '') })}
                    className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                    placeholder="1234 5678 9012 3456"
                    maxLength={16}
                    required
                    dir="ltr"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    تاريخ الانتهاء *
                  </label>
                  <input
                    type="month"
                    value={formData.creditCardExpiry}
                    onChange={(e) => setFormData({ ...formData, creditCardExpiry: e.target.value })}
                    className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                    required
                    dir="ltr"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    ملاحظات (اختياري)
                  </label>
                  <textarea
                    value={formData.notes}
                    onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                    className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 resize-none"
                    placeholder="أي ملاحظات إضافية للطلب"
                    rows={2}
                  />
                </div>
              </div>

              <p className="mt-4 text-sm text-gray-500">
                * هذا نظام تجريبي - لا يتم معالجة دفعات حقيقية
              </p>
            </div>
          </div>

          {/* Order Summary */}
          <div className="lg:col-span-1">
            <div className="bg-white rounded-xl shadow-md p-6 sticky top-24">
              <h2 className="text-xl font-semibold text-gray-900 mb-6">ملخص الطلب</h2>

              {/* Items */}
              <div className="space-y-4 mb-6 max-h-64 overflow-y-auto">
                {cart.items.map((item) => (
                  <div key={item.cartItemID} className="flex justify-between text-sm">
                    <div className="flex-1">
                      <p className="font-medium line-clamp-1">
                        {getBookTitle(item.bookTitle, item.bookTitleAr)}
                      </p>
                      <p className="text-gray-500">الكمية: {item.quantity}</p>
                    </div>
                    <span className="font-medium">{formatPrice(item.subtotal)}</span>
                  </div>
                ))}
              </div>

              <div className="border-t pt-4 space-y-3 mb-6">
                <div className="flex justify-between">
                  <span className="text-gray-600">المجموع الفرعي</span>
                  <span className="font-semibold">{formatPrice(cart.totalAmount)}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">الشحن</span>
                  <span className="text-green-600 font-medium">مجاني</span>
                </div>
                <div className="border-t pt-3 flex justify-between">
                  <span className="text-lg font-semibold">الإجمالي</span>
                  <span className="text-xl font-bold text-emerald-600">
                    {formatPrice(cart.totalAmount)}
                  </span>
                </div>
              </div>

              <button
                type="submit"
                disabled={submitting}
                className="w-full py-3 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
              >
                {submitting ? (
                  <>
                    <Loader2 className="w-5 h-5 animate-spin" />
                    جاري إتمام الطلب...
                  </>
                ) : (
                  'تأكيد الطلب'
                )}
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  );
}
