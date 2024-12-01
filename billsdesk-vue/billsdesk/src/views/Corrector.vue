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

const getInvoices = async () => {
    try {
        const response = await fetch('http://localhost:8000/api/company/invoices', {
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


//http://localhost:8000/api/company/invoices/process/9

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