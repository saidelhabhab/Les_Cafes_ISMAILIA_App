<template>
  <div class="container new-invoice">
    <h1 class="text-center mb-4">Edit Invoice</h1>

    <!-- Edit Invoice Form -->
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
        <label for="status" class="form-label">Status:</label>
        <select v-model="form.status" class="form-select" id="status" required>
          <option disabled value="">Select status</option>
          <option value="paid">Paid</option>
          <option value="pending">Pending</option>
          <option value="canceled">Canceled</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="due_date" class="form-label">Due Date:</label>
        <input type="date" v-model="form.due_date" class="form-control" id="due_date" required />
      </div>

      <div class="mb-3">
        <label for="payment_type" class="form-label">Payment Type:</label>
        <select v-model="form.payment_type" class="form-select" id="payment_type" required>
          <option disabled value="">Select payment type</option>
          <option value="check">Check</option>
          <option value="cash">Cash</option>
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
            <th>Unit</th>
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
            <td>{{ item.price}}</td>
            <td><input type="number" v-model.number="item.quantity" class="form-control" min="0" step="0.01" required /></td>
            <td>
              <select v-model="item.unit" class="form-select" required>
                <option value="ton">Ton</option>
                <option value="kg">Kg</option>
                <option value="g">Gram</option>
              </select>
            </td>
            <td>{{ calculateItemTotal(item) }}</td>
            <td>
              <button type="button" class="btn btn-sm btn-danger" @click="removeInvoiceItem(index)">Remove</button>
            </td>
          </tr>
        </tbody>
      </table>
      <button type="button" class="btn btn-secondary" @click="addInvoiceItem">Add Item</button>

      <div class="mb-3">
        <label for="amount" class="form-label">Amount ($):</label>
        <input type="number" :value="amount" class="form-control" id="amount" readonly />
      </div>

      <div class="mb-3">
        <label for="tva" class="form-label">TVA (%):</label>
        <input type="number" v-model.number="form.tva" class="form-control" id="tva" min="0" step="0.01" required />
      </div>

      <div class="mb-3">
        <label for="total-amount" class="form-label">Total Amount ($):</label>
        <input type="number" :value="totalAmountWithTva" class="form-control" id="total-amount" readonly />
      </div>

      <div class="mb-3">
        <label for="final_price" class="form-label">Final Price ($):</label>
        <input type="number" v-model.number="form.final_price" class="form-control" id="final_price" min="0" step="0.01" required />
      </div>

      <div class="mb-3">
        <label for="remaining_price" class="form-label">Remaining Price ($):</label>
        <input type="number" v-model.number="form.remaining_price" class="form-control" id="remaining_price" min="0" step="0.01" required />
      </div>

      <button type="submit" class="btn btn-success mt-3">Save Changes</button>

      <p v-if="errorMessage" class="text-danger mt-3">{{ errorMessage }}</p>
    </form>
  </div>
</template>

<script>
  import { ref, onMounted, computed } from 'vue';
  import axios from '../services/axios';
  import Swal from 'sweetalert2';
  import { useRoute, useRouter } from 'vue-router';

   export default {
    name: 'EditInvoice',
    setup() {
      const clients = ref([]);
      const products = ref([]);
      const route = useRoute();
      const router = useRouter();
      const invoiceId = ref(route.params.id);

      const form = ref({
        client_id: '',
        amount: 0,
        status: '',
        due_date: '',
        final_price: 0,
        remaining_price: 0,
        payment_type: '',
        tva: 0,
        invoice_items: [
          
        ],
      });
      const errorMessage = ref('');

      const fetchClients = async () => {
        try {
          const response = await axios.get('/clients');
          clients.value = response.data;
        } catch (error) {
          Swal.fire('Error', 'Failed to fetch clients.', 'error');
        }
      };

      const fetchProducts = async () => {
        try {
          const response = await axios.get('/products');
          products.value = response.data.items;
        } catch (error) {
          Swal.fire('Error', 'Failed to fetch products.', 'error');
        }
      };

      const fetchInvoiceData = async () => {
        try {
          const response = await axios.get(`/invoices/${invoiceId.value}`);
          form.value = { ...response.data, invoice_items: response.data.invoice_items || [] };
         // console.log("old data", JSON.stringify(form.value ))
        } catch (error) {
          Swal.fire('Error', 'Failed to fetch invoice data.', 'error');
        }
      };

      const addInvoiceItem = () => {
        form.value.invoice_items.push({  
          id: null, // New item, so no ID yet
          product_id: '', 
          quantity: 1, 
          unit: 'kg', 
          price: 0 });
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

      const convertToKg = (quantity, unit) => {
        return unit === 'ton' ? quantity * 1000 : unit === 'g' ? quantity / 1000 : quantity;
      };

      const calculateItemTotal = (item) => {
        const quantityInKg = convertToKg(item.quantity, item.unit);
        const total = (quantityInKg * item.price).toFixed(2);
        // Set the total in the item object
        item.total = total; 
        return total;
      };

      const updatePrice = (item) => {
        const selectedProduct = products.value.find((product) => product.id === item.product_id);
        item.price = selectedProduct ? parseFloat(selectedProduct.price) : 0;
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

      const handleSubmit = async () => {
        // Calculate totals for all invoice items
        form.value.invoice_items.forEach(item => {
          calculateItemTotal(item);
        });

          // Set the calculated amount to form
         form.value.amount = amount.value; // Set the computed amount

          // Set the total amount with TVA
         form.value.total_amount_with_tva = totalAmountWithTva.value; // Set this value before sending
        try {
          
         // console.log("Form Data:", JSON.stringify(form.value));

          // Send the form data to the server
          await axios.put(`/invoices/${invoiceId.value}`, form.value);
          Swal.fire('Success', 'Invoice updated successfully!', 'success');
          // Use router.push instead of this.$router.push
          router.push({ name: 'Invoices' });
        } catch (error) {
          console.error("Update Error:", error); // Log error for debugging
          errorMessage.value = error.response?.data?.error || 'Failed to update invoice. Please check your input.';
        }
      };


      onMounted(() => {
        fetchClients();
        fetchProducts();
        fetchInvoiceData();
      });

      return {
        clients,
        products,
        form,
        errorMessage,
        addInvoiceItem,
        removeInvoiceItem,
        calculateItemTotal,
        updatePrice,
        amount,
        totalAmountWithTva,
        handleSubmit,
        availableProducts
      };
    },
   };
</script>


<style scoped>
.new-invoice {
  max-width: 800px;
  margin: auto;
}
</style>
