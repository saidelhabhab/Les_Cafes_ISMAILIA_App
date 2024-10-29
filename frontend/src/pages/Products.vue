<template>
  <div class="container products">
    <h1 class="text-center mb-4">
      <i class="bi bi-box" style="margin-right: 0.5rem;"></i> {{ $t('products.title') }}
    </h1>
    <button class="btn btn-primary mb-3" @click="showAddModal = true">
      <i class="bi bi-plus-circle"></i> {{ $t('products.addNewProduct') }}
    </button>

    <!-- Search Fields -->
    <div class="mb-3 row">
      <div class="col-md-4">
        <div class="input-group">
          <span class="input-group-text">
            <i class="fas fa-search"></i>
          </span>
          <input
            type="text"
            class="form-control"
            v-model="searchTerm"
            :placeholder="$t('products.SearchProductName')"
            @input="fetchProducts"
          />
        </div>
      </div>
    </div>


    <!-- Products Table -->
    <table class="table table-striped">
      <thead class="thead-dark">
        <tr class="table-header">
          <th><i class="bi bi-box"></i> {{ $t('products.name') }}</th>
          <th><i class="bi bi-currency-dollar"></i> {{ $t('products.price') }}</th>
          <th><i class="bi bi-stack"></i> {{ $t('products.quantity') }}</th>
          <th><i class="bi bi-box-seam"></i> {{ $t('products.unit') }}</th> <!-- New Unit Column -->
          <th><i class="bi bi-gear"></i> {{ $t('products.actions') }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="product in products" :key="product.id">
          <td>{{ product.name }}</td>
          <td>{{ product.price }}</td>
          <td>{{ product.quantity }}</td>
          <td>{{ product.unit }}</td> <!-- Display Unit -->
          <td>
            <button class="btn btn-sm btn-info me-2" @click="showProductDetails(product)">
              <i class="bi bi-eye"></i> {{ $t('products.show') }}
            </button>
            <button class="btn btn-sm btn-warning me-2" @click="editProduct(product)">
              <i class="bi bi-pencil"></i> {{ $t('products.edit') }}
            </button>
            <button class="btn btn-sm btn-danger me-2" @click="deleteProduct(product.id)">
              <i class="bi bi-trash"></i> {{ $t('products.delete') }}
            </button>
            <button class="btn btn-sm btn-success" @click="openAddQuantityModal(product)">
              <i class="bi bi-plus"></i> Add New Quantity
            </button>

          </td>
        </tr>
      </tbody>
    </table>


    <!-- Pagination Controls -->
    <nav v-if="totalPages > 1">
      <ul class="pagination justify-content-center">
        <!-- Previous Button -->
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">
            <i class="fas fa-chevron-left"></i>
          </a>
        </li>

        <!-- Page Numbers -->
        <li
          class="page-item"
          v-for="page in totalPages"
          :key="page"
          :class="{ active: currentPage === page }"
        >
          <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
        </li>

        <!-- Next Button -->
        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">
            <i class="fas fa-chevron-right"></i>
          </a>
        </li>
      </ul>
    </nav>


    <!-- Modals for add/edit/view product remain unchanged -->

    <!-- Add Product Modal -->
    <div v-if="showAddModal" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $t('products.modalTitleAdd') }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="handleSubmit">
              <div class="mb-3">
                <label for="name" class="form-label">{{ $t('products.name') }}:</label>
                <input type="text" v-model="form.name" class="form-control" id="name" required />
              </div>
              <div class="mb-3">
                <label for="price" class="form-label">{{ $t('products.price') }} ($):</label>
                <input type="number" v-model.number="form.price" class="form-control" id="price" min="0" step="0.01" required />
              </div>
              <div class="mb-3">
                <label for="quantity" class="form-label">{{ $t('products.quantity') }}:</label>
                <input type="number" v-model.number="form.quantity" class="form-control" id="quantity" min="0" required />
              </div>

              <div class="mb-3">
                <label for="unit" class="form-label">{{ $t('products.unit') }}:</label>
                <select v-model="form.unit" class="form-control" id="unit" required>
                  <option value="ton">Ton</option>
                  <option value="kg">Kg</option>
                  <option value="g">Gram</option>
                </select>
              </div>

              <button type="submit" class="btn btn-success w-100">
                <i class="bi bi-plus-circle"></i> {{ $t('products.addProduct') }}
              </button>
            </form>
            <p v-if="errorMessage" class="text-danger mt-3">{{ errorMessage }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Product Modal -->
    <div v-if="showEditModal" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $t('products.modalTitleEdit') }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="handleSubmit">
              <!-- Product Name -->
              <div class="mb-3">
                <label for="name" class="form-label">{{ $t('products.name') }}:</label>
                <input type="text" v-model="form.name" class="form-control" id="name" required />
              </div>

              <!-- Price -->
              <div class="mb-3">
                <label for="price" class="form-label">{{ $t('products.price') }} ($):</label>
                <input type="number" v-model.number="form.price" class="form-control" id="price" min="0" step="0.01" required />
              </div>

              <!-- Quantity -->
              <div class="mb-3">
                <label for="quantity" class="form-label">{{ $t('products.quantity') }}:</label>
                <input type="number" v-model.number="form.quantity" class="form-control" id="quantity" min="0" required />
              </div>

              <div class="mb-3">
                <label for="unit" class="form-label">{{ $t('products.unit') }}:</label>
                <select v-model="form.unit" class="form-control" id="unit" required>
                  <option value="ton">Ton</option>
                  <option value="kg">Kg</option>
                  <option value="g">Gram</option>
                </select>
              </div>

              <button type="submit" class="btn btn-warning w-100">
                <i class="bi bi-pencil"></i> {{ $t('products.updateProduct') }}
              </button>
            </form>
            <p v-if="errorMessage" class="text-danger mt-3">{{ errorMessage }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- View Product Details Modal -->
    <div v-if="showViewModal" class="modal fade show d-block" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ $t('products.show') }}</h5>
          <button type="button" class="btn-close" @click="closeModal"></button>
        </div>
        <div class="modal-body">
          <img :src="`http://localhost:8002/storage/barcodes/products/${selectedProduct.barcode}.png`" alt="Barcode" width="150" />
          <p class="mt-3"><strong>{{ $t('products.name') }}:</strong> {{ selectedProduct.name }}  {{selectedProduct.barcode}} </p>
          <p><strong>{{ $t('products.price') }}:</strong> ${{ selectedProduct.price }}</p>
          <p><strong>{{ $t('products.quantity') }}:</strong> {{ selectedProduct.quantity }}  {{ selectedProduct.unit   }}</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeModal">{{ $t('products.close') }}</button>
        </div>
      </div>
    </div>
    </div>

    <!-- Modal for adding new quantity -->
    <div v-if="showAddQuantityModal" class="modal-overlay">
      <div class="modal-content">
        <span class="close" @click="closeAddQuantityModal">&times;</span>
        <div class="modal-header">
          <i class="bi bi-box-arrow-in-down"></i>
          <h2>Add New Quantity</h2>
        </div>
        <form @submit.prevent="addQuantity" class="modal-form">
          <div class="form-group">
            <label for="newQuantity">Quantity:</label>
            <input type="number" v-model="newQuantity" required />
          </div>
          <div class="form-group">
            <label for="unit">Unit:</label>
            <select v-model="newUnit" required>
              <option value="ton">Ton</option>
              <option value="kg">Kg</option>
              <option value="g">Gram</option>
            </select>
          </div>
          <button type="submit" class="submit-btn">Add Quantity</button>
        </form>
      </div>
    </div>




  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from '../services/axios';
import Swal from 'sweetalert2';

export default {
  name: 'Products',
  setup() {
    const products = ref([]);
    const showAddModal = ref(false);
    const showEditModal = ref(false);
    const showViewModal = ref(false); // New state for viewing product details
    const selectedProduct = ref(null); // Holds the selected product for viewing
    const showAddQuantityModal = ref(false); // State for Add Quantity modal
    const newQuantity = ref(0); // Quantity input for Add Quantity modal
    const newUnit = ref('ton'); // Default unit for Add Quantity modal
    
    const form = ref({
      id: null,
      name: '',
      price: 0,
      quantity: 0,
      unit: 'ton', // Default unit
    });
    const errorMessage = ref('');
    const searchTerm = ref('');
    const currentPage = ref(1);
    const totalPages = ref(1);
    const itemsPerPage = 8;


    // Fetch products with search and pagination
    const fetchProducts = async (page = 1) => {
      try {
        const params = {
          page,
          limit: itemsPerPage,
        };

        // Add the search term only if it's not empty
        if (searchTerm.value.trim()) {
          params.search = searchTerm.value.trim();
        }

        const response = await axios.get('/products', { params });
        products.value = response.data.items;
        totalPages.value = Math.ceil(response.data.total / itemsPerPage);
        currentPage.value = page; // Set the current page
      } catch (error) {
        console.error('Error fetching products:', error);
      }
    };


      // Handle page changes
    const changePage = (page) => {
      if (page < 1 || page > totalPages.value) return;
      fetchProducts(page);
    };


    // Handle form submission for adding or editing a product
    const handleSubmit = async () => {
      try {
       // console.log("Form submitted with data: ", form.value); // Debugging log
        if (showAddModal.value) {
          // Create new product
          await axios.post('/products', form.value);
          Swal.fire('Success', 'Product added successfully', 'success');
          console.log("form.value==> " ,form.value)
        } else if (showEditModal.value) {
          // Update existing product
          await axios.put(`/products/${form.value.id}`, form.value);
          console.log("form.value==> " ,form.value)
          Swal.fire('Success', 'Product updated successfully', 'success');
        }
        closeModal();       // Close modal after operation
        fetchProducts();     // Refresh the product list
      } catch (error) {
        errorMessage.value = error.response?.data?.message || 'Operation failed';
        Swal.fire('Error', errorMessage.value, 'error');
      }
    };
    
    
    // Pre-fill form for editing a product
    const editProduct = (product) => {
      //console.log("Editing product: ", JSON.stringify(product)); // Log the entire product object

      // Fill the form with product data
      form.value = { 
        id: product.id,
        name: product.name,
        price: product.price,
        quantity: product.quantity,
        unit: product.unit,
        
      };

      showEditModal.value = true;
    };


    const showProductDetails = (product) => {
      selectedProduct.value = product; // Set the selected product
      //console.log(selectedProduct.value)
      showViewModal.value = true; // Open the view modal
    };


    // Delete a product with confirmation
    const deleteProduct = async (id) => {
      Swal.fire({
        title: 'Are you sure?',
        text: 'You won’t be able to revert this!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            await axios.delete(`/products/${id}`);
            fetchProducts();
            Swal.fire('Deleted!', 'The product has been deleted.', 'success');
          } catch (error) {
            Swal.fire('Error', 'Failed to delete the product.', 'error');
          }
        }
      });
    };

    // Open the Add Quantity modal for a specific product
    const openAddQuantityModal = (product) => {
      selectedProduct.value = product;
      showAddQuantityModal.value = true;
    };

    // Close the Add Quantity modal
    const closeAddQuantityModal = () => {
      showAddQuantityModal.value = false;
      newQuantity.value = 0; // Reset quantity
      newUnit.value = 'ton'; // Reset unit to default
    };

    // Handle adding quantity to the selected product
    const addQuantity = async () => {
      if (!selectedProduct.value) return;

      try {
        const updatedQuantity = newQuantity.value;

        try {
          const response = await axios.post(`/products/${selectedProduct.value.id}/add-quantity`, {
            quantity: newQuantity.value,
            unit: newUnit.value,
          });
        //  console.log('Quantity added successfully:', response.data);
        } catch (error) {
          console.error('Error adding quantity:', error.response?.data || error.message);
        }


     // console.log("updatedQuantity ==>" ,updatedQuantity)

        Swal.fire('Success', 'Quantity added successfully', 'success');
        closeAddQuantityModal();
        fetchProducts();
      } catch (error) {
        Swal.fire('Error', 'Failed to add quantity', 'error');
      }
    };

    // Close the modal and reset the form
    const closeModal = () => {
      showAddModal.value = false;
      showEditModal.value = false;
      showViewModal.value = false; // Close the view modal
      selectedProduct.value = null; // Reset selected product
      form.value = { id: null, name: '', description: '', buy_price: 0, price: 0, quantity: 0, categoryId: null, subcategoryId: null };
      errorMessage.value = '';
      showAddQuantityModal.value = false;
    };

    // On component mount, fetch products and categories
    onMounted(() => {
      fetchProducts();
    });

    return {
      products,
      showAddModal,
      showEditModal,
      form,
      showViewModal, // Expose the new state
      selectedProduct, // Expose the selected product
      showProductDetails,
      showAddQuantityModal, // State for Add Quantity modal
      openAddQuantityModal, // Modal open function
      closeAddQuantityModal, // Modal close function
      newQuantity, // Quantity input
      newUnit, // Unit selection
      errorMessage,
      handleSubmit,
      editProduct,
      addQuantity,
      deleteProduct,
      closeModal,
      searchTerm,
      currentPage,
      totalPages,
      fetchProducts,
      changePage,
    };
  },
};

</script>

<style scoped>
  .products {
    padding: 2rem;
  }

  .table {
    margin-top: 1rem;
  }

  .modal {
    display: block;
  }

  /* Add Bootstrap styles for the table and buttons */
  .table-striped tbody tr:nth-of-type(odd) {
    background-color: #f9f9f9;
  }

  .btn-primary,
  .btn-warning,
  .btn-danger,
  .btn-success {
    position: relative;
  }

  .btn i {
    margin-right: 0.5rem;
  }

  .table-header {
    background-color: #f8f9fa; /* Light background color */
    color: #343a40; /* Dark text color */
    font-weight: bold; /* Bold text */
  }

  .table-header th {
    padding: 1rem; /* Padding for better spacing */
    border-bottom: 2px solid #007bff; /* Bottom border */
    background-color: rgb(96, 209, 238);
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
    color: #007bff; /* Link color */
    text-decoration: none; /* Remove underline */
    border-radius: 0.25rem; /* Rounded links */
    padding: 0.5rem 1rem; /* Padding for larger clickable area */
    transition: background-color 0.2s ease; /* Smooth transition for hover */
  }

  .page-link:hover {
    background-color: #e2e6ea; /* Change background on hover */
  }

  .page-item.disabled .page-link {
    color: #6c757d; /* Gray for disabled */
    pointer-events: none; /* Disable clicks */
  }

  .page-item.active .page-link {
    background-color: #007bff; /* Active page background */
    color: white; /* Active page text color */
  }

  .input-group {
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    border-radius: 0.5rem; /* Rounded corners */
  }

  .input-group-text {
    background-color: #007bff; /* Background color for icons */
    color: white; /* Icon color */
    border: none; /* Remove border */
  }

  .form-control,
  .form-select {
    border: 1px solid #ced4da; /* Standard border */
    border-radius: 0.25rem; /* Rounded edges */
  }

  .form-control:focus,
  .form-select:focus {
    border-color: #007bff; /* Highlight border on focus */
    box-shadow: 0 0 0.2rem rgba(0, 123, 255, 0.25); /* Focus shadow */
  }

  .form-control::placeholder {
    color: #6c757d; /* Placeholder text color */
    opacity: 1; /* Ensure opacity */
  }

  .input-group .form-control {
    border-top-left-radius: 0; /* Match rounded edges with icon */
    border-bottom-left-radius: 0; /* Match rounded edges with icon */
  }

  .input-group .form-select {
    border-top-right-radius: 0; /* Match rounded edges with icon */
    border-bottom-right-radius: 0; /* Match rounded edges with icon */
  }


  /* Modal overlay */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

/* Modal content */
.modal-content {
  background: #fff;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  max-width: 400px;
  width: 90%;
  text-align: center;
  position: relative;
}

/* Close button */
.close {
  position: absolute;
  top: 10px;
  right: 15px;
  font-size: 1.5rem;
  color: #888;
  cursor: pointer;
}

/* Modal header with icon */
.modal-header {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  color: #007bff;
  margin-bottom: 1rem;
}

.modal-header i {
  font-size: 2rem;
}

/* Form styles */
.modal-form .form-group {
  margin-bottom: 1rem;
  text-align: left;
}

.modal-form label {
  display: block;
  font-weight: bold;
  margin-bottom: 0.5rem;
}

.modal-form input,
.modal-form select {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.submit-btn {
  background-color: #007bff;
  color: #fff;
  padding: 0.5rem 1.5rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
}

.submit-btn:hover {
  background-color: #0056b3;
}



</style>
