import type { Metadata } from "next";
import { Toaster } from 'react-hot-toast';
import "./globals.css";
import Header from "@/components/Header";
import Footer from "@/components/Footer";
import AuthProvider from "@/components/AuthProvider";

export const metadata: Metadata = {
  title: "المكتبة العربية - متجر الكتب الإلكتروني",
  description: "وجهتك الأولى للكتب العربية والعالمية. نوفر لك أفضل الكتب في مختلف المجالات بأسعار مناسبة.",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="ar" dir="rtl">
      <body
        className="font-arabic antialiased bg-gray-50 min-h-screen flex flex-col"
      >
        <AuthProvider>
          <Header />
          <main className="flex-1">
            {children}
          </main>
          <Footer />
          <Toaster 
            position="top-center"
            toastOptions={{
              duration: 4000,
              style: {
                direction: 'rtl',
              },
            }}
          />
        </AuthProvider>
      </body>
    </html>
  );
}
