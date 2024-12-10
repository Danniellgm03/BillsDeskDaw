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
import ErrorsComponent from '@/components/ErrorsComponent.vue';
import { useNotificationService } from '@/utils/notificationService';

const { notify } = useNotificationService();
const errors = ref(null);

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
        notify({
            severity: 'error',
            summary: t('auth.invalid_invitation'),
            detail: t('auth.invalid_invitation'),
            life: 3000
        });
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
            notify({
                severity: 'error',
                summary: t('auth.error_register'),
                detail: t('auth.error_register'),
                life: 3000
            });
            router.push('/login');
        }
        return await response.json();
    } catch (error) {
        console.error('Error fetching invitation data:', error.message);
        notify({
            severity: 'error',
            summary: t('auth.error_register'),
            detail: t('auth.error_register'),
            life: 3000
        });
        router.push('/login');
    }
};


const handleRegister = async () => {
    if (password.value !== confirmPassword.value) {
        notify({
            severity: 'error',
            summary: t('auth.password_mismatch'),
            detail: t('auth.password_mismatch'),
            life: 3000
        });
        return;
    }

    if (email.value !== email_invitation.value) {
      
        notify({
            severity: 'error',
            summary: t('auth.email_mismatch'),
            detail: t('auth.email_mismatch'),
            life: 3000
        });
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
            notify({
                severity: 'error',
                summary: t('auth.error_register'),
                detail: t('auth.error_register'),
                life: 3000
            });
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
            notify({
                severity: 'error',
                summary: t('auth.error_register'),
                detail: t('auth.error_register'),
                life: 3000
            });
        }
    } catch (error) {
        console.error('Error registering:', error.message);
        notify({
            severity: 'error',
            summary: t('auth.error_register'),
            detail: t('auth.error_register'),
            life: 3000
        });
    } finally {
        loading.value = false;
    }
};
</script>
