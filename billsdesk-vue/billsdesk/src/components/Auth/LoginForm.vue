<template>
    <AuthLayout :loading="loading">
        <template #left-content>
            <h2>
              {{ $t('auth.welcome_back') }}
            </h2>
            <p>
              {{ $t('auth.login_desc') }}
            </p>

            <div class="form-group">
                <label for="email">
                  {{ $t('auth.email') }}
                </label>
                <InputText id="email" v-model="email" type="email" :placeholder="$t('auth.enter_email')" />
            </div>
            <div class="form-group">
                <label for="password">
                  {{ $t('auth.password') }}
                </label>
                <InputText id="password" v-model="password" type="password" :placeholder="$t('auth.enter_password')" />
            </div>
            <div class="forgot-password">
                <RouterLink to="/forgot-password">
                    {{ $t('auth.forgot_password') }}
                </RouterLink>
            </div>
            <Button :label="$t('auth.login')" @click="handleLogin" />
            <div class="register_link">
                <p>
                  {{ $t('auth.dont_have_account') }}
                  <RouterLink to="/register">
                    {{ $t('auth.register') }}
                </RouterLink></p>
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
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

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
      alert(t('auth.login_failed') + ': ' + (errorData.message || 'Unknown error'));
      return;
    }

    const data = await response.json();

    if (data.access_token) {
      await authStore.setUserData(data.user, `${data.token_type} ${data.access_token}`, data.permissions);
      router.push('/');
    } else {
      alert(t('auth.login_failed'));
    }
  } catch (error) {
    console.error('Error logging in:', error.message);
    alert(t('auth.login_failed'));
  }
  loading.value = false;
};
</script>

