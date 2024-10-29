// src/main.js
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import { createPinia } from 'pinia';

// Import SweetAlert2 and its styles
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js'; // Optional, for JS components
import 'bootstrap-icons/font/bootstrap-icons.css'; // Import Bootstrap Icons
import 'bootstrap';
import '@fortawesome/fontawesome-free/css/all.css';
import '@fortawesome/fontawesome-free/js/all.js';
import VueApexCharts from 'vue3-apexcharts';
import i18n from './i18n'; // Import i18n


const pinia = createPinia();

const app = createApp(App);

app.use(pinia);

// Use SweetAlert2
app.use(VueSweetalert2);
app.use(VueApexCharts);


app.use(router);
app.use(i18n); // Use i18n

app.mount('#app');



/*
import { createApp } from 'vue'
import './style.css'
import App from './App.vue'

createApp(App).mount('#app')

*/
