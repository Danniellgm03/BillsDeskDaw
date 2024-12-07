<template>
     <AuthLayout :loading="loading">
         <template #left-content>
            <h2>
              {{ $t('auth.forgot_password') }}
            </h2>
            <p>
              {{ $t('auth.forgot_password_desc') }}
            </p>

            <div class="form-group">
                <label for="email">
                  {{ $t('auth.email') }}
                </label>
                <InputText id="email" v-model="email" type="email" :placeholder="$t('auth.enter_email')" />
            </div>

            <Button :label="$t('auth.reset_password')" @click="handleResetPassword" />
        </template>
     </AuthLayout>
</template>

<script setup>
import AuthLayout from '@/layouts/AuthLayout.vue';
import { ref } from 'vue';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const router = useRouter();

const email = ref('');
const loading = ref(false);


const handleResetPassword = async () => {
  try {
    loading.value = true;
    const response = await fetch('http://localhost:8000/api/forgot-password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        email: email.value,
      }),
    });

    if (!response.ok) {
      const errorData = await response.json();
      console.error('Error resetting password:', errorData);
      router.push('/login');        
    } else {
      alert(t('auth.password_reset_email_sent'));
      router.push('/login');
    }
  } catch (error) {
    console.error('Error resetting password:', error);
    router.push('/login');
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped lang='scss'></style>