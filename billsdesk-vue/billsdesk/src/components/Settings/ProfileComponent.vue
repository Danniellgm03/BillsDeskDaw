<template>
    <div>
        <ErrorsComponent :errors="errors" v-if="errors != ''" />

        <!-- Name -->
        <SettingsLayout>
            <template #info>
                <div class="info_container">
                    <h4>
                        {{ $t('settings.profile_settings.name') }}
                    </h4>
                    <p>
                        {{ $t('settings.profile_settings.name_desc') }}
                    </p>
                </div>
            </template>
            <template #main>
                <div>
                    <InputText v-model="name" placeholder="Name" v-if="!loading" />
                    <Skeleton v-if="loading" width="20%" height="40px" />
                </div>
            </template>
        </SettingsLayout>
        <div class="divider"></div>
        <!-- Email -->
        <SettingsLayout>
            <template #info>
                <div class="info_container">
                    <h4>
                        {{ $t('settings.profile_settings.email') }}
                    </h4>
                    <p>
                        {{ $t('settings.profile_settings.email_desc') }}
                    </p>
                </div>
            </template>
            <template #main>
                <div>
                    <InputText v-model="email" placeholder="Email"  v-if="!loading" />
                    <Skeleton v-if="loading" width="20%" height="40px" />
                </div>
            </template>
        </SettingsLayout>
        <div class="divider"></div>
        <!-- Password -->
        <SettingsLayout>
            <template #info>
                <div class="info_container">
                    <h4>
                        {{ $t('settings.profile_settings.password') }}
                    </h4>
                    <p>
                        {{ $t('settings.profile_settings.password_desc') }}
                    </p>
                </div>
            </template>
            <template #main>
                <div  v-if="!loading">
                    <InputText v-model="password" :placeholder="$t('settings.profile_settings.password')"   />
                    <InputText v-model="confirmPassword" :placeholder="$t('settings.profile_settings.confirm_password')" style="margin-left: 10px"   />
                </div>
                <div v-if="loading" style="display: flex;gap: 10px;align-items:center">
                    <Skeleton width="20%" height="40px" />
                    <Skeleton width="20%" height="40px" />
                </div>

            </template>
        </SettingsLayout>
        <div class="divider"></div>
        <!-- Phone -->
        <SettingsLayout>
            <template #info>
                <div class="info_container">
                    <h4>
                        {{ $t('settings.profile_settings.phone') }}
                    </h4>
                    <p>
                        {{ $t('settings.profile_settings.phone_desc') }}
                    </p>
                </div>
            </template>
            <template #main>
                <div>
                    <InputText v-model="phone" placeholder="Phone"  v-if="!loading" />
                    <Skeleton v-if="loading" width="20%" height="40px" />
                </div>
            </template>
        </SettingsLayout>
        <div class="divider"></div>

        <!-- Address -->
        <SettingsLayout>
            <template #info>
                <div class="info_container">
                    <h4>
                        {{ $t('settings.profile_settings.address') }}
                    </h4>
                    <p>
                        {{ $t('settings.profile_settings.address_desc') }}
                    </p>
                </div>
            </template>
            <template #main>
                <div>
                    <InputText v-model="address" placeholder="Address"  v-if="!loading" />
                    <Skeleton v-if="loading" width="20%" height="40px" />
                </div>
            </template>
        </SettingsLayout>
        <div class="divider"></div>

        <!-- Company -->
        <SettingsLayout>
            <template #info>
                <div class="info_container">
                    <h4>
                        {{ $t('settings.profile_settings.company') }}
                    </h4>
                    <p>
                        {{ $t('settings.profile_settings.company_desc') }}
                    </p>
                </div>
            </template>
            <template #main>
                <div  v-if="!loading" >
                    {{ company }}
                </div>
                <div v-if="loading">
                   <Skeleton width="20%" height="40px" />
                </div>
            </template>
        </SettingsLayout>
        <div class="divider"></div>

        <!-- Role -->
        <SettingsLayout>
            <template #info>
                <div class="info_container">
                    <h4>
                        {{ $t('settings.profile_settings.role') }}
                    </h4>
                    <p>
                        {{ $t('settings.profile_settings.role_desc') }}
                    </p>
                </div>
            </template>
            <template #main>
                <div  v-if="!loading" >
                    {{ role.toUpperCase() }}
                </div>
                <div v-if="loading">
                    <Skeleton width="20%" height="40px" />
                </div>
            </template>
        </SettingsLayout>

        <button class="save_user" @click="saveUser" :disabled="loading">
            {{ $t('save') }}
        </button>
    </div>
</template>

<script setup>
import { ref, onBeforeMount } from 'vue';
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import InputText from 'primevue/inputtext';
import Cookies from 'js-cookie';
import Skeleton from 'primevue/skeleton';
import ErrorsComponent from '../ErrorsComponent.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const errors = ref([]);

// Variables reactivas
const name = ref('');
const email = ref('');
const password = ref('');
const confirmPassword = ref('');
const phone = ref('');
const address = ref('');
const company = ref('');
const role = ref('');
const loading = ref(true);

// Función para obtener el perfil
const getProfile = async () => {
    loading.value = true;
    try {
        const response = await fetch('http://localhost:8000/api/me/profile', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': Cookies.get('authToken') ?? ''
            }
        });
        
        const data = await response.json();
        
        if (response.ok) {
            // Asigna los valores a las variables reactivas
            name.value = data.name;
            email.value = data.email;
            company.value = data.company;
            role.value = data.role;
            phone.value = data.phone;
            address.value = data.address;
            // Agrega otros campos según sea necesario
        } else {
            console.error(data);
        }

    } catch (error) {
        console.error(error);
    }
    loading.value = false;
}

onBeforeMount(() => {
    getProfile();
});


const saveUser = async () => {

    loading.value = true;
    if(password.value !== confirmPassword.value) {
        alert(t('settings.profile_settings.password_mismatch'));
        loading.value = false;
        return;
    }

    try {
        const response = await fetch('http://localhost:8000/api/me/profile', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': Cookies.get('authToken') ?? ''
            },
            body: JSON.stringify({
                name: name.value,
                email: email.value,
                password: password.value,
                phone: phone.value,
                address: address.value,
            })
        });

        const data = await response.json();

        if (response.ok) {
            console.log(data);
        } else {
            console.error(data);
            errors.value = data.errors;
        }

    } catch (error) {
        console.error(error);
    }
    loading.value = false;
}

</script>

<style scoped lang="scss">
    .info_container {
        h4 {
            margin-bottom: 6px;
        }
    }
    
    .save_user {
        margin-top: 20px;
        padding: 10px 20px;
        background-color: #000000;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;

        &:hover {
            background-color: #333333;
        }

        &:disabled {
            background-color: #d5d5d5;
            cursor: not-allowed;
        }
    }
</style>
