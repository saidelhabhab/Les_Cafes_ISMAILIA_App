<template>
  <div class="container">
    <h1 class="page-title">{{ $t('purchasePage.title') }}</h1>

    <!-- Add Country Form -->
    <div class="form-container">
      <form @submit.prevent="addCountry" class="form-inline">
        <input
          v-model="newCountry"
          class="form-input"
          :placeholder="$t('purchasePage.addCountryPlaceholder')"
          required
        />
        <button type="submit" class="btn btn-add">
          <i class="fas fa-plus"></i> {{ $t('purchasePage.addCountry') }}
        </button>
      </form>
    </div>

    <!-- Country Search Dropdown -->
    <div class="form-container">
      <label class="search-label">
        <i class="fas fa-globe-americas"></i> &nbsp;
        {{ $t('purchasePage.selectCountry') }}
        <select v-model="searchCountry" class="search-select">
          <option value="">{{ $t('purchasePage.allCountries') }}</option>
          <option v-for="country in countries" :key="country.id" :value="country.name">
            {{ country.name }}
          </option>
        </select>
      </label>
    </div>


    <!-- Countries Table -->
    <div class="table-container">
      <table class="styled-table">
        <thead>
          <tr>
            <th>{{ $t('purchasePage.country') }}</th>
            <th>{{ $t('purchasePage.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="country in filteredCountries" :key="country.id">
            <td style="text-align: left ; color:green;"> <strong>{{ country.name }}</strong></td>
            <td>
              <button @click="showPurchases(country.id)" class="btn btn-show">
                <i class="fas fa-eye"></i> {{ $t('purchasePage.showPurchases') }}
              </button>
              <button @click="editCountry(country)" class="btn btn-edit">
                <i class="fas fa-edit"></i> {{ $t('purchasePage.edit') }}
              </button>
              <button @click="deleteCountry(country.id)" class="btn btn-delete">
                <i class="fas fa-trash"></i> {{ $t('purchasePage.delete') }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Edit Country Modal -->
    <div v-if="editMode" class="modal">
      <div class="modal-content">
        <h2>{{ $t('purchasePage.editCountry') }}</h2>
        <input v-model="editedCountry.name" class="form-input" />
        <div class="modal-actions me-2">
          <button @click="updateCountry" class="btn btn-update">
            <i class="fas fa-check"></i> {{ $t('purchasePage.update') }}
          </button>
          <button @click="cancelEdit" class="btn btn-cancel">
            <i class="fas fa-times"></i> {{ $t('purchasePage.cancel') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>


<script>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from '../services/axios';
import Swal from 'sweetalert2';

export default {
  name: 'Purchases',
  setup() {
    const router = useRouter();
    const countries = ref([]);
    const newCountry = ref('');
    const searchCountry = ref('');
    const editMode = ref(false);
    const editedCountry = ref({});

    const fetchCountries = async () => {
      try {
        const response = await axios.get('/countries');
        countries.value = response.data;
      } catch (error) {
        console.error('Error fetching countries:', error);
      }
    };

    const addCountry = async () => {
      try {
        const response = await axios.post('/countries', { name: newCountry.value });
        countries.value.push(response.data);
        newCountry.value = '';
        Swal.fire('Success', 'Le pays a été ajouté avec succès', 'success');
      } catch (error) {
        Swal.fire('Error', 'Impossible d\'ajouter le pays', 'error');
      }
    };

    const deleteCountry = async (id) => {
      const result = await Swal.fire({
        title: 'Êtes-vous sûr?',
        text: "Ce pays sera supprimé définitivement!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Oui, supprimez-le!',
        cancelButtonText: 'Non, annuler!',
      });

      if (result.isConfirmed) {
        try {
          await axios.delete(`/countries/${id}`);
          countries.value = countries.value.filter(country => country.id !== id);
          Swal.fire('Supprimé!', 'Le pays a été supprimé.', 'success');
        } catch (error) {
          Swal.fire('Erreur', 'Impossible de supprimer le pays', 'error');
        }
      }
    };

    const editCountry = (country) => {
      editedCountry.value = { ...country };
      editMode.value = true;
    };

    const updateCountry = async () => {
      try {
        await axios.put(`/countries/${editedCountry.value.id}`, editedCountry.value);
        const index = countries.value.findIndex(c => c.id === editedCountry.value.id);
        countries.value[index] = editedCountry.value;
        editMode.value = false;
        Swal.fire('Updated!', 'Le pays a été mis à jour avec succès', 'success');
      } catch (error) {
        Swal.fire('Error', 'Impossible de mettre à jour le pays', 'error');
      }
    };

    const cancelEdit = () => {
      editMode.value = false;
    };

    const showPurchases = (countryId) => {
      router.push({ name: 'ShowPurchases', params: { id: countryId } });
    };

    // Computed property for filtered countries
    const filteredCountries = computed(() => {
      return countries.value.filter(country =>
        !searchCountry.value || country.name.toLowerCase().includes(searchCountry.value.toLowerCase())
      );
    });

    onMounted(() => {
      fetchCountries();
    });

    return {
      countries,
      newCountry,
      searchCountry,
      editMode,
      editedCountry,
      addCountry,
      deleteCountry,
      editCountry,
      updateCountry,
      cancelEdit,
      showPurchases,
      filteredCountries,
    };
  },
};

</script>

<style scoped>
  .container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
  }

  .page-title {
    text-align: center;
    margin-bottom: 20px;
    font-size: 24px;
    font-weight: bold;
  }

  .form-container {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
  }

  .form-inline {
    display: flex;
  }

  .form-input {
    padding: 10px;
    font-size: 16px;
    margin-right: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
  }

  .table-container {
    margin-top: 20px;
  }

  .styled-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 18px;
    text-align: right;
  }

  .styled-table th, .styled-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #ddd;
  }

  .styled-table th {
    background-color: #f4f4f4;
  }

  .styled-table tr:hover {
    background-color: #f1f1f1;
  }

  .btn {
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
    margin: 0 5px;
  }

  .btn-add {
    background-color: #4CAF50;
    color: white;
  }

  .btn-show {
    background-color: #007BFF;
    color: white;
  }

  .btn-edit {
    background-color: #FFC107;
    color: white;
  }

  .btn-delete {
    background-color: #DC3545;
    color: white;
  }

  .modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .modal-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    max-width: 500px;
    width: 100%;
    text-align: center;
  }

  .modal-actions {
    margin-top: 20px;
  }

  .btn-update {
    background-color: #28A745;
    color: white;
  }

  .btn-cancel {
    background-color: #6C757D;
    color: white;
  }

  i {
    margin-right: 5px;
  }

  .form-container {
  display: flex;
  justify-content: center;
  margin: 20px 0;
}

.search-label {
  font-size: 18px;
  font-weight: bold;
  color: #333;
  display: flex;
  align-items: center;
}

.search-label i {
  margin-right: 10px;
  color: #007BFF;
  font-size: 22px;
  transition: color 0.3s ease;
}

.search-label i:hover {
  color: #0056b3;
}

.search-select {
  font-size: 16px;
  padding: 10px;
  margin-left: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
  outline: none;
  cursor: pointer;
  background-color: #f4f4f4;
  color: #333;
  width: 200px;
}

.search-select:hover {
  border-color: #007BFF;
}

.search-select:focus {
  border-color: #007BFF;
  box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

.search-select option {
  padding: 10px;
  background-color: #fff;
  color: #333;
}

</style>
