<template>
    <div class="purchases-container">
        <h1 class="title">
            <i class="fas fa-shopping-cart"></i>
            {{ $t('purchasesShow.title') }} &nbsp; <strong style="color: brown;"> {{ countryName }}</strong>
        </h1>

        <!-- Add Purchase Form -->
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
                    <option v-for="month in months" :key="month.value" :value="month.value">
                        {{ month.name }}
                    </option>
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
                <i class="fas fa-plus"></i>
                {{ $t('purchasesShow.addPurchase') }}
            </button>
        </form>

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
                <tr v-for="purchase in purchases" :key="purchase.id">
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

        <!-- Total Price per Year -->
        <div v-for="(total, year) in totalPrices" :key="year" class="total-price">
            <h3>{{ $t('purchasesShow.totalPrice')}} {{ year }} : <strong style="color: blue;"> {{ total.toFixed(2) }} DH</strong></h3>
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
                        <button @click="closeEditModal" class="btn btn-cancel">{{ $t('purchasePage.cancel') }}</button>
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
        { value: 1, name: 'January' },
        { value: 2, name: 'February' },
        { value: 3, name: 'March' },
        { value: 4, name: 'April' },
        { value: 5, name: 'May' },
        { value: 6, name: 'June' },
        { value: 7, name: 'July' },
        { value: 8, name: 'August' },
        { value: 9, name: 'September' },
        { value: 10, name: 'October' },
        { value: 11, name: 'November' },
        { value: 12, name: 'December' },
        ]);

        const selectedYear = ref(null);
        const selectedMonth = ref(null);
        const newQuantity = ref('');
        const newPrice = ref(''); // Add price
        const newUnit = ref('ton');
        const editMode = ref(false);
        const editedPurchase = ref({});


        const totalPrices = computed(() => {
        return purchases.value.reduce((acc, purchase) => {
            const year = purchase.year;
            const totalPrice = (Number(purchase.price) || 0) * (Number(purchase.quantity) || 0);
            acc[year] = (acc[year] || 0) + totalPrice;
            return acc;
        }, {});
        });

        const fetchPurchases = async () => {
            try {
                const countryId = route.params.id;
                const response = await axios.get(`/purchases/${countryId}`);
                purchases.value = response.data.purchases;

                // Sort purchases by year in descending order
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
            price: newPrice.value, // Send price
            unit: newUnit.value,
            purchase_date: new Date().toISOString().split('T')[0],
            });
            purchases.value.push(response.data);
            Swal.fire('Success', 'Purchase added successfully', 'success');

            // Refresh the page
            window.location.reload();
            resetForm();
        } catch (error) {
            Swal.fire('Error', 'Could not add purchase', 'error');
        }
        };

        const deletePurchase = async (id) => {
            // Afficher la boîte de dialogue de confirmation
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

            // Procéder uniquement si l'utilisateur a confirmé la suppression
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
            editedPurchase.value = { 
                ...purchase, 
                selectedYear: purchase.year, // Assuming purchase has a year property
                selectedMonth: purchase.month // Assuming purchase has a month property
            }; // Deep clone to avoid reference issues
            console.log(editedPurchase.value); // Log the edited purchase object to inspect its structure

        };


        const closeEditModal = () =>  {
            // Set editMode to false to close the modal
            editMode.value = false;

            // Optionally, reset the form fields if needed
            this.editedPurchase = {
                selectedYear: null,
                selectedMonth: null,
                quantity: null,
                price: null,
                unit: null
            };
        };




        const updatePurchase = async () => {
            try {
                await axios.put(`/purchases/${editedPurchase.value.id}`, {
                    year: editedPurchase.value.selectedYear, // Ensure these match your backend validation
                    month: editedPurchase.value.selectedMonth,
                    quantity: editedPurchase.value.quantity,
                    price: editedPurchase.value.price,
                    unit: editedPurchase.value.unit,
                });
                // If successful, handle the success response
                const index = purchases.value.findIndex(p => p.id === editedPurchase.value.id);
                purchases.value[index] = editedPurchase.value;
                editMode.value = false;
                Swal.fire('Updated!', 'Purchase has been updated.', 'success');
                 // Refresh the page
                window.location.reload();
            } catch (error) {
                // Log the entire error response
                console.error('Update error:', error.response.data);
                Swal.fire('Error', error.response.data.message || 'Could not update purchase', 'error');
            }
        };


        const cancelEdit = () => {
        editMode.value = false;
        };

        const resetForm = () => {
        selectedYear.value = null;
        selectedMonth.value = null;
        newQuantity.value = '';
        newPrice.value = ''; // Reset price
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
        fetchPurchases,
        addPurchase,
        deletePurchase,
        editPurchase,
        updatePurchase,
        cancelEdit,
        closeEditModal,
        resetForm,
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
        width: 400px;
        max-width: 90%;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        text-align: center;
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
</style>