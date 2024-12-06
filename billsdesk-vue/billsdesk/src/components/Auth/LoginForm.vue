<template>
    <AuthLayout :loading="loading">
        <template #left-content>
            <h2>Welcome back</h2>
            <p>Welcome back! Please enter your details</p>

            <div class="form-group">
                <label for="email">Email</label>
                <InputText id="email" v-model="email" type="email" placeholder="Enter your email" />
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <InputText id="password" v-model="password" type="password" placeholder="Enter your password" />
            </div>
            <div class="forgot-password">
                <RouterLink to="/forgot-password">Forgot password</RouterLink>
            </div>
            <Button label="Sign In" @click="handleLogin" />
            <div class="register_link">
                <p>Don't have an account? <RouterLink to="/register">Register</RouterLink></p>
            </div>
        </template>
    </AuthLayout>
</template>

<script setup>
import AuthLayout from '@/layouts/AuthLayout.vue';
import { ref } from 'vue';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import { useAuthStore } from '@/stores/authStore';
import { useRouter } from 'vue-router';

const router = useRouter();


const authStore = useAuthStore();

const email = ref('');
const password = ref('');
const loading = ref(false);

const handleLogin = async () => {
  try {
    loading.value = true;
    const response = await fetch('http://localhost:8000/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        email: email.value,
        password: password.value,
      }),
    });

    if (!response.ok) {
      const errorData = await response.json();
      console.error('Error logging in:', errorData);
      alert('Login failed: ' + (errorData.message || 'Unknown error'));
      return;
    }

    const data = await response.json();
    console.log('Login successful:', data);

    if (data.access_token) {
      await authStore.setUserData(data.user, `${data.token_type} ${data.access_token}`, data.permissions);
      router.push('/');
    } else {
      alert('Login failed: Token not received');
    }
  } catch (error) {
    console.error('Error logging in:', error.message);
    alert('An error occurred during login');
  }
  loading.value = false;
};
</script>

