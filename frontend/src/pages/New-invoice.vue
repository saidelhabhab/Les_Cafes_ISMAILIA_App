<template>
  <div class="container new-invoice">
    <h1 class="text-center mb-4">Add New Invoice</h1>

    <!-- Add Invoice Form -->
    <form @submit.prevent="handleSubmit" class="p-4 border rounded shadow">
      <div class="mb-3">
        <label for="client_id" class="form-label">Client:</label>
        <select v-model="form.client_id" class="form-select" id="client_id" required>
          <option disabled value="">Select a client</option>
          <option v-for="client in clients" :key="client.id" :value="client.id">
            {{ client.name }}
          </option>
        </select>
      </div>

      <div class="mb-3">
        <label for="due_date" class="form-label">Due Date:</label>
        <input type="date" style="max-width: 30%" v-model="form.due_date" class="form-control" id="due_date" required />
      </div>

      <div class="mb-3">
        <label for="payment_type" class="form-label">Payment Type:</label>
        <select v-model="form.payment_type" class="form-select" id="payment_type" required>
          <option disabled value="">Select payment type</option>
          <option value="cash">Cash</option>
          <option value="check">Check</option>
        </select>
      </div>

      <!-- Conditionally render the date input for "Check" payment method -->
      <div v-if="form.payment_type === 'check'">
        <label for="checkDate">Date of Cashing a Check:</label>
        <input type="date" v-model="form.checkDate" style="max-width: 30%" class="form-control" id="checkDate" required />
      </div>

      <h5 class="mt-4">Invoice Items</h5>
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Product</th>
            <th>Price ($)</th>
            <th>Quantity</th>
            <th>unit</th>
            <th>Total ($)</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in form.invoice_items" :key="index">
            <td>
              <select v-model="item.product_id" class="form-select" @change="updatePrice(item)" required>
                <option disabled value="">Select a product</option>
                <option 
                  v-for="product in availableProducts(index)" 
                  :key="product.id" 
                  :value="product.id"
                >
                  {{ product.name }} (Q :{{ product.quantity }})
                </option>
              </select>
            </td>
            <td>
              <span>{{ item.price.toFixed(2) }}</span>
            </td>
            
            <td>
              <input 
                type="number" 
                v-model.number="item.quantity" 
                class="form-control" 
                min="1" 
                step="1" 
                required 
              />
            </td>
            <td>
              <select v-model="item.unit" class="form-select" required>
                <option value="ton">Ton</option>
                <option value="kg">Kg</option>
                <option value="g">Gram</option>
              </select>
            </td>
            <td>
              {{ calculateTotal(item) }}
            </td>
            <td>
              <button type="button" class="btn btn-sm btn-danger" @click="removeInvoiceItem(index)">Remove</button>
            </td>
          </tr>
        </tbody>

      </table>

      <button type="button" class="btn btn-secondary" @click="addInvoiceItem">Add Item</button>

      <!-- Amount (Automatically Calculated) -->
      <div class="mb-3">
        <label for="amount" class="form-label">Amount ($):</label>
        <input type="number" v-model.number="amount" class="form-control" id="amount" readonly />
      </div>

      <div class="mb-3">
        <label for="tva" class="form-label">TVA (%):</label>
        <input type="number" v-model.number="form.tva" class="form-control" id="tva" min="0" step="0.01" />
      </div>

      <!-- Total Amount (Automatically Calculated) -->
      <div class="mb-3">
        <label for="total-amount" class="form-label">Total Amount ($):</label>
        <input type="number" v-model.number="totalAmountWithTva" class="form-control" id="total-amount" readonly />
      </div>

      <div class="mb-3">
        <label for="final_price" class="form-label">Final Price ($):</label>
        <input type="number" v-model.number="form.final_price" class="form-control" id="final_price" min="0" step="0.01" required />
      </div>

      <div class="mb-3">
        <label for="remaining_price" class="form-label">Remaining Price ($):</label>
        <input type="number" v-model.number="form.remaining_price" class="form-control" id="remaining_price" min="0" step="0.01" required />
      </div>

      <div class="mb-3">
        <label for="status" class="form-label">Status:</label>
        <select v-model="form.status" class="form-select" id="status" required>
          <option disabled value="">Select status</option>
          <option value="paid">Paid</option>
          <option value="pending">Pending</option>
        </select>
      </div>

      <button type="submit" class="btn btn-success mt-3">Save Invoice</button>

      <p v-if="errorMessage" class="text-danger mt-3">{{ errorMessage }}</p>
    </form>
  </div>
</template>

<script>
  import { ref, onMounted, computed } from 'vue';
  import axios from '../services/axios';
  import Swal from 'sweetalert2';
  import { useRouter } from 'vue-router';


  export default {
    name: 'NewInvoice',
    setup() {
      const clients = ref([]);
      const products = ref([]);
      const router = useRouter();

      const form = ref({
        client_id: '',
        amount: 0,
        total_amount_with_tva: 0,
        status: '',
        due_date: '',
        final_price: 0,
        remaining_price: 0,
        payment_type: '',
        checkDate: '',
        tva: 0,
        invoice_items: [
          {
            product_id: '',
            quantity: 1,
            unit: '', 
            price: 0,
            total: 0,
          },
        ],
      });
      const errorMessage = ref('');

      // Fetch all clients from the API
      const fetchClients = async () => {
        try {
          const response = await axios.get('/clients');
          clients.value = response.data;
        } catch (error) {
          console.error('Error fetching clients:', error);
          Swal.fire('Error', 'Failed to fetch clients.', 'error');
        }
      };

      // Fetch all products from the API
      const fetchProducts = async () => {
        try {
          const response = await axios.get('/products');
          //products.value = response.data;
          products.value = Array.isArray(response.data.items) ? response.data.items : [];
        // console.log('products.value :', products.value);
        } catch (error) {
          console.error('Error fetching products:', error);
          Swal.fire('Error', 'Failed to fetch products.', 'error');
        }
      };

      const addInvoiceItem = () => {
        form.value.invoice_items.push({
          product_id: '',
          unit: ' ', 
          quantity: 1,
          price: 0,
          total: 0,
        });
      };

      const availableProducts = (currentIndex) => {
        const selectedProductIds = form.value.invoice_items
          .filter((_, index) => index !== currentIndex)
          .map(item => item.product_id);
        return products.value.filter(product => !selectedProductIds.includes(product.id));
      };

      const removeInvoiceItem = (index) => {
        if (form.value.invoice_items.length > 1) {
          form.value.invoice_items.splice(index, 1);
        } else {
          Swal.fire('Warning', 'You must have at least one invoice item.', 'warning');
        }
      };
      
      const convertToKg =  (quantity, unit) => {
        if (unit === 'ton') {
          return quantity * 1000; // Convert tons to kilograms
        } else if (unit === 'g') {
          return quantity / 1000; // Convert grams to kilograms
        }
        return quantity; // No conversion needed if unit is kg
      };

      // Calculate total based on converted quantity in kilograms
      const calculateTotal = (item) => {
        const quantityInKg = convertToKg(item.quantity, item.unit); // Removed "this."
        return (quantityInKg * item.price).toFixed(2); // Calculate total with kg-based price
      };


      const updatePrice = (item) => {
        const selectedProduct = products.value.find(product => product.id === item.product_id);
        if (selectedProduct) {
          item.price = parseFloat(selectedProduct.price);
        } else {
          item.price = 0;
        }

        // Update the total whenever the price or quantity is changed
        item.total = parseFloat((item.price * item.quantity).toFixed(2));
      };

      const updateItemTotal = (item) => {
        item.total = calculateTotal(item.quantity, item.price);
      };

      const amount = computed(() => {
        return form.value.invoice_items.reduce((acc, item) => {
          const quantityInKg = convertToKg(item.quantity, item.unit);
          return acc + quantityInKg * item.price;
        }, 0).toFixed(2);
      });

      const totalAmountWithTva = computed(() => {
        const totalAmount = form.value.invoice_items.reduce((sum, item) => {
          const quantityInKg = convertToKg(item.quantity, item.unit);
          return sum + quantityInKg * item.price;
        }, 0);
        const tvaAmount = totalAmount * (form.value.tva / 100);
        return (totalAmount + tvaAmount).toFixed(2);
      });


      // Form validation
      const validateForm = () => {
        if (!form.value.client_id) {
          errorMessage.value = "Please select a client.";
          return false;
        }
        if (!form.value.status) {
          errorMessage.value = "Please select a status.";
          return false;
        }
        if (!form.value.due_date) {
          errorMessage.value = "Please select a due date.";
          return false;
        }
        if (!form.value.payment_type) {
          errorMessage.value = "Please select a payment type.";
          return false;
        }
        if (form.value.payment_type === 'check' && !form.value.checkDate) {
          errorMessage.value = 'Please select a date for cashing the check.';
          return false;
        }
        if (form.value.final_price < 0) {
          errorMessage.value = "Final Price must be a positive number.";
          return false;
        }
        if (form.value.remaining_price < 0) {
          errorMessage.value = "Remaining Price must be a positive number.";
          return false;
        }
        if (form.value.invoice_items.length === 0) {
          errorMessage.value = "Please add at least one invoice item.";
          return false;
        }
        for (const item of form.value.invoice_items) {
          if (!item.product_id) {
            errorMessage.value = "Please select a product for each invoice item.";
            return false;
          }
          if (item.quantity <= 0) {
            errorMessage.value = "Quantity must be greater than 0.";
            return false;
          }
          if (item.price <= 0) {
            errorMessage.value = "Price must be greater than 0.";
            return false;
          }
        }
        errorMessage.value = ''; // Clear any previous error messages
        return true;
      };

      const handleSubmit = async () => {
        // Validate the form before submitting
        if (!validateForm()) {
          return; // Stop the form submission if validation fails
        }

        // Set the computed amounts before submission
        form.value.amount = parseFloat(amount.value);
        form.value.total_amount_with_tva = parseFloat(totalAmountWithTva.value);

        // Remove 'total' from each invoice item before sending
        const payload = {
          ...form.value,
          invoice_items: form.value.invoice_items.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity,
            unit: item.unit,
            price: item.price,
          })),
        };

       // console.log('data ==> :', JSON.stringify(payload));

        try {
          const response = await axios.post('/invoices', payload); // Uncommented Axios request

          Swal.fire('Success', response.data.message, 'success'); // Displays success message

          resetForm();

          // Use router.push instead of this.$router.push
          router.push({ name: 'Invoices' });

        } catch (error) {
          console.error('Error submitting form:', error);
          if (error.response && error.response.data) {
            console.error('Backend error data:', error.response.data);
            if (error.response.data.errors) {
              // Display validation errors from backend
              const errors = Object.values(error.response.data.errors).flat();
              errorMessage.value = errors.join(' ');
            } else if (error.response.data.error) {
              // Display general error message from backend
              errorMessage.value = error.response.data.error;
            } else {
              errorMessage.value = 'Failed to save invoice. Please check your input.';
            }
          } else {
            errorMessage.value = 'Failed to save invoice. Please check your input.';
          }
        }
      };


      const resetForm = () => {
        form.value = {
          client_id: '',
          amount: 0,
          total_amount_with_tva: 0,
          status: '',
          due_date: '',
          checkDate: '',
          final_price: 0,
          remaining_price: 0,
          payment_type: '',
          
          tva: 0,
          invoice_items: [
            {
              product_id: '',
              unit: '', 
              quantity: 1,
              price: 0,
              total: 0,
            },
          ],
        };
        errorMessage.value = '';
      };

      // Fetch data when the component is mounted
      onMounted(() => {
        fetchClients();
        fetchProducts();
      });

      return {
        clients,
        products,
        form,
        errorMessage,
        addInvoiceItem,
        removeInvoiceItem,
        availableProducts,
        calculateTotal,
        updatePrice,
        handleSubmit,
        amount,
        totalAmountWithTva,
        updateItemTotal,
        convertToKg,
      };
    },
  };
</script>


<style scoped>
  .new-invoice {
    padding: 2rem;
  }

  h1 {
    color: #007bff; /* Bootstrap primary color */
  }

  .form-label {
    font-weight: bold;
  }

  .table th {
    background-color: #f8f9fa; /* Light background for table header */
  }

  .is-invalid {
    border-color: #dc3545; /* Bootstrap danger color */
  }

  .is-invalid + .invalid-feedback {
    display: block;
  }
</style>
