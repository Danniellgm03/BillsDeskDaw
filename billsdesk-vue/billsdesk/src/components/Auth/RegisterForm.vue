<template>
    <AuthLayout :loading="loading">
        <template #left-content>
            <ErrorsComponent :errors="errors" v-if="errors != null" />
            <h2>{{ $t('auth.register_create') }}</h2>
            <p>{{ $t('auth.register_desc', 'Join us! Please fill in the details to register') }}</p>

            <div class="form-group">
                <label for="name">{{ $t('auth.name', 'Name') }}</label>
                <InputText id="name" v-model="name" type="text" :placeholder="$t('auth.enter_name', 'Enter your name')" />
            </div>
            <div class="form-group">
                <label for="email">{{ $t('auth.email') }}</label>
                <InputText id="email" v-model="email" type="email" :placeholder="$t('auth.enter_email')" />
            </div>
            <div class="form-group">
                <label for="password">{{ $t('auth.password') }}</label>
                <InputText id="password" v-model="password" type="password" :placeholder="$t('auth.enter_password', 'Enter your password')" />
            </div>
            <div class="form-group">
                <label for="confirmPassword">{{ $t('auth.confirm_password', 'Confirm Password') }}</label>
                <InputText id="confirmPassword" v-model="confirmPassword" type="password" :placeholder="$t('auth.enter_confirm_password', 'Confirm your password')" />
            </div>

            <Button :label="$t('auth.register')" @click="handleRegister" />
            <div class="login_link">
                <p>
                    {{ $t('auth.already_have_account', 'Already have an account?') }}
                    <RouterLink to="/login">{{ $t('auth.login') }}</RouterLink>
                </p>
            </div>
        </template>
    </AuthLayout>
</template>


<script setup>
import AuthLayout from '@/layouts/AuthLayout.vue';
import { ref } from 'vue';
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
const password = ref('');
const confirmPassword = ref('');
const loading = ref(false);

const handleRegister = async () => {
    if (password.value !== confirmPassword.value) {
        alert(
            t('password_mismatch')
        );
        return;
    }

    try {
        loading.value = true;
        const response = await fetch('http://localhost:8000/api/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                name: name.value,
                email: email.value,
                password: password.value,
                password_confirmation: confirmPassword.value
            })
        });

        if (!response.ok) {
            const errorData = await response.json();
            console.error('Error registering:', errorData);
            errors.value = errorData.errors;    
            return;
        }

        const data = await response.json();
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
            router.push('/');
        } else {
           errors.value = data.errors;
        }
    } catch (error) {
        console.error('Error registering:', error.message);
        notify({
            severity: 'error',
            summary: t('auth.register_failed'),
            detail: t('auth.register_failed'),
            life: 3000
        });
    } finally {
        loading.value = false;
    }
};
</script>
