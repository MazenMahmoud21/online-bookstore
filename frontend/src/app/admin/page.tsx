'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { 
  BarChart3, 
  BookOpen, 
  Users, 
  Package, 
  TrendingUp,
  AlertCircle,
  CheckCircle,
  DollarSign
} from 'lucide-react';
import { reportService, publisherOrderService, orderService, bookService } from '@/lib/services';
import { useAuthStore } from '@/lib/store';
import type { MonthlySalesReport, TopSellingBook, TopCustomer, PublisherOrder } from '@/types';
import { formatPrice } from '@/lib/utils';
import { PageLoading } from '@/components/LoadingSpinner';

export default function AdminDashboard() {
  const router = useRouter();
  const { user, isAuthenticated, isLoading: authLoading } = useAuthStore();
  const [loading, setLoading] = useState(true);
  const [monthlySales, setMonthlySales] = useState<MonthlySalesReport | null>(null);
  const [topBooks, setTopBooks] = useState<TopSellingBook[]>([]);
  const [topCustomers, setTopCustomers] = useState<TopCustomer[]>([]);
  const [pendingOrders, setPendingOrders] = useState<PublisherOrder[]>([]);

  useEffect(() => {
    if (!authLoading) {
      if (!isAuthenticated) {
        router.push('/login');
        return;
      }
      if (user?.role !== 'Admin') {
        router.push('/');
        return;
      }
    }

    const fetchData = async () => {
      try {
        const now = new Date();
        const [salesRes, booksRes, customersRes, ordersRes] = await Promise.all([
          reportService.getMonthlySales(now.getFullYear(), now.getMonth() + 1),
          reportService.getTopSellingBooks(3, 5),
          reportService.getTopCustomers(3, 5),
          publisherOrderService.getPendingOrders(),
        ]);

        if (salesRes.success && salesRes.data) setMonthlySales(salesRes.data);
        if (booksRes.success && booksRes.data) setTopBooks(booksRes.data);
        if (customersRes.success && customersRes.data) setTopCustomers(customersRes.data);
        if (ordersRes.success && ordersRes.data) setPendingOrders(ordersRes.data);
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
      } finally {
        setLoading(false);
      }
    };

    if (isAuthenticated && user?.role === 'Admin') {
      fetchData();
    }
  }, [isAuthenticated, authLoading, user, router]);

  if (authLoading || loading) {
    return <PageLoading />;
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="flex items-center justify-between mb-8">
        <h1 className="text-3xl font-bold text-gray-900">لوحة التحكم</h1>
        <span className="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-sm font-medium">
          مدير النظام
        </span>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div className="bg-white rounded-xl shadow-md p-6">
          <div className="flex items-center justify-between mb-4">
            <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <DollarSign className="w-6 h-6 text-blue-600" />
            </div>
            <TrendingUp className="w-5 h-5 text-green-500" />
          </div>
          <p className="text-gray-600 text-sm mb-1">مبيعات الشهر</p>
          <p className="text-2xl font-bold text-gray-900">
            {monthlySales ? formatPrice(monthlySales.totalSales) : '0 ر.س'}
          </p>
        </div>

        <div className="bg-white rounded-xl shadow-md p-6">
          <div className="flex items-center justify-between mb-4">
            <div className="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
              <Package className="w-6 h-6 text-emerald-600" />
            </div>
          </div>
          <p className="text-gray-600 text-sm mb-1">طلبات الشهر</p>
          <p className="text-2xl font-bold text-gray-900">
            {monthlySales?.totalOrders || 0}
          </p>
        </div>

        <div className="bg-white rounded-xl shadow-md p-6">
          <div className="flex items-center justify-between mb-4">
            <div className="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
              <Users className="w-6 h-6 text-purple-600" />
            </div>
          </div>
          <p className="text-gray-600 text-sm mb-1">عملاء الشهر</p>
          <p className="text-2xl font-bold text-gray-900">
            {monthlySales?.uniqueCustomers || 0}
          </p>
        </div>

        <div className="bg-white rounded-xl shadow-md p-6">
          <div className="flex items-center justify-between mb-4">
            <div className="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
              <AlertCircle className="w-6 h-6 text-yellow-600" />
            </div>
          </div>
          <p className="text-gray-600 text-sm mb-1">طلبات تخزين معلقة</p>
          <p className="text-2xl font-bold text-gray-900">
            {pendingOrders.length}
          </p>
        </div>
      </div>

      {/* Quick Actions */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <Link
          href="/admin/books"
          className="bg-white rounded-xl shadow-md p-6 text-center hover:shadow-lg transition-shadow"
        >
          <BookOpen className="w-8 h-8 text-emerald-600 mx-auto mb-3" />
          <span className="font-medium text-gray-900">إدارة الكتب</span>
        </Link>
        <Link
          href="/admin/publishers"
          className="bg-white rounded-xl shadow-md p-6 text-center hover:shadow-lg transition-shadow"
        >
          <Users className="w-8 h-8 text-blue-600 mx-auto mb-3" />
          <span className="font-medium text-gray-900">إدارة الناشرين</span>
        </Link>
        <Link
          href="/admin/publisher-orders"
          className="bg-white rounded-xl shadow-md p-6 text-center hover:shadow-lg transition-shadow"
        >
          <Package className="w-8 h-8 text-purple-600 mx-auto mb-3" />
          <span className="font-medium text-gray-900">طلبات التخزين</span>
        </Link>
        <Link
          href="/admin/reports"
          className="bg-white rounded-xl shadow-md p-6 text-center hover:shadow-lg transition-shadow"
        >
          <BarChart3 className="w-8 h-8 text-yellow-600 mx-auto mb-3" />
          <span className="font-medium text-gray-900">التقارير</span>
        </Link>
      </div>

      <div className="grid lg:grid-cols-2 gap-8">
        {/* Top Selling Books */}
        <div className="bg-white rounded-xl shadow-md p-6">
          <h2 className="text-xl font-semibold text-gray-900 mb-6">
            الكتب الأكثر مبيعاً (آخر 3 أشهر)
          </h2>
          {topBooks.length > 0 ? (
            <div className="space-y-4">
              {topBooks.map((book, index) => (
                <div key={book.isbn} className="flex items-center gap-4">
                  <span className="w-8 h-8 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center font-bold text-sm">
                    {index + 1}
                  </span>
                  <div className="flex-1 min-w-0">
                    <p className="font-medium text-gray-900 truncate">
                      {book.titleAr || book.title}
                    </p>
                    <p className="text-sm text-gray-500">
                      {book.totalQuantitySold} مبيعات
                    </p>
                  </div>
                  <span className="font-semibold text-emerald-600">
                    {formatPrice(book.totalRevenue)}
                  </span>
                </div>
              ))}
            </div>
          ) : (
            <p className="text-gray-500 text-center py-8">لا توجد بيانات</p>
          )}
        </div>

        {/* Top Customers */}
        <div className="bg-white rounded-xl shadow-md p-6">
          <h2 className="text-xl font-semibold text-gray-900 mb-6">
            أفضل العملاء (آخر 3 أشهر)
          </h2>
          {topCustomers.length > 0 ? (
            <div className="space-y-4">
              {topCustomers.map((customer, index) => (
                <div key={customer.userID} className="flex items-center gap-4">
                  <span className="w-8 h-8 bg-purple-100 text-purple-700 rounded-full flex items-center justify-center font-bold text-sm">
                    {index + 1}
                  </span>
                  <div className="flex-1 min-w-0">
                    <p className="font-medium text-gray-900 truncate">
                      {customer.firstName} {customer.lastName}
                    </p>
                    <p className="text-sm text-gray-500">
                      {customer.orderCount} طلبات
                    </p>
                  </div>
                  <span className="font-semibold text-purple-600">
                    {formatPrice(customer.totalSpent)}
                  </span>
                </div>
              ))}
            </div>
          ) : (
            <p className="text-gray-500 text-center py-8">لا توجد بيانات</p>
          )}
        </div>
      </div>

      {/* Pending Publisher Orders */}
      {pendingOrders.length > 0 && (
        <div className="mt-8 bg-white rounded-xl shadow-md p-6">
          <div className="flex items-center justify-between mb-6">
            <h2 className="text-xl font-semibold text-gray-900">
              طلبات التخزين المعلقة
            </h2>
            <Link
              href="/admin/publisher-orders"
              className="text-emerald-600 hover:text-emerald-700 text-sm font-medium"
            >
              عرض الكل
            </Link>
          </div>
          <div className="space-y-4">
            {pendingOrders.slice(0, 5).map((order) => (
              <div
                key={order.pubOrderID}
                className="flex items-center justify-between p-4 bg-yellow-50 rounded-lg"
              >
                <div>
                  <p className="font-medium text-gray-900">
                    طلب #{order.pubOrderID} - {order.publisherName}
                  </p>
                  <p className="text-sm text-gray-600">
                    {order.items.length} كتاب - {formatPrice(order.totalAmount)}
                  </p>
                </div>
                <Link
                  href={`/admin/publisher-orders`}
                  className="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors"
                >
                  مراجعة
                </Link>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
