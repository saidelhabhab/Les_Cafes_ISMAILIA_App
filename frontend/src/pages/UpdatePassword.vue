<template>
    <div class="update-password-container">
      <h2 class="text-center">{{ $t('profile.updatePassword') }}</h2>
      <form @submit.prevent="handleUpdatePassword" class="form-container">
        <div class="form-group">
          <label for="current_password">{{ $t('profile.currentPassword') }}</label>
          <input
            type="password"
            id="current_password"
            v-model="currentPassword"
            required
            class="form-control"
          />
          <span v-if="errors.current_password" class="error">{{ errors.current_password[0] }}</span>
        </div>
  
        <div class="form-group">
          <label for="password">{{ $t('profile.newPassword') }}</label>
          <input
            type="password"
            id="password"
            v-model="newPassword"
            required
            class="form-control"
          />
          <span v-if="errors.password" class="error">{{ errors.password[0] }}</span>
        </div>
  
        <div class="form-group">
          <label for="password_confirmation">{{ $t('profile.confirmNewPassword') }}</label>
          <input
            type="password"
            id="password_confirmation"
            v-model="passwordConfirmation"
            required
            class="form-control"
          />
          <span v-if="errors.password_confirmation" class="error">{{ errors.password_confirmation[0] }}</span>
        </div>
  
        <button type="submit" class="btn btn-primary mb-3">{{ $t('profile.updatePassword') }}</button>
        <router-link to="/profile" class="btn btn-secondary btn-block">{{ $t('profile.returnToProfile') }}</router-link>
      </form>
    </div>
  </template>
  
  
  <script>
  import { ref } from 'vue';
  import { apiService } from '../services/axios'; // Import apiService
  import Swal from 'sweetalert2';
  
  export default {
    name: 'UpdatePassword',
    setup() {
      const currentPassword = ref('');
      const newPassword = ref('');
      const passwordConfirmation = ref('');
      const errors = ref({});
  
      const handleUpdatePassword = async () => {
        errors.value = {};
        try {
          const payload = {
            current_password: currentPassword.value,
            password: newPassword.value,
            password_confirmation: passwordConfirmation.value,
          };
          const response = await apiService.updatePassword(payload);
  
          Swal.fire({
            icon: 'success',
            title: 'Password Updated',
            text: response.data.message,
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false,
          });
  
          currentPassword.value = '';
          newPassword.value = '';
          passwordConfirmation.value = '';
        } catch (error) {
          if (error.response && error.response.status === 422) {
            errors.value = error.response.data.errors;
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Update Failed',
              text: error.response?.data?.message || 'An error occurred while updating your password.',
              confirmButtonText: 'OK',
            });
          }
        }
      };
  
      return {
        currentPassword,
        newPassword,
        passwordConfirmation,
        errors,
        handleUpdatePassword,
      };
    },
  };
  </script>
  
  <style scoped>
  .update-password-container {
    max-width: 500px;
    margin: 3rem auto;
    padding: 2rem;
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  }
  
  .form-container {
    display: flex;
    flex-direction: column;
  }
  
  .form-group {
    margin-bottom: 1.5rem;
  }
  
  label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #333;
  }
  
  input.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ced4da;
    border-radius: 5px;
    font-size: 1rem;
  }
  
  input.form-control:focus {
    border-color: #80bdff;
    outline: 0;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.25);
  }
  
  .error {
    color: #e3342f;
    font-size: 0.875rem;
    margin-top: 0.25rem;
  }
  
  button.btn {
    width: 100%;
    padding: 0.75rem;
    font-size: 1rem;
  }
  
  button.btn-primary {
    background-color: #007bff;
  }
  
  button.btn-secondary {
    background-color: #6c757d;
    margin-top: 1rem;
  }
  </style>
  