<template>
    <AuthLayout :loading="loading">
        <template #left-content>
            <h2>Create an Account</h2>
            <p>
                You have been invited to join our platform. Please fill out the form below to create your account.
            </p>

            <div class="form-group">
                <label for="name">Name</label>
                <InputText id="name" v-model="name" type="text" placeholder="Enter your name" />
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <InputText id="email" v-model="email" type="email" placeholder="Enter your email" :readonly="true" />
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <InputText id="password" v-model="password" type="password" placeholder="Enter your password" />
            </div>
            <div class="form-group">
                <label for="confirmPassword">Confirm Password</label>
                <InputText id="confirmPassword" v-model="confirmPassword" type="password" placeholder="Confirm your password" />
            </div>

            <Button label="Register" @click="handleRegister" />
            <div class="login_link">
                <p>Already have an account? <RouterLink to="/login">Login</RouterLink></p>
            </div>
        </template>
    </AuthLayout>
</template>

<script setup>
import AuthLayout from '@/layouts/AuthLayout.vue';
import { ref, onBeforeMount } from 'vue';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Cookies from 'js-cookie'; // Añadir el paquete js-cookie para manejar cookies



const name = ref('');
const email = ref('');
const email_invitation = ref('');
const password = ref('');
const confirmPassword = ref('');
const token = ref('');
const loading = ref(false);

onBeforeMount(async () => {
    token.value = await getInvitationData();
    const invitationData = await handleFetchInvitationData(token.value);
    
    if (invitationData) {
        email.value = invitationData[0].email; 
        email_invitation.value = invitationData[0].email;
    }
});

const getInvitationData = async () => {
    const searchParams = new URLSearchParams(window.location.search);
    const invitationToken = searchParams.get('token');
    if (!invitationToken) {
        alert('No invitation token found');
        window.location.href = '/login';
    }
    return invitationToken;
};

const handleFetchInvitationData = async (invitationToken) => {
    try {
        const response = await fetch(`http://localhost:8000/api/company/invitations/${invitationToken}`);
        if (!response.ok) {
            const errorData = await response.json();
            console.error('Error fetching invitation data:', errorData);
            alert('Error fetching invitation data: ' + (errorData.message || 'Unknown error'));
            window.location.href = '/login';
        }
        return await response.json();
    } catch (error) {
        console.error('Error fetching invitation data:', error.message);
        alert('An error occurred while fetching invitation data');
        window.location.href = '/login';
    }
};


const handleRegister = async () => {
    if (password.value !== confirmPassword.value) {
        alert('Passwords do not match');
        return;
    }

    if (email.value !== email_invitation.value) {
        alert('Email does not match the invitation email');
        return;
    }

    try {
        loading.value = true;
        const response = await fetch('http://localhost:8000/api/registerWithInvitation', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                name: name.value,
                email: email.value,
                password: password.value,
                password_confirmation: confirmPassword.value,
                token: token.value
            })
        });

        if (!response.ok) {
            const errorData = await response.json();
            console.error('Error registering:', errorData);
            alert('Registration failed: ' + (errorData.message || 'Unknown error'));
            return;
        }

        const data = await response.json();
        if (data.access_token) {
              localStorage.setItem('user_data', JSON.stringify(
                {
                    id: data.user.id,
                    name: data.user.name,
                    email: data.user.email,
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
            alert('Registration failed: Token not received');
        }
    } catch (error) {
        console.error('Error registering:', error.message);
        alert('An error occurred during registration');
    } finally {
        loading.value = false;
    }
};
</script>
