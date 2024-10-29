<template>
  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <!-- Profile Card -->
        <div class="card shadow-sm">
          <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
              <i class="bi bi-person-circle"></i> {{ $t('profile.editProfile') }}
            </h4>
          </div>
          <div class="card-body">
            <!-- Success Message -->
            <div v-if="successMessage" class="alert alert-success alert-dismissible fade show" role="alert">
              {{ successMessage }}
              <button type="button" class="btn-close" @click="successMessage = ''" aria-label="Close"></button>
            </div>
            <!-- Error Message -->
            <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
              {{ errorMessage }}
              <button type="button" class="btn-close" @click="errorMessage = ''" aria-label="Close"></button>
            </div>

            <form @submit.prevent="updateProfile" enctype="multipart/form-data">
              <!-- Name Field -->
              <div class="mb-3">
                <label for="name" class="form-label">{{ $t('clients.name') }}</label>
                <div class="input-group">
                  <span class="input-group-text" id="name-addon">
                    <i class="bi bi-person-fill"></i>
                  </span>
                  <input
                    type="text"
                    class="form-control"
                    id="name"
                    v-model="user.name"
                    required
                    aria-describedby="name-addon"
                    :placeholder="$t('clients.namePlaceholder')"
                  />
                </div>
                <div v-if="errors.name" class="text-danger mt-1">
                  {{ errors.name[0] }}
                </div>
              </div>

              <!-- Email Field -->
              <div class="mb-3">
                <label for="email" class="form-label">{{ $t('clients.email') }}</label>
                <div class="input-group">
                  <span class="input-group-text" id="email-addon">
                    <i class="bi bi-envelope-fill"></i>
                  </span>
                  <input
                    type="email"
                    class="form-control"
                    id="email"
                    v-model="user.email"
                    required
                    aria-describedby="email-addon"
                    :placeholder="$t('clients.emailPlaceholder')"
                  />
                </div>
                <div v-if="errors.email" class="text-danger mt-1">
                  {{ errors.email[0] }}
                </div>
              </div>

              <!-- Submit Button -->
              <button type="submit" class="btn btn-primary w-100" :disabled="isSubmitting">
                <span v-if="isSubmitting">
                  <i class="bi bi-hourglass-split"></i> {{ $t('profile.updating') }}...
                </span>
                <span v-else>
                  <i class="bi bi-pencil-square"></i> {{ $t('profile.updateProfile') }}
                </span>
              </button>
            </form>

            <!-- Change Password Button -->
            <div class="mt-4 text-center">
              <router-link to="/update-password" class="btn btn-secondary">
                <i class="bi bi-lock-fill"></i> {{ $t('profile.changePassword') }}
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


<script>
import axios from '../services/axios';
import Swal from 'sweetalert2';

export default {
  name: 'Profile',
  data() {
    return {
      user: {
        name: '',
        email: '',
      },
      successMessage: '',
      errorMessage: '',
      errors: {},
      isSubmitting: false, // For disabling the submit button
    };
  },
  async created() {
    try {
      const response = await axios.get('/user'); // Fetch current user details
      this.user = response.data;
    } catch (error) {
      Swal.fire('Error', 'Failed to fetch user data', 'error');
      this.$router.push('/login');
    }
  },
  methods: {
    async updateProfile() {
      this.isSubmitting = true;

      try {
        await axios.put('/user', this.user); // Send updated user data
        this.successMessage = 'Profile updated successfully!';
        this.errorMessage = '';
        this.errors = {};
        Swal.fire('Success', 'Profile updated successfully', 'success');
      } catch (error) {
        if (error.response && error.response.status === 422) {
          this.errors = error.response.data.errors; // Handle validation errors
          this.errorMessage = 'Please fix the errors below.';
        } else {
          this.errorMessage = error.response?.data?.message || 'Failed to update profile';
          Swal.fire('Error', this.errorMessage, 'error');
        }
      } finally {
        this.isSubmitting = false; // End submission
      }
    },
  },
};
</script>

<style scoped>
/* Your existing styles */
</style>
