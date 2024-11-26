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
          <div class="text-center "style="margin-top: -40px;" >
            <img src="@/assets/icon2.png" alt="Logo" class="mb-3" width="150" />
          </div>

          <!-- Client Information on the Right -->
            <div class="d-flex justify-content-end" style="margin-top: -33px;" >
              <div class="client-info-container d-flex align-items-start" >
                <h5 class="client-title me-3">{{ $t('dashboard.client') }} :</h5>
                <div class="card card-with-border" style="width: 300px;">
                  <div class="client-info-details">
                  <div class="info-item">{{ invoice.client.name }}</div>
                 <!--    <div class="info-item">{{ invoice.client.phone }}</div> -->
                  <div class="info-item">{{ invoice.client.email }}</div>
                  <div class="info-item">{{ invoice.client.address }}</div>
                  </div>
                </div>
              </div>
            </div>


          <div class="d-flex justify-content-between align-items-start table_bar" style="margin-top: -40px; ">
            <div class="text-center">
              <h1>{{ $t('invoices.title2') }}</h1>
            </div>
          </div>

          <!-- Total Section -->
          <div class="text-center">
            <table class="table factor">
              <tbody>
                <tr calss="factor_tr">
                  <th><strong>{{ $t('invoices.title2') }} Nº</strong></th>
                  <th><strong>{{ $t('invoices.date') }}</strong></th>
                  <th><strong>{{ $t('invoices.representative') }}</strong></th>
                  <th><strong>{{ $t('invoices.page') }}</strong></th>
                </tr>
                <tr>
                  <td>
                    <small class="text-muted"><img :src="`http://localhost:8002/storage/${invoice.factor_bar_code}`" alt="Barcode" width="150" />
                    <div>{{ invoice.factor_code }}</div> </small> 
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
          <div class="invoice-items" style="margin-top: -10px;">
            <table class="table tt">
              <thead class="text-center">
                <tr>
                  <th style="width: 100px;">{{$t('invoices.reference')}}</th>
                  <th>{{ $t('products.designation') }}</th>
                  <th style="width: 100px;">{{ $t('products.quantity1') }}</th>
                  <th style="width: 140px;">{{ $t('products.price1') }} </th>
                  <th style="width: 140px;">{{ $t('products.total1') }} </th>
                </tr>
              </thead>
              <tbody>
                <!-- Render actual rows -->
                <tr
                  v-for="(item, index) in getItemsForPage(pageIndex)"
                  :key="index"
                  :style="{
                    backgroundColor: 'transparent',
                    height:
                      getItemsForPage(pageIndex).length === 1 && index === 0 ? '320px' :
                      getItemsForPage(pageIndex).length === 2 && index === 1 ? '280px' :
                      getItemsForPage(pageIndex).length === 3 && index === 2 ? '260px' :
                      getItemsForPage(pageIndex).length === 4 && index === 3 ? '240px' :
                      getItemsForPage(pageIndex).length === 5 && index === 4 ? '200px' :
                      'auto'
                  }"
                >
                  <td>{{ getProductReference(item.product_id) || 'N/A' }}</td>
                  <td>{{ getProductName(item.product_id) || 'N/A' }}</td>
                  <td>{{ parseFloat(item.quantity) }}</td>
                  <td>{{ parseFloat(item.price).toFixed(2) }}</td>
                  <td>{{ parseFloat(item.total).toFixed(2) }}</td>
                </tr>
              </tbody>
            </table>
          </div>


          <!-- Total Section -->
        <div class="row total-amount ">
            
            <!-- Title Column (Left) -->
            <div class="col-sm-6 text-start title-tva p-0" style="margin: 0; --bs-gutter-x: 0 !important;">
              <p style="font-weight:600">{{ $t('invoices.invoiceTitle') }}</p>
              <p   v-if="$i18n.locale === 'en'">{{ invoice.amount_in_words_en }} {{ $t('returns.dh') }}</p>
              <p  v-else-if="$i18n.locale === 'fr'">{{ invoice.amount_in_words_fr }} {{ $t('returns.dh') }}</p>
              <p v-else>{{ invoice.amount_in_words_ar }} {{ $t('returns.dh') }}</p>
            </div>


            <!-- Table Column (Right) -->
            <div class="col-sm-6">
                <table class="table table-tva">
                    <tbody>
                        <tr>
                            <th><strong>{{ $t('invoices.amount') }}</strong></th>
                            <th><strong>{{ $t('invoices.tva') }} (20%)</strong></th>
                            <th><strong>{{ $t('invoices.totalWithTVA') }}</strong></th>
                        </tr>
                        <tr>
                            <td>{{ invoice.total_amount_with_tva }}</td>
                            <td>{{ invoice.amount_tva }}</td>
                            <td>{{ invoice.amount }} {{ $t('returns.dh') }}</td>
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
     <!--    <button class="btn btn-secondary btn-pdf" @click="downloadPDF">
          <i class="bi bi-file-earmark-pdf me-2"></i> {{ $t('invoices.downloadPDF') }}
        </button>  -->
      </div>
  </div>
</template>

<style scoped>

  .invoice-page {
      position: relative;
      border-top: 20px solid #683c11;  /* Add top border */
      border-bottom: 20px solid #683c11; /* Add bottom border */
      margin: 0 40px 0 40px;
      background: url("@/assets/icon1.png") center top 195px no-repeat, rgba(255, 255, 255, 0.7); /* Adds overlay */
      background-blend-mode: lighten; /* Adjust as needed (e.g., overlay, darken) */
      background-size: 600px auto;

  }



  
  /* Existing styles for th, td, tr */
  th, td, tr {
      border: 1px solid #000000;
      padding: 5px;
  }

  th {
      background-color: rgba(255, 255, 255, 0);
  }

  td{
    background-color: rgba(255, 255, 255, 0);
  }

 

  .factor {
    width: 60%;
  }


  .invoice-page {
      page-break-after: always; /* Ensure each invoice page is printed on a new page */
  }

  /* Existing styles for total-amount, address, etc. */
  .total-amount table {
      margin-top: -10px !important;
      width: 50%;
      margin-left: auto;
      margin-right: -10px;
  }


  .address {
      margin-top: 60px; /* Keep the top margin for spacing above */
      font-size: 12px; /* Font size */
      color: #683c11; /* Text color */
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


  .card-with-border {
    border: 2px solid #000000; /* Customize border width and color */
    padding: 10px;
  }

  .info-item{
    font-weight: bold;
  }

  .table-tva {
        width: 90% !important; /* Expand table width */
        border-collapse: collapse;

    }


  @media print{
    /* General settings for A4 sizing */
    .invoice-page {
        position: relative;
        border-top: 20px solid #683c11;
        border-bottom: 20px solid #683c11;
        margin: 0;
        padding: 0;
        page-break-after: always;
        height: 29.7cm;
        width: 21cm;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-sizing: border-box;
        background: url("@/assets/icon1.png") center top 195px no-repeat, rgba(255, 255, 255, 0.7) !important; /* Adds overlay */
        background-blend-mode: lighten !important; /* Adjust as needed (e.g., overlay, darken) */
        background-size: 600px auto !important;

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
      border: 1px solid #000000;
    }

    th {
        background-color: rgba(255, 255, 255, 0);
    }

    td{
      background-color: rgba(255, 255, 255, 0);
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
        padding: 0%;
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
        width: 100% !important; /* Expand table width */
        border-collapse: collapse;
        margin: 0 10px 10px 30px;
    }
    .title-tva{
     padding-left: 30px !important;
    }

    .address {
        font-size: 12px;
        color: #683c11;
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
      margin-top: -30px !important;
    }
    .factor{
      margin-left: 20px !important;
      margin-right: 20px !important;
      margin-top: -20px !important;
      
    }
    .client-info-container{
      margin-right: 20px !important;
      margin-top: 10px !important;
    }

    .total-amount {
      margin-top: -20px !important;
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

    const itemsPerPage = 5; // Define itemsPerPage as a constant
    
    const totalPages = computed(() => {
      if (!invoice.value || !invoice.value.invoice_items) return 1;
      return Math.ceil(invoice.value.invoice_items.length / itemsPerPage);
    });

    const formatDate = (date) => {
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Intl.DateTimeFormat('fr-FR', options).format(new Date(date));
    };

    const fetchInvoiceData = async () => {
      try {
        const response = await axios.get(`/invoices/${invoiceId}`);
        invoice.value = response.data;
       // console.log("data => ", invoice.value)
      } catch (error) {
        console.error('Error fetching invoice:', error);
        errorMessage.value = 'Failed to fetch invoice data.';
        Swal.fire('Error', 'Failed to fetch invoice data.', 'error');
      } finally {
        loading.value = false;
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

    const getProductName = (productId) => {
      if (!Array.isArray(products.value)) return 'Unknown Product'; 
      const product = products.value.find((p) => p.id === Number(productId));
      return product ? product.name : 'Unknown Product';
    };

    const getProductReference = (productId) => {
      if (!Array.isArray(products.value)) return 'Unknown Product'; 
      const product = products.value.find((p) => p.id === Number(productId));
      return product ? product.reference : 'Unknown Product';
    };

    const getItemsForPage = (pageIndex) => {
      const start = (pageIndex - 1) * itemsPerPage;
      const end = start + itemsPerPage;
      return invoice.value.invoice_items.slice(start, end);
    };

    const printInvoice = () => {
      const invoiceElement = document.querySelector('.show-invoice');
      const addressElement = document.querySelector('.address');
      const barcodeElement = document.querySelector('.invoice-barcode'); // Assuming barcode is in this class
      const originalContent = document.body.innerHTML;

      // Add the 'no-print' class to buttons to hide them
      document.querySelectorAll('.btn-print, .btn-pdf').forEach(btn => btn.classList.add('no-print'));

      // Check if the address is empty and add 'empty-address' class if it is
      if (addressElement && addressElement.textContent.trim() === '') {
        addressElement.classList.add('empty-address');
      }

      // If barcode is found, append its name to the invoice
      let invoiceName = "Invoice";
      if (barcodeElement) {
        const barcodeName = barcodeElement.getAttribute('data-barcode-name'); // Assuming the name is in a data attribute
        const barcodeNameElement = document.createElement('div');
        barcodeNameElement.classList.add('barcode-name');
        barcodeNameElement.textContent = `Barcode Name: ${barcodeName}`;
        
        // Append the barcode name before the invoice content
        invoiceElement.prepend(barcodeNameElement);

        // Set the invoice name
        invoiceName = `Invoice_${barcodeName}`;
      }

      // Modify the document title to include invoice name for the PDF
      document.title = invoiceName; // Set the title to the invoice name

      // Optionally, show a visible hint for the user to rename the file
      const renameHint = document.createElement('div');
      renameHint.classList.add('rename-hint');
      renameHint.style.textAlign = "center";
      renameHint.style.marginBottom = "15px";
      renameHint.innerHTML = `<strong>Please save the PDF as: ${invoiceName}.pdf</strong>`;
      document.body.prepend(renameHint);

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
        margin: 0, // Set margin to 0 for no margin
        filename: 'invoice.pdf',
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }, // Use A4 format
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
      getProductReference,
      totalPages,
      itemsPerPage, // Make sure itemsPerPage is included here
      
    };
  },


};
</script>

