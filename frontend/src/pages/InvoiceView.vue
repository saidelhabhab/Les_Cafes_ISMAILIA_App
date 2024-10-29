<template>
  <div>
    <!-- Invoice Section -->
    <div class="container my-5">
      <!-- Loading Indicator -->
      <div v-if="loading" class="text-center">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden"> {{ $t('invoices.loading2') }}</span>
        </div>
        <p class="mt-3">Loading invoice details...</p>
      </div>

      <!-- Invoice Details -->
      <div v-else-if="invoice" class="p-4 border rounded shadow show-invoice bg-white">
        <!-- Header Section: Company and Logo -->
        <div class="d-flex justify-content-between align-items-start mb-4">
          <!-- Left: Company Information -->
          <div class="company-info">
            <img :src="`http://localhost:8002/storage/${invoice.factor_bar_code}`" alt="Barcode" width="150" />
            {{ invoice.factor_code }}
            <h3>Mon Stock</h3>
            
          </div>
          <!-- Invoice Title -->
        <div class="text-center mb-5">
          <h1>{{ $t('invoices.title2') }}</h1>
        </div>
          <!-- Right: Logo and Date -->
          <div class="text-end">
            <img src="@/assets/image.png" alt="Logo" class="mb-4" width="150" />
            <p>Date: {{ formatDate(today) }}</p>
          </div>
        </div>

        

        <!-- Client Information -->
        <div class="client-info mb-1">
          <h5>{{ $t('invoices.billTo') }}</h5>
          <p>{{ invoice.client.name }}</p>
          <p>{{ invoice.client.address }}</p>
          <p>Phone: {{ invoice.client.phone }}</p>
          <p>Email: {{ invoice.client.email }}</p>
        </div>

        <!-- Invoice Items Table -->
        <div class="invoice-items">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>{{ $t('products.title') }}</th>
                <th>{{ $t('products.quantity') }}</th>
                <th>{{ $t('products.price') }}</th>
                <th>{{ $t('products.total') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in invoice.invoice_items" :key="index">
                <td>{{ index + 1 }}</td>
                <td>{{ getProductName(item.product_id) || 'N/A' }}</td>
                <td>{{ parseFloat(item.quantity) }}</td>
                <td>${{ parseFloat(item.price).toFixed(2) }}</td>
                <td>${{ (parseFloat(item.price) * parseFloat(item.quantity)).toFixed(2) }}</td>
              </tr>
            </tbody>
          </table>
        </div>


        <div class="text-end total-amount">
          <!-- Amount (Subtotal) -->
          <h4>{{ $t('invoices.amount') }} {{ invoice.amount }} {{ $t('returns.dh') }}</h4>      
          <!-- TVA (20%) Calculation -->
          <h5>{{ $t('invoices.tva') }} ({{ invoice.tva }}%) </h5>   
          <!-- Total with TVA -->
          <h4>{{ $t('invoices.totalWithTVA') }} {{ invoice.total_amount_with_tva }} {{ $t('returns.dh') }}</h4>
        </div>

        <!-- Signature Section -->
        <div class="mt-5 mb-5">
          <div class="text-end my-5">
            <p> <u> {{ $t('invoices.signature') }} </u> </p>
          </div>
        </div>

        <div class="address text-center mt-5">
          <p>1234 Street Name, City, State, ZIP</p>
          <p>Phone: (123) 456-7890, Fax: (123) 456-7891</p>
          <p>Website: www.company.com</p>
        </div>

        <!-- Print and PDF Buttons -->
        <div class="d-flex justify-content-between align-items-center mt-5">
          <button class="btn btn-secondary me-2 btn-print" @click="printInvoice">
            <i class="bi bi-printer me-2"></i> {{ $t('invoices.printInvoice') }}
          </button>
          <button class="btn btn-info btn-pdf" @click="downloadPDF">
            <i class="bi bi-file-earmark-pdf me-2"></i> {{ $t('invoices.downloadPDF') }}
          </button>
        </div>

      </div>

      <!-- Error State -->
      <div v-else class="text-center mt-5">
        <div class="alert alert-danger" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ errorMessage || 'Invoice not found.' }}
        </div>
      </div>
    </div>
  </div>
</template>


<script>
import { ref, onMounted } from 'vue';
import axios from '../services/axios';
import Swal from 'sweetalert2';
import { useRoute, useRouter } from 'vue-router';
import html2pdf from 'html2pdf.js'; // Import the html2pdf library

export default {
  name: 'ShowInvoice',
  setup() {
    const invoice = ref(null);
    const products = ref([]); // Initialize as an empty array
    const productsLoading = ref(true);
    const errorMessage = ref('');
    const loading = ref(true);
    const route = useRoute();
    const router = useRouter();
    const invoiceId = route.params.id; // Retrieve invoice ID from route parameters
    const today = new Date(); // Current date

    // Fetch the invoice data by ID
    const fetchInvoiceData = async () => {
      try {
        const response = await axios.get(`/invoices/${invoiceId}`);
        invoice.value = response.data;
      //  console.log("Data : ", JSON.stringify(invoice.value));
      } catch (error) {
        console.error('Error fetching invoice:', error);
        errorMessage.value = 'Failed to fetch invoice data.';
        Swal.fire('Error', 'Failed to fetch invoice data.', 'error');
      } finally {
        loading.value = false;
      }
    };

    // Fetch all products to map product_id to product name
    const fetchProducts = async () => {
      try {
        const response = await axios.get('/products');
        //console.log('Products data:', response.data); // Log API response for debugging
        // Assign the items array from the response to products.value
        products.value = Array.isArray(response.data.items) ? response.data.items : []; 
        
      } catch (error) {
        console.error('Error fetching products:', error);
        Swal.fire('Error', 'Failed to fetch products.', 'error');
      } finally {
        productsLoading.value = false; // Set loading to false in the end
      }
    };

    // Get product name by product_id
    const getProductName = (productId) => {
     // console.log('Current products.value:', products.value); // Log current products value
      if (!Array.isArray(products.value)) return 'Unknown Product'; // Ensure it's an array
      const product = products.value.find((p) => p.id === Number(productId)); 
      return product ? product.name : 'Unknown Product';
    };

    // Navigate to the edit invoice page
    const editInvoice = () => {
      router.push(`/invoices/${invoiceId}/edit`);
    };

    // Helper function to format dates
    const formatDate = (dateStr) => {
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(dateStr).toLocaleDateString(undefined, options);
    };

    // Helper function to capitalize the first letter of a string
    const capitalizeFirstLetter = (string) => {
      if (!string) return '';
      return string.charAt(0).toUpperCase() + string.slice(1);
    };

    // Print the invoice
      const printInvoice = () => {
      const invoiceElement = document.querySelector('.show-invoice');
      const originalContent = document.body.innerHTML; // Save the original body content

      // Temporarily hide the print and PDF buttons
      document.querySelectorAll('.btn-print, .btn-pdf').forEach(btn => btn.classList.add('no-print'));

      // Replace the body content with only the invoice section for printing
      document.body.innerHTML = invoiceElement.outerHTML;

      // Trigger print
      window.print();

      // Restore the original content after printing
      document.body.innerHTML = originalContent;

      // Reload the page to reinitialize Vue components
      window.location.reload(); // Refresh the page to restore the original state
    };



   // Download the invoice as PDF
    const downloadPDF = () => {
      const invoiceElement = document.querySelector('.show-invoice');

      // Temporarily hide the print and PDF buttons for the PDF generation
      document.querySelectorAll('.btn-print, .btn-pdf').forEach(btn => btn.classList.add('no-pdf'));

      const options = {
        margin: 1,
        filename: `invoice-${invoiceId}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' },
      };

      // Generate the PDF
      html2pdf().from(invoiceElement).set(options).save().finally(() => {
        // Restore the buttons after PDF generation
        document.querySelectorAll('.btn-print, .btn-pdf').forEach(btn => btn.classList.remove('no-pdf'));
      });
    };

    const getBarcodeUrl = (barcodePath) => {

      

  if (!barcodePath) return ''; // Return empty if there's no barcode path
  console.log("->",`/storage/${barcodePath}`);
  return `/storage/${barcodePath}`; // Correct path to your Laravel storage
};

    onMounted(() => {
      fetchProducts();
      fetchInvoiceData();
    });

    return {
      invoice,
      getBarcodeUrl,
      products,
      errorMessage,
      loading,
      editInvoice,
      getProductName,
      formatDate,
      printInvoice,
      downloadPDF, // Return the new downloadPDF method
      today, // Current date for the additional page
      capitalizeFirstLetter,
    };
  },
};
</script>

<style scoped>
  @media print {
    /* Hide the print and PDF buttons during printing */
    .btn-print, .btn-pdf {
      display: none !important;
    }

    /* Hide any other unnecessary elements for print */
    .navbar, .footer, .sidebar {
      display: none !important;
    }

    /* Adjust invoice styling for print */
    .show-invoice {
      margin: 0;
      padding: 20px;
      width: 100%;
    }
  }

  /* Optionally hide buttons during PDF export */
  .no-pdf {
    display: none;
  }

  .company-info p, .client-info p{
    line-height: 1; /* Adjust to desired spacing */
  }

  .address{
    padding: 10px;
    font-size: 0.8rem;
  }
  .address p{
    line-height: 0.3;
  }

  .address div p {
    margin: 0; /* Remove default margin to avoid extra space between lines */
  }
</style>

  