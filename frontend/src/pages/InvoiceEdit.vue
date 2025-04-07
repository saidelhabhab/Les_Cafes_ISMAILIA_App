<template>
  <div class="container new-invoice">
    <h1 class="text-center mb-4">{{ $t('invoices.editInvoice')}}</h1>

    <!-- Edit Invoice Form -->
    <form @submit.prevent="handleSubmit" class="p-4 border rounded shadow">
      <div class="mb-3">
        <label for="client_id" class="form-label">Client:</label>
        <select v-model="form.client_id" class="form-select" id="client_id" required>
          <option disabled value="">{{ $t('invoices.selectClient')}}</option>
          <option v-for="client in clients" :key="client.id" :value="client.id">
            {{ client.name }}
          </option>
        </select>
      </div>

      <div class="mb-3">
        <label for="factor_code" class="form-label">{{  $t('returns.factorCode') }}</label>
        <input type="number" v-model="form.factor_code" class="form-control" id="factor_code" required />
      </div>


      <div class="mb-3">
        <label for="status" class="form-label">{{  $t('invoices.status') }}</label>
        <select v-model="form.status" class="form-select" id="status" required>
          <option disabled value="">{{  $t('invoices.selectStatus') }}</option>
          <option value="paid">{{  $t('invoices.paid') }}</option>
          <option value="pending">{{  $t('invoices.pending') }}</option>
          <option value="canceled">Canceled</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="due_date" class="form-label">{{  $t('invoices.dueDate') }}</label>
        <input type="date" v-model="form.due_date" class="form-control" id="due_date" required />
      </div>

      <div class="mb-3">
        <label for="payment_type" class="form-label">{{  $t('invoices.paymentType') }}</label>
        <select v-model="form.payment_type" class="form-select" id="payment_type" required>
          <option disabled value="">{{  $t('invoices.selectPaymentType') }}</option>
          <option value="cash">{{  $t('invoices.cash') }}</option>
          <option value="check">{{  $t('invoices.check') }}</option>
        </select>
      </div>

      <!-- Conditionally render the date input for "Check" payment method -->
      <div v-if="form.payment_type === 'check'">
        <label for="checkDate">{{  $t('invoices.checkDate') }}</label>
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
            <td><input type="number" v-model.number="item.quantity" class="form-control" min="1"  required /></td>
        
            <td>{{ calculateItemTotal(item) }}</td>
            <td>
              <button type="button" class="btn btn-sm btn-danger" @click="removeInvoiceItem(index)">{{  $t('invoices.remove') }}</button>
            </td>
          </tr>
        </tbody>
      </table>
      <button type="button" class="btn btn-secondary" @click="addInvoiceItem">{{  $t('invoices.addItem') }}</button>

      <!-- Total Amount (Automatically Calculated) -->
      <div class="mb-3 mt-3">
        <label for="total-amount" class="form-label">{{  $t('invoices.amount') }}</label>
        <input type="number" v-model.number="totalAmountWithoutTva" class="form-control" id="total-amount" readonly />
      </div>

      <div class="mb-3">
        <label for="tva" class="form-label">{{  $t('invoices.tva') }} (20%)</label>
        <input type="number" v-model.number="tva" class="form-control" id="tva" readonly/>
      </div>

            <!-- Amount (Automatically Calculated) -->
      <div class="mb-3 ">
        <label for="amount" class="form-label">{{  $t('invoices.totalAmount') }}</label>
        <input type="number" v-model.number="amount" class="form-control" id="amount" readonly />
      </div>



      <button type="submit" class="btn btn-success mt-3">{{  $t('invoices.updateInvoice') }}</button>

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
  methods: {
  },
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
        payment_type: '',
        tva: 0,
        invoice_items: [],
        amount_in_words_en: '', // New field for English
        amount_in_words_fr: '', // New field for French
        amount_in_words_ar: '', // New field for Arabic
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


      const calculateItemTotal = (item) => {
        const total = (item.quantity * item.price).toFixed(2);
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
          return acc + item.quantity * item.price;
        }, 0).toFixed(2);
      });

        // Calcul de la TVA
      const tva = computed(() => {
        return parseFloat(amount.value - totalAmountWithoutTva.value).toFixed(2);
      });



      const totalAmountWithoutTva = computed(() => {
        const totalAmount = form.value.invoice_items.reduce((sum, item) => {
          return sum + item.quantity * item.price;
        }, 0);
        const tauxTVA = 20; // 20% de TVA
        const totalHT = totalAmount / (1 + tauxTVA / 100);

        return totalHT.toFixed(2);
      });

       // Utility functions to convert numbers to words
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
      
      const amountInWordsEN = computed(() => numberToWordsWithDecimalsEN(amount.value));
      const amountInWordsFR = computed(() => numberToWordsWithDecimalsFR(amount.value));
      const amountInWordsAR = computed(() => numberToWordsWithDecimalsAR(amount.value));

      const handleSubmit = async () => {
        // Calculate totals for all invoice items
        form.value.invoice_items.forEach(item => {
          calculateItemTotal(item);
        });

          // Set the calculated amount to form
         form.value.amount = amount.value; // Set the computed amount

          // Set the total amount with TVA
         form.value.total_amount_with_tva = totalAmountWithoutTva.value; // Set this value before sending

         // Set the computed values in the form
        form.value.amount_in_words_en = amountInWordsEN.value;
        form.value.amount_in_words_fr = amountInWordsFR.value;
        form.value.amount_in_words_ar = amountInWordsAR.value;
        try {
          
         // console.log("Form Data:", JSON.stringify(form.value));

          // Send the form data to the server
          await axios.put(`/invoices/${invoiceId.value}`, form.value);
          Swal.fire('Success', 'Invoice updated successfully!', 'success');
          // Use router.push instead of this.$router.push
          router.push({ name: 'Invoices' });
        } catch (error) {
          console.error("Erreur de mise à jour :", error);
          if (error.response && error.response.status === 422) {
            Swal.fire('Erreur', error.response.data.error, 'error');
          } else {
            errorMessage.value = error.response?.data?.error || 'Échec de la mise à jour. Veuillez vérifier vos entrées.';
          }
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
        totalAmountWithoutTva,
        handleSubmit,
        availableProducts,
        amountInWordsEN,
        amountInWordsFR,
        amountInWordsAR,
        tva
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
