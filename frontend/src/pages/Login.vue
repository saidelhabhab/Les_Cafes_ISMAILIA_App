<!-- src/pages/Login.vue -->
<template>
  <div class="container login-container">
    <h2 class="text-center mb-4">Login</h2>
    <form @submit.prevent="handleLogin" class="needs-validation" novalidate>
      <div class="mb-3">
        <label for="email" class="form-label">Email:</label>
        <input
          type="email"
          id="email"
          v-model="email"
          class="form-control"
          required
          placeholder="Enter your email"
        />
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password:</label>
        <input
          type="password"
          id="password"
          v-model="password"
          class="form-control"
          required
          placeholder="Enter your password"
        />
      </div>
      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
    <p v-if="errorMessage" class="alert alert-danger mt-3">{{ errorMessage }}</p>
  </div>
</template>

<script>
import { ref } from 'vue';
import { useAuthStore } from '../store/auth';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';

export default {
  name: 'Login',
  setup() {
    const email = ref('');
    const password = ref('');
    const errorMessage = ref('');
    const authStore = useAuthStore();
    const router = useRouter();

    const handleLogin = async () => {
      try {
        await authStore.login({ email: email.value, password: password.value });
        
        // Success notification
        Swal.fire({
          icon: 'success',
          title: 'Login Successful',
          text: 'Welcome back!',
          timer: 2000,
          timerProgressBar: true,
          showConfirmButton: false,
        });

        router.push('/'); // Redirect to dashboard after login
      } catch (error) {
        errorMessage.value = error.response.data.message || 'Login failed';

        // Error notification
        Swal.fire({
          icon: 'error',
          title: 'Login Failed',
          text: errorMessage.value,
          confirmButtonText: 'Retry',
          customClass: {
            confirmButton: 'btn btn-danger',
          },
          buttonsStyling: false,
        });
      }
    };

    return {
      email,
      password,
      handleLogin,
      errorMessage,
    };
  },
};
</script>

<style scoped>
.login-container {
  max-width: 400px;
  margin-top: 50px;
  padding: 2rem;
  border: 1px solid #ccc;
  border-radius: 8px;
  background-color: #fff;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
</style>
