<template>
    <AuthLayout :loading="loading">
        <template #left-content v-if="!invalidToken">
            <h2>Reset Password</h2>
            <p>Please enter your new password below.</p>
            <div class="form-group">
                <label for="password">Password</label>
                <InputText id="password" v-model="password" type="password" placeholder="Enter your password" />
            </div>
            <div class="form-group">
                <label for="confirmPassword">Confirm Password</label>
                <InputText id="confirmPassword" v-model="confirmPassword" type="password" placeholder="Confirm your password" />
            </div>
            <Button label="Reset" @click="handleResetPassword" />
        </template>
        <template #left-content v-else>
            <h2>Invalid or expired token</h2>
            <p>The token you provided is invalid or has expired. Please request a new password reset link.</p>
            <Button label="Go login" @click="toLogin"></Button>
        </template>
    </AuthLayout>
</template>

<script setup>
import AuthLayout from '@/layouts/AuthLayout.vue';
import { ref, onBeforeMount } from 'vue';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import { useRouter } from 'vue-router';

const router = useRouter();

const password = ref('');
const confirmPassword = ref('');
const token = ref('');
const email = ref('');
const loading = ref(false);

const invalidToken = ref(false);

const toLogin = () => {
    router.push('/login');
};


const isValidTokenResetPassword = async (token_str, email_str) => {
  try {
    loading.value = true;
    const response = await fetch('http://localhost:8000/api/isValidTokenResetPassword', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        email: email_str,
        token: token_str,
      }),
    });

    if(response.status == '404'){
        invalidToken.value = true;
        return;
    }

  } catch (error) {
    console.error('Error validating token:', error);
    router.push('/login');
  } finally {
    loading.value = false;
  }
};



// Extraer token y email de la URL
onBeforeMount(async () => {
    const searchParams = new URLSearchParams(window.location.search);
    token.value = searchParams.get('token');
    email.value = searchParams.get('email');

    if (!token.value || !email.value) {
        alert('Invalid or missing token/email');
        router.push('/login');
    }

    await isValidTokenResetPassword(token.value, email.value);
});

// Manejar el restablecimiento de contraseña
const handleResetPassword = async () => {
    if (!password.value || !confirmPassword.value) {
        alert('Please fill in all fields');
        return;
    }

    if (password.value !== confirmPassword.value) {
        alert('Passwords do not match');
        return;
    }

    try {
        loading.value = true;

        const response = await fetch('http://localhost:8000/api/reset-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                token: token.value,
                email: email.value,
                password: password.value,
                password_confirmation: confirmPassword.value,
            }),
        });

        if (!response.ok) {
            const errorData = await response.json();
            console.error('Error resetting password:', errorData);
            alert('Reset password failed: ' + (errorData.message || 'Unknown error'));
            return;
        }

        alert('Password reset successfully. Please log in.');
        router.push('/login');
    } catch (error) {
        console.error('Error resetting password:', error.message);
        alert('An error occurred during password reset');
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped lang='scss'></style>
