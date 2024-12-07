<template>
    <AuthLayout :loading="loading">
        <template #left-content v-if="!invalidToken">
            <h2>{{ $t('auth.reset_password') }}</h2>
            <p>{{ $t('auth.reset_password_desc', 'Please enter your new password below.') }}</p>
            <div class="form-group">
                <label for="password">{{ $t('auth.password') }}</label>
                <InputText 
                    id="password" 
                    v-model="password" 
                    type="password" 
                    :placeholder="$t('auth.enter_password')" 
                />
            </div>
            <div class="form-group">
                <label for="confirmPassword">{{ $t('auth.confirm_password') }}</label>
                <InputText 
                    id="confirmPassword" 
                    v-model="confirmPassword" 
                    type="password" 
                    :placeholder="$t('auth.enter_confirm_password')" 
                />
            </div>
            <Button :label="$t('auth.reset_password')" @click="handleResetPassword" />
        </template>
        <template #left-content v-else>
            <h2>{{ $t('auth.invalid_token', 'Invalid or expired token') }}</h2>
            <p>{{ $t('auth.invalid_token_desc', 'The token you provided is invalid or has expired. Please request a new password reset link.') }}</p>
            <Button :label="$t('auth.go_login', 'Go login')" @click="toLogin"></Button>
        </template>
    </AuthLayout>
</template>

<script setup>
import AuthLayout from '@/layouts/AuthLayout.vue';
import { ref, onBeforeMount } from 'vue';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

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

        if (response.status === 404) {
            invalidToken.value = true;
            return;
        }
    } catch (error) {
        console.error(t('auth.error_validating_token'), error);
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
        alert(t('auth.invalid_or_missing_token_email'));
        router.push('/login');
    }

    await isValidTokenResetPassword(token.value, email.value);
});

// Manejar el restablecimiento de contraseña
const handleResetPassword = async () => {
    if (!password.value || !confirmPassword.value) {
        alert(t('auth.fill_all_fields'));
        return;
    }

    if (password.value !== confirmPassword.value) {
        alert(t('auth.password_mismatch'));
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
            console.error(t('auth.error_resetting_password'), errorData);
            alert(t('auth.reset_password_failed', { message: errorData.message || t('auth.unknown_error') }));
            return;
        }

        alert(t('auth.reset_password_success'));
        router.push('/login');
    } catch (error) {
        console.error(t('auth.error_resetting_password'), error.message);
        alert(t('auth.error_occurred'));
    } finally {
        loading.value = false;
    }
};
</script>


<style scoped lang='scss'></style>
