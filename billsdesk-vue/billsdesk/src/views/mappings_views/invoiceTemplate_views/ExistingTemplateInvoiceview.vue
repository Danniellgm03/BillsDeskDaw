<template>
  <div>
    <div class="container_templates" v-if="!loading">
      <div v-for="template in templates" :key="template.id">
        <InvoiceTemplateContent :template="template" @select-template="handleTemplateSelection" />
      </div>
    </div>
    <div class="loading_container"  v-else>
      <LoadingTemplate />
    </div>
    <div v-if="templates <= 0 && !loading" class="container_not_found">
      <img src="/not_found.webp" alt="not found">
         <button class="button_back">
            <router-link to="/mapping-settings/mapping">Create new template</router-link>
        </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onBeforeMount, computed } from 'vue';
import { useInvoiceTemplateStore } from '@/stores/invoiceTemplaceStore';
import InvoiceTemplateContent from '@/components/Mapping/InvoiceTemplateContent.vue';
import Cookies from 'js-cookie';
import { useRouter } from 'vue-router';  
import LoadingTemplate from '@/components/LoadingTemplate.vue';


const router = useRouter();

const loading = ref(false);

const invoiceTemplateStore = useInvoiceTemplateStore();
const selectedTemplate = computed(() => invoiceTemplateStore.template);

const templates = ref([]);

onBeforeMount(async () => {
  templates.value = await fetchAllTemplateInvoices();
});

const fetchAllTemplateInvoices = async () => {
  try {
    loading.value = true;
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
    loading.value = false;
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

    .container_not_found {
        flex-direction: column;

        .button_back{
          margin-top: 20px;
          background-color: #007bff;
          color: white;
          border: none;
          border-radius: 5px;
          cursor: pointer;

          a {
              color: white;
              text-decoration: none;
              width: 100%;
              height: 100%;
              padding: 10px 20px;
              display: block;
          }
      }
    }


    .container_templates {
        margin-top: 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        width: 100%;
    }

    
.loading_container {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 50px 0;
}
    
</style>
