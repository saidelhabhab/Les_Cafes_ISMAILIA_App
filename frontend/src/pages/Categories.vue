<template>
    <div class="container py-4">
      <!-- Page Title -->
      <h1 class="text-primary mb-4">
        <i class="bi bi-tags"></i> {{ $t('categories.manage') }}
      </h1>
  
      <!-- Add Category Form -->
      <div class="mb-5 p-3 border rounded shadow-sm bg-light">
        <h2 class="h4 text-success">
          <i class="bi bi-plus-circle"></i> {{ $t('categories.addCategory') }}
        </h2>
        <form @submit.prevent="addCategory">
          <div class="input-group">
            <input
              v-model="newCategory"
              type="text"
              class="form-control"
              placeholder="Category name"
              required
            />
            <button type="submit" class="btn btn-success">
              <i class="bi bi-plus-circle-fill"></i> {{ $t('categories.add') }}
            </button>
          </div>
        </form>
      </div>
  
      <!-- Categories and Subcategories List -->
      <div class="mb-4 p-3 border rounded shadow-sm bg-light">
        <h2 class="h4 text-primary">
          <i class="bi bi-list-ul"></i> {{ $t('categories.list') }}
        </h2>
        <ul class="list-group">
          <li v-for="category in categories" :key="category.id" class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <strong class="text-dark">{{ category.name }}</strong>
              <div>
                <button
                  @click="editCategory(category)"
                  class="btn btn-warning btn-sm me-2"
                  title="Edit Category"
                >
                  <i class="bi bi-pencil-square"></i> {{ $t('categories.edit') }}
                </button>
                <button
                  @click="deleteCategory(category.id)"
                  class="btn btn-danger btn-sm"
                  title="Delete Category"
                >
                  <i class="bi bi-trash"></i> {{ $t('categories.delete') }}
                </button>
              </div>
            </div>
  
            <!-- Add Subcategory Form -->
            <form @submit.prevent="addSubcategory(category.id)" class="mt-3">
              <div class="input-group">
                <input
                  v-model="newSubcategories[category.id]"
                  type="text"
                  class="form-control"
                  placeholder="Subcategory name"
                />
                <button type="submit" class="btn btn-secondary">
                  <i class="bi bi-plus-circle-fill"></i> {{ $t('categories.addSubcategory') }}
                </button>
              </div>
            </form>
  
            <!-- Subcategories List -->
            <ul class="list-group mt-2 ms-3">
              <li
                v-for="subcategory in category.subcategories"
                :key="subcategory.id"
                class="list-group-item d-flex justify-content-between align-items-center"
              >
                {{ subcategory.name }}
                <button
                  @click="deleteSubcategory(subcategory.id)"
                  class="btn btn-danger btn-sm"
                  title="Delete Subcategory"
                >
                  <i class="bi bi-trash"></i> {{ $t('categories.delete') }}
                </button>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </template>

  
<script>
  import { ref, onMounted } from 'vue'
  import axios from '../services/axios';
  
  export default {
    setup() {
      const categories = ref([])
      const newCategory = ref('')
      const newSubcategories = ref({})
  
      const fetchCategories = async () => {
        const response = await axios.get('/categories')
        categories.value = response.data
      }
  
      const addCategory = async () => {
        await axios.post('/categories', { name: newCategory.value })
        newCategory.value = ''
        fetchCategories()
      }
  
      const editCategory = async (category) => {
        const newName = prompt('Enter new name:', category.name)
        if (newName) {
          await axios.put(`/categories/${category.id}`, { name: newName })
          fetchCategories()
        }
      }
  
      const deleteCategory = async (id) => {
        await axios.delete(`/categories/${id}`)
        fetchCategories()
      }
  
      const addSubcategory = async (categoryId) => {
        const name = newSubcategories.value[categoryId]
        if (name) {
          await axios.post('/subcategories', { name, category_id: categoryId })
          newSubcategories.value[categoryId] = ''
          fetchCategories()
        }
      }
  
      const deleteSubcategory = async (id) => {
        await axios.delete(`/subcategories/${id}`)
        fetchCategories()
      }
  
      onMounted(() => {
        fetchCategories()
      })
  
      return {
        categories,
        newCategory,
        newSubcategories,
        addCategory,
        editCategory,
        deleteCategory,
        addSubcategory,
        deleteSubcategory
      }
    }
  }
</script>
  
<style scoped>
    .container {
    max-width: 800px;
    }

    h1, h2 {
    font-weight: 600;
    }

    .list-group-item {
    border: none;
    padding-left: 0;
    }

    .list-group-item .btn-sm {
    font-size: 0.8rem;
    }

    .input-group {
    max-width: 500px;
    }

    .form-control {
    border-radius: 0.25rem;
    }
</style>