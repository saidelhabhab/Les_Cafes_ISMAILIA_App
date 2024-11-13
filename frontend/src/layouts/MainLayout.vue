<template>
  <div :dir="currentLanguage === 'ar' ? 'rtl' : 'ltr'" class="main-layout">
    <header>
      <nav>
        <!-- Logo -->
        <router-link to="/" class="logo-link">
          <img src="@/assets/icon2.png" alt="Logo" class="logo" />
        </router-link>

        <!-- Navigation Links -->
        <router-link
          v-for="(link, index) in navLinks"
          :key="index"
          :to="link.path"
          :class="{ 'is-active': isActive(link.path) }"
        >
          {{ $t(link.name) }}
        </router-link>

        <!-- Logout Button -->
        <button @click="logout">{{ $t('logout') }}</button>

        <!-- Language Switcher -->
        <div class="language-switcher">
          <select v-model="currentLanguage" @change="changeLanguage($event.target.value)" class="styled-select">
            <option value="en">English</option>
            <option value="fr">Français</option>
            <option value="ar">العربية</option>
          </select>
          <img :src="getFlag(currentLanguage)" alt="Flag" class="flag-icon" />
        </div>
      </nav>
    </header>

    <main>
      <router-view />
    </main>

    <footer>
      <p>© {{ year }} {{ $t('footer') }} <a href="mailto:saidelhabhab@gmail.com">saidelhabhab@gmail.com</a></p>
    </footer>
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import { useAuthStore } from '../store/auth';
import { useRouter, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';

import usFlag from '@/assets/flags/us.png';
import frFlag from '@/assets/flags/fr.png';
import arFlag from '@/assets/flags/ar.png';

export default {
  name: 'MainLayout',
  setup() {
    const authStore = useAuthStore();
    const router = useRouter();
    const route = useRoute();
    const { locale } = useI18n();

    const currentLanguage = ref(locale.value);
    const year = ref(new Date().getFullYear());

    const navLinks = [
      { path: '/', name: 'dashboardNav' },
      { path: '/purchases', name: 'purchasesNav' },
      { path: '/products', name: 'productsNav' },
      { path: '/clients', name: 'clientsNav' },
      { path: '/invoices', name: 'invoicesNav' },
      { path: '/returns', name: 'returnsNav' },
      { path: '/analytics', name: 'analyticsNav' },
      { path: '/profile', name: 'profileNav' },
    ];

    const logout = () => {
      authStore.logout();
      router.push('/login');
    };

    const changeLanguage = (lang) => {
      currentLanguage.value = lang;
      locale.value = lang;
    };

    const getFlag = (lang) => {
      switch (lang) {
        case 'en':
          return usFlag;
        case 'fr':
          return frFlag;
        case 'ar':
          return arFlag;
        default:
          return '';
      }
    };

    const isActive = (path) => route.path === path;

    return {
      logout,
      currentLanguage,
      changeLanguage,
      getFlag,
      year,
      navLinks,
      isActive,
    };
  },
};
</script>

<style scoped>
.main-layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

header {
  background-color: #c7c7c77e;

 
}

nav {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.logo-link {
  display: flex;
  align-items: center;
  margin-right: 2rem;
}

.logo {
  height: 70px;
  width: 100px;
}

nav a {
  color: rgb(2, 2, 2);
  text-decoration: none;
  font-weight: bold;
  padding: 0.5rem 0.75rem;
  border-radius: 5px;
  transition: background-color 0.3s;
}

nav a:hover {
  background-color: #701a1a;
  color:#eeebeb;
}

.is-active {
  background-color: #ffae42;
  color: rgb(102, 10, 10);
}

nav button {
  margin-left: auto;
  padding: 0.5rem 1rem;
  background-color: #ff4d4d;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s;
}

nav button:hover {
  background-color: #e60000;
}

/* Language Switcher Styles */
.language-switcher {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.styled-select {
  padding: 0.4rem 0.75rem;
  border-radius: 8px;
  border: 1px solid #ccc;
  background-color: #fff;
  font-size: 1rem;
  font-weight: bold;
  color: #333;
  cursor: pointer;
  outline: none;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.styled-select:hover,
.styled-select:focus {
  border-color: #42b983;
  box-shadow: 0 0 10px rgba(66, 185, 131, 0.5);
}

.flag-icon {
  width: 25px;
  height: 18px;
  border-radius: 4px;
}

main {
  flex: 1;
  padding: 2rem;
}

footer {
  background-color: #f4f4f4;
  text-align: center;
  padding: 1rem;
}

footer p a {
  color: #701a1a;
  text-decoration: none;
  font-weight: bold;
}

footer p a:hover {
  text-decoration: underline;
}

/* RTL support */
[dir="rtl"] nav {
  flex-direction: row-reverse;
}

[dir="rtl"] .logo-link {
  margin-left: 2rem;
  margin-right: 0;
}
</style>
