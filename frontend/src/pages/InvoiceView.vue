<template>
  <div>
    <!-- Invoice Section -->
  
      <!-- Loading Indicator -->
      <div v-if="loading" class="text-center">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">{{ $t('invoices.loading2') }}</span>
        </div>
        <p class="mt-3">Loading invoice details...</p>
      </div>

      <!-- Invoice Details -->
      <div v-else-if="invoice" class="show-invoice">

        <div v-for="pageIndex in totalPages" :key="pageIndex" class="invoice-page">
          <!-- Logo at the Center -->
          <br>
          <div class="text-center mb-4">
            <img src="@/assets/image.png" alt="Logo" class="mb-3" width="200" />
          </div>

          <!-- Client Information on the Right -->
          <div class="d-flex justify-content-end">
            <div class="client-info-container">
              <h5 class="client-title">{{ $t('invoices.billTo') }}</h5>
              <div class="client-info-card card p-3">
                <div class="client-info-details">
                  <div class="info-item">{{ invoice.client.name }}</div>
                  <div class="info-item">{{ invoice.client.address }}</div>
                  <div class="info-item">{{ invoice.client.phone }}</div>
                  <div class="info-item">{{ invoice.client.email }}</div>
                </div>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-start table_bar">
            <div class="text-center">
              <h1>{{ $t('invoices.title2') }}</h1>
            </div>
          </div>

          <!-- Total Section -->
          <div class="text-center">
            <table class="table factor">
              <tbody>
                <tr>
                  <th><strong>{{ $t('invoices.reference') }}</strong></th>
                  <th><strong>{{ $t('invoices.date') }}</strong></th>
                  <th><strong>{{ $t('invoices.representative') }}</strong></th>
                  <th><strong>{{ $t('invoices.page') }}</strong></th>
                </tr>
                <tr>
                  <td>
                    <img :src="`http://localhost:8002/storage/${invoice.factor_bar_code}`" alt="Barcode" width="150" />
                    <div>{{ invoice.factor_code }}</div>
                  </td>
                  <td>
                    <p>{{ formatDate(today) }}</p>
                  </td>
                  <td></td>
                  <td>{{ pageIndex }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Invoice Items Table -->
          <div class="invoice-items">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>{{ $t('products.title') }}</th>
                  <th>{{ $t('products.quantity') }}</th>
                  <th>{{ $t('products.price') }}  ({{ $t('returns.dh') }}) </th>
                  <th>{{ $t('products.total') }}  ({{ $t('returns.dh') }})</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in getItemsForPage(pageIndex)" :key="index">
                  <td>{{ (pageIndex - 1) * itemsPerPage + index + 1 }}</td>
                  <td>{{ getProductName(item.product_id) || 'N/A' }}</td>
                  <td>{{ parseFloat(item.quantity) }} {{ item.unit }}</td>
                  <td>{{ parseFloat(item.price).toFixed(2) }} </td>
                  <td>{{ (parseFloat(item.total).toFixed(2)) }} </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Total Section -->
        <div class="row total-amount">
            
            <!-- Title Column (Left) -->
            <div class="col-md-6 text-start title-tva">
                <p><strong>{{ $t('invoices.invoiceTitle') }}</strong></p>
                <div v-if="$i18n.locale === 'en'">{{ invoice.amount_in_words_en }} {{ $t('returns.dh') }}</div>
                <div v-else-if="$i18n.locale === 'fr'">{{ invoice.amount_in_words_fr }} {{ $t('returns.dh') }}</div>
                <div v-else>{{ invoice.amount_in_words_ar }} {{ $t('returns.dh') }}</div>
            </div>

            <!-- Table Column (Right) -->
            <div class="col-md-6">
                <table class="table table-tva">
                    <tbody>
                        <tr>
                            <th><strong>{{ $t('invoices.amount') }}</strong></th>
                            <th><strong>{{ $t('invoices.tva') }} ({{ invoice.tva }}%)</strong></th>
                            <th><strong>{{ $t('invoices.totalWithTVA') }}</strong></th>
                        </tr>
                        <tr>
                            <td>{{ invoice.amount }}</td>
                            <td>{{ invoice.amount_tva }}</td>
                            <td>{{ invoice.total_amount_with_tva }} {{ $t('returns.dh') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

          <!-- Address Information -->
          <div class="address text-center">
            <h5> {{ $t('titleOfApp') }}</h5>
            <p>68, Rue Nejjarine - MEKNES - Tel. : 05.35.53.06.34 ICE : 001727048000029</p>
            <p class="mb-1">R.C.Nº: 1.594 MEKNES - C.N.S.S. Nº : 1769522 - T.V.A. Nº : 305.350 - Patente Nº : 17523121 IF. : 23428120</p>
          </div>
        </div>
        <hr v-if="pageIndex < totalPages" />
      </div>
      
      <!-- Error State -->
      <div v-else class="text-center mt-5">
        <div class="alert alert-danger" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ errorMessage || 'Invoice not found.' }}
        </div>
      </div>
   
    
      <!-- Print and PDF Buttons -->
      <div class="d-flex justify-content-between align-items-center mt-5">
        <button class="btn btn-secondary me-2 btn-print" @click="printInvoice">
          <i class="bi bi-printer me-2"></i> {{ $t('invoices.printInvoice') }}
        </button>
      <!--  <button class="btn btn-secondary btn-pdf" @click="downloadPDF">
          <i class="bi bi-file-earmark-pdf me-2"></i> {{ $t('invoices.downloadPDF') }}
        </button> -->
      </div>
  </div>
</template>

<style scoped>

  .invoice-page {
      position: relative;
      border-top: 20px solid maroon;  /* Add top border */
      border-bottom: 20px solid maroon; /* Add bottom border */
      margin: 0 40px 0 40px;
      background: url("@/assets/img.png") center center no-repeat; /* Center the image */
      background-size: 500px auto; /* Adjust size; change if needed */
  }


  
  /* Existing styles for th, td, tr */
  th, td, tr {
      border: 1px solid maroon;
  }

  th {
      background-color: rgba(95, 189, 192, 0.479);
  }

  td{
    background-color: rgba(255, 255, 255, 0.26);
  }

  .factor{
    width: 60%;
  }

  .invoice-page {
      page-break-after: always; /* Ensure each invoice page is printed on a new page */
  }

  /* Existing styles for total-amount, address, etc. */
  .total-amount table {
      width: 50%;
      margin-left: auto;
  }


  .address {
      margin-top: 60px; /* Keep the top margin for spacing above */
      font-size: 12px; /* Font size */
      color: maroon; /* Text color */
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Font family */
      font-weight: bold; /* Font weight */
      margin-bottom: 0; /* No margin below the address */
      line-height: 1; /* Set line height to 1 to reduce spacing between lines */
  }

  .address p {
      margin: 0; /* Remove default margins from paragraphs */
  }
  /* Hide print and PDF buttons during printing */
  .no-print {
      display: none !important;
  }

  .text-start p {
      margin-bottom: 0;
  }

  .row{
    margin: 0px;
  }




  @media print{
    /* General settings for A4 sizing */
    .invoice-page {
        position: relative;
        border-top: 20px solid maroon;
        border-bottom: 20px solid maroon;
        margin: 0;
        padding: 0;
        page-break-after: always;
        height: 29.7cm;
        width: 21cm;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-sizing: border-box;
        background: center center no-repeat !important;
        background-size: 500px auto !important;
        background-image: url("@/assets/img.png") !important;

        visibility: visible;
        /* Center and scale the image nicely */
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        -webkit-print-color-adjust: exact;
   }

    .show-invoice{
      height: 29.7cm; /* Exact A4 page height */
      width: 21cm; /* Exact A4 page width */
    }

    /* Existing styles for th, td, tr */
    th, td, tr {
        border: 1px solid maroon;
    }

    th {
      background-color: rgba(95, 189, 192, 0.479) !important;
    }

    td{
      background-color: rgba(255, 255, 255, 0.26) !important;
    }
  

    /* Hide elements with the .no-print class */
    .no-print {
        display: none !important;
    }

    /* Hide everything by default, then show only invoice */
    body * {
        visibility: hidden;
    }
    .show-invoice, .show-invoice * {
        visibility: visible;
    }
    .show-invoice {
        position: absolute;
        top: 0;
        left: 0;
    }

    /* Page margins */
    @page {
        margin: 0;
    }

    /* Styling for the content to fill page */
    body, html {
        margin: 0;
        padding: 0;
        overflow: hidden;
        height: 100%;
        width: 100%;
    }

    /* Ensure table and address alignment */
    .table-tva {
        width: 50% !important; /* Expand table width */
        border-collapse: collapse;
        margin: 0 10px 10px 30px;
    }
    .title-tva{
     padding-left: 30px !important;
    }

    .address {
        font-size: 12px;
        color: maroon;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-weight: bold;
        line-height: 1.1;
        margin-top: 110px;
        margin-bottom: 0;
    }


    /* Minor adjustments for right/bottom clipping */
    .invoice-page {
        transform: translate(-0.05cm, -0.05cm); /* Slight shift for exact fit */
        margin-bottom: -0.1cm; /* Negative margin to prevent bottom overflow */
        margin-right: -0.1cm; /* Negative margin to prevent right overflow */
    }

    .table_bar {
      margin-left: 20px !important;
      margin-right: 20px !important;
    }
    .invoice-items{
      margin-left: 20px !important;
      margin-right: 20px !important;
    }
    .factor{
      margin-left: 20px !important;
      margin-right: 20px !important;
    }
    .client-info-container{
      margin-right: 20px !important;
    }
  }




</style>

<script>
import { ref, onMounted, computed } from 'vue';
import axios from '../services/axios';
import Swal from 'sweetalert2';
import { useRoute, useRouter } from 'vue-router';
import html2pdf from 'html2pdf.js'; // Import the html2pdf library

export default {
  name: 'ShowInvoice',
  setup() {
    const invoice = ref(null);
    const products = ref([]);
    const productsLoading = ref(true);
    const errorMessage = ref('');
    const loading = ref(true);
    const route = useRoute();
    const router = useRouter();
    const invoiceId = route.params.id; 
    const today = new Date(); 

    const itemsPerPage = 3; // Define itemsPerPage as a constant
    const totalPages = computed(() => {
      if (!invoice.value || !invoice.value.invoice_items) return 1;
      return Math.ceil(invoice.value.invoice_items.length / itemsPerPage);
    });

    const formatDate = (date) => {
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Intl.DateTimeFormat('en-US', options).format(new Date(date));
    };

    const fetchInvoiceData = async () => {
      try {
        const response = await axios.get(`/invoices/${invoiceId}`);
        invoice.value = response.data;
      } catch (error) {
        console.error('Error fetching invoice:', error);
        errorMessage.value = 'Failed to fetch invoice data.';
        Swal.fire('Error', 'Failed to fetch invoice data.', 'error');
      } finally {
        loading.value = false;
      }
    };

    const fetchProducts = async () => {
      try {
        const response = await axios.get('/products');
        products.value = Array.isArray(response.data.items) ? response.data.items : [];
      } catch (error) {
        console.error('Error fetching products:', error);
        Swal.fire('Error', 'Failed to fetch products.', 'error');
      } finally {
        productsLoading.value = false; 
      }
    };

    const getProductName = (productId) => {
      if (!Array.isArray(products.value)) return 'Unknown Product'; 
      const product = products.value.find((p) => p.id === Number(productId));
      return product ? product.name : 'Unknown Product';
    };

    const getItemsForPage = (pageIndex) => {
      const start = (pageIndex - 1) * itemsPerPage;
      const end = start + itemsPerPage;
      return invoice.value.invoice_items.slice(start, end);
    };

    const printInvoice = () => {
      const invoiceElement = document.querySelector('.show-invoice');
      const addressElement = document.querySelector('.address');
      const originalContent = document.body.innerHTML; 

      // Add the 'no-print' class to buttons to hide them
      document.querySelectorAll('.btn-print, .btn-pdf').forEach(btn => btn.classList.add('no-print'));

      // Check if the address is empty and add 'empty-address' class if it is
      if (addressElement && addressElement.textContent.trim() === '') {
          addressElement.classList.add('empty-address');
      }

      // Set body content to only the invoice element for printing
      document.body.innerHTML = invoiceElement.outerHTML;

      // Trigger print
      window.print();

      // Restore original content and reload page to reset classes
      document.body.innerHTML = originalContent;
      window.location.reload(); 
    };

    const downloadPDF = () => {
      const invoiceElement = document.querySelector('.show-invoice');

      // Add the class for PDF download
      invoiceElement.classList.add('pdf-download');

      const options = {
          margin: 0.5,
          filename: 'invoice.pdf',
          html2canvas: { scale: 2 },
          jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' },
      };

      html2pdf().from(invoiceElement).set(options).save().then(() => {
          // Remove the class after downloading
          invoiceElement.classList.remove('pdf-download');
      });
    };


    onMounted(() => {
      fetchInvoiceData();
      fetchProducts();
    });

    return {
      invoice,
      products,
      loading,
      errorMessage,
      today,
      formatDate,
      printInvoice,
      downloadPDF,
      getProductName,
      getItemsForPage,
      totalPages,
      itemsPerPage, // Make sure itemsPerPage is included here
    };
  },


};
</script>

