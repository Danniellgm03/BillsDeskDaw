<template>
    <div>
        <SettingsLayout>
            <template #info>
                <h2>
                    {{ $t('settings.contact_templates.title') }}
                </h2>
                <p>
                    {{ $t('settings.contact_templates.p1') }}
                </p>
                <p>
                    {{ $t('settings.contact_templates.p2') }}
                </p>
                <p>
                    {{ $t('settings.contact_templates.p3') }}
                </p>
            </template>
            <template #main>
                <form @submit.prevent="createContact" class="form_contact" :class="{
                    'editLoading': editLoading
                }">
                    <ErrorsComponent :errors="errors" v-if="(Object.keys(errors)).length > 0" />
                    <div class="form-group">
                        <label for="name" class="form-label">
                            {{ $t('settings.contact_templates.name') }}
                        </label>
                        <InputText type="text" class="form-control" id="name" v-model="contact.name"/>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">
                            {{ $t('settings.contact_templates.email') }}
                        </label>
                        <InputText type="email" class="form-control" id="email" v-model="contact.email"/>
                    </div>
                    <div class="form-group">
                        <label for="phone" class="form-label">
                            {{ $t('settings.contact_templates.phone') }}
                        </label>
                        <InputText type="text" class="form-control" id="phone" v-model="contact.phone"/>
                    </div>
                    <div class="form-group">
                        <label for="address" class="form-label">
                            {{ $t('settings.contact_templates.address') }}
                        </label>
                        <InputText type="text" class="form-control" id="address" v-model="contact.address"/>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        {{ $t('settings.contact_templates.create_contact') }}
                    </button>
                </form>
            </template>
        </SettingsLayout>
        <div class="divider"></div>
        <section class="contacts_container">
            <h3>
                {{ $t('settings.contact_templates.title') }}
            </h3>
            <div class="contacts" v-if="!loading">
                <ContactComponentContent v-for="contact in contacts" :contact="contact" :key="contact.id" @editContact="editContact"/>
            </div>
            <div class="loading_container" v-else>
                <LoadingTemplate />
            </div>
            <div v-if="contacts.length <= 0 && !loading" class="container_not_found">
                    <img src="/not_found.webp" alt="not found">
            </div>
        </section>

        <Drawer v-model:visible="isDrawerOpen" position="right" class="p-drawer_styled">
               <template #header>
                <header>
                    <i class="pi pi-file"></i> {{ $t('settings.contact_templates.contact_edit') }}
                </header>
            </template>
            <form @submit.prevent="updateContact" class="form_contact" :class="{
                'editLoading': editLoading
            }" >
                <ErrorsComponent :errors="errorsEdit" v-if="(Object.keys(errorsEdit)).length > 0" />
                <div class="form-group" >
                    <label for="name" class="form-label">
                        {{ $t('settings.contact_templates.name') }}
                    </label>
                    <InputText type="text" class="form-control" id="name" v-model="contactSelected.name" :disabled="editLoading"/>
                </div>
                <div class="form-group">
                    <label for="email" class="form-label">
                        {{ $t('settings.contact_templates.email') }}
                    </label>
                    <InputText type="email" class="form-control" id="email" v-model="contactSelected.email"  :disabled="editLoading"/>
                </div>
                <div class="form-group">
                    <label for="phone" class="form-label">
                        {{ $t('settings.contact_templates.phone') }}
                    </label>
                    <InputText type="text" class="form-control" id="phone" v-model="contactSelected.phone"  :disabled="editLoading"/>
                </div>
                <div class="form-group">
                    <label for="address" class="form-label">
                        {{ $t('settings.contact_templates.address') }}
                    </label>
                    <InputText type="text" class="form-control" id="address" v-model="contactSelected.address"  :disabled="editLoading"/>
                </div>
                <button type="submit" class="btn btn-primary" >
                    {{ $t('settings.contact_templates.update_contact') }}
                </button>
            </form>
        </Drawer>



    </div>
</template>

<script setup>
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import { ref, onBeforeMount } from 'vue';
import Cookies from 'js-cookie'; 
import InputText from 'primevue/inputtext';
import ContactComponentContent from '../Contact/ContactComponentContent.vue';
import Drawer from 'primevue/drawer';
import LoadingTemplate from '@/components/LoadingTemplate.vue';
import { useNotificationService } from '@/utils/notificationService';
const { notify } = useNotificationService();
import ErrorsComponent from '../ErrorsComponent.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();


const errors = ref({});
const errorsEdit = ref({});
const isDrawerOpen = ref(false);

const loading = ref(false);


const contact = ref({
    name: '',
    email: '',
    phone: '',
    address: ''
});

const contactSelected = ref(null);

const editLoading = ref(false);

const contacts = ref([])

const editContact = (contact) => {
    isDrawerOpen.value = true;
    contactSelected.value = {
        ...contact
    };
}


onBeforeMount(async () => {
    contacts.value = await fetchAllContacts();
})

const updateContact = async () => {
    try {

        if ( contactSelected.value.email === '' || contactSelected.value.phone === '' || contactSelected.value.address === '') {
            notify({
                severity: 'error',
                summary: t('error'),
                detail: t('all_fields_are_required'),
            });
            return;
        }

        editLoading.value = true;
        const response = await fetch(`http://localhost:8000/api/company/contacts/${contactSelected.value.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': Cookies.get('authToken') ?? ''
            },
            body: JSON.stringify(contactSelected.value)
        });

        const data = await response.json();

        if (response.status === 422) {
            errorsEdit.value = data.errors;
            editLoading.value = false;
            return;
        }

        contacts.value = await fetchAllContacts();
        isDrawerOpen.value = false;
        editLoading.value = false;
        notify({
            severity: 'success',
            summary: t('success'),
            detail: t('settings.contact_templates.contact_updated'),
        });
        errorsEdit.value = {};
        return data;
    } catch (error) {
        console.log(error)
        notify({
            severity: 'error',
            summary: t('error'),
            detail:  t('settings.contact_templates.failed_update_contact'),
        });
    }
}

const fetchAllContacts = async () => {
    try {
        loading.value = true;
        const response = await fetch('http://localhost:8000/api/company/contacts', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': Cookies.get('authToken') ?? ''
            }
        });

        const data = await response.json();
        loading.value = false;
        return data;
    } catch (error) {
        console.log(error)
    }
}

const createContact = async () =>{
    try {

        if (contact.value.name === '' || contact.value.email === '' || contact.value.phone === '' || contact.value.address === '') {
            notify({
                severity: 'error',
                summary: t('error'),
                detail: t('all_fields_are_required'),
            });
            return;
        }

        editLoading.value = true;
        const response = await fetch('http://localhost:8000/api/company/contacts', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': Cookies.get('authToken') ?? ''
            },
            body: JSON.stringify(contact.value)
        });

        const data = await response.json();


        if (response.status === 422) {
            errors.value = data.errors;
            editLoading.value = false;
            return;
        }

        editLoading.value = false;
        contacts.value = await fetchAllContacts();
        notify({
            severity: 'success',
            summary: t('success'),
            detail: t('settings.contact_templates.contact_created'),
        });
        contact.value = {
            name: '',
            email: '',
            phone: '',
            address: ''
        }
        errors.value = {};
        return data;
    } catch (error) {
        console.log(error)
        notify({
            severity: 'error',
            summary: t('error'),
            detail: t('settings.contact_templates.failed_create_contact'),
        });
    }
}


</script>

<style scoped lang='scss'>


    .form_contact{

        &.editLoading{
            pointer-events: none;
            opacity: .5;
        }
        
        .form-group{
            margin-bottom: 1rem;
        }

        .form-label{
            display: block;
            margin-bottom: .5rem;
        }

        .form-control{
            width: 100%;
            padding: .375rem .75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: .25rem;
            transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
        }

        .btn{
            display: inline-block;
            font-weight: 400;
            color: #212529;
            text-align: center;
            vertical-align: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            background-color: transparent;
            border: 1px solid transparent;
            padding: .375rem .75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: .25rem;
            transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
            
            &:disabled{
                cursor: not-allowed;
                pointer-events: none;
                opacity: .65;
            }
        
        }

        .btn-primary{
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover{
            color: #fff;
            background-color: #0069d9;
            border-color: #0062cc;
        }
        
    }


    .contacts{
        display: flex;
        flex-wrap: wrap;
        gap: 5px 10px;
        margin-top: 20px;
    }


</style>