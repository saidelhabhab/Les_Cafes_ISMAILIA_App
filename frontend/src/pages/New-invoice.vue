<template>
  <div class="container new-invoice">
    <h1 class="text-center mb-4">{{  $t('invoices.addNewInvoice') }}</h1>

    <!-- Add Invoice Form -->
    <form @submit.prevent="handleSubmit" class="p-4 border rounded shadow">
      <div class="mb-3">
        <label for="client_id" class="form-label">
          <i class="bi bi-person-badge me-2"></i> {{  $t('invoices.client') }}
        </label>
        <div class="d-flex align-items-center">
          <select v-model="form.client_id" class="form-select" id="client_id" required>
            <option disabled value="">{{ $t('invoices.selectClient')}}</option>
            <option v-for="client in clients" :key="client.id" :value="client.id">
              {{ client.name }}
            </option>
          </select>
          <button type="button" class="btn custom-button ms-3" @click="showAddModal = true">
            <i class="bi bi-plus-circle me-1"></i> {{  $t('clients.addNewClient') }}
          </button>
        </div>
      </div>

      <!-- Add/Edit Client Modal -->
      <div
        v-if="showAddModal"
        class="modal fade show d-block"
        tabindex="-1"
        aria-modal="true"
        role="dialog"
      >
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="bi bi-person-plus me-2"></i>
                {{ showAddModal ? $t('clients.modalTitleAdd') : $t('clients.modalTitleEdit') }}
              </h5>
              <button type="button" class="btn-close" @click="closeModal"></button>
            </div>
            <div class="modal-body">
              <form @submit.prevent="addNewClient">
                <div class="mb-3">
                  <label for="cin" class="form-label">
                    <i class="bi bi-card-text me-2"></i>{{ $t('clients.cin') }}:
                  </label>
                  <input type="text" v-model="newClient.cin" class="form-control" id="cin" required />
                </div>
                <div class="mb-3">
                  <label for="name" class="form-label">
                    <i class="bi bi-person me-2"></i>{{ $t('clients.name') }}:
                  </label>
                  <input type="text" v-model="newClient.name" class="form-control" id="name" required />
                </div>
                <div class="mb-3">
                  <label for="email" class="form-label">
                    <i class="bi bi-envelope me-2"></i>{{ $t('clients.email') }}:
                  </label>
                  <input type="email" v-model="newClient.email" class="form-control" id="email" required />
                </div>
                <div class="mb-3">
                  <label for="phone" class="form-label">
                    <i class="bi bi-telephone me-2"></i>{{ $t('clients.phone') }}:
                  </label>
                  <input type="tel" v-model="newClient.phone" class="form-control" id="phone" required />
                </div>
                <div class="mb-3">
                  <label for="address" class="form-label">
                    <i class="bi bi-geo-alt me-2"></i>{{ $t('clients.address') }}:
                  </label>
                  <textarea v-model="newClient.address" class="form-control" id="address"></textarea>
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


      <div class="mb-3" style="width: 30%;">
        <label for="due_date" class="form-label">{{  $t('invoices.dueDate') }}</label>
        <input type="date" v-model="form.due_date" class="form-control" id="due_date" required />
      </div>

      <div class="mb-3" >
        <label for="payment_type" class="form-label">{{  $t('invoices.paymentType') }}</label>
        <select style="width: 30%;" v-model="form.payment_type" class="form-select" id="payment_type" required>
          <option disabled value="">{{  $t('invoices.selectPaymentType') }}</option>
          <option value="cash">{{  $t('invoices.cash') }}</option>
          <option value="check">{{  $t('invoices.check') }}</option>
        </select>
      </div>

      <!-- Conditionally render the date input for "Check" payment method -->
      <div v-if="form.payment_type === 'check'">
        <label for="checkDate"  class="form-label">{{  $t('invoices.checkDate') }}</label>
        <input type="date" v-model="form.checkDate" style="max-width: 30%" class="form-control" id="checkDate" required />
      </div>

      <h5 class="mt-4">{{  $t('invoices.invoiceItems') }}</h5>
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>{{  $t('invoices.product') }}</th>
            <th>{{  $t('invoices.price') }}</th>
            <th>{{  $t('invoices.quantity') }}</th>
            <th>{{  $t('invoices.total') }}</th>
            <th>{{  $t('invoices.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in form.invoice_items" :key="index">
            <td>
              <select v-model="item.product_id" class="form-select" @change="updatePrice(item)" required>
                <option disabled value="">{{  $t('invoices.selectProduct') }}</option>
                <option v-for="product in availableProducts(index)" :key="product.id" :value="product.id">
                  {{ product.name }} (Q :{{ product.quantity }})
                </option>
              </select>
            </td>
            <td>
              <span>{{ item.price.toFixed(2) }}</span>
            </td>
            <td>
              <input type="number" v-model.number="item.quantity" class="form-control" min="1"  required />
            </td>
            <td>
              {{ calculateTotal(item) }}
            </td>
            <td>
              <button type="button" class="btn btn-sm btn-danger" @click="removeInvoiceItem(index)">{{  $t('invoices.remove') }}</button>
            </td>
          </tr>
        </tbody>
      </table>

      <button type="button" class="btn btn-secondary" @click="addInvoiceItem">{{  $t('invoices.addItem') }}</button>

      <!-- Amount (Automatically Calculated) -->
      <div class="mb-3 mt-3">
        <label for="amount" class="form-label">{{  $t('invoices.amount') }}</label>
        <input type="number" v-model.number="amount" class="form-control" id="amount" readonly />
      </div>

      <div class="mb-3">
        <label for="tva" class="form-label">{{  $t('invoices.tva') }}</label>
        <input type="number" v-model.number="form.tva" class="form-control" id="tva" min="0" step="0.01" />
      </div>

      <!-- Total Amount (Automatically Calculated) -->
      <div class="mb-3">
        <label for="total-amount" class="form-label">{{  $t('invoices.totalAmount') }}</label>
        <input type="number" v-model.number="totalAmountWithTva" class="form-control" id="total-amount" readonly />
      </div>



      <div class="mb-3">
        <label for="remaining_price" class="form-label">{{  $t('invoices.remainingPrice') }}</label>
        <input type="number" v-model.number="form.remaining_price" class="form-control" id="remaining_price" min="0" step="0.01" required />
      </div>

      <div class="mb-3" style="width: 30%">
        <label for="status" class="form-label" >{{  $t('invoices.status') }}</label>
        <select v-model="form.status" class="form-select" id="status" required>
          <option disabled value="">{{  $t('invoices.selectStatus') }}</option>
          <option value="paid">{{  $t('invoices.paid') }}</option>
          <option value="pending">{{  $t('invoices.pending') }}</option>
        </select>
      </div>

      <button type="submit" class="btn btn-success mt-3">{{  $t('invoices.saveInvoice') }}</button>


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
  methods: {
  },
    name: 'NewInvoice',
    setup() {
      const clients = ref([]);
      const products = ref([]);
      const router = useRouter();
      const showAddModal = ref(false);

      const form = ref({
        client_id: '',
        amount: 0,
        total_amount_with_tva: 0,
        status: '',
        due_date: '',
        remaining_price: 0,
        payment_type: '',
        checkDate: '',
        tva: 0,
        invoice_items: [
          {
            product_id: '',
            quantity: 0,
            price: 0,
            total: 0,
          },
        ],
        amount_in_words_en: '', // New field for English
        amount_in_words_fr: '', // New field for French
        amount_in_words_ar: '', // New field for Arabic
      });
      const errorMessage = ref('');

        // New Client Form
      const newClient = ref({
        cin: '',
        name: '',
        email: '',
        phone: '',
        address: '',
      });

       // Add New Client
       const addNewClient = async () => {
        if (!newClient.value.cin || !newClient.value.name || !newClient.value.email || !newClient.value.phone) {
            Swal.fire('Error', 'Please fill all client details.', 'error');
            return;
        }

        try {
            const response = await axios.post('/clients', newClient.value);
            clients.value.push(response.data); // Update client list with the new client
            form.value.client_id = response.data.id; // Set new client ID in form
            Swal.fire('Success', 'Client added successfully!', 'success');
            newClient.value = { id: null, cin: '', name: '', email: '', phone: '', address: '' }; // Reset new client form
            closeModal(); // Close the modal after successful addition
        } catch (error) {
            console.error('Error adding new client:', error);
            // Check if the error response has data and display it
            const message = error.response?.data?.message || 'Failed to add new client.';
            Swal.fire('Error', message, 'error');
        }
    };


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
        let allProducts = [];
        let page = 1;
        let hasMore = true;

        try {
          while (hasMore) {
            const response = await axios.get(`/products?page=${page}`);
            const items = Array.isArray(response.data.items) ? response.data.items : [];
            allProducts = [...allProducts, ...items];

            // Check if there are more pages
            hasMore = response.data.items.length > 0; // Adjust if pagination metadata is available
            page += 1; // Move to the next page
          }

          products.value = allProducts;
        // console.log('products.value :', products.value);
        } catch (error) {
          console.error('Error fetching products:', error);
          Swal.fire('Error', 'Failed to fetch products.', 'error');
        }
      };


      const addInvoiceItem = () => {
        form.value.invoice_items.push({
          product_id: '',
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
      
    

      // Calculate total based on converted quantity in kilograms
      const calculateTotal = (item) => {
        return (item.quantity * item.price).toFixed(2); // Calculate total with kg-based price
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
          return acc + item.quantity * item.price;
        }, 0).toFixed(2);
      });

      const totalAmountWithTva = computed(() => {
        const totalAmount = form.value.invoice_items.reduce((sum, item) => {
          return sum + item.quantity * item.price;
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


      // Utility functions to convert numbers to words (same as before)
      const numberToWordsEN = (num) => {
          const words = [
              '', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten',
              'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen',
              'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'
          ];
          if (num < 20) return words[num];
          if (num < 100) return words[20 + Math.floor(num / 10) - 2] + (num % 10 !== 0 ? '-' + words[num % 10] : '');
          if (num < 1000) return words[Math.floor(num / 100)] + ' hundred' + (num % 100 !== 0 ? ' and ' + numberToWordsEN(num % 100) : '');
          if (num < 1000000) return numberToWordsEN(Math.floor(num / 1000)) + ' thousand' + (num % 1000 !== 0 ? ' ' + numberToWordsEN(num % 1000) : '');
          if (num < 1000000000) return numberToWordsEN(Math.floor(num / 1000000)) + ' million' + (num % 1000000 !== 0 ? ' ' + numberToWordsEN(num % 1000000) : '');
          return 'Number too large';
      };

      const numberToWordsFR = (num) => {
          const words = [
              '', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf', 'dix',
              'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf',
              'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix'
          ];
          if (num < 20) return words[num];
          if (num < 100) return words[20 + Math.floor(num / 10) - 2] + (num % 10 !== 0 ? (num < 80 ? '-' : ' ') + words[num % 10] : '');
          if (num < 1000) return words[Math.floor(num / 100)] + ' cent' + (num % 100 !== 0 ? ' ' + numberToWordsFR(num % 100) : '');
          if (num < 1000000) return numberToWordsFR(Math.floor(num / 1000)) + ' mille' + (num % 1000 !== 0 ? ' ' + numberToWordsFR(num % 1000) : '');
          if (num < 1000000000) return numberToWordsFR(Math.floor(num / 1000000)) + ' million' + (num % 1000000 !== 0 ? ' ' + numberToWordsFR(num % 1000000) : '');
          return 'Nombre trop grand';
      };

      const numberToWordsAR = (num) => {
          const units = ['', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة', 'ستة', 'سبعة', 'ثمانية', 'تسعة'];
          const teens = ['عشرة', 'أحد عشر', 'اثنا عشر', 'ثلاثة عشر', 'أربعة عشر', 'خمسة عشر', 'ستة عشر', 'سبعة عشر', 'ثمانية عشر', 'تسعة عشر'];
          const tens = ['', 'عشرون', 'ثلاثون', 'أربعون', 'خمسون', 'ستون', 'سبعون', 'ثمانون', 'تسعون'];
          if (num < 10) return units[num];
          if (num < 20) return teens[num - 10];
          if (num < 100) {
              const tenPart = Math.floor(num / 10);
              const unitPart = num % 10;
              return (unitPart !== 0 ? units[unitPart] + ' و ' : '') + (tenPart > 0 ? tens[tenPart - 1] : '');
          }
          if (num < 1000) {
              const hundredPart = Math.floor(num / 100);
              const remainder = num % 100;
              const hundredText = hundredPart === 2 ? 'مائتان' : (hundredPart > 1 ? units[hundredPart] + ' مئة' : 'مئة');
              return hundredText + (remainder !== 0 ? ' و ' + numberToWordsAR(remainder) : '');
          }
          if (num < 1000000) {
              const thousandPart = Math.floor(num / 1000);
              const remainder = num % 1000;
              const thousandText = thousandPart === 1 ? 'ألف' : (thousandPart === 2 ? 'ألفان' : numberToWordsAR(thousandPart) + ' آلاف');
              return thousandText + (remainder !== 0 ? ' و ' + numberToWordsAR(remainder) : '');
          }
          if (num < 1000000000) {
              const millionPart = Math.floor(num / 1000000);
              const remainder = num % 1000000;
              const millionText = millionPart === 1 ? 'مليون' : (millionPart === 2 ? 'مليونان' : numberToWordsAR(millionPart) + ' ملايين');
              return millionText + (remainder !== 0 ? ' و ' + numberToWordsAR(remainder) : '');
          }
          return 'رقم كبير جداً';
      };

      // Function to convert decimal numbers to words with tenths and hundredths
      const numberToWordsWithDecimalsEN = (num) => {
          const [integerPart, decimalPart] = num.toString().split('.');
          const words = numberToWordsEN(parseInt(integerPart));
          if (decimalPart) {
              const decimalNumber = parseInt(decimalPart);
              const unit = decimalPart.length === 1 ? "tenth" : "hundredth";
              const decimalWords = numberToWordsEN(decimalNumber);
              return `${words} and ${decimalWords} ${unit}${decimalNumber > 1 ? 's' : ''}`;
          }
          return words;
      };

      const numberToWordsWithDecimalsFR = (num) => {
          const [integerPart, decimalPart] = num.toString().split('.');
          const words = numberToWordsFR(parseInt(integerPart));
          if (decimalPart) {
              const decimalNumber = parseInt(decimalPart);
              const unit = decimalPart.length === 1 ? "dixième" : "centième";
              const decimalWords = numberToWordsFR(decimalNumber);
              return `${words} et ${decimalWords} ${unit}${decimalNumber > 1 ? 's' : ''}`;
          }
          return words;
      };

      const numberToWordsWithDecimalsAR = (num) => {
          const [integerPart, decimalPart] = num.toString().split('.');
          const words = numberToWordsAR(parseInt(integerPart));
          if (decimalPart) {
              const decimalNumber = parseInt(decimalPart);
              const unit = decimalPart.length === 1 ? "عُشر" : "جزء من المائة";
              const decimalWords = numberToWordsAR(decimalNumber);
              return `${words} و ${decimalWords} ${unit}`;
          }
          return words;
      };

      
      const amountInWordsEN = computed(() => numberToWordsWithDecimalsEN(totalAmountWithTva.value));
      const amountInWordsFR = computed(() => numberToWordsWithDecimalsFR(totalAmountWithTva.value));
      const amountInWordsAR = computed(() => numberToWordsWithDecimalsAR(totalAmountWithTva.value));


      const handleSubmit = async () => {
        // Validate the form before submitting
        if (!validateForm()) {
          return; // Stop the form submission if validation fails
        }

        // Set the computed amounts before submission
        form.value.amount = parseFloat(amount.value);
        form.value.total_amount_with_tva = parseFloat(totalAmountWithTva.value);

        // Set the computed values in the form
        form.value.amount_in_words_en = amountInWordsEN.value;
        form.value.amount_in_words_fr = amountInWordsFR.value;
        form.value.amount_in_words_ar = amountInWordsAR.value;
          

        // Remove 'total' from each invoice item before sending
        const payload = {
          ...form.value,
          invoice_items: form.value.invoice_items.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity,
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
          remaining_price: 0,
          payment_type: '',
          tva: 0,
          invoice_items: [
            {
              product_id: '',
              quantity: 0,
              price: 0,
              total: 0,
            },
          ],
          amount_in_words_en: '', // New field for English
          amount_in_words_fr: '', // New field for French
          amount_in_words_ar: '', // New field for Arabic
        };
        errorMessage.value = '';
      };


      const closeModal = () => {
      showAddModal.value = false;
      newClient.value = { id: null, cin: '', name: '', email: '', phone: '', address: '' };
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
        amountInWordsEN,
        amountInWordsFR,
        amountInWordsAR,
        newClient,
        addNewClient,
        showAddModal,
        closeModal,
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
  .custom-button {
    color: #007bff; /* Text color */
    background-color: transparent; /* Transparent background */
    border: 2px solid #007bff; /* Initial transparent border */
    border-radius: 5px; /* Rounded corners */
    padding: 1px 10px; /* Padding for button */
    text-decoration: none; /* No underline */
    transition: border-color 0.3s, color 0.3s; /* Smooth transitions */
  }

  .custom-button:hover {
    color: rgb(6, 119, 44); /* Change text color on hover */
    border-color: rgb(6, 119, 44)/* Change border color on hover */
  }
</style>
