<template>
  <div :dir="currentLanguage === 'ar' ? 'rtl' : 'ltr'" class="main-layout">
    <header>
      <nav>
        <router-link to="/">{{ $t('dashboardNav') }}</router-link>
        <router-link to="/purchases">{{ $t('purchasesNav') }}</router-link>
        <router-link to="/products">{{ $t('productsNav') }}</router-link>
        <router-link to="/clients">{{ $t('clientsNav') }}</router-link>
        <router-link to="/invoices">{{ $t('invoicesNav') }}</router-link>
        <router-link to="/returns">{{ $t('returnsNav') }}</router-link>
        <router-link to="/analytics">{{ $t('analyticsNav') }}</router-link>
        <router-link to="/categories">{{ $t('categoriesNav') }}</router-link>
        <router-link to="/profile">{{ $t('profileNav') }}</router-link>
        <button @click="logout">{{ $t('logout') }}</button>

        <!-- Improved Language Switcher with Styled Select Dropdown -->
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
      <p>{{ $t('footer') }}  <a href="mailto:saidelhabhab@gmail.com">saidelhabhab@gmail.com</a></p>
    </footer>
  </div>
</template>

<script>
import { ref } from 'vue';
import { useAuthStore } from '../store/auth';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';

import usFlag from '@/assets/flags/us.png';
import frFlag from '@/assets/flags/fr.png';
import arFlag from '@/assets/flags/ar.png';

export default {
  name: 'MainLayout',
  setup() {
    const authStore = useAuthStore();
    const router = useRouter();
    const { locale } = useI18n();

    const currentLanguage = ref(locale.value);

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

    return {
      logout,
      currentLanguage,
      changeLanguage,
      getFlag,
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
  background-color: #42b983;
  padding: 1rem;
}

nav {
  display: flex;
  gap: 1rem;
  align-items: center;
}

nav a {
  color: white;
  text-decoration: none;
  font-weight: bold;
}

nav a.router-link-active {
  text-decoration: underline;
}

nav button {
  margin-left: auto;
  padding: 0.5rem 1rem;
  background-color: #ff4d4d;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
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

/* Styled Select Dropdown */
.styled-select {
  padding: 0.5rem 1rem;
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

.styled-select:hover {
  border-color: #42b983;
  box-shadow: 0 0 10px rgba(66, 185, 131, 0.5);
}

.styled-select:focus {
  border-color: #42b983;
  box-shadow: 0 0 10px rgba(66, 185, 131, 0.8);
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

/* RTL support */
[dir="rtl"] {
  text-align: right;
}

[dir="rtl"] nav {
  flex-direction: row-reverse;
}
</style>
