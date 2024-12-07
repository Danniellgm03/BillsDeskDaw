<template>
    <div>
        <div class="container_header">
            <h2>{{ $t('corrector.title') }}</h2>
        </div>
        <div class="search_file">
            <input v-model="search_input" type="text" :placeholder="$t('corrector.search_input')" @change="searchFile" />
            <i class="pi pi-search" @click="searchFile"></i>
        </div>
        <section class="invoices_manager">
            <div class="header_invoice_manager">
                <h4>{{ $t('corrector.recent_invoices') }}</h4>
                <div class="view_tab" @click="changeLayout">
                    <button :class="{ active: layout === 'grid_extend' }">
                        <i class="pi pi-table"></i>
                    </button>
                    <button :class="{ active: layout === 'grid' }">
                        <i class="pi pi-th-large"></i>
                    </button>
                </div>
            </div>
            <div :class="['container_invoices', layout]" v-if="!invoices_loading">
                <InvoiceContentComponent v-for="invoice in invoices" :key="invoice.id" :invoice="invoice" @correctInvoice="correctInvoice" />
            </div>
            <div class="loading_container" v-else>
                <LoadingTemplate/>
            </div>
            <div v-if="invoices.length <= 0 && !invoices_loading" class="container_not_found">
               <img src="/not_found.webp" alt="not found">
            </div>

            <div class="pagination" :class="{
                'd-none': invoices.length <= 0 || invoices_loading
            }">
                <Paginator v-model:page="pagination.page" :totalRecords="pagination.total" :rows="pagination.limit"
                    :rowsPerPageOptions="[5, 10, 20]" @page="pageChange" />
            </div>

        </section>

        <Drawer v-model:visible="isDrawerOpen" style="width: 50%;"  position="right" class="p-drawer_styled" :visible="isDrawerOpen" :modal="true" :showHeader="false" :baseZIndex="10000" @onHide="isDrawerOpen = false">
             <template #header>
                <header>
                    <i class="pi pi-file"></i> {{ $t('corrector.drawer_title') }}
                </header>
            </template>
            <div v-if="!drawerContentLoading">
                <p style="margin-bottom: 20px;margin-top: 20px;"><strong>{{ $t('corrector.preview_corrected') }}: </strong></p>
                <TableCorrector style="margin-bottom: 20px;margin-top: 20px;" :invoiceProccesed="drawerContentInvoice" />
                <div class="form-group name_invoice_edit">
                    <ErrorsComponent :errors="errors"  v-if="errors != null"/>
                    <label for="name_invoice"><strong>{{ $t('corrector.name_invoice') }}:</strong></label>
                    <InputText id="name_invoice" v-model="invoiceSelected.name_invoice" />
                    <label for="status"><strong>{{ $t('corrector.status') }}:</strong></label>
                    <Select name="status" id="status" v-model="invoiceSelected.status" :options="optionsSelect" 
                    optionLabel="name" optionValue="value" :placeholder="$t('corrector.select_status')"/>
                    <br>
                    <br>
                    <label for="date_to_pay"><strong>{{ $t('corrector.date_to_pay') }}:</strong></label>
                    <DatePicker v-model="invoiceSelected.date_to_pay"  @update:modelValue="checkDate"/>
                    <p v-if="dateWarning" style="color: red; font-weight: bold;margin-top: 10px;">
                        <strong>{{ $t('corrector.payment_date') }}</strong>
                    </p>
                    <br>
                    <br>
                    <label for="contact_id"><strong>{{ $t('corrector.contact') }}:</strong></label>
                    <Select name="contact_id" id="contact_id" v-model="invoiceSelected.contact_id" :options="contacts"
                    optionLabel="name" optionValue="id" :placeholder="$t('corrector.select_contact')"/>
                    <br>
                    <button class="save_name" @click="updateInvoice" style="margin-top: 10px;">
                       {{ $t('corrector.update') }}
                    </button>
                </div>
                <button class="download_button" @click="downloadCorrected(invoiceSelected)">
                    <i class="pi pi-download"></i>
                    {{ $t('corrector.download_corrected') }}
                </button>
            </div>
            <div class="loading_container" v-else>
                <LoadingTemplate/>
            </div>
        </Drawer>


    </div>
</template>

<script setup>
import { ref, onBeforeMount } from 'vue';
import Cookies from 'js-cookie';
import LoadingTemplate from '@/components/LoadingTemplate.vue';
import InvoiceContentComponent from '@/components/Corrector/InvoiceContentComponent.vue';
import Drawer from 'primevue/drawer';
import TableCorrector from '@/components/Corrector/TableCorrector.vue';
import InputText from 'primevue/inputtext';
import { useNotificationService } from '@/utils/notificationService';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import Paginator from 'primevue/paginator';
import ErrorsComponent from '@/components/ErrorsComponent.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const errors = ref(null);


const pagination = ref({
    page: 1,
    limit: 5,
    total: 0,
    search: ''
});


const { notify } = useNotificationService();


const optionsSelect = ref([
    { name: 'Pending', value: 'pending' },
    { name: 'Corrected', value: 'corrected' },
    { name: 'Rejected', value: 'rejected' },
    { name: 'Paid', value: 'paid'}
]);


const loading = ref(true);
const invoices_loading = ref(true);
const search_input = ref('');
const invoices = ref([]);
const isDrawerOpen = ref(false);
const drawerContentLoading = ref(true);
const drawerContentInvoice = ref({});
const invoiceSelected = ref({});
const contacts = ref([]);

const layout = ref('grid_extend'); 

const changeLayout = () => {
    if (layout.value === 'grid') {
        layout.value = 'grid_extend';
    } else {
        layout.value = 'grid';
    }
};

const dateWarning = ref(false);

const checkDate = (newDate) => {
    const today = new Date();
    const selectedDate = new Date(newDate);

    // Comparar fechas (sin considerar hora)
    if (selectedDate < today.setHours(0, 0, 0, 0)) {
        dateWarning.value = true; // Muestra la advertencia
    } else {
        dateWarning.value = false; // Oculta la advertencia
    }

    invoiceSelected.value.date_to_pay = newDate;
};

const correctInvoice = async (invoice) => {
    isDrawerOpen.value = true;
    drawerContentLoading.value = true;
    const processedInvoice = await processInvoice(invoice);
    drawerContentInvoice.value = processedInvoice.data;    

    invoiceSelected.value = {
        ...invoice
    };
    errors.value = null;
    contacts.value = await getAllContacts();

    drawerContentLoading.value = false;
};

const getAllContacts = async () => {
    try {
        const response = await fetch('http://localhost:8000/api/company/contacts', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': Cookies.get('authToken') ?? ''
            }
        });

        const data = await response.json();
        return data;
    } catch (error) {
        console.log(error);
    }
};

onBeforeMount(async () => {
    const data = await getInvoices();
    invoices.value = data;
    invoices_loading.value = false;
});

const searchFile = async () => {
    invoices_loading.value = true;
    const data = await getInvoices(
        pagination.value.page,
        pagination.value.limit,
        search_input.value
    );
    invoices.value = data;
    invoices_loading.value = false;
};

const updateInvoice = async () => {
    try{
        drawerContentLoading.value = true;
        const response = await fetch(`http://localhost:8000/api/company/invoices/${invoiceSelected.value.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': Cookies.get('authToken') ?? ''
            },
            body: JSON.stringify({
                name_invoice: invoiceSelected.value.name_invoice,
                status: invoiceSelected.value.status,
                date_to_pay: invoiceSelected.value.date_to_pay,
                contact_id: invoiceSelected.value.contact_id
            })
        });

        const data = await response.json();

        if(data.errors){

            errors.value = data.errors;

            notify({
                severity: 'error',
                summary: t('error'),
                detail: t('corrector.failed_save_invoice'),
            });
        }else{
            notify({
                severity: 'success',
                summary: t('success'),
                detail: t('corrector.invoice_saved'),
            });
        }

        drawerContentLoading.value = false;

    } catch (error) {
        console.log(error);
        notify({
            severity: 'error',
            summary: t('error'),
            detail: t('corrector.failed_save_invoice'),
        });

        errors.value = null;

        drawerContentLoading.value = false;
    }
    
};

const getInvoices = async (
    page = 1,
    limit = 5,
    search = '',
) => {

    let url = 'http://localhost:8000/api/company/invoices';


    url = `${url}?page=${page}&per_page=${limit}`;

    if (search) {
        url = `${url}&search=${search}`;
    }

    try {
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': Cookies.get('authToken') ?? ''
            }
        });

        const data = await response.json();

        let data_json = data.data;

        pagination.value = {
            total: data_json.total,
            page: data_json.current_page,
            limit: data_json.per_page,
            search: search_input.value
        };

        return data_json.data;
    } catch (error) {
        console.log(error);
    }
};

const pageChange = async (event) => {
    invoices_loading.value = true;
    const data = await getInvoices(event.page + 1, event.rows, search_input.value);
    invoices.value = data;
    invoices_loading.value = false;
};


const processInvoice = async (invoice) => {
    try {
        const response = await fetch(`http://localhost:8000/api/company/invoices/process/${invoice.id}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': Cookies.get('authToken') ?? ''
            }
        });

        const data = await response.json();
        return data;
    } catch (error) {
        console.log(error);
    }
};

const downloadCorrected = async (invoice) => {
    try {
        const response = await fetch(`http://localhost:8000/api/company/invoices/process/${invoice.id}/download`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': Cookies.get('authToken') ?? ''
            }
        });

        const data = await response.blob();
        const url = window.URL.createObjectURL(data);
        const a = document.createElement('a');
        a.href = url;
        a.download = ``;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.log(error);
    }
}



</script>

<style scoped lang='scss'>

    .form-group {
        margin-top: 20px;
        margin-bottom: 20px;

        label {
            display: block;
            margin-bottom: 5px;
        }

        input {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-bottom: 10px;

            &:focus {
                outline: none;
            }
        }

        button {
            background-color: #000000;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    }

    .container_header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        position: sticky;
        top: -20px;
        background-color: #fff;
        padding: 20px 0;
        border-bottom: 1px solid #ccc;
    }

    .search_file{
        position: relative;

        input{
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;

            &:focus{
                outline: none;
            }
        }

        i{
            font-size: 1.15em;
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }
    }
    

    .invoices_manager {
        margin-top: 20px;
        h4 {
            margin-bottom: 20px;
            font-weight: 300;
        }
    }

    .header_invoice_manager {
        display: flex;
        justify-content: space-between;
        align-items: center;

        .view_tab {
            display: flex;
            gap: 4px;
            background-color: #f1f1f1;
            border-radius: 5px;
            overflow: hidden;

            button{
                background-color: transparent;
                border: none;


                i{
                    font-size: 1.15em;
                    padding: 5px;
                    cursor: pointer;
                }

                &.active {
                    background-color: #007bff;
                    color: white;
                }
            }

        }
    }


    .container_invoices {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 20px;

        &.grid {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        }

        &.grid_extend {
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        }
    }

    
    .loading_container {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 50px 0;
    }

    .download_button{
        background-color: #007bff;
        color: white;
        width: 100%;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 20px;
        display: flex;
        align-items: center;
        justify-content: center;

        i{
            margin-right: 5px;
        }
    }

    .pagination {
        margin-top: 20px;
        display: flex;
        justify-content: center;

        &.d-none {
            display: none;
        }
    }

</style>