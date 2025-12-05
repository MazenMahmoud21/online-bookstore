'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { Package, Eye, ArrowRight } from 'lucide-react';
import { orderService } from '@/lib/services';
import { useAuthStore } from '@/lib/store';
import type { CustomerOrder } from '@/types';
import { formatPrice, formatDate, getOrderStatusAr, getOrderStatusColor } from '@/lib/utils';
import { PageLoading } from '@/components/LoadingSpinner';

export default function OrdersPage() {
  const router = useRouter();
  const { isAuthenticated, isLoading: authLoading } = useAuthStore();
  const [orders, setOrders] = useState<CustomerOrder[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push('/login');
      return;
    }

    const fetchOrders = async () => {
      try {
        const response = await orderService.getMyOrders();
        if (response.success && response.data) {
          setOrders(response.data);
        }
      } catch (error) {
        console.error('Error fetching orders:', error);
      } finally {
        setLoading(false);
      }
    };

    if (isAuthenticated) {
      fetchOrders();
    }
  }, [isAuthenticated, authLoading, router]);

  if (authLoading || loading) {
    return <PageLoading />;
  }

  if (orders.length === 0) {
    return (
      <div className="container mx-auto px-4 py-16 text-center">
        <Package className="w-24 h-24 text-gray-300 mx-auto mb-6" />
        <h1 className="text-2xl font-bold text-gray-900 mb-4">لا توجد طلبات</h1>
        <p className="text-gray-600 mb-8">لم تقم بإجراء أي طلبات بعد</p>
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
      <h1 className="text-3xl font-bold text-gray-900 mb-8">طلباتي</h1>

      <div className="space-y-4">
        {orders.map((order) => (
          <div key={order.custOrderID} className="bg-white rounded-xl shadow-md p-6">
            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
              <div>
                <div className="flex items-center gap-3 mb-2">
                  <span className="text-lg font-semibold">طلب #{order.custOrderID}</span>
                  <span
                    className={`px-3 py-1 rounded-full text-sm font-medium ${getOrderStatusColor(order.status)}`}
                  >
                    {getOrderStatusAr(order.status)}
                  </span>
                </div>
                <p className="text-gray-600 text-sm">{formatDate(order.orderDate)}</p>
              </div>

              <div className="flex items-center gap-4">
                <span className="text-xl font-bold text-emerald-600">
                  {formatPrice(order.totalAmount)}
                </span>
                <Link
                  href={`/orders/${order.custOrderID}`}
                  className="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium"
                >
                  <Eye className="w-4 h-4" />
                  التفاصيل
                </Link>
              </div>
            </div>

            {/* Order Items Preview */}
            <div className="border-t pt-4">
              <p className="text-sm text-gray-600 mb-2">{order.items.length} كتاب</p>
              <div className="flex flex-wrap gap-2">
                {order.items.slice(0, 3).map((item) => (
                  <span
                    key={item.custOrderItemID}
                    className="px-3 py-1 bg-gray-100 rounded-full text-sm text-gray-700"
                  >
                    {item.bookTitleAr || item.bookTitle}
                  </span>
                ))}
                {order.items.length > 3 && (
                  <span className="px-3 py-1 bg-gray-100 rounded-full text-sm text-gray-700">
                    +{order.items.length - 3} أخرى
                  </span>
                )}
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
