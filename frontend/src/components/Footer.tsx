import Link from 'next/link';
import { BookOpen, Mail, Phone, MapPin } from 'lucide-react';

export default function Footer() {
  return (
    <footer className="bg-gray-900 text-white">
      <div className="container mx-auto px-4 py-12">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
          {/* About */}
          <div>
            <div className="flex items-center gap-2 text-xl font-bold mb-4">
              <BookOpen className="w-8 h-8 text-emerald-400" />
              <span>المكتبة العربية</span>
            </div>
            <p className="text-gray-400 text-sm leading-relaxed">
              وجهتك الأولى للكتب العربية والعالمية. نوفر لك أفضل الكتب في مختلف المجالات بأسعار مناسبة.
            </p>
          </div>

          {/* Quick Links */}
          <div>
            <h3 className="text-lg font-semibold mb-4">روابط سريعة</h3>
            <ul className="space-y-2">
              <li>
                <Link href="/" className="text-gray-400 hover:text-emerald-400 transition-colors">
                  الرئيسية
                </Link>
              </li>
              <li>
                <Link href="/books" className="text-gray-400 hover:text-emerald-400 transition-colors">
                  جميع الكتب
                </Link>
              </li>
              <li>
                <Link href="/search" className="text-gray-400 hover:text-emerald-400 transition-colors">
                  البحث المتقدم
                </Link>
              </li>
              <li>
                <Link href="/login" className="text-gray-400 hover:text-emerald-400 transition-colors">
                  تسجيل الدخول
                </Link>
              </li>
            </ul>
          </div>

          {/* Categories */}
          <div>
            <h3 className="text-lg font-semibold mb-4">التصنيفات</h3>
            <ul className="space-y-2">
              <li>
                <Link href="/books?categoryID=1" className="text-gray-400 hover:text-emerald-400 transition-colors">
                  علوم
                </Link>
              </li>
              <li>
                <Link href="/books?categoryID=2" className="text-gray-400 hover:text-emerald-400 transition-colors">
                  فن
                </Link>
              </li>
              <li>
                <Link href="/books?categoryID=3" className="text-gray-400 hover:text-emerald-400 transition-colors">
                  دين
                </Link>
              </li>
              <li>
                <Link href="/books?categoryID=4" className="text-gray-400 hover:text-emerald-400 transition-colors">
                  تاريخ
                </Link>
              </li>
              <li>
                <Link href="/books?categoryID=6" className="text-gray-400 hover:text-emerald-400 transition-colors">
                  أدب
                </Link>
              </li>
            </ul>
          </div>

          {/* Contact */}
          <div>
            <h3 className="text-lg font-semibold mb-4">تواصل معنا</h3>
            <ul className="space-y-3">
              <li className="flex items-center gap-3 text-gray-400">
                <MapPin className="w-5 h-5 text-emerald-400 flex-shrink-0" />
                <span>الرياض، المملكة العربية السعودية</span>
              </li>
              <li className="flex items-center gap-3 text-gray-400">
                <Phone className="w-5 h-5 text-emerald-400 flex-shrink-0" />
                <span dir="ltr">+966 50 123 4567</span>
              </li>
              <li className="flex items-center gap-3 text-gray-400">
                <Mail className="w-5 h-5 text-emerald-400 flex-shrink-0" />
                <span>info@bookstore.sa</span>
              </li>
            </ul>
          </div>
        </div>

        {/* Bottom Bar */}
        <div className="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400 text-sm">
          <p>جميع الحقوق محفوظة © {new Date().getFullYear()} المكتبة العربية</p>
        </div>
      </div>
    </footer>
  );
}
