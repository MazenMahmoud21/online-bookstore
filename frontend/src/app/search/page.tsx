'use client';

import { useEffect, useState } from 'react';
import { Search, Filter } from 'lucide-react';
import { bookService, publisherService } from '@/lib/services';
import type { Book, Category, Publisher, BookSearchParams } from '@/types';
import BookList from '@/components/BookList';

export default function SearchPage() {
  const [books, setBooks] = useState<Book[]>([]);
  const [categories, setCategories] = useState<Category[]>([]);
  const [publishers, setPublishers] = useState<Publisher[]>([]);
  const [loading, setLoading] = useState(false);
  const [totalCount, setTotalCount] = useState(0);
  const [hasSearched, setHasSearched] = useState(false);

  const [filters, setFilters] = useState<BookSearchParams>({
    isbn: '',
    title: '',
    author: '',
    publisher: '',
    categoryID: undefined,
    minPrice: undefined,
    maxPrice: undefined,
    inStock: undefined,
    page: 1,
    pageSize: 12,
  });

  useEffect(() => {
    const fetchData = async () => {
      try {
        const [categoriesRes, publishersRes] = await Promise.all([
          bookService.getCategories(),
          publisherService.getPublishers(),
        ]);

        if (categoriesRes.success && categoriesRes.data) {
          setCategories(categoriesRes.data);
        }
        if (publishersRes.success && publishersRes.data) {
          setPublishers(publishersRes.data);
        }
      } catch (error) {
        console.error('Error fetching data:', error);
      }
    };

    fetchData();
  }, []);

  const handleSearch = async (e?: React.FormEvent) => {
    if (e) e.preventDefault();
    
    setLoading(true);
    setHasSearched(true);

    try {
      const response = await bookService.getBooks(filters);
      if (response.success && response.data) {
        setBooks(response.data.items);
        setTotalCount(response.data.totalCount);
      }
    } catch (error) {
      console.error('Error searching books:', error);
    } finally {
      setLoading(false);
    }
  };

  const clearFilters = () => {
    setFilters({
      isbn: '',
      title: '',
      author: '',
      publisher: '',
      categoryID: undefined,
      minPrice: undefined,
      maxPrice: undefined,
      inStock: undefined,
      page: 1,
      pageSize: 12,
    });
    setBooks([]);
    setHasSearched(false);
  };

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="text-center mb-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-2">البحث المتقدم</h1>
        <p className="text-gray-600">ابحث عن الكتب باستخدام معايير متعددة</p>
      </div>

      {/* Search Form */}
      <form onSubmit={handleSearch} className="bg-white rounded-xl shadow-md p-6 mb-8">
        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          {/* ISBN */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              رقم ISBN
            </label>
            <input
              type="text"
              value={filters.isbn}
              onChange={(e) => setFilters({ ...filters, isbn: e.target.value })}
              className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500"
              placeholder="978-..."
              dir="ltr"
            />
          </div>

          {/* Title */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              عنوان الكتاب
            </label>
            <input
              type="text"
              value={filters.title}
              onChange={(e) => setFilters({ ...filters, title: e.target.value })}
              className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500"
              placeholder="ابحث بالعنوان..."
            />
          </div>

          {/* Author */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              اسم المؤلف
            </label>
            <input
              type="text"
              value={filters.author}
              onChange={(e) => setFilters({ ...filters, author: e.target.value })}
              className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500"
              placeholder="ابحث بالمؤلف..."
            />
          </div>

          {/* Category */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              التصنيف
            </label>
            <select
              value={filters.categoryID || ''}
              onChange={(e) => setFilters({ ...filters, categoryID: e.target.value ? Number(e.target.value) : undefined })}
              className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500"
            >
              <option value="">جميع التصنيفات</option>
              {categories.map((cat) => (
                <option key={cat.categoryID} value={cat.categoryID}>
                  {cat.categoryNameAr}
                </option>
              ))}
            </select>
          </div>

          {/* Publisher */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              الناشر
            </label>
            <select
              value={filters.publisher || ''}
              onChange={(e) => setFilters({ ...filters, publisher: e.target.value })}
              className="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500"
            >
              <option value="">جميع الناشرين</option>
              {publishers.map((pub) => (
                <option key={pub.publisherID} value={pub.name}>
                  {pub.name}
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
                onChange={(e) => setFilters({ ...filters, minPrice: e.target.value ? Number(e.target.value) : undefined })}
                className="w-1/2 px-3 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500"
                dir="ltr"
              />
              <input
                type="number"
                placeholder="إلى"
                value={filters.maxPrice || ''}
                onChange={(e) => setFilters({ ...filters, maxPrice: e.target.value ? Number(e.target.value) : undefined })}
                className="w-1/2 px-3 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500"
                dir="ltr"
              />
            </div>
          </div>
        </div>

        {/* In Stock Checkbox */}
        <div className="mt-6">
          <label className="flex items-center gap-3 cursor-pointer">
            <input
              type="checkbox"
              checked={filters.inStock || false}
              onChange={(e) => setFilters({ ...filters, inStock: e.target.checked || undefined })}
              className="w-5 h-5 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500"
            />
            <span className="text-gray-700">متوفر في المخزون فقط</span>
          </label>
        </div>

        {/* Action Buttons */}
        <div className="mt-6 flex flex-col sm:flex-row gap-4">
          <button
            type="submit"
            className="flex-1 px-6 py-3 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 transition-colors flex items-center justify-center gap-2"
          >
            <Search className="w-5 h-5" />
            بحث
          </button>
          <button
            type="button"
            onClick={clearFilters}
            className="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors"
          >
            مسح الكل
          </button>
        </div>
      </form>

      {/* Results */}
      {hasSearched && (
        <div>
          <div className="mb-6">
            <p className="text-gray-600">
              تم العثور على <span className="font-semibold">{totalCount}</span> نتيجة
            </p>
          </div>

          <BookList books={books} loading={loading} />
        </div>
      )}

      {!hasSearched && (
        <div className="text-center py-16">
          <Filter className="w-16 h-16 text-gray-300 mx-auto mb-4" />
          <p className="text-gray-600">استخدم النموذج أعلاه للبحث عن الكتب</p>
        </div>
      )}
    </div>
  );
}
