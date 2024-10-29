<template>
    <div class="container return-details-container">
      <h2 class="my-4 text-center m-0">
        <i class="fas fa-receipt"></i> Return Details
      </h2>
  
      <div v-if="returnData" class="card shadow-lg p-4 mb-5 bg-white rounded">
        <div class="card-body">
          <h3 class="card-title text-info">
            <i class="fas fa-clipboard-list m-3"></i> Return ID:  {{  returnData.id }}
          </h3>
  
          <ul class="list-group list-group-flush">
            <li class="list-group-item">
              <i class="fas fa-file-invoice m-3"></i> <strong>Invoice ID : </strong> {{  returnData.invoice_id }}
            </li>
            <li class="list-group-item">
              <i class="fas fa-box m-3" ></i> <strong>Product ID: </strong> {{ returnData.product_id }}
            </li>
            <li class="list-group-item">
              <i class="fas fa-undo-alt m-3"></i> <strong>Quantity Returned : </strong> {{ returnData.quantity }}
            </li>
            <li class="list-group-item">
              <i class="fas fa-calendar-alt m-3"></i> <strong>Date of Return:  </strong> {{ formatDate(returnData.created_at) }}
            </li>
          </ul>
        </div>
      </div>
  
      <div v-else class="text-center">
        <i class="fas fa-spinner fa-spin fa-2x"></i>
        <p>Loading return data...</p>
      </div>
  
      <button class="btn btn-primary mt-4" @click="goBack">
        <i class="fas fa-arrow-left"></i> Go Back
      </button>
    </div>
  </template>
  
  <script>
  import axios from "../services/axios";
  import { ref, onMounted } from "vue";
  import { useRoute, useRouter } from "vue-router";
  
  export default {
    setup() {
      const returnData = ref(null);
      const route = useRoute();
      const router = useRouter();
  
      const fetchReturnData = async () => {
        const returnId = route.params.id;
        try {
          const response = await axios.get(`/returns/${returnId}`);
          returnData.value = response.data;
        } catch (error) {
          console.error("Error fetching return data:", error);
        }
      };
  
      const formatDate = (dateString) => {
        const options = { year: "numeric", month: "long", day: "numeric" };
        return new Date(dateString).toLocaleDateString(undefined, options);
      };
  
      const goBack = () => {
        router.go(-1); // Navigate back to the previous page
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
  .return-details-container {
    max-width: 800px;
    margin: auto;
  }
  
  .card {
    border: none;
    background-color: #f9f9f9;
  }
  
  .list-group-item {
    font-size: 1.1rem;
    display: flex;
    align-items: center;
  }
  
  .list-group-item i {
    margin-right: 10px;
    color: #007bff;
  }
  
  .card-title i {
    margin-right: 8px;
  }
  
  .btn-primary {
    display: block;
    width: fit-content;
    margin: auto;
    font-size: 1.2rem;
    padding: 10px 20px;
  }
  </style>
  