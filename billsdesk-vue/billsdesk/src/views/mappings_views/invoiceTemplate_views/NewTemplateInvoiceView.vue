<template>
    <div>
        <form action="" @submit.prevent="createInvoiceTemplate">
            <div class="form-group">
                <label for="template_name">Template name</label>
                <input type="text" id="template_name" v-model="invoiceTemplateJson.template_name">
            </div>
            <button type="submit">Save</button>
        </form>
    </div>
</template>

<script setup>
import { useMappingColumnsStore } from '@/stores/mappingColumnsStore';
import { useInvoiceTemplateStore } from '@/stores/invoiceTemplaceStore';
import { computed, ref } from 'vue';
import Cookies from 'js-cookie';
import { useRouter } from 'vue-router';  // Asegúrate de importar useRouter

// Inicializa el router
const router = useRouter();

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
    console.log(JSON.stringify(invoiceTemplateJson.value));
    try {
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

        // Redirigir al usuario a la ruta de "templates existentes"
        router.push('/mapping-settings/invoice-template/existing');
        
    } catch (error) {
        console.error(error);
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
}
</style>
