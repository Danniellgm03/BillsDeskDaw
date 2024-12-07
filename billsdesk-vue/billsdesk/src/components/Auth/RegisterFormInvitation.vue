<template>
    <AuthLayout :loading="loading">
        <template #left-content>
            <h2>{{ $t('auth.register_create') }}</h2>
            <p>
                {{ $t('auth.register_invitation_desc', 'You have been invited to join our platform. Please fill out the form below to create your account.') }}
            </p>

            <div class="form-group">
                <label for="name">{{ $t('auth.name') }}</label>
                <InputText id="name" v-model="name" type="text" :placeholder="$t('auth.enter_name')" />
            </div>
            <div class="form-group">
                <label for="email">{{ $t('auth.email') }}</label>
                <InputText id="email" v-model="email" type="email" :placeholder="$t('auth.enter_email')" :readonly="true" />
            </div>
            <div class="form-group">
                <label for="password">{{ $t('auth.password') }}</label>
                <InputText id="password" v-model="password" type="password" :placeholder="$t('auth.enter_password')" />
            </div>
            <div class="form-group">
                <label for="confirmPassword">{{ $t('auth.confirm_password') }}</label>
                <InputText id="confirmPassword" v-model="confirmPassword" type="password" :placeholder="$t('auth.enter_confirm_password')" />
            </div>

            <Button :label="$t('auth.register')" @click="handleRegister" />
            <div class="login_link">
                <p>
                    {{ $t('auth.already_have_account') }}
                    <RouterLink to="/login">{{ $t('auth.login') }}</RouterLink>
                </p>
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
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const router = useRouter();


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
        alert(t('auth.invalid_invitation'));
        router.push('/login');
    }
    return invitationToken;
};

const handleFetchInvitationData = async (invitationToken) => {
    try {
        const response = await fetch(`http://localhost:8000/api/company/invitations/${invitationToken}`);
        if (!response.ok) {
            const errorData = await response.json();
            console.error('Error fetching invitation data:', errorData);
            alert(t('auth.error_register'));
            router.push('/login');
        }
        return await response.json();
    } catch (error) {
        console.error('Error fetching invitation data:', error.message);
        alert(t('auth.error_register'));
        router.push('/login');
    }
};


const handleRegister = async () => {
    if (password.value !== confirmPassword.value) {
        alert(
            t('password_mismatch')
        );
        return;
    }

    if (email.value !== email_invitation.value) {
        alert(
            t('auth.email_mismatch')
        );
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
            alert(
                t('auth.error_register')
            );
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
            router.push('/login');
        } else {
            alert(
                t('auth.error_register')
            );
        }
    } catch (error) {
        console.error('Error registering:', error.message);
        alert(
            t('auth.error_register')
        );
    } finally {
        loading.value = false;
    }
};
</script>
