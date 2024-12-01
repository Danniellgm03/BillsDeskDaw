<template>
    <div>
        <form action="" @submit.prevent="createInvoiceTemplate">
            <div class="form-group">
                <label for="template_name">Template name</label>
                <InputText type="text" id="template_name" v-model="invoiceTemplateJson.template_name" :disabled="loading" />
            </div>
            <button type="submit" :disabled="loading">Save</button>
        </form>
    </div>
</template>

<script setup>
import { useMappingColumnsStore } from '@/stores/mappingColumnsStore';
import { useInvoiceTemplateStore } from '@/stores/invoiceTemplaceStore';
import { computed, ref } from 'vue';
import Cookies from 'js-cookie';
import { useRouter } from 'vue-router';  // Asegúrate de importar useRouter
import InputText from 'primevue/inputtext';
import { useNotificationService } from '@/utils/notificationService';

const { notify } = useNotificationService();


// Inicializa el router
const router = useRouter();

const loading = ref(false);

const mappingColumnsStore = useMappingColumnsStore();
const columnsToMap = computed(() => mappingColumnsStore.columns);

const invoiceTemplateStore = useInvoiceTemplateStore();
const invoiceTemplateJsonStore = ref(invoiceTemplateStore.invoiceTemplate);

const invoiceTemplateJson = ref({
    "template_name": "",
    "company_id": 1,
    "column_mappings": columnsToMap.value,
    "formulas": [],
    "validations_rules": [],
    "aggregations": []
});

const createInvoiceTemplate = async () => {
    try {
        loading.value = true;
        const response = await fetch('http://localhost:8000/api/company/invoice-templates', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': Cookies.get('authToken') ?? ''
            },
            body: JSON.stringify(invoiceTemplateJson.value)
        });

        if (!response.ok) {
            throw new Error('Error creating invoice template');
        }

        const data = await response.json();

        // Actualiza el store con los datos del template creado
        invoiceTemplateStore.setTemplateName(data.template.template_name);
        invoiceTemplateStore.setTemplateId(data.template.id);
        invoiceTemplateStore.setColumnMappings(data.template.column_mappings);
        invoiceTemplateStore.setFormulas(data.template.formulas);
        invoiceTemplateStore.setValidationRules(data.template.validations_rules);
        invoiceTemplateStore.setAggregations(data.template.aggregations);

        notify({
            severity: 'success',
            summary: 'Success',
            detail: 'Invoice template created successfully'
        });

        
        // Redirigir al usuario a la ruta de "templates existentes"
        router.push('/mapping-settings/invoice-template/existing');
        loading.value = false;
        
    } catch (error) {
        console.error(error);
        notify({
            severity: 'error',
            summary: 'Error',
            detail: 'Error creating invoice template'
        });
    }
};
</script>

<style scoped lang='scss'>
.form-group {
    margin-bottom: 20px;
    margin-top: 20px;

    label {
        display: block;
        margin-bottom: 5px;
    }

    input {
        width: 100%;
        padding: 10px;
        border: 1px solid #e7e7e7;
        border-radius: 5px;
    }
}

button {
    padding: 10px 20px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;

    &:disabled {
        background-color: #ccc;
        cursor: not-allowed;
    }
}
</style>
