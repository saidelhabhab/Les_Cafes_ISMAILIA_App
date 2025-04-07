<template>
    <div>
      <!-- Invoice Section -->
    
        
  
        <!-- Invoice Details -->
        <div  class="show-invoice">
  
          <div  class="invoice-page">
            <!-- Logo at the Center -->
            <br>
            <div class="text-center  logo" >
              <img src="@/assets/icon2.png" alt="Logo" class="mb-3" width="150" />
            </div>
  
           
            <!-- Address Information -->
            <div class="address text-center">
              <h5> {{ $t('titleOfApp') }}</h5>
              <p>68, Rue Nejjarine - MEKNES - Tel. : 05.35.53.06.34 ICE : 001727048000029</p>
              <p class="mb-1">R.C.Nº: 1.594 MEKNES - C.N.S.S. Nº : 1769522 - T.V.A. Nº : 305.350 - Patente Nº : 17523121 IF. : 23428120</p>
            </div>
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

      
    .logo{
        margin-top: -750px;
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
      
    }
  
  
  
  
</style>
  
  <script>
  import { ref, onMounted, computed } from 'vue';
  import html2pdf from 'html2pdf.js'; // Import the html2pdf library
  
  export default {
    name: 'ShowInvoice',
    setup() {
    
  
  
  
  
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
       
      });
  
      return {
        
    
        printInvoice,
        downloadPDF,
       
        
      };
    },
  
  
  };
  </script>
  
  