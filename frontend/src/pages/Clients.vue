<template>
  <div class="container clients">
    <h1 class="text-center mb-4">
      <i class="bi bi-people-fill me-2"></i> {{ $t('clients.title') }}
    </h1>

    <!-- Add New Client Button -->
    <button class="btn btn-primary mb-3" @click="showAddModal = true">
      <i class="bi bi-plus-circle me-2"></i> {{ $t('clients.addNewClient') }}
    </button>
    
   <!-- Search Input Fields -->
    <div class="mb-4 d-flex justify-content-center align-items-center">
      <div class="input-group me-2" style="width: 200px;">
        <input type="text" v-model="searchCin" class="form-control" placeholder="Recherche par CIN" />
      </div>
      <div class="input-group me-2" style="width: 200px;">
        <input type="text" v-model="searchName" class="form-control" placeholder="Recherche par Nom" />
      </div>
      <div class="form-check me-2">
        <input
          type="checkbox"
          id="filterRemainingPrice"
          v-model="filterRemainingPrice"
          class="form-check-input"
        />
        <label for="filterRemainingPrice" class="form-check-label">
          {{ $t('clients.filterRemainingPrice') }}
        </label>
      </div>

      <button class="btn btn-primary" @click="searchClients">
        <i class="bi bi-search me-1"></i> Search
      </button>
    </div>





    <!-- Clients Table -->
    <table class="table table-striped table-hover">
      <thead class="table-dark">
        <tr>
          <th><i class="bi bi-person-fill me-2"></i> {{ $t('clients.cin') }}</th>
          <th><i class="bi bi-person-fill me-2"></i> {{ $t('clients.name') }}</th>
          <th><i class="bi bi-envelope-fill me-2"></i> {{ $t('clients.email') }}</th>
          <th><i class="bi bi-telephone-fill me-2"></i> {{ $t('clients.final_price') }}</th>
          <th><i class="bi bi-geo-alt-fill me-2"></i> {{ $t('clients.remaining_price') }}</th>
          <th><i class="bi bi-tools me-2"></i> {{ $t('clients.actions') }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="client in paginatedClients" :key="client.id">
          <td>{{ client.cin }}</td>
          <td>{{ client.name }}</td>
          <td>{{ client.email }}</td>
          <td> <strong>{{ client.final_price }}</strong></td>
          <td> <strong>{{ client.remaining_price }}</strong></td>
          <td  class="d-inline-flex gap-2">
            <button class="btn btn-sm btn-primary btn-sm" @click="viewClient(client)">
                <i class="bi bi-eye-fill me-1"></i> 
            </button>
            <button class="btn btn-sm btn-warning btn-sm" @click="editClient(client)">
              <i class="bi bi-pencil-fill me-1"></i> 
            </button>
            <button class="btn btn-sm btn-danger btn-sm" @click="deleteClient(client.id)">
              <i class="bi bi-trash-fill me-1"></i> 
            </button>
          </td>
        </tr>
      </tbody>
    </table>

      <!-- Pagination -->
    <nav aria-label="Page navigation">
      <ul class="pagination justify-content-center mb-4">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">
            <i class="bi bi-chevron-left"></i> Previous
          </a>
        </li>
        <li class="page-item" v-for="page in totalPages" :key="page" :class="{ active: currentPage === page }">
          <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
        </li>
        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">
            Next <i class="bi bi-chevron-right"></i>
          </a>
        </li>
      </ul>
    </nav>


    <!-- Add/Edit Client Modal -->
    <div v-if="showAddModal || showEditModal" class="modal fade show d-block" tabindex="-1" aria-modal="true" role="dialog">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">{{ showAddModal ? $t('clients.modalTitleAdd') : $t('clients.modalTitleEdit') }}</h5>
              <button type="button" class="btn-close" @click="closeModal"></button>
            </div>
            <div class="modal-body">
              <form @submit.prevent="handleSubmit">
                <div class="mb-3">
                  <label for="cin" class="form-label">{{ $t('clients.cin') }}:</label>
                  <input type="text" v-model="form.cin" class="form-control" id="cin"  />
                </div>
                <div class="mb-3">
                  <label for="name" class="form-label">{{ $t('clients.name') }}:</label>
                  <input type="text" v-model="form.name" class="form-control" id="name" required />
                </div>
                <div class="mb-3">
                  <label for="email" class="form-label">{{ $t('clients.email') }}:</label>
                  <input type="email" v-model="form.email" class="form-control" id="email"  />
                </div>
                <div class="mb-3">
                  <label for="phone" class="form-label">{{ $t('clients.phone') }}:</label>
                  <input type="tel" v-model="form.phone" class="form-control" id="phone"  />
                </div>
                <div class="mb-3">
                  <label for="address" class="form-label">{{ $t('clients.address') }}:</label>
                  <textarea v-model="form.address" class="form-control" id="address"></textarea>
                </div>
                <div class="mb-3">
                  <label for="final_price" class="form-label">{{ $t('clients.final_price') }}:</label>
                  <textarea v-model="form.final_price" class="form-control" id="final_price"></textarea>
                </div>
                <div class="mb-3">
                  <label for="remaining_price" class="form-label">{{ $t('clients.remaining_price') }}:</label>
                  <textarea v-model="form.remaining_price" class="form-control" id="remaining_price"></textarea>
                </div>
                <button type="submit" class="btn btn-success w-100">
                  <i class="bi bi-save me-1"></i> {{ showAddModal ? $t('clients.addClient') : $t('clients.updateClient') }}
                </button>
              </form>
              <p v-if="errorMessage" class="text-danger mt-3">{{ errorMessage }}</p>
            </div>
          </div>
        </div>
    </div>

    <!-- Modal Backdrop -->
    <div v-if="showAddModal || showEditModal" class="modal-backdrop fade show"></div>
    
    <!-- View Client Details Modal -->
    <div v-if="showViewModal" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $t('clients.show') }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <p><strong>{{ $t('clients.cin') }}:</strong> {{ selectedClient.cin }}</p>
            <p><strong>{{ $t('clients.name') }}:</strong> {{ selectedClient.name }}</p>
            <p><strong>{{ $t('clients.email') }}:</strong> {{ selectedClient.email }}</p>
            <p><strong>{{ $t('clients.phone') }}:</strong> {{ selectedClient.phone }}</p>
            <p><strong>{{ $t('clients.address') }}:</strong> {{ selectedClient.address }}</p>
            <p><strong>{{ $t('clients.final_price') }}:</strong> {{ selectedClient.final_price }} Dhs</p>
            <p><strong>{{ $t('clients.remaining_price') }}:</strong> {{ selectedClient.remaining_price }} Dhs</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">{{ $t('clients.close') }}</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import { ref, onMounted, computed, watch } from 'vue';
  import axios from '../services/axios';
  import Swal from 'sweetalert2';
  import debounce from 'lodash/debounce'; // Import lodash debounce for optimizing search

  export default {
    name: 'Clients',
    setup() {
      const clients = ref([]);
      const searchCin = ref('');
      const searchName = ref('');
      const paginatedClients = ref([]);
      const currentPage = ref(1);
      const itemsPerPage = 8;
      const totalPages = computed(() => Math.ceil(filteredClients.value.length / itemsPerPage));
      const showAddModal = ref(false);
      const showEditModal = ref(false);
      const showViewModal = ref(false);
      const selectedClient = ref({});
      const loading = ref(false); // Loading state
      const filterRemainingPrice = ref(false); // Add the filter state
      const selectedClientIds = ref([]); // For bulk delete
      const errorMessage = ref('');

      const form = ref({
        id: null,
        cin: '',
        name: '',
        email: '',
        phone: '',
        address: '',
      });


      const fetchClients = async () => {
        try {
          loading.value = true;
          const response = await axios.get('/clients');
          clients.value = response.data;
          updatePaginatedClients();
        } catch (error) {
          console.error(error);
          Swal.fire('Error', 'Failed to fetch clients.', 'error');
        } finally {
          loading.value = false;
        }
      };

      const updatePaginatedClients = () => {
        const start = (currentPage.value - 1) * itemsPerPage;
        paginatedClients.value = filteredClients.value.slice(start, start + itemsPerPage);
      };
    


      const filteredClients = computed(() => {
        return clients.value.filter(client => {
          const matchesCin = (client.cin || '').includes(searchCin.value); // Handle null cin
          const matchesName = client.name.toLowerCase().includes(searchName.value.toLowerCase());
          const matchesRemainingPrice = !filterRemainingPrice.value || client.remaining_price > 0; // Check remaining price if filter is active

          return matchesCin && matchesName && matchesRemainingPrice;
        });
      });




      const changePage = (page) => {
        if (page < 1 || page > totalPages.value) return;
        currentPage.value = page;
        updatePaginatedClients();
      };

      const searchClients = debounce(() => {
        currentPage.value = 1;
        updatePaginatedClients();
      }, 300); // Debounced to optimize performance

      watch([searchCin, searchName], searchClients);

      const handleSubmit = async () => {
        try {
          loading.value = true;
          if (showAddModal.value) {
            await axios.post('/clients', form.value);
            Swal.fire('Success', 'Client added successfully!', 'success');
          } else if (showEditModal.value) {
            await axios.put(`/clients/${form.value.id}`, form.value);
            Swal.fire('Success', 'Client updated successfully!', 'success');
          }
          closeModal();
          fetchClients();
        } catch (error) {
          errorMessage.value = error.response?.data?.message || 'Operation failed.';
          Swal.fire('Error', errorMessage.value, 'error');
        } finally {
          loading.value = false;
        }
      };

      const viewClient = (client) => {
        selectedClient.value = { ...client };
        showViewModal.value = true;
      };

      const editClient = (client) => {
        form.value = { ...client };
        showEditModal.value = true;
      };

      const deleteClient = async (clientId) => {
        try {
          const result = await Swal.fire({
            title: 'Are you sure?',
            text: 'You won\'t be able to revert this!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!',
            reverseButtons: true,
          });

          if (result.isConfirmed) {
            loading.value = true;
            await axios.delete(`/clients/${clientId}`);
            await fetchClients();
            Swal.fire('Deleted!', 'Your client has been deleted.', 'success');
          }
        } catch (error) {
          Swal.fire('Error', 'There was an error deleting the client.', 'error');
          console.error(error);
        } finally {
          loading.value = false;
        }
      };

      const bulkDeleteClients = async () => {
        try {
          const result = await Swal.fire({
            title: 'Are you sure?',
            text: 'You will delete multiple clients!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete all!',
            cancelButtonText: 'No, cancel!',
            reverseButtons: true,
          });

          if (result.isConfirmed) {
            loading.value = true;
            await axios.post('/clients/bulk-delete', { ids: selectedClientIds.value });
            await fetchClients();
            Swal.fire('Deleted!', 'Selected clients have been deleted.', 'success');
          }
        } catch (error) {
          Swal.fire('Error', 'There was an error deleting the clients.', 'error');
          console.error(error);
        } finally {
          selectedClientIds.value = [];
          loading.value = false;
        }
      };

      const closeModal = () => {
        showAddModal.value = false;
        showEditModal.value = false;
        showViewModal.value = false;
        form.value = { id: null, cin: '', name: '', email: '', phone: '', address: '' };
        selectedClient.value = {};
        errorMessage.value = '';
      };

      onMounted(fetchClients);

      return {
        clients,
        searchCin,
        searchName,
        paginatedClients,
        currentPage,
        totalPages,
        fetchClients,
        changePage,
        searchClients,
        handleSubmit,
        viewClient,
        editClient,
        deleteClient,
        bulkDeleteClients,
        closeModal,
        showAddModal,
        showEditModal,
        showViewModal,
        selectedClient,
        selectedClientIds,
        loading,
        form,
        filterRemainingPrice, // Return the checkbox state
        errorMessage,
      };
    },
  };
</script>


<style scoped>

  .clients {
    padding: 2rem;
  }

  h1 {
    color: #007bff;
  }

  .table th {
    background-color: #343a40;
    color: white;
  }

  .table tbody tr:hover {
    background-color: #f1f1f1;
  }

  .modal {
    display: block; /* Show the modal */
  }

  .modal-dialog {
    max-width: 600px;
  }

  .modal-backdrop {
    z-index: 1040 !important;
  }

  .modal-content {
    z-index: 1100 !important;
  }

  .btn-close {
    background-color: transparent;
    border: none;
  }

  .input-group input {
    border: 1px solid #007bff; /* Change border color to match the button */
    border-radius: 5px; /* Slightly rounded corners */
    transition: border-color 0.3s; /* Smooth transition for border color */
  }

  .input-group input:focus {
    border-color: #0056b3; /* Darker blue when focused */
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5); /* Add a shadow effect */
  }

  .pagination {
  padding: 1rem;
  background-color: #f8f9fa; /* Light background */
  border-radius: 0.5rem; /* Rounded corners */
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1); /* Subtle shadow */
  }

  .page-item {
    margin: 0 0.25rem; /* Space between items */
  }

  .page-link {
    color: #094101; /* Link color */
    text-decoration: none; /* Remove underline */
    border-radius: 0.25rem; /* Rounded links */
    padding: 0.5rem 1rem; /* Padding for larger clickable area */
    transition: background-color 0.2s ease; /* Smooth transition for hover */
  }

  .page-link:hover {
    background-color: #e2e6ea; /* Change background on hover */
  }

</style>

