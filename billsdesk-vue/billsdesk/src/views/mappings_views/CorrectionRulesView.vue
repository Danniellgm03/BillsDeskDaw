<template>
  <div>
    <h2>{{ $t('corrector.template.rules.title') }}:</h2>
    
    <div v-if="!loading">
      <ErrorsComponent :errors="errors" v-if="errors != null"/>
      <p style="
      margin: 10px 0;
      "><strong>
          {{ invoiceTemplate?.template_name }}
      </strong></p>
  
  
      <!-- Formulario -->
      <form @submit.prevent="saveCorrectionRule" v-if="!loading">
        <h3>
          {{ $t('corrector.template.rules.create_or_edit_correction_rule') }}
        </h3>
  
        <!-- Nombre de la regla -->
        <div class="form-group">
          <label for="rule_name">
            {{ $t('corrector.template.rules.rule_name') }}
          </label>
          <InputText type="text" v-model="ruleData.rule_name" />
        </div>
  
        <!-- Condiciones -->
        <div class="form-group conditions">
          <label for="conditions">
            {{ $t('corrector.template.rules.conditions') }}
          </label>
          <div v-for="(condition, index) in ruleData.conditions" :key="index" class="condition">
            <InputText v-model="condition.field" placeholder="Field" />
            <Select v-model="condition.operator" :options="opertators" optionLabel="label" optionValue="value" 
            :placeholder="$t('corrector.template.rules.select_operator')" />
            <InputText v-model="condition.value" :placeholder="$t('corrector.template.rules.value')" />
            <button type="button" @click="removeCondition(index)" class="buttonremove">
              {{ $t('corrector.template.rules.remove_condition') }}
            </button>
          </div>
          <button type="button" @click="addCondition" class="button_add">
            {{ $t('corrector.template.rules.add_condition') }}
          </button>
        </div>
  
        <!-- Correcciones -->
        <div class="form-group corrections">
          <label for="corrections">
            {{ $t('corrector.template.rules.corrections') }}
          </label>
          <div v-for="(correction, index) in ruleData.corrections" :key="index" class="correction">
            <InputText v-model="correction.field" :placeholder="$t('corrector.template.rules.field')" />
            <InputText v-model="correction.new_column" :placeholder="$t('corrector.template.rules.new_column_optional')" />
            <InputText v-model="correction.action" placeholder="Action (update, subtract, etc.)" />
  
            <label>
               {{ $t('corrector.template.rules.correction_values') }}
            </label>
            <div v-for="(value, idx) in correction.value" :key="idx" class="value">
              <InputText v-model="value.min" placeholder="Min" />
              <InputText v-model="value.max" placeholder="Max" />
              <InputText v-model="value.step" :placeholder="$t('corrector.template.rules.step') + '(Optional)'" />
              <InputText v-model="value.value" :placeholder="$t('corrector.template.rules.value')" />
              <button type="button" @click="removeCorrectionValue(correction, idx)" class="buttonremove">
                {{ $t('corrector.template.rules.remove_value') }}
              </button>
            </div>
            <button type="button" @click="addCorrectionValue(correction)" class="button_add">
              {{ $t('corrector.template.rules.add_value') }}
            </button>
            <button type="button" @click="removeCorrection(index)" class="buttonremove">
              {{ $t('corrector.template.rules.remove_correction') }}
            </button>
          </div>
          <button type="button" @click="addCorrection" class="button_add">
            {{ $t('corrector.template.rules.add_correction') }}
          </button>
        </div>
  
        <button type="submit">
          {{ $t('corrector.template.rules.save_correction_rule') }}
        </button>
      </form>
  
      <div v-if="correctionRules.length > 0 " class="correction-rules-container">
          <h3>
              {{ $t('corrector.template.rules.correction_rule_created') }}
          </h3>
          <div v-for="(rule, index) in correctionRules" :key="index" class="correction-rule-card">
              <div class="rule-header">
              <h4>{{ rule.rule_name }}</h4>
              <button type="button" class="delete-button" @click="deleteRule(index)">
                  {{ $t('corrector.template.rules.delete_correction_rule') }}
              </button>
              </div>
              
              <div class="rule-section">
              <p><strong>{{ $t('corrector.template.rules.conditions') }}:</strong></p>
              <ul class="conditions-list">
                  <li v-for="(condition, idx) in rule.conditions" :key="idx" class="condition-item">
                  <span>{{ condition.field }}</span>
                  <span>{{ condition.operator }}</span>
                  <span>{{ condition.value }}</span>
                  </li>
              </ul>
              </div>
  
              <div class="rule-section">
              <p><strong>{{ $t('corrector.template.rules.corrections') }}:</strong></p>
              <ul class="corrections-list">
                  <li v-for="(correction, idx) in rule.corrections" :key="idx" class="correction-item">
                  <div class="correction-info">
                      <span><strong>{{ $t('corrector.template.rules.field') }}:</strong> {{ correction.field }}</span>
                      <span><strong>{{ $t('corrector.template.rules.new_field') }}:</strong> {{ correction.new_column }}</span>
                      <span><strong>{{ $t('corrector.template.rules.action') }}:</strong> {{ correction.action }}</span>
                  </div>
                  <ul class="values-list">
                      <li v-for="(value, i) in correction.value" :key="i" class="value-item">
                      <span><strong>Min:</strong> {{ value.min }}</span>
                      <span><strong>Max:</strong> {{ value.max }}</span>
                      <span><strong>{{ $t('corrector.template.rules.step') }}:</strong> {{ value.step || 'N/A' }}</span>
                      <span><strong>{{ $t('corrector.template.rules.value') }}:</strong> {{ value.value }}</span>
                      </li>
                  </ul>
                  </li>
              </ul>
              </div>
          </div>
      </div>
  
      <!-- button to route finish -->
      <button @click="fetchSaveInvoice" :disabled="loading">
        {{ $t('corrector.template.rules.finish_mapping') }}
      </button>
      
    </div>
    <div  class="loading_container" v-else>
      <LoadingTemplate />
    </div>

  </div>
</template>
<script setup>
import { ref, computed, onMounted, onBeforeMount } from 'vue';
import { useInvoiceTemplateStore } from '@/stores/invoiceTemplaceStore';
import { useSelectedFileStore } from '@/stores/selectedFileStore';
import Cookies from 'js-cookie';
import InputText from 'primevue/inputtext';
import { useRouter } from 'vue-router';
import LoadingTemplate from '@/components/LoadingTemplate.vue';
import { useNotificationService } from '@/utils/notificationService';
import ErrorsComponent from '@/components/ErrorsComponent.vue';
import Select from 'primevue/select';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const opertators = [
  { label: t('equal'), value: '==' },
  { label: t('not_equal'), value: '!=' },
  { label: t('greater_than'), value: '>' },
  { label: t('less_than'), value: '<' },
  { label: t('greater_than_or_equal'), value: '>=' },
  { label: t('less_than_or_equal'), value: '<=' }
]


const errors = ref(null);

const { notify } = useNotificationService();

const router = useRouter();

const loading = ref(false);

const invoiceTemplateStore = useInvoiceTemplateStore();
const invoiceTemplate = computed(() => invoiceTemplateStore.template);

const selectedFileStore = useSelectedFileStore();
const selectedFile = computed(() => selectedFileStore.selectedFile);

const correctionRules = ref([]); // Aquí se guardan las reglas cargadas
const ruleData = ref({
  rule_name: '',
  conditions: [], // Solo una condición
  corrections: [], // Solo una corrección
  template_id: invoiceTemplate.value.template_id,
  company_id: 1,
});

onBeforeMount(async () => {
  if (invoiceTemplate.value.template_id == '' || Object.keys(selectedFile.value).length === 0) {
    router.push('/mapping-settings/selecting-files');
    return;
  }else{
    const data = await fetchCorrectionRules();
    correctionRules.value = data.correction_rules;
  }

});

// Cargar las Correction Rules desde el endpoint
const fetchCorrectionRules = async () => {
  try {
    loading.value = true;
    const response = await fetch(`http://localhost:8000/api/company/invoices/template/${invoiceTemplate.value.template_id}/correction-rules`, {
      headers: { Authorization: `Bearer ${Cookies.get('authToken')}` },
    });
    const data = await response.json();
    loading.value = false;
    return data;
  } catch (error) {
    console.error('Error fetching correction rules:', error);
    return [];
  }
};

// Métodos para manejar el formulario
const addCondition = () => {
  if (ruleData.value.conditions.length === 0) {
    ruleData.value.conditions.push({ field: '', operator: '', value: '' });
  }
};
const removeCondition = () => {
  ruleData.value.conditions = [];
};

const addCorrection = () => {
  if (ruleData.value.corrections.length === 0) {
    ruleData.value.corrections.push({ field: '', new_column: '', action: '', value: [] });
  }
};
const removeCorrection = () => {
  ruleData.value.corrections = [];
};

const addCorrectionValue = (correction) => correction.value.push({ min: '', max: '', step: '', value: '' });
const removeCorrectionValue = (correction, idx) => correction.value.splice(idx, 1);

const saveCorrectionRule = async () => {
  try {
    loading.value = true;
    const response = await fetch(`http://localhost:8000/api/company/correction-rules`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${Cookies.get('authToken')}`,
      },
      body: JSON.stringify(ruleData.value),
    });
    const data = await response.json();

    if(data.errors) {
      errors.value = data.errors;
      loading.value = false;
      return;
    }

    ruleData.value = {
      rule_name: '',
      conditions: [],
      corrections: [],
      template_id: invoiceTemplate.value.template_id,
      company_id: 1,
    };

    // Actualizar las reglas de corrección
    let corrections = await fetchCorrectionRules();
    correctionRules.value = corrections.correction_rules;

    notify({
      severity: 'success',
      summary: t('success'),
      detail: t('corrector.template.rules.success_save_correction_rule'),
    });

    loading.value = false;
  } catch (error) {
    console.error('Error saving correction rule:', error);

    notify({
      severity: 'error',
      summary: t('error'),
      detail: t('corrector.template.rules.failed_save_correction_rule'),
    });
  } finally {
    loading.value = false;
    errors.value = null;
  }
};


const fetchSaveInvoice = async () => {
  try {
    loading.value = true;
    const response = await fetch(`http://localhost:8000/api/company/invoices`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${Cookies.get('authToken')}`,
      },
      body: JSON.stringify({
        template_id: invoiceTemplate.value.template_id,
        file_id: selectedFile.value.id,
      }),
    });
    const data = await response.json();

    loading.value = false;

    notify({
      severity: 'success',
      summary: t('success'),
      detail: t('corrector.template.rules.invoice_saved'),
    });

    router.push('/corrector');
  } catch (error) {
    console.error('Error saving invoice:', error);
    notify({
      severity: 'error',
      summary: t('error'),
      detail: t('corrector.template.rules.failed_save_invoice'),
    });
  }
};
</script>


<style scoped lang="scss">
    form {
        padding: 20px;
        background: #f7f7f7;
        border-radius: 5px;

        label {
            display: block;
            margin-bottom: 5px;
            margin-top: 10px;
        }
    }
    .form-group {
         margin-bottom: 20px;

         &.conditions{
            background-color: #ebebeb;
            padding: 20px;
            border-radius: 5px;

            .condition {
                display: flex;
                gap: 10px;
                margin-bottom: 10px;
                align-items: center;
                flex-wrap: wrap;
                background-color: #f5f5f5; 
                padding: 10px;
                margin-top: 10px;
                border-radius: 5px;
            }
         }

        &.corrections{
            border-radius: 5px;
            background-color: #ebebeb;
            padding: 20px;

            .correction {
                display: flex;
                gap: 10px;
                margin-bottom: 10px;
                align-items: center;
                flex-wrap: wrap;
                background-color: #f5f5f5; 
                padding: 10px;
                margin-top: 10px;
                border-radius: 5px;
            }

            .value {
                display: flex;
                gap: 10px;
                margin-bottom: 10px;
                align-items: center;
                flex-wrap: wrap;
                background-color: #f5f5f5; 
                padding: 10px;
                margin-top: 10px;
                border-radius: 5px;
            }
        }
    }
    button {
    margin-top: 10px;
    padding: 10px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    }
    button:hover {
    background-color: #0056b3;
    }
    .button_add {
    background-color: #28a745;
    }
    .button_add:hover {
    background-color: #218838;
    }
    .buttonremove {
    background-color: #dc3545;
    }
    .buttonremove:hover {
    background-color: #c82333;
    }

    .correction-rules-container {
  background-color: #f9f9f9;
  padding: 20px;
  border-radius: 10px;

  h3 {
    margin-bottom: 20px;
    font-size: 1.5rem;
    color: #333;
  }
}

.correction-rule-card {
  background: #ffffff;
  padding: 20px;
  border: 1px solid #e0e0e0;
  border-radius: 10px;
  margin-bottom: 20px;

  .rule-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;

    h4 {
      margin: 0;
      font-size: 1.2rem;
      color: #007bff;
    }

    .delete-button {
      background-color: #dc3545;
      color: #fff;
      border: none;
      border-radius: 5px;
      padding: 5px 10px;
      cursor: pointer;

      &:hover {
        background-color: #c82333;
      }
    }
  }

  .rule-section {
    margin-bottom: 20px;

    p {
      font-size: 1rem;
      font-weight: bold;
      color: #333;
    }

    ul {
      padding-left: 20px;

      .condition-item,
      .correction-item,
      .value-item {
        margin-bottom: 5px;
        padding: 5px 10px;
        background: #f1f1f1;
        border-radius: 5px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;

        span {
          font-size: 0.9rem;
          color: #555;
        }
      }
    }

    .correction-info {
      display: flex;
      flex-direction: column;
      margin-bottom: 10px;

      span {
        margin-bottom: 5px;
        color: #333;
      }
    }
  }
}

</style>
