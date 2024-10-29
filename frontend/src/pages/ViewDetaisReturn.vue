<template>
    <div class="container">
      <h2 class="my-4">Return Details</h2>
  
      <div v-if="returnData" class="return-details">
        <h3>Return ID: {{ returnData.id }}</h3>
        <p><strong>Invoice ID:</strong> {{ returnData.invoice_id }}</p>
        <p><strong>Product ID:</strong> {{ returnData.product_id }}</p>
        <p><strong>Quantity Returned:</strong> {{ returnData.quantity }}</p>
        <p><strong>Date of Return:</strong> {{ formatDate(returnData.created_at) }}</p>
        <p><strong>Amount:</strong> {{ returnData.amount }} €</p>
        <p><strong>Total Amount with TVA:</strong> {{ returnData.total_amount_with_tva }} €</p>
        <p><strong>Price:</strong> {{ returnData.price }} €</p>
        <!-- Add more fields as necessary -->
      </div>
  
      <div v-else>
        <p>Loading return data...</p>
      </div>
  
      <button class="btn btn-primary mt-4" @click="goBack">Go Back</button>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  import { ref, onMounted } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  
  export default {
  props: {
  },
    setup() {
      const returnData = ref(null);
      const route = useRoute();
      const router = useRouter();
  
      const fetchReturnData = async () => {
        const returnId = route.params.id; // Assuming you have a route parameter for ID
        try {
          const response = await axios.get(`/api/returns/${returnId}`);
          returnData.value = response.data;
        } catch (error) {
          console.error("Error fetching return data:", error);
        }
      };
  
      const formatDate = (dateString) => {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateString).toLocaleDateString(undefined, options);
      };
  
      const goBack = () => {
        router.go(-1); // Go back to the previous page
      };
  
      onMounted(() => {
        fetchReturnData();
      });
  
      return {
        returnData,
        formatDate,
        goBack,
      };
    },
  };
  </script>
  
  <style scoped>
  .return-details {
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 20px;
    margin-bottom: 20px;
  }
  </style>
  