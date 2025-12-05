'use client';

import { useEffect } from 'react';
import { useAuthStore } from '@/lib/store';

export default function AuthProvider({ children }: { children: React.ReactNode }) {
  const { initializeAuth } = useAuthStore();

  useEffect(() => {
    initializeAuth();
  }, [initializeAuth]);

  return <>{children}</>;
}
