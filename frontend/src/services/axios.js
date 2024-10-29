// src/services/axios.js
import axios from 'axios';
import { useAuthStore } from '../store/auth';
import router from '../router';

// Create an Axios instance
const axiosInstance = axios.create({
  baseURL: 'http://localhost:8002/api', // Adjust to your backend URL
  withCredentials: true, // Required for Sanctum CSRF protection if using cookies
});

// Set CSRF token in headers if required (Laravel Sanctum may require this)
const csrfToken = document.head.querySelector('meta[name="csrf-token"]');

if (csrfToken) {
  axiosInstance.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;
}

// Request interceptor to attach the token from localStorage
axiosInstance.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token'); // Retrieve token from localStorage
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor to handle global errors
axiosInstance.interceptors.response.use(
  (response) => response,
  (error) => {
    const originalRequest = error.config;

    // Avoid infinite loops by checking if the request is already for logout
    if (error.response && error.response.status === 401 && originalRequest.url !== '/logout') {
      const authStore = useAuthStore();
      authStore.logout(); // Clear user data and token
      router.push({ name: 'Login', query: { redirect: router.currentRoute.value.fullPath } });
    }
    return Promise.reject(error);
  }
);

// API service methods
const apiService = {
  
  // Products
  getProducts: () => {
    return axiosInstance.get('/products');
  },
  createProduct: (productData) => {
    return axiosInstance.post('/products', productData);
  },
  updateProduct: (productId, productData) => {
    return axiosInstance.put(`/products/${productId}`, productData);
  },
  deleteProduct: (productId) => {
    return axiosInstance.delete(`/products/${productId}`);
  },

  // Clients
  getClients: () => {
    return axiosInstance.get('/clients');
  },
  createClient: (clientData) => {
    return axiosInstance.post('/clients', clientData);
  },
  updateClient: (clientId, clientData) => {
    return axiosInstance.put(`/clients/${clientId}`, clientData);
  },
  deleteClient: (clientId) => {
    return axiosInstance.delete(`/clients/${clientId}`);
  },

  // Analytics
  getWeeklySales: () => {
    return axiosInstance.get('/analytics/week/sales');
  },
  getMonthlySales: () => {
    return axiosInstance.get('/analytics/month/sales');
  },
  getWeeklyStocks: () => {
    return axiosInstance.get('/analytics/week/stocks');
  },
  getMonthlyStocks: () => {
    return axiosInstance.get('/analytics/month/stocks');
  },

  // Total Revenue
  getTotalRevenue: () => {
    return axiosInstance.get('/total-revenue');
  },

   // Logout
   logout: () => {
    return axiosInstance.post('/logout');
  },

  // Update Password
  updatePassword: (payload) => {
    return axiosInstance.put('/user/password', payload);
  },
  // Update Profile
  updateProfile: (formData) => {
    return axiosInstance.put('/user', formData);
  },
};

export { apiService };
export default axiosInstance;
