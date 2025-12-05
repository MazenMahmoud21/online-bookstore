'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useState } from 'react';
import { ShoppingCart, User, Menu, X, BookOpen, LogOut, Settings, Package, BarChart3 } from 'lucide-react';
import { useAuthStore } from '@/lib/store';
import toast from 'react-hot-toast';

export default function Header() {
  const router = useRouter();
  const { user, isAuthenticated, logout } = useAuthStore();
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [isUserMenuOpen, setIsUserMenuOpen] = useState(false);

  const handleLogout = () => {
    logout();
    toast.success('تم تسجيل الخروج بنجاح');
    router.push('/');
    setIsUserMenuOpen(false);
  };

  return (
    <header className="bg-emerald-700 text-white shadow-lg sticky top-0 z-50">
      <div className="container mx-auto px-4">
        <div className="flex items-center justify-between h-16">
          {/* Logo */}
          <Link href="/" className="flex items-center gap-2 text-xl font-bold">
            <BookOpen className="w-8 h-8" />
            <span>المكتبة العربية</span>
          </Link>

          {/* Desktop Navigation */}
          <nav className="hidden md:flex items-center gap-6">
            <Link href="/" className="hover:text-emerald-200 transition-colors">
              الرئيسية
            </Link>
            <Link href="/books" className="hover:text-emerald-200 transition-colors">
              الكتب
            </Link>
            <Link href="/search" className="hover:text-emerald-200 transition-colors">
              البحث
            </Link>
          </nav>

          {/* Desktop Actions */}
          <div className="hidden md:flex items-center gap-4">
            {isAuthenticated ? (
              <>
                <Link
                  href="/cart"
                  className="relative p-2 hover:bg-emerald-600 rounded-full transition-colors"
                >
                  <ShoppingCart className="w-6 h-6" />
                </Link>

                <div className="relative">
                  <button
                    onClick={() => setIsUserMenuOpen(!isUserMenuOpen)}
                    className="flex items-center gap-2 p-2 hover:bg-emerald-600 rounded-lg transition-colors"
                  >
                    <User className="w-6 h-6" />
                    <span>{user?.username}</span>
                  </button>

                  {isUserMenuOpen && (
                    <div className="absolute left-0 mt-2 w-56 bg-white text-gray-900 rounded-lg shadow-lg overflow-hidden">
                      <div className="p-3 bg-emerald-50 border-b">
                        <p className="font-medium">{user?.firstName} {user?.lastName}</p>
                        <p className="text-sm text-gray-600">{user?.email}</p>
                      </div>
                      
                      <Link
                        href="/profile"
                        onClick={() => setIsUserMenuOpen(false)}
                        className="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors"
                      >
                        <Settings className="w-5 h-5 text-gray-500" />
                        <span>الملف الشخصي</span>
                      </Link>
                      
                      <Link
                        href="/orders"
                        onClick={() => setIsUserMenuOpen(false)}
                        className="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors"
                      >
                        <Package className="w-5 h-5 text-gray-500" />
                        <span>طلباتي</span>
                      </Link>

                      {user?.role === 'Admin' && (
                        <Link
                          href="/admin"
                          onClick={() => setIsUserMenuOpen(false)}
                          className="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors"
                        >
                          <BarChart3 className="w-5 h-5 text-gray-500" />
                          <span>لوحة التحكم</span>
                        </Link>
                      )}

                      <button
                        onClick={handleLogout}
                        className="flex items-center gap-3 px-4 py-3 w-full hover:bg-red-50 text-red-600 transition-colors border-t"
                      >
                        <LogOut className="w-5 h-5" />
                        <span>تسجيل الخروج</span>
                      </button>
                    </div>
                  )}
                </div>
              </>
            ) : (
              <div className="flex items-center gap-3">
                <Link
                  href="/login"
                  className="px-4 py-2 hover:bg-emerald-600 rounded-lg transition-colors"
                >
                  تسجيل الدخول
                </Link>
                <Link
                  href="/register"
                  className="px-4 py-2 bg-white text-emerald-700 rounded-lg hover:bg-emerald-100 transition-colors"
                >
                  إنشاء حساب
                </Link>
              </div>
            )}
          </div>

          {/* Mobile Menu Button */}
          <button
            onClick={() => setIsMenuOpen(!isMenuOpen)}
            className="md:hidden p-2 hover:bg-emerald-600 rounded-lg transition-colors"
          >
            {isMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
          </button>
        </div>

        {/* Mobile Menu */}
        {isMenuOpen && (
          <div className="md:hidden py-4 border-t border-emerald-600">
            <nav className="flex flex-col gap-2">
              <Link
                href="/"
                onClick={() => setIsMenuOpen(false)}
                className="px-4 py-2 hover:bg-emerald-600 rounded-lg transition-colors"
              >
                الرئيسية
              </Link>
              <Link
                href="/books"
                onClick={() => setIsMenuOpen(false)}
                className="px-4 py-2 hover:bg-emerald-600 rounded-lg transition-colors"
              >
                الكتب
              </Link>
              <Link
                href="/search"
                onClick={() => setIsMenuOpen(false)}
                className="px-4 py-2 hover:bg-emerald-600 rounded-lg transition-colors"
              >
                البحث
              </Link>
              
              {isAuthenticated ? (
                <>
                  <Link
                    href="/cart"
                    onClick={() => setIsMenuOpen(false)}
                    className="px-4 py-2 hover:bg-emerald-600 rounded-lg transition-colors"
                  >
                    سلة التسوق
                  </Link>
                  <Link
                    href="/profile"
                    onClick={() => setIsMenuOpen(false)}
                    className="px-4 py-2 hover:bg-emerald-600 rounded-lg transition-colors"
                  >
                    الملف الشخصي
                  </Link>
                  <Link
                    href="/orders"
                    onClick={() => setIsMenuOpen(false)}
                    className="px-4 py-2 hover:bg-emerald-600 rounded-lg transition-colors"
                  >
                    طلباتي
                  </Link>
                  {user?.role === 'Admin' && (
                    <Link
                      href="/admin"
                      onClick={() => setIsMenuOpen(false)}
                      className="px-4 py-2 hover:bg-emerald-600 rounded-lg transition-colors"
                    >
                      لوحة التحكم
                    </Link>
                  )}
                  <button
                    onClick={handleLogout}
                    className="px-4 py-2 text-right hover:bg-red-600 rounded-lg transition-colors"
                  >
                    تسجيل الخروج
                  </button>
                </>
              ) : (
                <>
                  <Link
                    href="/login"
                    onClick={() => setIsMenuOpen(false)}
                    className="px-4 py-2 hover:bg-emerald-600 rounded-lg transition-colors"
                  >
                    تسجيل الدخول
                  </Link>
                  <Link
                    href="/register"
                    onClick={() => setIsMenuOpen(false)}
                    className="px-4 py-2 bg-white text-emerald-700 rounded-lg hover:bg-emerald-100 transition-colors"
                  >
                    إنشاء حساب
                  </Link>
                </>
              )}
            </nav>
          </div>
        )}
      </div>
    </header>
  );
}
