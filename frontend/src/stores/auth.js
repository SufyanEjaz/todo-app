import { defineStore } from 'pinia';
import api from '../api/auth';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
  }),
  actions: {
    async login(credentials) {
      const response = await api.post('/login', credentials);
      this.token = response.data.token;
      localStorage.setItem('token', this.token);
      this.user = response.data.user;
    },
    async register(credentials) {
      const response = await api.post('/register', credentials);
      this.token = response.data.token;
      localStorage.setItem('token', this.token);
      this.user = response.data.user;
    },
    async logout() {
      await api.post('/logout');
      this.token = null;
      this.user = null;
      localStorage.removeItem('token');
    },
  },
  getters: {
    isAuthenticated: (state) => !!state.token,
  },
});
