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
import Cookies from 'js-cookie'; // Importa js-cookie para manejar cookies

const email = ref('');
const password = ref('');
const loading = ref(false);

const handleLogin = async () => {
    try {
        loading.value = true;
        const response = await fetch('http://localhost:8000/api/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email: email.value,
                password: password.value
            })
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
            localStorage.setItem('user_data', JSON.stringify(
                {
                    id: data.user.id,
                    name: data.user.name,
                    email: data.user.email,
                    company_id: data.user.company_id,
                }
            ));
            Cookies.set('authToken', `${data.token_type} ${data.access_token}`, {
                expires: 1, // Expira en 1 día
                secure: true, // Solo se envía por HTTPS
                sameSite: 'Strict', // Mejora la seguridad contra CSRF
                httpOnly: false, // No HttpOnly porque se usa en el frontend
            });
            window.location.href = '/';
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
