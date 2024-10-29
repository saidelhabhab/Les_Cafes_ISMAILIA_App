import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
  plugins: [vue(),],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:8002', // Laravel backend
        changeOrigin: true,
        secure: false,
      },
    },
    host: 'localhost', // optional
    port: 3002, // set the port to 3002
    open: true, // optional: automatically open the browser
  },
});
