<template>
  <div>
    <div v-if="templates.length === 0">No templates available.</div>
    <div v-else class="container_templates">
      <div v-for="template in templates" :key="template.id">
        <InvoiceTemplateContent :template="template" @select-template="handleTemplateSelection" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onBeforeMount, computed } from 'vue';
import { useInvoiceTemplateStore } from '@/stores/invoiceTemplaceStore';
import InvoiceTemplateContent from '@/components/Mapping/InvoiceTemplateContent.vue';
import Cookies from 'js-cookie';
import { useRouter } from 'vue-router';  

const router = useRouter();

const invoiceTemplateStore = useInvoiceTemplateStore();
const selectedTemplate = computed(() => invoiceTemplateStore.template);

const templates = ref([]);

onBeforeMount(async () => {
  templates.value = await fetchAllTemplateInvoices();
});

const fetchAllTemplateInvoices = async () => {
  try {
    const response = await fetch('http://localhost:8000/api/company/invoice-templates', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': Cookies.get('authToken') ?? ''
      }
    });

    if (!response.ok) {
      throw new Error('Error fetching invoice templates');
    }

    const data = await response.json();
    return data; // Suponiendo que la respuesta tiene la propiedad `templates`
  } catch (error) {
    console.error(error);
    return [];
  }
};

const handleTemplateSelection = (selectedTemplate) => {
  invoiceTemplateStore.setTemplateName(selectedTemplate.template_name);
  invoiceTemplateStore.setTemplateId(selectedTemplate.id);
  invoiceTemplateStore.setColumnMappings(selectedTemplate.column_mappings);
  invoiceTemplateStore.setFormulas(selectedTemplate.formulas);
  invoiceTemplateStore.setValidationRules(selectedTemplate.validations_rules);
  invoiceTemplateStore.setAggregations(selectedTemplate.aggregations);

  router.push('/mapping-settings/invoice-template/edit/'+ selectedTemplate.id);
};
</script>
<style scoped lang="scss">
    .container_templates {
        margin-top: 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        width: 100%;
    }
    
</style>
