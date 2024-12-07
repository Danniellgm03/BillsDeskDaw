<template>
    <div class="invoice_container"  @click="correctInvoice(invoice)">
        <header class="rapid_actions">
            <!-- <i class="pi pi-file-check"></i> -->
        </header>
        <i class="pi pi-file"></i>
        <p class="invoice_id">{{ invoice.name_invoice  }}</p>
        <!--  get status, total_amount and saved_amount. If is null, put in the html Not Corrected -->
        <p class="invoice_status" :class="invoice.status.toLowerCase()">{{ invoice.status }}</p>
        <div class="divider"></div>
        <div class="footer_container">
            <div class="date">
                <p>{{ prettyDate(invoice.created_at) }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';


defineProps({
    invoice: {
        type: Object,
        required: true,
    },
});

const prettyDate = (date) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(date).toLocaleDateString('es-ES', options);
};

// Emisor de eventos
const emit = defineEmits(['correctInvoice']);

// Método para manejar el clic
const correctInvoice = (invoice) => {
    emit('correctInvoice', invoice); // Emitir el objeto invoice
};


</script>

<style scoped lang='scss'>

    .invoice_container{
        width: 100%;
        background-color: #f1f1f1;
        border-radius: 5px;
        display: flex;
        align-items: center;
        flex-direction: column;
        padding: 20px 5px;
        gap: 6px;
        cursor: pointer;

        .rapid_actions {
            width: 100%;
            display: flex;
            justify-content: flex-end;
            padding: 0 10px;
        }

        .invoice_id {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .invoice_status {
            font-size: 1.2rem;
            font-weight: bold;
        }

        .divider {
            width: 100%;
            height: 1px;
            background-color: #d1d1d1;
            margin: 10px 0;
        }

        .footer_container {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 5px;
            padding: 0 10px;

            strong {
                font-size: 1.2rem;
                font-weight: bold;
            }

            p {
                font-size: 1rem;
            }
        }
    }

    .invoice_container:hover {
        background-color: #e1e1e1;
    }

    .invoice_container .pi {
        font-size: 3rem;
        color: #333;
    }

    .invoice_container .pi-file-check {
        font-size: 2rem;
        color: #333;
    }

    .invoice_status{
        padding: 5px 10px;
        border-radius: 5px;
        font-size: .8em !important;

        &.pending {
            color: #d94a1c;
            background-color: #ffc8b7;
        }

        &.corrected {
            color: #00bd00;
            background-color: #c9e7cc;
        }

        &.rejected {
            color: #ff0000;
            background-color: #ffd9d9;
        }

        &.paid{
            color: #0006ff;
            background-color: #e0d9ff;
        }
    }

</style>