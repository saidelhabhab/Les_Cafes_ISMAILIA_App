<template>
  <div class="container returns-container">
    <h2 class="my-4 text-center"><i class="fas fa-undo"></i> {{ $t('returns.title') }}</h2>

    <!-- Existing Returns -->
    <section class="return-list mb-5">
      <h3><i class="fas fa-list"></i> {{ $t('returns.existingReturns') }}</h3>
      <table class="table table-bordered table-hover">
        <thead class="thead-dark bg-dark text-white">
          <tr>
            <th>
              <i class="fas fa-receipt"></i> {{ $t('returns.returnId') }}
            </th>
            <th>
              <i class="fas fa-file-invoice"></i> {{ $t('returns.invoiceId') }}
            </th>
            <th>
              <i class="fas fa-box-open"></i> {{ $t('returns.product') }}
            </th>
            <th>
              <i class="fas fa-sort-numeric-up-alt"></i> {{ $t('returns.quantity') }}
            </th>
            <th>
              <i class="fas fa-calendar-alt"></i> {{ $t('returns.date') }}
            </th>
            <th>
              <i class="fas fa-cogs"></i> {{ $t('returns.actions') }}
            </th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="returnItem in returns" :key="returnItem.id">
            <td>{{ returnItem.id }}</td>
            <td>{{ returnItem.invoice_id }}</td>
            <td>{{ returnItem.product_id }}</td>
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
    </section>

    <!-- Create Return Form -->
    <section class="create-return">
      <h3><i class="fas fa-plus-circle"></i> {{ $t('returns.createReturn') }}</h3>
      <form @submit.prevent="handleCreateReturn">
        <div class="form-group">
          <label for="invoice">{{ $t('returns.selectInvoice') }}:</label>
          <select
            id="invoice"
            v-model="selectedInvoice"
            class="form-control"
            @change="fetchInvoiceProducts"
            required
          >
            <option disabled value="">{{ $t('returns.selectInvoicePlaceholder') }}</option>
            <option v-for="invoice in invoices" :key="invoice.id" :value="invoice.id">
              {{ $t('returns.invoiceLabel', { id : invoice.factor_code , name: invoice.client.name, date: formatDate(invoice.created_at) }) }}
            </option>
          </select>
        </div>

        <!-- List products in returnItems -->
        <div class="form-group product-item border rounded p-3 m-3" v-for="(item, index) in returnItems" :key="index">
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
              {{ product.name }} ({{ $t('returns.available') }}: {{ product.quantity }} {{ product.unit }})
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
        <div class="form-group">
          <h4>{{ $t('returns.totalPriceWithTVA') }}: {{ totalPriceWithTVA.toFixed(2) }} {{ $t('returns.dh') }}</h4>
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
import { ref, onMounted } from 'vue';
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

            }));

        //  console.log("Initialized returnItems:", JSON.stringify(returnItems.value, null, 2));

        } catch (error) {
          console.error('Error fetching products for the selected invoice:', error);
        }
      } else {
        selectedProducts.value = [];
        returnItems.value = [{ product_id: '', quantity: 1, invoice_item_id: null, invoice_id: null }]; // Ensure invoice_id is also included here
        totalPriceWithTVA.value = 0;
        tvaRate.value = 0;
      }
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

    // Remove a return item and update totalPriceWithTVA
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
          totalPriceWithTVA.value -= removedItem.quantity * product.price;
        }

        if (removedItem.invoice_item_id) {
          removedItems.value.push({
            invoice_item_id: removedItem.invoice_item_id, 
            product_id: removedItem.product_id, 
            quantity: removedItem.quantity,
            price : removedItem.price,
            unit : removedItem.unit
          });
        } else {
          console.error('Removed item does not have an invoice_item_id:', removedItem);
        }

          // Update showSubmitReturn based on remaining return items
          showSubmitReturn.value = returnItems.value.length > 0;

        returnItems.value.splice(index, 1);
        Swal.fire('Removed!', 'The item has been removed.', 'success');
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
        console.log("Removed Items Debug:", JSON.stringify(removedItems.value, null, 2));

        // Validate if removedItems have product_id and invoice_item_id
        const validRemovedItems = removedItems.value.filter(item => {
          console.log(`Processing item:`, item);
          return item.product_id && item.invoice_item_id;
        });

        // Log validRemovedItems
        console.log("Valid Removed Items:", JSON.stringify(validRemovedItems, null, 2));

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

       console.log("Payload to be sent:", JSON.stringify(payload));

        // Step 4: Make the API request to create a return
        const createResponse = await axios.post('/returns', payload);
        
        // Success message after creating the return
        Swal.fire('Success', `Created successfully!`, 'success');

        // Step 5: Remove old invoice items (if any)
       if (payload.deleted_invoice_item_ids.length > 0) {
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
          
          

        // Clear form and messages
        selectedInvoice.value = '';
        returnItems.value = [{ product_id: '', quantity: 1, invoice_item_id: null,price:'' }];
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

    return {
      returns,
      invoices,
      selectedInvoice,
      returnItems,
      selectedProducts,
      successMessage,
      errorMessage,
      totalPriceWithTVA,
      handleCreateReturn,
      getProductNameById,
      fetchInvoiceProducts,
      removeReturnItem,
      getMaxQuantity,
      updateQuantity,
      formatDate,
      returnToStock, // Add the new method here
      showSubmitReturn, // Expose the variable for template usage
      formatDate,
      viewReturn
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
</style>