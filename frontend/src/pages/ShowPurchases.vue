<template>
    <div class="purchases-container">
        <h1 class="title">
            <i class="fas fa-shopping-cart"></i>
            {{ $t('purchasesShow.title') }} &nbsp; <strong style="color: brown;"> {{ countryName }}</strong>
        </h1>

        <!-- Button to Open Add Purchase Modal -->
        <button @click="showAddModal = true" class="btn btn-success mb-2">
            <i class="fas fa-plus"></i> {{ $t('purchasesShow.addPurchase') }}
        </button>

        <!-- Add Purchase Modal -->
        <div v-if="showAddModal" class="modal">
            <div class="modal-add">
                <h2>
                    <i class="fas fa-plus-circle"></i> {{ $t('purchasesShow.addPurchase') }}
                </h2>
                <form @submit.prevent="addPurchase" class="purchase-form">
                    <label>
                        <i class="fas fa-calendar-alt"></i>
                        {{ $t('purchasesShow.year') }}
                        <select v-model="selectedYear" class="form-select">
                            <option v-for="year in lastTenYears" :key="year">{{ year }}</option>
                        </select>
                    </label>

                    <label>
                        <i class="fas fa-calendar"></i>
                        {{ $t('purchasesShow.month') }}
                        <select v-model="selectedMonth" class="form-select">
                            <option v-for="month in months" :key="month.value" :value="month.value">{{ month.name }}</option>
                        </select>
                    </label>

                    <label>
                        <i class="fas fa-weight-hanging"></i>
                        {{ $t('purchasesShow.quantity') }}
                        <input v-model="newQuantity" type="number" placeholder="Quantity" required class="form-input" />
                    </label>

                    <label>
                        <i class="fas fa-dollar-sign"></i>
                        {{ $t('purchasesShow.price') }}
                        <input v-model="newPrice" type="number" placeholder="Price" required class="form-input" />
                    </label>

                    <label>
                        <i class="fas fa-box"></i>
                        {{ $t('purchasesShow.unit') }}
                        <select v-model="newUnit" class="form-select">
                            <option value="ton">Ton</option>
                            <option value="kg">Kilograms</option>
                            <option value="g">Grams</option>
                        </select>
                    </label>

                    <button type="submit" class="btn btn-add">
                        <i class="fas fa-plus"></i> {{ $t('purchasesShow.addPurchase') }}
                    </button>
                    <button @click="showAddModal = false" type="button" class="btn btn-cancel">
                        {{ $t('purchasePage.cancel') }}
                    </button>
                </form>
            </div>
        </div>

      
        <div>
            <!-- Year Selector Dropdown -->
            <div class="year-selector">
            <label for="year-select">{{ $t('analytics.selectYear') }}</label>
            <select v-model="searchYear" id="year-select" class="search-select m-3">
                <option value="">{{ $t('analytics.allYear') }}</option>
                <option v-for="year in availableYears" :key="year" :value="year">
                {{ year }}
                </option>
            </select>
            </div>

            <!-- Display Total Price per Year -->
            <div v-for="(total, year) in filteredTotalPrices" :key="year" class="total-price-card">
            <h3 class="total-price-header">{{ $t('purchasesShow.totalPrice') }} <strong>{{ year }}:</strong></h3>
            <strong class="total-price-amount">{{ total.toFixed(2) }} DH</strong>
            </div>
        </div>


        

       <!-- Search Filters -->
        <div class="search-filters">
            <label>
                {{ $t('purchasesShow.year') }}
                <input type="text" v-model="searchYear" placeholder="Enter year" class="search-input">
            </label>
            <label>
                {{ $t('purchasesShow.month') }}
                <select v-model="searchMonth" class="search-select">
                    <option value="">{{ $t('purchasesShow.allMonths') }}</option>
                    <option v-for="month in months" :key="month.value" :value="month.value">{{ month.name }}</option>
                </select>
            </label>
        </div>


        <!-- Purchases Table -->
        <table class="purchases-table">
            <thead>
                <tr>
                    <th>{{ $t('purchasesShow.year') }}</th>
                    <th>{{ $t('purchasesShow.month') }}</th>
                    <th>{{ $t('purchasesShow.quantity') }}</th>
                    <th>{{ $t('purchasesShow.unit') }}</th>
                    <th>{{ $t('purchasesShow.price') }}</th>
                    <th>{{ $t('purchasesShow.totalPrice1') }}</th>
                    <th>{{ $t('purchasesShow.date') }}</th>
                    <th>{{ $t('purchasePage.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="purchase in paginatedPurchases" :key="purchase.id">

                    <td style="color: brown;" > <strong>{{ purchase.year }}</strong></td>
                    <td>{{ months.find(m => m.value === purchase.month).name }}</td>
                    <td>{{ purchase.quantity }}</td>
                    <td>{{ purchase.unit }}</td>
                    <td>{{ purchase.price }}</td>
                    <td>{{ purchase.total_price ? purchase.total_price : (purchase.price * purchase.quantity) }}</td>
                    <td>{{ purchase.purchase_date }}</td>
                    <td>
                        <button @click="editPurchase(purchase)" class="btn btn-edit btn-sm m-1">
                            <i class="fas fa-edit"></i>
                            {{ $t('purchasesShow.edit') }}
                        </button>
                        <button @click="deletePurchase(purchase.id)" class="btn btn-delete btn-sm">
                            <i class="fas fa-trash"></i>
                            {{ $t('purchasesShow.delete') }}
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination Controls -->
        <div class="pagination-controls">
            <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1" class="pagination-button">
                {{ $t('purchasesShow.previous') }}
            </button>
            <span class="pagination-info">{{ $t('purchasesShow.page') }} {{ currentPage }} / {{ totalPages }}</span>
            <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages" class="pagination-button">
                {{ $t('purchasesShow.next') }}
            </button>
        </div>

    
        <!-- Edit Purchase Modal -->
        <div v-if="editMode" class="modal">
            <div class="modal-content">
                <h2>
                    <i class="fas fa-edit"></i> {{ $t('purchasesShow.editPurchase') }}
                </h2>
                <form @submit.prevent="updatePurchase" class="form">
                    <div class="form-group">
                        <label>{{ $t('purchasesShow.year') }}</label>
                        <select v-model="editedPurchase.selectedYear" class="form-select">
                            <option v-for="year in lastTenYears" :key="year" :value="year">{{ year }}</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>{{ $t('purchasesShow.month') }}</label>
                        <select v-model="editedPurchase.selectedMonth" class="form-select">
                            <option v-for="month in months" :key="month.value" :value="month.value">{{ month.name }}</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>{{ $t('purchasesShow.quantity') }}</label>
                        <input
                            v-model="editedPurchase.quantity"
                            type="number"
                            class="form-input"
                            placeholder="Quantity"
                            required
                        />
                    </div>

                    <div class="form-group">
                        <label>{{ $t('purchasesShow.price') }}</label>
                        <input
                            v-model="editedPurchase.price"
                            type="number"
                            class="form-input"
                            placeholder="Price"
                            required
                        />
                    </div>

                    <div class="form-group">
                        <label>{{ $t('purchasesShow.unit') }}</label>
                        <select v-model="editedPurchase.unit" class="form-select">
                            <option value="ton">Ton</option>
                            <option value="kg">Kilograms</option>
                            <option value="g">Grams</option>
                        </select>
                    </div>

                    <div class="modal-actions">
                        <button type="submit" class="btn btn-update">{{ $t('purchasePage.update') }}</button>
                        <button @click="editMode = false" class="btn btn-cancel">{{ $t('purchasePage.cancel') }}</button>
                    </div>
                </form>
            </div>
        </div>



        <!-- Notifications -->
        <div class="notifications" v-if="notification">{{ notification }}</div>
    </div>
</template>
  
<script>
import { ref, onMounted, computed } from 'vue';
import axios from '../services/axios';
import Swal from 'sweetalert2';
import { useRoute, useRouter } from 'vue-router';

export default {
    name: 'Purchases',
    setup() {
        const route = useRoute();
        const router = useRouter();
        const countryName = ref('');
        const purchases = ref([]);
        const lastTenYears = ref([]);
        const months = ref([
            { value: 1, name: 'Janvier' },
            { value: 2, name: 'Février' },
            { value: 3, name: 'Mars' },
            { value: 4, name: 'Avril' },
            { value: 5, name: 'Mai' },
            { value: 6, name: 'Juin' },
            { value: 7, name: 'Juillet' },
            { value: 8, name: 'Août' },
            { value: 9, name: 'Septembre' },
            { value: 10, name: 'Octobre' },
            { value: 11, name: 'Novembre' },
            { value: 12, name: 'Décembre' },
        ]);

        const searchYear = ref('');
        const searchMonth = ref('');
        const currentPage = ref(1);
        const itemsPerPage = ref(10);

        const showAddModal = ref(false); 
        const selectedYear = ref(null);
        const selectedMonth = ref(null);
        const newQuantity = ref('');
        const newPrice = ref('');
        const newUnit = ref('ton');
        const editMode = ref(false);
        const editedPurchase = ref({});

         // Compute total prices by year
    const totalPrices = computed(() => {
      return purchases.value.reduce((acc, purchase) => {
        const year = purchase.year;
        const totalPrice = (Number(purchase.price) || 0) * (Number(purchase.quantity) || 0);
        acc[year] = (acc[year] || 0) + totalPrice;
        return acc;
      }, {});
    });

    // Available years for the year dropdown
    const availableYears = computed(() => {
      return [...new Set(purchases.value.map(purchase => purchase.year))];
    });

    // Filter total prices based on selected year or show last 3 years
    const filteredTotalPrices = computed(() => {
      if (searchYear.value) {
        // Show total for the selected year only
        return { [searchYear.value]: totalPrices.value[searchYear.value] || 0 };
      } else {
        // Show the totals for the last 3 years if no specific year is selected
        const sortedYears = Object.keys(totalPrices.value)
          .sort((a, b) => b - a)
          .slice(0, 3);

        return sortedYears.reduce((acc, year) => {
          acc[year] = totalPrices.value[year];
          return acc;
        }, {});
      }
    });


        const filteredPurchases = computed(() => {
            return purchases.value.filter(purchase => {
                return (
                    (!searchYear.value || purchase.year.toString().includes(searchYear.value)) &&
                    (!searchMonth.value || purchase.month.toString() === searchMonth.value.toString())
                );
            });
        });


        const paginatedPurchases = computed(() => {
            const start = (currentPage.value - 1) * itemsPerPage.value;
            const end = start + itemsPerPage.value;
            return filteredPurchases.value.slice(start, end);
        });

        const totalPages = computed(() => {
            return Math.ceil(filteredPurchases.value.length / itemsPerPage.value);
        });

        const goToPage = (page) => {
            if (page >= 1 && page <= totalPages.value) {
                currentPage.value = page;
            }
        };

        const fetchPurchases = async () => {
            try {
                const countryId = route.params.id;
                const response = await axios.get(`/purchases/${countryId}`);
                purchases.value = response.data.purchases;
                purchases.value.sort((a, b) => b.year - a.year || b.month - a.month);
                countryName.value = response.data.country.name;
            } catch (error) {
                Swal.fire('Error', 'Could not fetch purchases', 'error');
            }
        };

        const addPurchase = async () => {
            const countryId = route.params.id;
            try {
                const response = await axios.post('/purchases', {
                    country_id: countryId,
                    year: selectedYear.value,
                    month: selectedMonth.value,
                    quantity: newQuantity.value,
                    price: newPrice.value,
                    unit: newUnit.value,
                    purchase_date: new Date().toISOString().split('T')[0],
                });
                purchases.value.push(response.data);
                Swal.fire('Success', 'Purchase added successfully', 'success');
                window.location.reload();
                resetForm();
            } catch (error) {
                Swal.fire('Error', 'Could not add purchase', 'error');
            }
        };

        const deletePurchase = async (id) => {
            const result = await Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: "Cet achat sera supprimé définitivement !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Oui, supprimez-le !',
                cancelButtonText: 'Non, annuler !',
            });

            if (result.isConfirmed) {
                try {
                    await axios.delete(`/purchases/${id}`);
                    purchases.value = purchases.value.filter(purchase => purchase.id !== id);
                    Swal.fire('Supprimé !', 'L\'achat a été supprimé.', 'success');
                } catch (error) {
                    Swal.fire('Erreur', 'Impossible de supprimer l\'achat', 'error');
                }
            }
        };

        const editPurchase = (purchase) => {
            editMode.value = true;
            editedPurchase.value = { ...purchase, selectedYear: purchase.year, selectedMonth: purchase.month };
        };

        const updatePurchase = async () => {
            try {
                await axios.put(`/purchases/${editedPurchase.value.id}`, {
                    year: editedPurchase.value.selectedYear,
                    month: editedPurchase.value.selectedMonth,
                    quantity: editedPurchase.value.quantity,
                    price: editedPurchase.value.price,
                    unit: editedPurchase.value.unit,
                });
                const index = purchases.value.findIndex(p => p.id === editedPurchase.value.id);
                purchases.value[index] = editedPurchase.value;
                editMode.value = false;
                Swal.fire('Updated!', 'Purchase has been updated.', 'success');
                window.location.reload();
            } catch (error) {
                console.error('Update error:', error.response.data);
                Swal.fire('Error', error.response.data.message || 'Could not update purchase', 'error');
            }
        };

        const resetForm = () => {
            selectedYear.value = null;
            selectedMonth.value = null;
            newQuantity.value = '';
            newPrice.value = '';
            newUnit.value = 'ton';
        };

        onMounted(() => {
            const currentYear = new Date().getFullYear();
            lastTenYears.value = [currentYear + 1, ...Array.from({ length: 10 }, (v, k) => currentYear - k)];
            fetchPurchases();
        });

        return {
            countryName,
            purchases,
            lastTenYears,
            months,
            selectedYear,
            selectedMonth,
            newQuantity,
            newPrice,
            newUnit,
            editMode,
            editedPurchase,
            totalPrices,
            availableYears,
      filteredTotalPrices,
            filteredPurchases,
            paginatedPurchases,
            totalPages,
            goToPage,
            fetchPurchases,
            addPurchase,
            deletePurchase,
            editPurchase,
            updatePurchase,
            resetForm,
            showAddModal,
            searchYear,
            searchMonth,
            currentPage,
            itemsPerPage,
        };
    },
};
</script>



<style scoped>
    .purchases-container {
    max-width: 800px;
    margin: auto;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    background-color: #f9f9f9;
    }

    .title {
    font-size: 24px;
    margin-bottom: 20px;
    color: #333;
    display: flex;
    align-items: center;
    }

    .title i {
    margin-right: 10px;
    color: #007bff;
    }

    .purchase-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-bottom: 20px;
    }

    .form-group {
    display: flex;
    align-items: center;
    gap: 10px;
    }

    .form-select,
    .form-input {
    flex: 1;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    }

    .btn {
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 14px;
        cursor: pointer;
        margin: 0 5px;
    }

    .btn-add {
    background-color: #28a745;
    }

    .btn-edit {
    background-color: #ffc107;
    }

    .btn-delete {
    background-color: #dc3545;
    }

    .purchases-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    }

    .purchases-table th,
    .purchases-table td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: left;
    }

    .purchases-table th {
    background-color: #f2f2f2;
    }

    /* Modal background to cover the entire screen */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
        z-index: 1000;
    }

    /* Modal content box */
    .modal-content {
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        width: 500px;
        max-width: 90%;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        text-align: center;
    }
    .modal-add{
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        width: 500px;
        max-width: 90%;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        text-align: left;
    }

    h2 {
        margin-bottom: 20px;
        font-size: 22px;
        font-weight: 600;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
    }

    .form-input, .form-select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 16px;
    }

    .form-select {
        cursor: pointer;
    }

    /* Buttons styles */
    .modal-actions {
        margin-top: 20px;
    }


    .btn-update {
        background-color: #28a745;
        color: white;
    }

    .btn-cancel {
        background-color: #dc3545;
        color: white;
    }

    .btn i {
        margin-right: 5px;
    }

    .total-price-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: box-shadow 0.3s;
    }

    .total-price-card:hover {
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    }

    .total-price-header {
        font-size: 1.1rem;
        color: #333;
        margin: 0;
        font-weight: normal;
    }

    .total-price-amount {
        font-size: 1.2rem;
        color: #007bff; /* Blue color */
        font-weight: bold;
    }

    .search-filters {
    display: flex;
    gap: 15px;
    padding: 10px;
    background-color: #4e363675;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    }

    .search-filters label {
        display: flex;
        flex-direction: column;
        font-weight: bold;
        color: #333;
    }

    .search-input,
    .search-select {
        padding: 8px;
        margin-top: 5px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        width: 200px;
        max-width: 250px;
        transition: border-color 0.3s ease;
    }

    .search-input:focus,
    .search-select:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 4px rgba(0, 123, 255, 0.3);
    }

    .search-select {
        background-color: #fff;
        appearance: none;
    }


    .pagination-controls {
        display: flex;
        align-items: center;
        gap: 15px;
        justify-content: center;
        padding: 10px;
        background-color: #f1f1f1;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-top: 20px;
    }

    .pagination-button {
        padding: 8px 16px;
        font-size: 14px;
        font-weight: bold;
        color: #fff;
        background-color: #007bff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .pagination-button:disabled {
        background-color: #ddd;
        color: #aaa;
        cursor: not-allowed;
    }

    .pagination-button:not(:disabled):hover {
        background-color: #0056b3;
    }

    .pagination-info {
        font-size: 14px;
        color: #333;
        font-weight: bold;
    }


</style>