import { create } from 'zustand';
import Cookies from 'js-cookie';
import type { User, AuthResponse } from '@/types';

interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  setAuth: (authResponse: AuthResponse) => void;
  logout: () => void;
  setUser: (user: User) => void;
  initializeAuth: () => void;
}

export const useAuthStore = create<AuthState>((set) => ({
  user: null,
  token: null,
  isAuthenticated: false,
  isLoading: true,

  setAuth: (authResponse: AuthResponse) => {
    const user: User = {
      userID: authResponse.userID,
      username: authResponse.username,
      email: authResponse.email,
      role: authResponse.role as 'Admin' | 'Customer',
      firstName: '',
      lastName: '',
      createdAt: '',
    };

    Cookies.set('token', authResponse.token, { expires: 1 });
    Cookies.set('user', JSON.stringify(user), { expires: 1 });

    set({
      user,
      token: authResponse.token,
      isAuthenticated: true,
      isLoading: false,
    });
  },

  logout: () => {
    Cookies.remove('token');
    Cookies.remove('user');
    set({
      user: null,
      token: null,
      isAuthenticated: false,
      isLoading: false,
    });
  },

  setUser: (user: User) => {
    Cookies.set('user', JSON.stringify(user), { expires: 1 });
    set({ user });
  },

  initializeAuth: () => {
    const token = Cookies.get('token');
    const userStr = Cookies.get('user');

    if (token && userStr) {
      try {
        const user = JSON.parse(userStr) as User;
        set({
          user,
          token,
          isAuthenticated: true,
          isLoading: false,
        });
      } catch {
        set({ isLoading: false });
      }
    } else {
      set({ isLoading: false });
    }
  },
}));
