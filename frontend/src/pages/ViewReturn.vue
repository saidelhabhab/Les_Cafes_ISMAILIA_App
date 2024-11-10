<template>
  <div class="container return-details-container">
    <h2 class="header">
      <i class="fas fa-receipt"></i> Return Details
    </h2>

    <div v-if="returnData" class="card shadow-lg p-4 mb-5 bg-white rounded">
      <div class="card-body">
        <h3 class="card-title">
          <i class="fas fa-clipboard-list icon-highlight"></i> Return ID: &nbsp {{ returnData.id }}
        </h3>

        <ul class="list-group list-group-flush">
          <li class="list-group-item">
            <i class="fas fa-file-invoice icon"></i> <strong>Invoice ID:</strong> &nbsp {{ factor_code || 'Loading...' }}
          </li>
          <li class="list-group-item">
            <i class="fas fa-box icon"></i> <strong>Product Name:</strong> &nbsp {{ productName || 'Loading...' }}
          </li>
          <li class="list-group-item">
            <i class="fas fa-undo-alt icon"></i> <strong>Quantity Returned:</strong> &nbsp {{ returnData.quantity }}  {{ returnData.unit }}
          </li>
          <li class="list-group-item">
            <i class="fas fa-calendar-alt icon"></i> <strong>Date of Return:</strong> &nbsp {{ formatDate(returnData.created_at) }}
          </li>
        </ul>
      </div>
    </div>

    <div v-else class="loading">
      <i class="fas fa-spinner fa-spin fa-2x"></i>
      <p>Loading return data...</p>
    </div>

    <button class="btn-primary mt-4" @click="goBack">
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
    const productName = ref(null);
    const factor_code = ref(null);
    const route = useRoute();
    const router = useRouter();

    const fetchReturnData = async () => {
      const returnId = route.params.id;
      try {
        const response = await axios.get(`/returns/${returnId}`);
        returnData.value = response.data;
        fetchProductName(response.data.product_id);
        fetchBareCode(response.data.invoice_id);
      } catch (error) {
        console.error("Error fetching return data:", error);
      }
    };

    const fetchProductName = async (productId) => {
      try {
        const response = await axios.get(`/products/${productId}`);
        productName.value = response.data.name;
      } catch (error) {
        console.error("Error fetching product name:", error);
        productName.value = "Product not found";
      }
    };

    const fetchBareCode = async (invoice_id) => {
      try {
        const response = await axios.get(`/invoices/${invoice_id}`);
        factor_code.value = response.data.factor_code;
      } catch (error) {
        console.error("Error fetching factor_code:", error);
        factor_code.value = "factor_code not found";
      }
    };

    const formatDate = (dateString) => {
      const options = { year: "numeric", month: "long", day: "numeric" };
      return new Date(dateString).toLocaleDateString(undefined, options);
    };

    const goBack = () => {
      router.go(-1);
    };

    onMounted(() => {
      fetchReturnData();
    });

    return {
      returnData,
      productName,
      formatDate,
      goBack,
      factor_code,
    };
  },
};
</script>

<style scoped>
.return-details-container {
  max-width: 800px;
  margin: auto;
  padding-top: 20px;
}

.header {
  font-size: 2rem;
  color: #333;
  text-align: center;
  margin-bottom: 1.5rem;
}

.card {
  border: none;
  background-color: #ffffff;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.card-title {
  font-size: 1.6rem;
  color: #007bff;
  margin-bottom: 1rem;
}

.icon-highlight {
  color: #007bff;
  margin-right: 10px;
}

.list-group-item {
  font-size: 1.2rem;
  color: #4a4a4a;
  padding: 0.8rem 1rem;
  background-color: #f8f9fa;
  border-radius: 8px;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
}

.icon {
  margin-right: 10px;
  color: #007bff;
  font-size: 1.3rem;
}

.loading {
  text-align: center;
  color: #007bff;
  font-size: 1.1rem;
}

.btn-primary {
  display: block;
  width: fit-content;
  margin: auto;
  font-size: 1.1rem;
  padding: 10px 20px;
  color: #fff;
  background-color: #007bff;
  border: none;
  border-radius: 5px;
  transition: background-color 0.3s;
}

.btn-primary:hover {
  background-color: #0056b3;
}

.btn-primary i {
  margin-right: 5px;
}
</style>
