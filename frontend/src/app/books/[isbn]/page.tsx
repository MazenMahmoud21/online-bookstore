'use client';

import { useEffect, useState } from 'react';
import { useParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import { Book as BookIcon, ShoppingCart, Minus, Plus, ArrowRight, Loader2 } from 'lucide-react';
import toast from 'react-hot-toast';
import { bookService, cartService } from '@/lib/services';
import { useAuthStore } from '@/lib/store';
import type { Book } from '@/types';
import { formatPrice, getBookTitle, getCategoryName } from '@/lib/utils';
import { PageLoading } from '@/components/LoadingSpinner';

export default function BookDetailPage() {
  const params = useParams();
  const router = useRouter();
  const { isAuthenticated } = useAuthStore();
  const [book, setBook] = useState<Book | null>(null);
  const [loading, setLoading] = useState(true);
  const [quantity, setQuantity] = useState(1);
  const [addingToCart, setAddingToCart] = useState(false);

  const isbn = params.isbn as string;

  useEffect(() => {
    const fetchBook = async () => {
      try {
        const response = await bookService.getBook(isbn);
        if (response.success && response.data) {
          setBook(response.data);
        }
      } catch (error) {
        console.error('Error fetching book:', error);
        toast.error('فشل في تحميل بيانات الكتاب');
      } finally {
        setLoading(false);
      }
    };

    if (isbn) {
      fetchBook();
    }
  }, [isbn]);

  const handleAddToCart = async () => {
    if (!isAuthenticated) {
      toast.error('يجب تسجيل الدخول أولاً');
      router.push('/login');
      return;
    }

    if (!book) return;

    setAddingToCart(true);
    try {
      const response = await cartService.addToCart(book.isbn, quantity);
      if (response.success) {
        toast.success('تمت إضافة الكتاب إلى السلة');
      } else {
        toast.error(response.messageAr || 'فشل في إضافة الكتاب للسلة');
      }
    } catch {
      toast.error('فشل في إضافة الكتاب للسلة');
    } finally {
      setAddingToCart(false);
    }
  };

  if (loading) {
    return <PageLoading />;
  }

  if (!book) {
    return (
      <div className="container mx-auto px-4 py-16 text-center">
        <h1 className="text-2xl font-bold text-gray-900 mb-4">الكتاب غير موجود</h1>
        <Link href="/books" className="text-emerald-600 hover:text-emerald-700">
          العودة للكتب
        </Link>
      </div>
    );
  }

  const displayTitle = getBookTitle(book.title, book.titleAr);
  const displayCategory = getCategoryName(book.categoryName, book.categoryNameAr);
  const isInStock = book.quantityInStock > 0;

  return (
    <div className="container mx-auto px-4 py-8">
      {/* Breadcrumb */}
      <nav className="mb-8 flex items-center gap-2 text-sm text-gray-600">
        <Link href="/" className="hover:text-emerald-600">الرئيسية</Link>
        <ArrowRight className="w-4 h-4" />
        <Link href="/books" className="hover:text-emerald-600">الكتب</Link>
        <ArrowRight className="w-4 h-4" />
        <span className="text-gray-900">{displayTitle}</span>
      </nav>

      <div className="grid lg:grid-cols-2 gap-12">
        {/* Book Image */}
        <div className="flex justify-center">
          <div className="w-full max-w-md aspect-[3/4] bg-gradient-to-br from-emerald-100 to-emerald-50 rounded-2xl flex items-center justify-center shadow-lg">
            {book.imageUrl ? (
              <img
                src={book.imageUrl}
                alt={displayTitle}
                className="w-full h-full object-cover rounded-2xl"
              />
            ) : (
              <BookIcon className="w-32 h-32 text-emerald-300" />
            )}
          </div>
        </div>

        {/* Book Details */}
        <div>
          {/* Category Badge */}
          {displayCategory && (
            <Link
              href={`/books?categoryID=${book.categoryID}`}
              className="inline-block px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-sm mb-4 hover:bg-emerald-200 transition-colors"
            >
              {displayCategory}
            </Link>
          )}

          <h1 className="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">
            {displayTitle}
          </h1>

          {/* Authors */}
          {book.authors && book.authors.length > 0 && (
            <p className="text-lg text-gray-600 mb-4">
              <span className="font-medium">المؤلف:</span>{' '}
              {book.authors.map((a) => a.name).join('، ')}
            </p>
          )}

          {/* Publisher & Year */}
          <div className="flex flex-wrap gap-4 text-gray-600 mb-6">
            {book.publisherName && (
              <span>
                <span className="font-medium">الناشر:</span> {book.publisherName}
              </span>
            )}
            {book.publicationYear && (
              <span>
                <span className="font-medium">سنة النشر:</span> {book.publicationYear}
              </span>
            )}
          </div>

          {/* ISBN */}
          <p className="text-gray-600 mb-6">
            <span className="font-medium">رقم ISBN:</span>{' '}
            <span dir="ltr">{book.isbn}</span>
          </p>

          {/* Price */}
          <div className="mb-6">
            <span className="text-4xl font-bold text-emerald-600">
              {formatPrice(book.sellingPrice)}
            </span>
          </div>

          {/* Stock Status */}
          <div className="mb-6">
            <span
              className={`inline-flex items-center px-4 py-2 rounded-full text-sm font-medium ${
                isInStock
                  ? 'bg-green-100 text-green-700'
                  : 'bg-red-100 text-red-700'
              }`}
            >
              {isInStock
                ? `متوفر (${book.quantityInStock} قطعة)`
                : 'غير متوفر حالياً'}
            </span>
          </div>

          {/* Quantity Selector */}
          {isInStock && (
            <div className="mb-6">
              <label className="block text-sm font-medium text-gray-700 mb-2">
                الكمية
              </label>
              <div className="flex items-center gap-3">
                <button
                  onClick={() => setQuantity(Math.max(1, quantity - 1))}
                  className="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                  <Minus className="w-4 h-4" />
                </button>
                <span className="text-xl font-semibold w-12 text-center">{quantity}</span>
                <button
                  onClick={() => setQuantity(Math.min(book.quantityInStock, quantity + 1))}
                  className="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                  <Plus className="w-4 h-4" />
                </button>
              </div>
            </div>
          )}

          {/* Add to Cart Button */}
          <button
            onClick={handleAddToCart}
            disabled={!isInStock || addingToCart}
            className="w-full lg:w-auto px-8 py-4 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-3"
          >
            {addingToCart ? (
              <>
                <Loader2 className="w-5 h-5 animate-spin" />
                جاري الإضافة...
              </>
            ) : (
              <>
                <ShoppingCart className="w-5 h-5" />
                {isInStock ? 'إضافة للسلة' : 'غير متوفر'}
              </>
            )}
          </button>

          {/* Description */}
          {(book.descriptionAr || book.description) && (
            <div className="mt-8 pt-8 border-t">
              <h2 className="text-xl font-semibold text-gray-900 mb-4">وصف الكتاب</h2>
              <p className="text-gray-600 leading-relaxed">
                {book.descriptionAr || book.description}
              </p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
