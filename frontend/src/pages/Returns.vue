<template>
  <div class="container returns-container">
    <h2 class="my-4 text-center" style="color: brown;"><i class="fas fa-undo"></i> {{ $t('returns.title') }}</h2>


    <!-- Search Section -->
    <section class="search-section my-4">
      <h4>{{ $t('returns.search') }}</h4>
      <div class="form-row align-items-end mb-3">
        <!-- Search by Product Name -->
        <div class="form-group col-md-3">
          <input
            type="text"
            v-model="searchProduct"
            class="form-control"
            :placeholder="$t('returns.searchProduct')"
          />
        </div>

        <!-- Search by Start Date -->
        <div class="form-group col-md-3">
          <label for="">
            <h5>{{ $t('returns.startDate') }}</h5>
            <input
              type="date"
              v-model="searchStartDate"
              class="form-control"
              :placeholder="$t('returns.startDate')"
            /> 
          </label>
        </div>

        <!-- Search by End Date -->
        <div class="form-group col-md-3">
          <label for="">
            <h5>{{ $t('returns.endDate') }}</h5>
            <input
              type="date"
              v-model="searchEndDate"
              class="form-control"
              :placeholder="$t('returns.endDate')"
            />
          </label>
        </div>

        <!-- Clear Button -->
        <div class="form-group col-md-3">
          <button class="btn btn-secondary btnClear" @click="clearSearch">{{ $t('returns.removeReturnItem') }}</button>
        </div>
      </div>
    </section>



    <!-- Existing Returns -->
    <section class="return-list mb-5">
      <h3><i class="fas fa-list"></i> {{ $t('returns.existingReturns') }}</h3>
      <table class="table table-bordered table-hover">
        <thead class="thead-dark bg-dark text-white">
          <tr>
            <th><i class="fas fa-receipt"></i> {{ $t('returns.returnId') }}</th>
            <th><i class="fas fa-file-invoice"></i> {{ $t('returns.invoiceId') }}</th>
            <th><i class="fas fa-box-open"></i> {{ $t('returns.product') }}</th>
            <th><i class="fas fa-sort-numeric-up-alt"></i> {{ $t('returns.quantity') }}</th>
            <th><i class="fas fa-calendar-alt"></i> {{ $t('returns.date') }}</th>
            <th><i class="fas fa-cogs"></i> {{ $t('returns.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="returnItem in paginatedReturns" :key="returnItem.id">
            <td>{{ returnItem.id }}</td>
            <td>{{ getProductBarcodeById(returnItem.product_id) }}</td>
            <td>{{ getProductNameById(returnItem.product_id) }}</td>
            <td>{{ returnItem.quantity }}</td>
            <td>{{ formatDate(returnItem.created_at) }}</td>
            <td>
              <button class="btn btn-info btn-sm" @click="viewReturn(returnItem.id)">
                <i class="fas fa-eye"></i> {{ $t('returns.view') }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
          <li class="page-item" :class="{ disabled: currentPage === 1 }">
            <a class="page-link" @click="changePage(currentPage - 1)" aria-label="Previous">
              <span aria-hidden="true">&laquo;</span>
            </a>
          </li>
          <li v-for="page in totalPages" :key="page" class="page-item" :class="{ active: page === currentPage }">
            <a class="page-link" @click="changePage(page)">{{ page }}</a>
          </li>
          <li class="page-item" :class="{ disabled: currentPage === totalPages }">
            <a class="page-link" @click="changePage(currentPage + 1)" aria-label="Next">
              <span aria-hidden="true">&raquo;</span>
            </a>
          </li>
        </ul>
      </nav>
    </section>


    <!-- Create Return Form -->
    <section class="create-return mb-5">
      <h3 style="color: #225203"><i class="fas fa-plus-circle"></i> {{ $t('returns.createReturn') }}</h3>
      <form @submit.prevent="handleCreateReturn">
        <div class="form-group">
          <label for="invoice">{{ $t('returns.selectInvoice') }}</label>
          <select
            id="invoice"
            v-model="selectedInvoice"
            class="form-control"
            @change="fetchInvoiceProducts"
            required
          >
            <option disabled value="">{{ $t('returns.selectInvoicePlaceholder') }}</option>
            <option v-for="invoice in invoices" :key="invoice.id" :value="invoice.id">
              {{ $t('returns.invoiceLabel', { id : invoice.factor_code , name: invoice.client.name, date: formatDate(invoice.due_date) }) }}
            </option>
          </select>
        </div>

        <!-- List products in returnItems -->
        <div v-if="selectedInvoice" class="form-group product-item border rounded p-3 m-3" v-for="(item, index) in returnItems" :key="index">
          <label>{{ $t('returns.product') }}:</label>
          <select
            v-model="item.product_id"
            @change="updateQuantity(index)"
            class="form-control"
            required
          >
            <option disabled value="">{{ $t('returns.selectProduct') }}</option>
            <option
              v-for="product in selectedProducts"
              :key="product.id"
              :value="product.id"
            >
              {{ product.name }} ({{ $t('returns.available') }}: {{ product.quantity }})
            </option>
          </select>

          <!-- Input for Return Quantity -->
          <label>{{ $t('returns.returnQuantity') }}:</label>
          <div class="quantity-input mt-2">
            <input
              type="number"
              v-model.number="item.return_quantity"
              :max="item.quantity" 
              class="form-control mt-2"
              :placeholder="$t('returns.quantityPlaceholder')"
            />
          </div>

          <!-- New Input and Button to Return the Product to Stock -->
          <button
            type="button"
            class="btn btn-success btn-sm mt-2 m-2"
            @click="returnToStock(item.invoice_id,item.product_id, item.return_quantity, item.invoice_item_id, item.quantity,item.price)"
          >
            <i class="fas fa-arrow-alt-circle-up"></i> {{ $t('returns.returnToStock', { quantity: item.return_quantity }) }}
          </button>

          <button
            type="button"
            class="btn btn-danger btn-sm mt-2 m-2"
            @click="removeReturnItem(index)"
          >
            <i class="fas fa-trash-alt"></i> {{ $t('returns.removeReturnItem') }}
          </button>
        </div>

        <!-- Display Total Price (with TVA) -->
        <div class="form-group mt-5">
          <h4>{{ $t('returns.totalPriceWithTVA') }} <strong style="color:brown">{{ totalPriceWithTVA.toFixed(2) }} {{ $t('returns.dh') }} </strong> </h4>
        </div>

        <!-- Conditionally Render Submit Return Button -->
        <button 
          v-if="showSubmitReturn" 
          type="submit" 
          class="btn btn-primary mt-3"
        >
          <i class="fas fa-check"></i> {{ $t('returns.submitReturn') }}
        </button>
      </form>

      <div v-if="successMessage" class="alert alert-success mt-3">
        <i class="fas fa-check-circle"></i> {{ returns.successMessage }}
      </div>
      <div v-if="errorMessage" class="alert alert-danger mt-3">
        <i class="fas fa-exclamation-circle"></i> {{ returns.errorMessage }}
      </div>
    </section>
  </div>
</template>


<script>
import { ref, onMounted ,computed} from 'vue';
import axios from '../services/axios'; // Ensure this path is correct
import Swal from 'sweetalert2';
import { useRouter } from 'vue-router';

export default {
  methods: {
  },
  name: 'Returns',
  setup() {
    const returns = ref([]);
    const invoices = ref([]);
    const selectedInvoice = ref('');
    const returnItems = ref([{ product_id: '', quantity: 1, invoice_item_id: null }]);
    const selectedProducts = ref([]);
    const successMessage = ref('');
    const errorMessage = ref('');
    const totalPriceWithTVA = ref(0); // Track the total price with TVA
    const tvaRate = ref(0); // TVA rate from invoice
    const router = useRouter();
    const removedItems = ref([]); // To track removed invoice item IDs
    const showSubmitReturn = ref(false);
    const products = ref([]); // Store all products here
    const searchProduct = ref('');
    const searchStartDate = ref(null);
    const searchEndDate = ref(null);
    const currentPage = ref(1);
    const itemsPerPage = ref(6); // Adjust items per page as needed


    // Fetch existing returns
    const fetchReturns = async () => {
      try {
        const response = await axios.get('/returns');
        returns.value = response.data;
       // console.log('fetching returns:', response.data);
      } catch (error) {
        console.error('Error fetching returns:', error);
      }
    };

    // Fetch all invoices
    const fetchInvoices = async () => {
      try {
        const response = await axios.get('/invoices');
        invoices.value = response.data;
      } catch (error) {
        console.error('Error fetching invoices:', error);
      }
    };


   // Fetch all products and store them
   const fetchProducts = async () => {
      try {
        const response = await axios.get('/products'); // Fetch products data
        if (response.data && Array.isArray(response.data.items)) {
          products.value = response.data.items; // Extract the 'items' array containing products
        } else {
          console.error('Expected an array of products under "items", got:', response.data);
        }
      } catch (error) {
        console.error('Error fetching products:', error);
      }
    };

    // Get product name by ID from products list
    const getProductNameById = (productId) => {
      const product = products.value.find((p) => p.id === productId);
      return product ? product.name : `Product ${productId}`;
    };

    // Get product barcode by ID from products list
    const getProductBarcodeById = (productId) => {
      const product = products.value.find((p) => p.id === productId);
      return product ? product.barcode : `Barcode not available`;
    };

    // Fetch products associated with the selected invoice
    const fetchInvoiceProducts = async () => {
      if (selectedInvoice.value) {
        try {
          const response = await axios.get(`/invoices/${selectedInvoice.value}`);
          const invoice = response.data;

          // Set the TVA rate
          tvaRate.value = parseFloat(invoice.tva) / 100; // Convert percentage to decimal

          // Set the total price with TVA
          totalPriceWithTVA.value = parseFloat(invoice.total_amount_with_tva);

          // Map invoice items to selectedProducts
          selectedProducts.value = invoice.invoice_items.map((item) => ({
            id: item.product_id,
            name:  getProductNameById(item.product_id),
            quantity: parseFloat(item.quantity),
            price: parseFloat(item.price),
            unit: item.unit,
          }));

         // console.log("Selected Products:", JSON.stringify(selectedProducts.value, null, 2));

          // Initialize returnItems with the desired structure, including invoice.id
          returnItems.value = invoice.invoice_items
            .filter((item) => parseFloat(item.quantity) > 0)
            .map((item) => ({
              product_id: item.product_id,
              quantity: parseFloat(item.quantity),
              invoice_item_id: item.id, // Ensure 'id' is the correct invoice_item_id
              invoice_id: invoice.id, // Add the invoice ID here
              price: parseFloat(item.price),
              unit: item.unit

            }));

        //  console.log("Initialized returnItems:", JSON.stringify(returnItems.value, null, 2));

        } catch (error) {
          console.error('Error fetching products for the selected invoice:', error);
        }
      } else {
        selectedProducts.value = [];
        returnItems.value = [{ product_id: '', quantity: 1, invoice_item_id: null, invoice_id: null ,unit:''}]; // Ensure invoice_id is also included here
        totalPriceWithTVA.value = 0;
        tvaRate.value = 0;
      }
    };


     // Computed property for filtered returns
    const filteredReturns = computed(() => {
      return returns.value.filter((returnItem) => {
        const productName = getProductNameById(returnItem.product_id).toLowerCase();
        const searchProductValue = searchProduct.value.toLowerCase();

        const matchesProduct = productName.includes(searchProductValue);

        const itemDate = new Date(returnItem.created_at);
        const startDate = searchStartDate.value ? new Date(searchStartDate.value) : null;
        const endDate = searchEndDate.value ? new Date(searchEndDate.value) : null;

        const withinDateRange =
          (!startDate || itemDate >= startDate) &&
          (!endDate || itemDate <= endDate);

        return matchesProduct && withinDateRange;
      });
    });

    // Computed property for total pages
    const totalPages = computed(() => {
      return Math.ceil(filteredReturns.value.length / itemsPerPage.value);
    });

    // Computed property for paginated returns
    const paginatedReturns = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage.value;
      return filteredReturns.value.slice(start, start + itemsPerPage.value);
    });

    const changePage = (page) => {
      if (page < 1 || page > totalPages.value) return;
      currentPage.value = page;
    };



    onMounted(() => {
      fetchReturns();
      fetchInvoices();
      fetchProducts();
    });

  

     // Method to handle returning products to stock
    const returnToStock = async (invoice_id, product_id, return_quantity, invoice_item_id, original_quantity,price) => {
   
      //console.log("data ==>" ,invoice_id, product_id, return_quantity, invoice_item_id, original_quantity,price)
      try {
        if (return_quantity <= 0) {
          Swal.fire('Error', 'Please enter a valid quantity to return.', 'error');
          return;
        }

        // Check if return_quantity is equal to original_quantity
        if (return_quantity === original_quantity) {
          Swal.fire('Notice', 'You are returning the full quantity. Please use the "Remove" button instead.', 'info');
          return;
        }

         // Check if the return quantity exceeds the available stock quantity
        if (return_quantity > original_quantity) {
          Swal.fire('Notice', 'Return quantity exceeds the available quantity in stock.', 'info');
          return;
        }


        // Send request to the API to return product to stock along with invoice item data
        const response = await axios.post('/return-quantity', {
          invoice_id,
          product_id,
          quantity: return_quantity,
          invoice_item_id, // Include invoice item ID in the request
          price,
        });

        //console.log("data==>",response.data)
          // Refresh the page
          window.location.reload();

        if (response.status === 200) {
          Swal.fire('Success', 'Product returned to stock successfully!', 'success');
        } else {
          Swal.fire('Error', 'Failed to return product to stock.', 'error');
        }
      } catch (error) {
        console.error('Error details:', error);  // Log the full error object
        Swal.fire('Error', error.response?.data?.message || 'An error occurred.', 'error');
      }
    };

    // Inside the removeReturnItem method
    const removeReturnItem = async (index) => {
      const result = await Swal.fire({
        title: 'Are you sure?',
        text: 'You are about to remove this item from the return list.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, remove it!',
        cancelButtonText: 'No, keep it'
      });

      if (result.isConfirmed) {
        const removedItem = returnItems.value[index];
        const product = selectedProducts.value.find(p => p.id === removedItem.product_id);

        if (product) {
          // Calculate the item total price based on quantity in kg
          const itemTotalPrice = removedItem.price * removedItem.quantity; // Adjusted to reflect the original calculation
          // Deduct item total price from totalPriceWithTVA
          totalPriceWithTVA.value -= itemTotalPrice;

          // Deduct TVA if applicable
          const tvaAmount = tvaRate.value > 0 ? itemTotalPrice * tvaRate.value : 0;
          totalPriceWithTVA.value -= tvaAmount; // Deduct TVA

          // Add removed item details to removedItems
          if (removedItem.invoice_item_id) {
            removedItems.value.push({
              invoice_item_id: removedItem.invoice_item_id, 
              product_id: removedItem.product_id, 
              quantity: removedItem.quantity,
              price: removedItem.price,
              unit: removedItem.unit
            });
          } else {
            console.error('Removed item does not have an invoice_item_id:', removedItem);
          }

          // Update showSubmitReturn based on remaining return items
          showSubmitReturn.value = returnItems.value.length > 0;

          returnItems.value.splice(index, 1);
          Swal.fire('Removed!', 'The item has been removed.', 'success');
        }
      }
    };

      // Get the maximum available quantity for a product
    const getMaxQuantity = (product_id) => {
        const product = selectedProducts.value.find((p) => p.id === product_id);
        return product ? product.quantity : 0;
    };

      // Update quantity ensuring it does not exceed the available quantity
      const updateQuantity = (index) => {
        const product_id = returnItems.value[index].product_id;
        const product = selectedProducts.value.find((p) => p.id === product_id);
        if (product) {
          // Ensure the quantity does not exceed the available quantity
          const newQuantity = Math.min(returnItems.value[index].quantity, product.quantity);
          // Calculate the difference in quantity
          const quantityDifference = newQuantity - returnItems.value[index].quantity;
          // Update the total price with TVA accordingly
          totalPriceWithTVA.value += quantityDifference * product.price * (1 + tvaRate.value);
          // Update the quantity
          returnItems.value[index].quantity = newQuantity;
        }
      };

    // Handle the creation of a return
    const handleCreateReturn = async () => {

      successMessage.value = '';
      errorMessage.value = '';

      try {
        // Step 1: Check removedItems for valid data
      //  console.log("Removed Items Debug:", JSON.stringify(removedItems.value, null, 2));

        // Validate if removedItems have product_id and invoice_item_id
        const validRemovedItems = removedItems.value.filter(item => {
          console.log(`Processing item:`, item);
          return item.product_id && item.invoice_item_id;
        });

        // Log validRemovedItems
      // console.log("Valid Removed Items:", JSON.stringify(validRemovedItems, null, 2));

        // Step 2: Ensure there are valid items to process
        if (validRemovedItems.length === 0) {
          console.error("No valid items found for removal or return.");
          Swal.fire('Error', 'No valid items to process.', 'error');
          return;
        }

        // Step 3: Create return and add valid items
        const payload = {
          invoice_id: selectedInvoice.value,
          items: validRemovedItems.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity || 0,  // Default to 0 if quantity is missing
            price: item.price,
            unit: item.unit
          })),
          total_amount_with_tva: totalPriceWithTVA.value.toFixed(2),
          deleted_invoice_item_ids: validRemovedItems.map(item => item.invoice_item_id),  // Using invoice_item_id here
        };

      // console.log("Payload to be sent:", JSON.stringify(payload));

        // Step 4: Make the API request to create a return
        const createResponse = await axios.post('/returns', payload);
        
        // Success message after creating the return
        Swal.fire('Success', `Created successfully!`, 'success');

        // Step 5: Remove old invoice items (if any)
      /* if (payload.deleted_invoice_item_ids.length > 0) {
          const deleteResponse = await axios.delete('/invoice-items', {
            data: {
              invoice_id: selectedInvoice.value,
              deleted_invoice_item_ids: payload.deleted_invoice_item_ids,
            }
            
          });

          if (deleteResponse.status === 200) {
            Swal.fire('Success', 'Old invoice items deleted successfully.', 'success');
             // Refresh the page
          window.location.reload();

          }
        }
        */
        
        // Clear form and messages
        selectedInvoice.value = '';
        returnItems.value = [{ product_id: '', quantity: 1, invoice_item_id: null,price:'',unit:'' }];
        successMessage.value = `Return created successfully!`;
        fetchReturns(); // Refresh the returns list
      } catch (error) {
        console.error("Error:", error.response?.data || error.message);

        // Show error message if something goes wrong
        Swal.fire('Error', error.response?.data?.message || 'An error occurred.', 'error');
      }
    };

    const viewReturn = (id) => {
      // Log the ID to ensure it's correct
      console.log("Navigating to return ID:", id);
      // Navigate to the ViewReturn page with the selected return ID
      router.push({ name: 'ViewReturn', params: { id } });
    };
    // Format date for display
    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('fr-FR');
    };



    const clearSearch = () => {
      searchProduct.value = '';
      searchStartDate.value = null;
      searchEndDate.value = null;
    };


    return {
      returns,
      invoices,
      clearSearch,
      selectedInvoice,
      returnItems,
      selectedProducts,
      successMessage,
      errorMessage,
      totalPriceWithTVA,
      handleCreateReturn,
      getProductNameById,
      getProductBarcodeById,
      fetchInvoiceProducts,
      removeReturnItem,
      getMaxQuantity,
      updateQuantity,
      formatDate,
      returnToStock, // Add the new method here
      showSubmitReturn, // Expose the variable for template usage
      formatDate,
      viewReturn,
      searchProduct,
      searchStartDate,
      searchEndDate,
      filteredReturns,
      filteredReturns,
      paginatedReturns,
      currentPage,
      totalPages,
      itemsPerPage,
      changePage,
      clearSearch,
    };
  },
};
</script>


<style>
.returns-container {
  background-color: #f8f9fa;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.return-list {
  background-color: #ffffff;
  border-radius: 10px;
  padding: 15px;
}

.create-return {
  background-color: #ffffff;
  border-radius: 10px;
  padding: 15px;
}

.btn {
  display: flex;
  align-items: center;
}

.btn i {
  margin-right: 5px;
}

.table th, .table td {
  vertical-align: middle;
}

.product-item {
  border: 1px solid #007bff; /* Blue border */
  background-color: #f9f9f9; /* Light background for contrast */
  transition: box-shadow 0.3s;
}

.product-item:hover {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Shadow on hover for depth */
}

thead.thead-dark {
  background-color: #343a40;
  color: white;
}

thead th {
  padding: 12px;
  text-align: left;
  font-size: 16px;
}

thead th i {
  margin-right: 8px;
}

.search-section {
  background-color: #f8f9fa; /* Light background */
  padding: 20px; /* Padding around the section */
  border-radius: 5px; /* Rounded corners */
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.473); /* Subtle shadow */
}

.search-section h4 {
  margin-bottom: 15px; /* Space below the heading */
}

.search-section .form-row {
  display: flex; /* Use flexbox for alignment */
  align-items: flex-end; /* Align items to the bottom */
  margin-bottom: 15px; /* Space between the form row and button */
}

.search-section .form-group {
  margin-right: 10px; /* Space between form groups */
}

.search-section .btn {
  width: 50%; /* Full-width button */
  background-color: rgb(87, 3, 21);
}



</style>