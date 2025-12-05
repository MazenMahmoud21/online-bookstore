'use client';

import Link from 'next/link';
import { Book as BookIcon } from 'lucide-react';
import type { Book } from '@/types';
import { formatPrice, getBookTitle, getCategoryName } from '@/lib/utils';

interface BookCardProps {
  book: Book;
}

export default function BookCard({ book }: BookCardProps) {
  const displayTitle = getBookTitle(book.title, book.titleAr);
  const displayCategory = getCategoryName(book.categoryName, book.categoryNameAr);
  const isInStock = book.quantityInStock > 0;

  return (
    <Link href={`/books/${book.isbn}`}>
      <div className="bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden group h-full flex flex-col">
        {/* Image */}
        <div className="relative h-48 bg-gradient-to-br from-emerald-100 to-emerald-50 flex items-center justify-center">
          {book.imageUrl ? (
            <img
              src={book.imageUrl}
              alt={displayTitle}
              className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            />
          ) : (
            <BookIcon className="w-20 h-20 text-emerald-300" />
          )}
          
          {/* Stock Badge */}
          <div
            className={`absolute top-3 left-3 px-2 py-1 rounded-full text-xs font-medium ${
              isInStock
                ? 'bg-green-100 text-green-700'
                : 'bg-red-100 text-red-700'
            }`}
          >
            {isInStock ? 'متوفر' : 'غير متوفر'}
          </div>

          {/* Category Badge */}
          {displayCategory && (
            <div className="absolute top-3 right-3 px-2 py-1 bg-emerald-600 text-white rounded-full text-xs">
              {displayCategory}
            </div>
          )}
        </div>

        {/* Content */}
        <div className="p-4 flex-1 flex flex-col">
          <h3 className="text-lg font-semibold text-gray-900 line-clamp-2 mb-2 group-hover:text-emerald-600 transition-colors">
            {displayTitle}
          </h3>
          
          {/* Authors */}
          {book.authors && book.authors.length > 0 && (
            <p className="text-sm text-gray-600 mb-2">
              {book.authors.map((a) => a.name).join('، ')}
            </p>
          )}

          {/* Publisher */}
          {book.publisherName && (
            <p className="text-xs text-gray-500 mb-2">{book.publisherName}</p>
          )}

          {/* Price */}
          <div className="mt-auto pt-3 border-t border-gray-100">
            <p className="text-xl font-bold text-emerald-600">{formatPrice(book.sellingPrice)}</p>
          </div>
        </div>
      </div>
    </Link>
  );
}
