// src/stores/authStore.js
import { defineStore } from 'pinia';
import Cookies from 'js-cookie';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    permissions: [],
    token: null,
  }),
  actions: {
    async setUserData(user, token, permissions) {
      this.user = user;
      this.token = token;
      this.permissions = permissions;

      // Guardar token en cookies
      Cookies.set('authToken', token, {
        expires: 1, // Expira en 1 día
        secure: true,
        sameSite: 'Strict',
        httpOnly: false,
      });

      // Guardar datos del usuario en localStorage
      localStorage.setItem('user_data', JSON.stringify(user));
      localStorage.setItem('permissions', JSON.stringify(permissions));
    },
    loadUserData() {
      const userData = localStorage.getItem('user_data');
      const token = Cookies.get('authToken');

      if (userData && token) {
        this.user = JSON.parse(userData);
        this.token = token;

        const permissions = localStorage.getItem('permissions');
        this.permissions = permissions ? JSON.parse(permissions) : [];
      }
    },
    logout() {
      this.user = null;
      this.permissions = [];
      this.token = null;

      Cookies.remove('authToken');
      localStorage.removeItem('user_data');
      localStorage.removeItem('permissions');
    },
  },
});
