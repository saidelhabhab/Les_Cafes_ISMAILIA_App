<template>
    <div>
      <!-- Invoice Section -->
      <div class="show-invoice">
        <div class="invoice-page">
          <!-- Logo at the Center -->
          <br>
          <div class="text-center" style="margin-top: -40px;">
            <img src="@/assets/icon2.png" alt="Logo" class="mb-3" width="150" />
          </div>
  
          <!-- Address Information (Footer) -->
          <div class="address text-center">
            <h5>{{ $t('titleOfApp') }}</h5>
            <p>68, Rue Nejjarine - MEKNES - Tel. : 05.35.53.06.34 ICE : 001727048000029</p>
            <p class="mb-1">R.C.Nº: 1.594 MEKNES - C.N.S.S. Nº : 1769522 - T.V.A. Nº : 305.350 - Patente Nº : 17523121 IF. : 23428120</p>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <style scoped>
  .invoice-page {
    position: relative;
    border-top: 20px solid #683c11; /* Add top border */
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
  
  @media print {
    .invoice-page {
      page-break-after: always;
      height: 29.7cm; /* Exact A4 page height */
      width: 21cm; /* Exact A4 page width */
      background: url("@/assets/icon1.png") center top 195px no-repeat, rgba(255, 255, 255, 0.7) !important;
      visibility: visible;
      -webkit-print-color-adjust: exact;
    }
  }
  </style>
  
  
  <script>
  export default {
    name: 'EmptyInvoice',
  };
  </script>
  