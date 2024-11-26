// src/router/index.js

import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../store/auth';

// Import your components
const Login = () => import('../pages/Login.vue');
const MainLayout = () => import('../layouts/MainLayout.vue'); // Import MainLayout
const Dashboard = () => import('../pages/Dashboard.vue');
const Products = () => import('../pages/Products.vue');
const Clients = () => import('../pages/Clients.vue');
const Invoices = () => import('../pages/Invoices.vue');
const Returns = () => import('../pages/Returns.vue');
const Analytics = () => import('../pages/Analytics.vue');
const NotFound = () => import('../pages/NotFound.vue'); // 404 Page
const Profile = () => import('../pages/Profile.vue');
const NewInvoice = () => import('../pages/New-invoice.vue'); // Adjust the import as necessary
const InvoiceView = () => import('../pages/InvoiceView.vue');
const InvoiceEdit = () => import('../pages/InvoiceEdit.vue');
const ViewReturn = () => import('../pages/ViewReturn.vue');
const UpdatePassword = () => import('../pages/UpdatePassword.vue'); // Import UpdatePassword
const Purchases = () => import('../pages/Purchases.vue'); // Import Purchases component
const ShowPurchases = () => import('../pages/ShowPurchases.vue'); // Import Purchases component
const EmptyInvoice = () =>import('../pages/EmptyInvoice.vue')


const routes = [
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { guest: true }, // Accessible only to unauthenticated users
  },
  {
    path: '/',
    component: MainLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: Dashboard,
      },
      {
        path: 'products',
        name: 'Products',
        component: Products,
      },
      {
        path: 'clients',
        name: 'Clients',
        component: Clients,
      },
      {
        path: 'invoices',
        name: 'Invoices',
        component: Invoices,
      },
      {
        path: 'invoices/new',
        name: 'NewInvoice',
        component: NewInvoice,
      },
      {
        path: '/invoices/empty-invoice',
        component: EmptyInvoice, 
        name: 'EmptyInvoice', 
      },
      {
        path: 'invoices/:id',
        name: 'InvoiceView',
        component: InvoiceView,
        props: true,
      },
      {
        path: 'invoices/:id/edit',
        name: 'InvoiceEdit',
        component: InvoiceEdit,
        props: true,
      },
      {
        path: 'returns',
        name: 'Returns',
        component: Returns,
      },
      {
        path: 'returns/:id',
        name: 'ViewReturn',
        component: ViewReturn,
        props: true,
      },
      {
        path: 'analytics',
        name: 'Analytics',
        component: Analytics,
      },
      {
        path: 'profile',
        name: 'Profile',
        component: Profile,
      },
      {
        path: 'update-password',
        name: 'UpdatePassword',
        component: UpdatePassword,
      },
      {
        path: '/categories',
        name: 'Categories',
        component: () => import('@/pages/Categories.vue')
      },
      {
        path: 'purchases',
        name: 'Purchases',
        component: Purchases,
      },

      {
        path: '/purchases/:id',
        name: 'ShowPurchases',
        component: ShowPurchases,
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: NotFound,
    meta: { guest: true }, // Accessible to all, typically for 404
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation Guard
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();

  if (to.meta.requiresAuth && !authStore.isAuthenticated()) {
    // Route requires auth and user is not authenticated
    next({ name: 'Login', query: { redirect: to.fullPath } });
  } else if (to.meta.guest && authStore.isAuthenticated()) {
    // Route is for guests and user is authenticated
    next({ name: 'Dashboard' });
  } else {
    // Proceed as normal
    next();
  }
});

export default router;
