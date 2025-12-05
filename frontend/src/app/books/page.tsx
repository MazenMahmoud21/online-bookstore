'use client';

import { useEffect, useState, Suspense } from 'react';
import { useSearchParams } from 'next/navigation';
import { Search, Filter, X } from 'lucide-react';
import { bookService } from '@/lib/services';
import type { Book, Category, BookSearchParams } from '@/types';
import BookList from '@/components/BookList';
import { PageLoading } from '@/components/LoadingSpinner';

function BooksContent() {
  const searchParams = useSearchParams();
  const [books, setBooks] = useState<Book[]>([]);
  const [categories, setCategories] = useState<Category[]>([]);
  const [loading, setLoading] = useState(true);
  const [totalPages, setTotalPages] = useState(1);
  const [showFilters, setShowFilters] = useState(false);

  const [filters, setFilters] = useState<BookSearchParams>({
    title: '',
    author: '',
    categoryID: searchParams.get('categoryID') ? Number(searchParams.get('categoryID')) : undefined,
    minPrice: undefined,
    maxPrice: undefined,
    inStock: undefined,
    page: 1,
    pageSize: 12,
  });

  useEffect(() => {
    const fetchCategories = async () => {
      try {
        const response = await bookService.getCategories();
        if (response.success && response.data) {
          setCategories(response.data);
        }
      } catch (error) {
        console.error('Error fetching categories:', error);
      }
    };
    fetchCategories();
  }, []);

  useEffect(() => {
    const fetchBooks = async () => {
      setLoading(true);
      try {
        const response = await bookService.getBooks(filters);
        if (response.success && response.data) {
          setBooks(response.data.items);
          setTotalPages(response.data.totalPages);
        }
      } catch (error) {
        console.error('Error fetching books:', error);
      } finally {
        setLoading(false);
      }
    };
    fetchBooks();
  }, [filters]);

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    setFilters({ ...filters, page: 1 });
  };

  const clearFilters = () => {
    setFilters({
      title: '',
      author: '',
      categoryID: undefined,
      minPrice: undefined,
      maxPrice: undefined,
      inStock: undefined,
      page: 1,
      pageSize: 12,
    });
  };

  const selectedCategory = categories.find(c => c.categoryID === filters.categoryID);

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="flex flex-col lg:flex-row gap-8">
        {/* Filters Sidebar - Desktop */}
        <aside className="hidden lg:block w-72 flex-shrink-0">
          <div className="bg-white rounded-xl shadow-md p-6 sticky top-24">
            <div className="flex items-center justify-between mb-6">
              <h2 className="text-lg font-semibold">تصفية النتائج</h2>
              <button
                onClick={clearFilters}
                className="text-sm text-emerald-600 hover:text-emerald-700"
              >
                مسح الكل
              </button>
            </div>

            <div className="space-y-6">
              {/* Category Filter */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  التصنيف
                </label>
                <select
                  value={filters.categoryID || ''}
                  onChange={(e) => setFilters({ ...filters, categoryID: e.target.value ? Number(e.target.value) : undefined, page: 1 })}
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                >
                  <option value="">جميع التصنيفات</option>
                  {categories.map((cat) => (
                    <option key={cat.categoryID} value={cat.categoryID}>
                      {cat.categoryNameAr}
                    </option>
                  ))}
                </select>
              </div>

              {/* Price Range */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  نطاق السعر (ر.س)
                </label>
                <div className="flex gap-2">
                  <input
                    type="number"
                    placeholder="من"
                    value={filters.minPrice || ''}
                    onChange={(e) => setFilters({ ...filters, minPrice: e.target.value ? Number(e.target.value) : undefined, page: 1 })}
                    className="w-1/2 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                    dir="ltr"
                  />
                  <input
                    type="number"
                    placeholder="إلى"
                    value={filters.maxPrice || ''}
                    onChange={(e) => setFilters({ ...filters, maxPrice: e.target.value ? Number(e.target.value) : undefined, page: 1 })}
                    className="w-1/2 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                    dir="ltr"
                  />
                </div>
              </div>

              {/* In Stock */}
              <div>
                <label className="flex items-center gap-3 cursor-pointer">
                  <input
                    type="checkbox"
                    checked={filters.inStock || false}
                    onChange={(e) => setFilters({ ...filters, inStock: e.target.checked || undefined, page: 1 })}
                    className="w-5 h-5 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500"
                  />
                  <span className="text-gray-700">متوفر في المخزون فقط</span>
                </label>
              </div>
            </div>
          </div>
        </aside>

        {/* Main Content */}
        <div className="flex-1">
          {/* Search Bar */}
          <form onSubmit={handleSearch} className="mb-6">
            <div className="flex gap-3">
              <div className="flex-1 relative">
                <input
                  type="text"
                  placeholder="ابحث عن كتاب بالعنوان..."
                  value={filters.title}
                  onChange={(e) => setFilters({ ...filters, title: e.target.value })}
                  className="w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                />
                <Search className="absolute top-1/2 -translate-y-1/2 right-4 w-5 h-5 text-gray-400" />
              </div>
              <button
                type="submit"
                className="px-6 py-3 bg-emerald-600 text-white font-medium rounded-xl hover:bg-emerald-700 transition-colors"
              >
                بحث
              </button>
              <button
                type="button"
                onClick={() => setShowFilters(!showFilters)}
                className="lg:hidden px-4 py-3 border border-gray-300 rounded-xl hover:bg-gray-50"
              >
                <Filter className="w-5 h-5" />
              </button>
            </div>
          </form>

          {/* Mobile Filters */}
          {showFilters && (
            <div className="lg:hidden mb-6 bg-white rounded-xl shadow-md p-6">
              <div className="flex items-center justify-between mb-4">
                <h2 className="text-lg font-semibold">التصفية</h2>
                <button onClick={() => setShowFilters(false)}>
                  <X className="w-5 h-5" />
                </button>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <select
                  value={filters.categoryID || ''}
                  onChange={(e) => setFilters({ ...filters, categoryID: e.target.value ? Number(e.target.value) : undefined, page: 1 })}
                  className="px-3 py-2 border border-gray-300 rounded-lg"
                >
                  <option value="">جميع التصنيفات</option>
                  {categories.map((cat) => (
                    <option key={cat.categoryID} value={cat.categoryID}>
                      {cat.categoryNameAr}
                    </option>
                  ))}
                </select>
                <button
                  onClick={clearFilters}
                  className="px-4 py-2 border border-gray-300 rounded-lg text-gray-600"
                >
                  مسح الكل
                </button>
              </div>
            </div>
          )}

          {/* Active Filters */}
          {selectedCategory && (
            <div className="mb-4 flex items-center gap-2">
              <span className="text-gray-600">التصنيف:</span>
              <span className="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-sm flex items-center gap-2">
                {selectedCategory.categoryNameAr}
                <button onClick={() => setFilters({ ...filters, categoryID: undefined, page: 1 })}>
                  <X className="w-4 h-4" />
                </button>
              </span>
            </div>
          )}

          {/* Results Count */}
          <div className="mb-6">
            <p className="text-gray-600">
              عرض الصفحة {filters.page} من {totalPages}
            </p>
          </div>

          {/* Books Grid */}
          <BookList books={books} loading={loading} />

          {/* Pagination */}
          {totalPages > 1 && (
            <div className="mt-8 flex justify-center gap-2">
              <button
                onClick={() => setFilters({ ...filters, page: (filters.page || 1) - 1 })}
                disabled={(filters.page || 1) <= 1}
                className="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                السابق
              </button>
              {[...Array(Math.min(5, totalPages))].map((_, i) => {
                const page = i + 1;
                return (
                  <button
                    key={page}
                    onClick={() => setFilters({ ...filters, page })}
                    className={`px-4 py-2 rounded-lg ${
                      filters.page === page
                        ? 'bg-emerald-600 text-white'
                        : 'border border-gray-300 hover:bg-gray-50'
                    }`}
                  >
                    {page}
                  </button>
                );
              })}
              <button
                onClick={() => setFilters({ ...filters, page: (filters.page || 1) + 1 })}
                disabled={(filters.page || 1) >= totalPages}
                className="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                التالي
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

export default function BooksPage() {
  return (
    <Suspense fallback={<PageLoading />}>
      <BooksContent />
    </Suspense>
  );
}
