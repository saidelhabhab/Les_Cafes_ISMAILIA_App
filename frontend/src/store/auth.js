// src/store/auth.js
import { defineStore } from 'pinia';
import axios from '../services/axios';
import { ref } from 'vue';
import router from '../router';

export const useAuthStore = defineStore('auth', () => {

  const user = ref(null);
  const token = ref(localStorage.getItem('token') || '');

  const isAuthenticated = () => !!token.value;
  const userRole = () => (user.value ? user.value.role : 'guest');

  const login = async ({ email, password }) => {
    try {
      const response = await axios.post('/login', { email, password });
      token.value = response.data.access_token;
      localStorage.setItem('token', token.value);
      user.value = response.data.user;
      console.log('Login success, token:', token.value);  // Check if token is saved
      return response;
    } catch (error) {
      throw error;
    }
  };
  

  const logout = async () => {
    try {
      await axios.post('/logout');
    } catch (error) {
      console.error('Logout failed:', error);
    } finally {
      user.value = null;
      token.value = '';
      localStorage.removeItem('token');
      router.push({ name: 'Login' });
    }
  };

  const fetchUser = async () => {
    try {
      const response = await axios.get('/user');
      user.value = response.data;
    } catch (error) {
      console.error('Fetch user failed:', error);
      logout();
    }
  };

  return {
    user,
    token,
    isAuthenticated,
    userRole,
    login,
    logout,
    fetchUser,
  };
});
