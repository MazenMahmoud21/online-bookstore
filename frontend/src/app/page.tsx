'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { BookOpen, TrendingUp, Users, ShoppingBag, Search, ArrowLeft, Star } from 'lucide-react';
import { bookService } from '@/lib/services';
import type { Book, Category } from '@/types';
import BookList from '@/components/BookList';

export default function Home() {
  const [featuredBooks, setFeaturedBooks] = useState<Book[]>([]);
  const [categories, setCategories] = useState<Category[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const [booksRes, categoriesRes] = await Promise.all([
          bookService.getBooks({ pageSize: 8, inStock: true }),
          bookService.getCategories(),
        ]);

        if (booksRes.success && booksRes.data) {
          setFeaturedBooks(booksRes.data.items);
        }
        if (categoriesRes.success && categoriesRes.data) {
          setCategories(categoriesRes.data);
        }
      } catch (error) {
        console.error('Error fetching data:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, []);

  return (
    <div>
      {/* Hero Section */}
      <section className="bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 text-white">
        <div className="container mx-auto px-4 py-16 lg:py-24">
          <div className="max-w-3xl">
            <h1 className="text-4xl lg:text-6xl font-bold mb-6 leading-tight">
              مرحباً بك في
              <br />
              <span className="text-emerald-200">المكتبة العربية</span>
            </h1>
            <p className="text-xl lg:text-2xl text-emerald-100 mb-8 leading-relaxed">
              وجهتك الأولى للكتب العربية والعالمية. اكتشف آلاف الكتب في مختلف المجالات بأسعار مناسبة.
            </p>
            <div className="flex flex-col sm:flex-row gap-4">
              <Link
                href="/books"
                className="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-emerald-700 font-semibold rounded-xl hover:bg-emerald-50 transition-colors"
              >
                <BookOpen className="w-5 h-5" />
                تصفح الكتب
              </Link>
              <Link
                href="/search"
                className="inline-flex items-center justify-center gap-2 px-8 py-4 border-2 border-white text-white font-semibold rounded-xl hover:bg-white/10 transition-colors"
              >
                <Search className="w-5 h-5" />
                البحث المتقدم
              </Link>
            </div>
          </div>
        </div>

        {/* Stats */}
        <div className="bg-white/10 backdrop-blur-sm">
          <div className="container mx-auto px-4 py-8">
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-8">
              <div className="text-center">
                <div className="flex justify-center mb-2">
                  <BookOpen className="w-8 h-8 text-emerald-200" />
                </div>
                <div className="text-3xl font-bold">+1000</div>
                <div className="text-emerald-200">كتاب متاح</div>
              </div>
              <div className="text-center">
                <div className="flex justify-center mb-2">
                  <Users className="w-8 h-8 text-emerald-200" />
                </div>
                <div className="text-3xl font-bold">+500</div>
                <div className="text-emerald-200">عميل سعيد</div>
              </div>
              <div className="text-center">
                <div className="flex justify-center mb-2">
                  <TrendingUp className="w-8 h-8 text-emerald-200" />
                </div>
                <div className="text-3xl font-bold">+50</div>
                <div className="text-emerald-200">ناشر</div>
              </div>
              <div className="text-center">
                <div className="flex justify-center mb-2">
                  <ShoppingBag className="w-8 h-8 text-emerald-200" />
                </div>
                <div className="text-3xl font-bold">+2000</div>
                <div className="text-emerald-200">طلب مكتمل</div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Categories Section */}
      <section className="py-16 bg-white">
        <div className="container mx-auto px-4">
          <div className="text-center mb-12">
            <h2 className="text-3xl font-bold text-gray-900 mb-4">تصفح حسب التصنيف</h2>
            <p className="text-gray-600">اختر التصنيف المناسب لاهتماماتك</p>
          </div>

          <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
            {categories.map((category) => (
              <Link
                key={category.categoryID}
                href={`/books?categoryID=${category.categoryID}`}
                className="flex flex-col items-center p-6 bg-gray-50 rounded-xl hover:bg-emerald-50 hover:shadow-md transition-all group"
              >
                <Star className="w-10 h-10 text-emerald-600 mb-3 group-hover:scale-110 transition-transform" />
                <span className="font-medium text-gray-800 text-center">
                  {category.categoryNameAr}
                </span>
              </Link>
            ))}
          </div>
        </div>
      </section>

      {/* Featured Books Section */}
      <section className="py-16 bg-gray-50">
        <div className="container mx-auto px-4">
          <div className="flex items-center justify-between mb-12">
            <div>
              <h2 className="text-3xl font-bold text-gray-900 mb-2">أحدث الكتب</h2>
              <p className="text-gray-600">اكتشف أحدث الإصدارات في مكتبتنا</p>
            </div>
            <Link
              href="/books"
              className="hidden md:flex items-center gap-2 text-emerald-600 font-medium hover:text-emerald-700 transition-colors"
            >
              عرض الكل
              <ArrowLeft className="w-5 h-5" />
            </Link>
          </div>

          <BookList books={featuredBooks} loading={loading} />

          <div className="mt-8 text-center md:hidden">
            <Link
              href="/books"
              className="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 text-white font-medium rounded-lg hover:bg-emerald-700 transition-colors"
            >
              عرض جميع الكتب
              <ArrowLeft className="w-5 h-5" />
            </Link>
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="py-16 bg-white">
        <div className="container mx-auto px-4">
          <div className="text-center mb-12">
            <h2 className="text-3xl font-bold text-gray-900 mb-4">لماذا تختارنا؟</h2>
            <p className="text-gray-600">نقدم لك أفضل تجربة تسوق للكتب</p>
          </div>

          <div className="grid md:grid-cols-3 gap-8">
            <div className="text-center p-8 bg-gray-50 rounded-2xl">
              <div className="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <BookOpen className="w-8 h-8 text-emerald-600" />
              </div>
              <h3 className="text-xl font-semibold text-gray-900 mb-3">تشكيلة واسعة</h3>
              <p className="text-gray-600">
                آلاف الكتب في مختلف المجالات من العلوم والدين والأدب والتاريخ وغيرها
              </p>
            </div>

            <div className="text-center p-8 bg-gray-50 rounded-2xl">
              <div className="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <TrendingUp className="w-8 h-8 text-emerald-600" />
              </div>
              <h3 className="text-xl font-semibold text-gray-900 mb-3">أسعار تنافسية</h3>
              <p className="text-gray-600">
                نقدم أفضل الأسعار في السوق مع عروض وخصومات مستمرة
              </p>
            </div>

            <div className="text-center p-8 bg-gray-50 rounded-2xl">
              <div className="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <ShoppingBag className="w-8 h-8 text-emerald-600" />
              </div>
              <h3 className="text-xl font-semibold text-gray-900 mb-3">توصيل سريع</h3>
              <p className="text-gray-600">
                خدمة توصيل سريعة وموثوقة لجميع مناطق المملكة العربية السعودية
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-16 bg-gradient-to-r from-emerald-600 to-teal-600 text-white">
        <div className="container mx-auto px-4 text-center">
          <h2 className="text-3xl font-bold mb-4">ابدأ رحلتك القرائية اليوم</h2>
          <p className="text-xl text-emerald-100 mb-8 max-w-2xl mx-auto">
            سجل الآن واحصل على خصم 10% على أول طلب لك
          </p>
          <Link
            href="/register"
            className="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-emerald-700 font-semibold rounded-xl hover:bg-emerald-50 transition-colors"
          >
            إنشاء حساب مجاني
          </Link>
        </div>
      </section>
    </div>
  );
}
