<template>
    <div>
        <div class="container_header">
            <h2>Corrector</h2>
        </div>
        <div class="search_file">
            <input v-model="search_input" type="text" placeholder="Search Invoice By Id or Name" @change="searchFile" />
            <i class="pi pi-search" @click="searchFile"></i>
        </div>
        <section class="invoices_manager">
            <div class="header_invoice_manager">
                <h4>Recent Invoices</h4>
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
        </section>


        <Drawer v-model:visible="isDrawerOpen" style="width: 50% !important;"  position="right" class="p-drawer_styled" :visible="isDrawerOpen" :modal="true" :showHeader="false" :baseZIndex="10000" @onHide="isDrawerOpen = false">
             <template #header>
                <header>
                    <i class="pi pi-file"></i>Invoice Corrector
                </header>
            </template>
            <div v-if="!drawerContentLoading">
                <p style="margin-bottom: 20px;margin-top: 20px;"><strong>Preview Corrected: </strong></p>
                <TableCorrector style="margin-bottom: 20px;margin-top: 20px;" :invoiceProccesed="drawerContentInvoice" />
                <div class="form-group name_invoice_edit">
                    <label for="name_invoice"><strong>Name Invoice:</strong></label>
                    <InputText id="name_invoice" v-model="invoiceSelected.name_invoice" />
                    <label for="status"><strong>Status:</strong></label>
                    <Select name="status" id="status" v-model="invoiceSelected.status" :options="optionsSelect" 
                    optionLabel="name" optionValue="value" placeholder="Select Status"/>
                    <br>
                    <br>
                    <label for="date_to_pay"><strong>Date to pay:</strong></label>
                    <DatePicker v-model="invoiceSelected.date_to_pay"  @update:modelValue="checkDate"/>
                    <p v-if="dateWarning" style="color: red; font-weight: bold;margin-top: 10px;">
                        <strong>The payment date has already passed!</strong>
                    </p>
                    <br>
                    <button class="save_name" @click="updateInvoice" style="margin-top: 10px;">
                        Update
                    </button>
                </div>
                <button class="download_button" @click="downloadCorrected(invoiceSelected)">
                    <i class="pi pi-download"></i>
                    Download corrected
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
    invoiceSelected.value = invoice;

    drawerContentLoading.value = false;
};

onBeforeMount(async () => {
    const data = await getInvoices();
    invoices.value = data;
    invoices_loading.value = false;
});

const searchFile = async () => {
    invoices_loading.value = true;
    const data = await getInvoices(search_input.value);
    invoices.value = data;
    invoices_loading.value = false;
};

const updateInvoice = async () => {
    try{
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
                date_to_pay: invoiceSelected.value.date_to_pay
            })
        });

        const data = await response.json();

        if(data.errors){
            notify({
                severity: 'error',
                summary: 'Error',
                detail: 'An error occurred',
            });
        }else{
            notify({
                severity: 'success',
                summary: 'Success',
                detail: 'Correction rule saved successfully',
            });
        }

    } catch (error) {
        console.log(error);
        notify({
            severity: 'error',
            summary: 'Error',
            detail: 'An error occurred',
        });
    }
    
};

const getInvoices = async (
    search
) => {

    let url = 'http://localhost:8000/api/company/invoices';

    if (search) {
        url = `http://localhost:8000/api/company/invoices?search=${search}`;
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
        return data;
    } catch (error) {
        console.log(error);
    }
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

</style>