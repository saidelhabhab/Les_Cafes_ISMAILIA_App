<template>
  <div class="container invoices">
    <h1 class="text-center mb-4">
      <i class="bi bi-receipt me-2"></i> {{ $t('invoices.title') }}
    </h1>

    <!-- Flex container for buttons -->
    <div class="d-flex justify-content-between mb-3">
      <!-- First Add New Invoice Button -->
      <router-link to="/invoices/new" class="btn btn-primary" style="width: 30%;">
        <i class="bi bi-plus-circle me-2"></i> {{ $t('invoices.addNewInvoice') }}
      </router-link>

      <!-- Second Add New Invoice Button -->
      <router-link to="/invoices/empty-invoice" class="btn btn-info" style="width: 30%;">
        <i class="bi bi-file-earmark-plus me-2"></i> Ajouter une facteur vide
      </router-link>
    </div>
    
     <!-- Search by Client -->
     <div class="mb-3 search-container">
      <label for="clientSearch" class="form-label">
        <i class="bi bi-search me-2"></i> {{ $t('invoices.searchClientPlaceholder') }}
      </label>
      <input
        id="clientSearch"
        type="text"
        v-model="searchQuery" 
        class="form-control"
        :placeholder="$t('invoices.searchClientPlaceholder')"
        style="width: 30%;"
      />
    </div>

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

    <div v-else>
      <table class="table table-bordered table-hover">
        <thead class="table-light">
          <tr>
            <th><i class="bi bi-hash me-2"></i> {{ $t('invoices.id') }}</th>
            <th><i class="bi bi-person-fill me-2"></i> {{ $t('invoices.client') }}</th>
            <th><i class="bi bi-currency-dollar me-2"></i> {{ $t('invoices.total') }}</th>
            <th><i class="bi bi-calendar-event me-2"></i> {{ $t('invoices.dueDate') }}</th>
            <th><i class="bi bi-card-checklist me-2"></i> {{ $t('invoices.status') }}</th>
            <th><i class="bi bi-tools me-2"></i> {{ $t('invoices.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="invoice in paginatedInvoices" :key="invoice.id">
            <td>{{ invoice.factor_code }}</td>
            <td style="color:maroon;"> <strong>{{ invoice.client?.name || 'N/A' }}</strong></td>
            <td><strong> {{ parseFloat(invoice.amount).toFixed(2) }} {{ $t('returns.dh') }} </strong></td>
            <td>{{ new Date(invoice.due_date).toLocaleDateString() }}</td>
            <td style="color:green"> 
              <strong>
              {{ $t(`invoices.${invoice.status}`) }}
              <i class="bi bi-pencil-fill" @click="openModal(invoice)" style="cursor: pointer;"></i>
              </strong>
            </td>
            <td class="d-inline-flex gap-2">
              <router-link :to="`/invoices/${invoice.id}`" class="btn btn-info btn-sm">
                <i class="bi bi-eye-fill"></i> {{ $t('invoices.view') }}
              </router-link>
              <router-link :to="`/invoices/${invoice.id}/edit`" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil-fill"></i> {{ $t('invoices.edit') }}
              </router-link>
              <button class="btn btn-danger btn-sm" @click="removeInvoice(invoice.id)">
                <i class="bi bi-trash-fill"></i> {{ $t('invoices.delete') }}
              </button>
            </td>

          </tr>
        </tbody>
      </table>

      <!-- Pagination Controls -->
      <div class="pagination-controls">
        <button class="pagination-button" @click="prevPage" :disabled="currentPage === 1">{{ $t('purchasesShow.previous') }}</button>
        <span class="pagination-info">{{ currentPage }} / {{ totalPages }}</span>
        <button class="pagination-button" @click="nextPage" :disabled="currentPage === totalPages">{{ $t('purchasesShow.next') }}</button>
      </div>

       <!-- Modal -->
       <div v-if="isModalOpen" class="modal">
        <div class="modal-content">
          <span @click="closeModal" class="close">&times;</span>
          <h2 class="modal-title">
            <i class="bi bi-pencil-fill modal-icon"></i>
            {{ $t('invoices.edit') }}
          </h2>
          <select v-model="selectedStatus" class="status-select">
            <option value="paid">{{ $t('invoices.paid') }}</option>
            <option value="pending">{{ $t('invoices.pending') }}</option>
          </select>
          <button @click="updateStatus" class="save-button">{{ $t('invoices.saveInvoice') }}</button>
        </div>
      </div>

    </div>

    
  </div>
</template>


<script>
  import { ref, onMounted, computed } from 'vue'; // Add 'computed' to the import
  import axios from '../services/axios';
  import Swal from 'sweetalert2';

  export default {
    name: 'InvoiceList',
    setup() {
      const invoices = ref([]);
      const loading = ref(true);
      const isModalOpen = ref(false);
      const selectedInvoice = ref(null);
      const selectedStatus = ref('Paid');
      const searchQuery = ref(''); // New reactive variable for search input
      // Pagination properties
    const currentPage = ref(1);
    const itemsPerPage = ref(10); // Adjust as needed

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

      const openModal = (invoice) => {
        selectedInvoice.value = invoice;
        selectedStatus.value = invoice.status;
        isModalOpen.value = true;
      };

      const closeModal = () => {
        isModalOpen.value = false;
        selectedInvoice.value = null;
      };

      const updateStatus = async () => {
        try {
          await axios.put(`/invoices/changeStatus/${selectedInvoice.value.id}`, {
            status: selectedStatus.value,
          });
          Swal.fire('Success', 'Status changed successfully!', 'success');
          await fetchInvoices();
          closeModal();
        } catch (error) {
          console.error('Error updating status:', error);
        }
      };

      const capitalizeFirstLetter = (string) => string.charAt(0).toUpperCase() + string.slice(1);

       // Computed property to filter invoices based on searchQuery and paginate results
      const filteredInvoices = computed(() => {
        let filtered = invoices.value;
        if (searchQuery.value) {
          filtered = filtered.filter(invoice =>
            invoice.client?.name.toLowerCase().includes(searchQuery.value.toLowerCase())
          );
        }
        return filtered;
      });

      const paginatedInvoices = computed(() => {
        const start = (currentPage.value - 1) * itemsPerPage.value;
        const end = start + itemsPerPage.value;
        return filteredInvoices.value.slice(start, end);
      });

      // Total pages for pagination
    const totalPages = computed(() => {
      return Math.ceil(filteredInvoices.value.length / itemsPerPage.value);
    });

    const prevPage = () => {
      if (currentPage.value > 1) currentPage.value--;
    };

    const nextPage = () => {
      if (currentPage.value < totalPages.value) currentPage.value++;
    };

      onMounted(fetchInvoices);

      return {
        invoices,
        loading,
        removeInvoice,
        capitalizeFirstLetter,
        isModalOpen,
        selectedInvoice,
        selectedStatus,
        closeModal,
        openModal,
        updateStatus,
        searchQuery,      // New search query binding
        filteredInvoices, // Computed property for filtered invoices
        paginatedInvoices,
        currentPage,
        totalPages,
        prevPage,
        nextPage,
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


  .modal {
    display: flex;
    justify-content: center;
    align-items: center;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
  }

  .modal-content {
    background-color: #ffffff;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    width: 400px; /* Adjust width as needed */
    text-align: center; /* Center text inside modal */
    animation: fadeIn 0.3s; /* Animation for modal appearance */
  }

  .close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
  }

  .close:hover,
  .close:focus {
    color: #000;
    text-decoration: none;
  }

  .modal-title {
    margin: 0;
    font-size: 24px;
    color: #333;
  }

  .modal-icon {
    font-size: 24px; /* Adjust icon size */
    margin-right: 8px; /* Space between icon and title */
    color: #007bff; /* Icon color */
  }

  .status-select {
    width: 100%; /* Full width */
    padding: 10px;
    margin: 20px 0; /* Margin above and below the select */
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .save-button {
    background-color: #007bff; /* Bootstrap primary color */
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s;
  }

  .save-button:hover {
    background-color: #0056b3; /* Darker shade on hover */
  }

  /* Animation for modal appearance */
  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }


  .pagination-controls {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 15px;
  margin-top: 20px;
}

.pagination-button {
  background-color: #007bff; /* Primary color */
  color: white;
  border: none;
  border-radius: 5px;
  padding: 8px 12px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background-color 0.3s, box-shadow 0.3s;
}

.pagination-button:hover {
  background-color: #0056b3; /* Darker primary color on hover */
}

.pagination-button:disabled {
  background-color: #d3d3d3; /* Disabled button color */
  cursor: not-allowed;
}

.pagination-info {
  font-size: 1rem;
  font-weight: bold;
  color: #333;
}


</style>
