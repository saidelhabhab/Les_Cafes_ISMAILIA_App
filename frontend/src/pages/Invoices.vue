<template>
  <div class="container invoices">
    <h1 class="text-center mb-4">
      <i class="bi bi-receipt me-2"></i> {{ $t('invoices.title') }}
    </h1>

    <!-- Add New Invoice Button -->
    <router-link to="/invoices/new" class="btn btn-primary mb-3">
      <i class="bi bi-plus-circle me-2"></i> {{ $t('invoices.addNewInvoice') }}
    </router-link>

    <!-- Loading State -->
    <div v-if="loading" class="text-center my-3">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">{{ $t('invoices.loading') }}</span>
      </div>
      <p class="mt-2">{{ $t('invoices.loadingInvoices') }}</p>
    </div>

    <!-- No Invoices Found -->
    <div v-else-if="invoices.length === 0" class="text-center my-3">
      <i class="bi bi-folder-x me-2"></i> {{ $t('invoices.noInvoicesFound') }}
    </div>

    <!-- Invoices Table -->
    <div v-else>
      <table class="table table-bordered table-hover">
        <thead class="table-light">
          <tr>
            <th><i class="bi bi-hash me-2"></i> {{ $t('invoices.id') }}</th>
            <th><i class="bi bi-person-fill me-2"></i> {{ $t('invoices.client') }}</th>
            <th><i class="bi bi-currency-dollar me-2"></i> {{ $t('invoices.total') }}</th>
            <th><i class="bi bi-card-checklist me-2"></i> {{ $t('invoices.status') }}</th>
            <th><i class="bi bi-calendar-event me-2"></i> {{ $t('invoices.dueDate') }}</th>
            <th><i class="bi bi-tools me-2"></i> {{ $t('invoices.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="invoice in invoices" :key="invoice.id">
            <td>{{ invoice.factor_code }}</td>
            <td>{{ invoice.client?.name || 'N/A' }}</td>
            <td>${{ parseFloat(invoice.total_amount_with_tva).toFixed(2) }}</td>
            <td>{{ capitalizeFirstLetter(invoice.status) }}</td>
            <td>{{ new Date(invoice.due_date).toLocaleDateString() }}</td>
            <td>
              <router-link :to="`/invoices/${invoice.id}`" class="btn btn-info btn-sm me-2">
                <i class="bi bi-eye-fill"></i> {{ $t('invoices.view') }}
              </router-link>
              <router-link :to="`/invoices/${invoice.id}/edit`" class="btn btn-warning btn-sm me-2">
                <i class="bi bi-pencil-fill"></i> {{ $t('invoices.edit') }}
              </router-link>
              <button class="btn btn-danger btn-sm me-2" @click="removeInvoice(invoice.id)">
                <i class="bi bi-trash-fill"></i> {{ $t('invoices.delete') }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>


<script>
import { ref, onMounted } from 'vue';
import axios from '../services/axios';
import Swal from 'sweetalert2';

export default {
  name: 'InvoiceList',
  setup() {
    const invoices = ref([]);
    const loading = ref(true);

    const fetchInvoices = async () => {
      loading.value = true;
      try {
        const response = await axios.get('/invoices');
        invoices.value = response.data;
      } catch (error) {
        Swal.fire('Error', 'Failed to fetch invoices.', 'error');
      } finally {
        loading.value = false;
      }
    };

    const removeInvoice = async (invoiceId) => {
      const confirmed = await Swal.fire({
        title: 'Are you sure?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'No, cancel!',
      });

      if (confirmed.isConfirmed) {
        try {
          await axios.delete(`/invoices/${invoiceId}`);
          Swal.fire('Deleted!', 'Invoice has been deleted.', 'success');
          fetchInvoices(); // Refresh the invoice list
        } catch (error) {
          Swal.fire('Error', 'Failed to delete invoice.', 'error');
        }
      }
    };

    const capitalizeFirstLetter = (string) => string.charAt(0).toUpperCase() + string.slice(1);

    // Fetch invoices when the component is mounted
    onMounted(fetchInvoices);

    return {
      invoices,
      loading,
      removeInvoice,
      capitalizeFirstLetter,
    };
  },
};
</script>

<style scoped>
.container {
  padding: 2rem;
}

h1 {
  color: #007bff; /* Bootstrap primary color */
}

.table th {
  background-color: #f8f9fa; /* Light background for table header */
}

.table tbody tr:hover {
  background-color: #f1f1f1; /* Row hover effect */
}

.btn-sm {
  margin-right: 5px;
}

.spinner-border {
  margin: auto;
}
</style>
