<template>
  <div class="dashboard">
    <h1 class="text-center dashboard-title">
      <i class="bi bi-house-door-fill"></i> {{ $t('dashboard.title') }}
    </h1>

    <div class="metrics">
      <div class="metric-card bg-primary text-white">
        <i class="bi bi-box"></i>
        <h3>{{ $t('dashboard.totalProducts') }}</h3>
        <p>{{ totalProducts }}</p>
      </div>
      <div class="metric-card bg-success text-white">
        <i class="bi bi-person-fill"></i>
        <h3>{{ $t('dashboard.totalClients') }}</h3>
        <p>{{ totalClients }}</p>
      </div>
      <div class="metric-card bg-warning text-dark">
        <i class="bi bi-file-earmark-text"></i>
        <h3>{{ $t('dashboard.totalInvoices') }}</h3>
        <p>{{ totalInvoices }}</p>
      </div>
      <div class="metric-card bg-info text-white">
        <i class="bi bi-currency-dollar"></i>
        <h3>{{ $t('dashboard.totalRevenue') }}</h3>
        <p>${{ totalRevenue }}</p>
      </div>
    </div>

    <div class="recent-activities">
      <h2>
        <i class="bi bi-file-earmark-text"></i> {{ $t('dashboard.recentInvoices') }}
      </h2>
      <table>
        <thead>
          <tr>
            <th>
              <i class="bi bi-list-check"></i> {{ $t('dashboard.id') }}
            </th>
            <th>
              <i class="bi bi-person-fill"></i> {{ $t('dashboard.client') }}
            </th>
            <th>
              <i class="bi bi-currency-dollar"></i> {{ $t('dashboard.total') }}
            </th>
            <th>
              <i class="bi bi-calendar"></i> {{ $t('dashboard.date') }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="invoice in recentInvoices" :key="invoice.id">
            <td>{{ invoice.id }}</td>
            <td>{{ invoice.client.name }}</td>
            <td>${{ invoice.total_amount_with_tva }}</td>
            <td>{{ formatDate(invoice.created_at) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>



<script>
import { onMounted, ref } from 'vue';
import axios from '../services/axios';

export default {
  name: 'Dashboard',
  setup() {
    const totalProducts = ref(0);
    const totalClients = ref(0);
    const totalInvoices = ref(0);
    const totalRevenue = ref(0);
    const recentInvoices = ref([]);

    const fetchTotalRevenue = async () => {
      try {
        const response = await axios.get('/total-revenue'); // API endpoint for total revenue
       // console.log("Total Revenue Response:", response.data);
        totalRevenue.value = response.data.total_revenue; // Set the total revenue
       // console.log("totalRevenue.value", totalRevenue.value);
      } catch (error) {
        console.error('Error fetching total revenue:', error);
      }
    };

    const fetchMetrics = async () => {
    try {
      const [productsRes, clientsRes, invoicesRes, recentInvoicesRes] = await Promise.all([
        axios.get('/products'),
        axios.get('/clients'),
        axios.get('/invoices'),
        axios.get('/invoices?limit=5&sort=desc'),
      ]);

     // console.log("productsRes ==> ", productsRes); // Log the full response for debugging

      totalProducts.value = productsRes.data.items.length;
    //  console.log("product ==> ", productsRes.data.length);
      totalClients.value = clientsRes.data.length;
      totalInvoices.value = invoicesRes.data.length;
      totalRevenue.value = invoicesRes.data.reduce((acc, invoice) => acc + invoice.total_amount_with_tva, 0);
      recentInvoices.value = recentInvoicesRes.data.slice(0, 5);
    } catch (error) {
      console.error("Error fetching metrics:", error);
    }
  };


    const formatDate = (dateStr) => {
      const options = { year: 'numeric', month: 'short', day: 'numeric' };
      return new Date(dateStr).toLocaleDateString(undefined, options);
    };

    onMounted(() => {
      fetchMetrics();
    });

    return {
      totalProducts,
      totalClients,
      totalInvoices,
      totalRevenue,
      recentInvoices,
      formatDate,
    };
  },
};
</script>


<style scoped>
.dashboard-title {
  text-align: center; /* Center the text */
  margin-bottom: 2rem; /* Add margin below the title */
  font-size: 2.5rem; /* Increase font size for visibility */
  color: #333; /* Text color */
}

.dashboard-title i {
  margin-right: 0.5rem; /* Space between icon and text */
  font-size: 2rem; /* Adjust icon size */
}

h1 {
  display: flex;
  align-items: center;
}

h1 i {
  margin-right: 0.5rem;
}

.metrics {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
}

.metric-card {
  flex: 1;
  padding: 1rem;
  border-radius: 8px;
  text-align: center;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
}

.metric-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

.metric-card i {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}
.recent-activities {
  margin-top: 2rem;
}

.recent-activities h2 {
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
}

.recent-activities h2 i {
  margin-right: 0.5rem;
  color: #007bff;
}

.recent-activities table {
  width: 100%;
  border-collapse: collapse;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.recent-activities th,
.recent-activities td {
  border: 1px solid #ddd;
  padding: 0.75rem;
  text-align: left;
}

.recent-activities th {
  background-color: #f0f0f0;
  position: relative;
}

.recent-activities th i {
  margin-right: 0.25rem;
}

.recent-activities tr:nth-child(even) {
  background-color: #f9f9f9;
}

.recent-activities tr:hover {
  background-color: #f1f1f1;
}

.recent-activities td {
  font-size: 14px;
}
</style>
