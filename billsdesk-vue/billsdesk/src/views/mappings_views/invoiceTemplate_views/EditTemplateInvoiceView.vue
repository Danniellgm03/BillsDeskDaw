<template>
  <div>
    <form @submit.prevent="saveTemplate">
      <!-- {{ templateData }} -->
      <div class="form-group">
        <label for="template_name">{{ $t('corrector.template.template_name') }}</label>
        <InputText type="text" v-model="templateData.template_name" v-if="!loading" />
        <Skeleton v-else width="100%" height="30px" />
      </div>

      <!-- Column Mappings (simple key-value pairs) -->
      <div class="form-group mapping">
        <label>{{ $t('corrector.template.edit.columns_mapping') }}</label>
        <div v-if="!loading">
          <div v-for="(value, key, index) in templateData.column_mappings" :key="index" >
            <label><small>{{ $t('corrector.template.edit.column_key') }}: {{ key }}</small></label>
            <InputText type="text" v-model="templateData.column_mappings[key]" :placeholder="($t('corrector.template.edit.column')) + key" /> 
          </div>
        </div>
        <div class="loading_container" v-if="loading">
            <LoadingTemplate />
        </div>
      </div>

      <!-- Formulas -->
      <div class="form-group formulas">
        <label>{{ $t('corrector.template.edit.formulas') }}</label>
        <div v-if="!loading">
          <div v-for="(formula, index) in templateData.formulas" :key="index">
            <InputText type="text" v-model="formula.new_column" :placeholder="$t('corrector.template.edit.new_column')" />
            <InputText type="text" v-model="formula.formula" :placeholder="$t('corrector.template.edit.formulas')" />
            <button type="button" @click="removeFormula(index)" class="buttonremove">{{ $t('corrector.template.edit.remove_formula') }}</button>
          </div>
          <button type="button" @click="addFormula" class="button_add">{{ $t('corrector.template.edit.add_formula') }}</button>
        </div>
        <div class="loading_container" v-else>
          <LoadingTemplate />
        </div>
      </div>

      <!-- Validation Rules -->
      <div class="form-group">
        <label>{{ $t('corrector.template.edit.validation_rules') }}</label>
        <div v-if="!loading">
          <div v-for="(rule, index) in templateData.validation_rules" :key="index">
            <div>
              <InputText type="text" v-model="rule.field" placeholder="Field" />
              <Select v-model="rule.operator" :options="opertators" optionLabel="label" optionValue="value" 
              :placeholder="$t('corrector.template.edit.select_operator')" style="
              width: 100%;
              margin: 5px;
              padding: 5px;
              " />
              <InputText type="text" v-model="rule.value" placeholder="Value" />
              <div class="color_picker" :style="{
                borderColor: '#'+rule.highlight || '#000',
              }">
                <label for="">{{ $t('corrector.template.edit.celd_highlight_color') }}</label>
                <ColorPicker v-model="rule.highlight" :placeholder="$t('corrector.template.edit.highlight_optional')" defaultColor="b1b1b1" />
                <!-- remove color -->
                 <a @click="rule.highlight = ''" class="remove_color">
                  <i class="pi pi-times"></i>
                 </a>
              </div>
              <div class="color_picker" :style="{
                borderColor: '#'+rule.row_highlight || '#000',
              }">
                <label for="">{{ $t('corrector.template.edit.row_highlight') }}</label>
                <ColorPicker v-model="rule.row_highlight" :placeholder="$t('corrector.template.edit.row_highlight_optional')" defaultColor="b1b1b1" />
                <!-- remove color -->
                 <a @click="rule.row_highlight = ''" class="remove_color">
                  <i class="pi pi-times"></i>
                 </a>
              </div>
              <button type="button" @click="removeValidationRule(index)" class="buttonremove">
                {{ $t('corrector.template.edit.remove_validation_rule') }}
              </button>
            </div>
            <!-- Conditions -->
            <div class="conditions_group">
              <label>
                {{ $t('corrector.template.edit.conditions') }}
              </label>
              <div v-for="(condition, idx) in rule.conditions" :key="idx" class="condition_inputs">
                <InputText type="text" v-model="condition.field" :placeholder="$t('corrector.template.edit.condition_field')" />
                <Select v-model="condition.operator" :options="opertators" optionLabel="label" optionValue="value" 
                :placeholder="$t('corrector.template.edit.select_operator')"  style="
                width: 100%;
                margin: 5px;
                padding: 5px;
                " />
                <InputText type="text" v-model="condition.value" placeholder="Condition Value" />
                <div class="color_picker" :style="{
                  borderColor: '#'+condition.highlight || '#000',
                }">
                  <label for="">{{ $t('corrector.template.edit.celd_highlight_color') }}</label>
                  <ColorPicker v-model="condition.highlight" :placeholder="$t('corrector.template.edit.highlight_optional')" defaultColor="b1b1b1" />
                  <!-- remove color -->
                  <a @click="condition.highlight = ''" class="remove_color">
                    <i class="pi pi-times"></i>
                  </a>
                </div>
                
                <div class="color_picker" :style="{
                  borderColor: '#'+condition.row_highlight || '#000',
                }">
                  <label for="">{{ $t('corrector.template.edit.row_highlight') }}</label>
                  <ColorPicker v-model="condition.row_highlight" :placeholder="$t('corrector.template.edit.row_highlight_optional')" defaultColor="b1b1b1" />
                  <!-- remove color -->
                  <a @click="condition.row_highlight = ''" class="remove_color">
                    <i class="pi pi-times"></i>
                  </a>
                </div>
                <button type="button" @click="removeCondition(rule, idx)" class="buttonremove">
                  {{ $t('corrector.template.edit.remove_condition') }}
                </button>
              </div>
              <button type="button" @click="addCondition(rule)" class="button_add condition">
                {{ $t('corrector.template.edit.add_condition') }}
              </button>
            </div>
          </div>
          <button type="button" @click="addValidationRule" class="button_add">
            {{ $t('corrector.template.edit.add_validation_rule') }}
          </button>
        </div>
        <div class="loading_container" v-else>
          <LoadingTemplate />
        </div>
      </div>


      <!-- duplicate field into validation rules -->
      <div class="form-group duplicated_fields">
        <label>
          {{ $t('corrector.template.edit.duplicated') }}
        </label>
        <div v-if="!loading">
          <div v-for="(duplicate, index) in duplicated_fields" :key="index" class="duplicated_field">
            <InputText type="text" v-model="duplicate.duplicate_field" :placeholder="$t('corrector.template.edit.duplicated')" />
            <div class="color_picker" :style="{
              borderColor: '#'+duplicate.row_highlight || '#000',
            }">
              <label for="">{{ $t('corrector.template.edit.row_highlight') }}</label>
              <ColorPicker v-model="duplicate.row_highlight"  :placeholder="$t('corrector.template.edit.row_highlight_optional')" defaultColor="b1b1b1" />
              <!-- remove color -->
              <a @click="duplicate.row_highlight = ''" class="remove_color">
                <i class="pi pi-times"></i>
              </a>
            </div>
            <button type="button" @click="removeDuplicatedField(index)" class="buttonremove">
              {{ $t('corrector.template.edit.remove_duplicated') }}
            </button>
          </div>
          <button type="button" @click="addDuplicatedField" class="button_add">
            {{ $t('corrector.template.edit.add_duplicated') }}
          </button>

        </div>
        <div class="loading_container" v-else>
          <LoadingTemplate />
        </div>
      </div>


      <!-- Aggregations -->
      <div class="form-group aggregations">
        <label>
          {{ $t('corrector.template.edit.aggregation') }} 
        </label>
        <div v-if="!loading">
          <div v-for="(aggregation, index) in templateData.aggregations" :key="index">
            <InputText type="text" v-model="aggregation.type" :placeholder="$t('corrector.template.edit.aggregation_field') " />
            <InputText type="text" v-model="aggregation.fields" :placeholder="$t('corrector.template.edit.field_comma_separated')" />
            <button type="button" @click="removeAggregation(index)" class="buttonremove">
              {{ $t('corrector.template.edit.remove_aggregation') }}
            </button>
          </div>
          <button type="button" @click="addAggregation"  class="button_add">
            {{ $t('corrector.template.edit.add_aggregation') }}
          </button>
        </div>
        <div class="loading_container" v-else>
          <LoadingTemplate />
        </div>
      </div>

      <button type="submit" :disabled="loading">
        {{ $t('corrector.template.edit.save_template') }}
      </button>
      <button type="submit" @click.prevent="saveAndContinue()" style="margin-left: 10px;" :disabled="loading">
        {{ $t('corrector.template.edit.save_and_finish') }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useInvoiceTemplateStore } from '@/stores/invoiceTemplaceStore'; // Asegúrate de que el store esté configurado para manejar el template
import Cookies from 'js-cookie';
import InputText from 'primevue/inputtext';
import Skeleton from 'primevue/skeleton';
import LoadingTemplate from '@/components/LoadingTemplate.vue';
import { useNotificationService } from '@/utils/notificationService';
import Select from 'primevue/select';
import ColorPicker from 'primevue/colorpicker';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();


const { notify } = useNotificationService();


const opertators = [
  { label: t('equal'), value: '==' },
  { label: t('not_equal'), value: '!=' },
  { label: t('greater_than'), value: '>' },
  { label: t('less_than'), value: '<' },
  { label: t('greater_than_or_equal'), value: '>=' },
  { label: t('less_than_or_equal'), value: '<=' }
]

const loading = ref(false);

const route = useRoute();
const router = useRouter();
const templateData = ref({
  template_name: "",
  company_id: 1,
  column_mappings: {},
  formulas: [],
  validation_rules: [],
  aggregations: []
});


const saveAndContinue = async () => {
  await saveTemplate();
  router.push('/mapping-settings/correction-rules');
};

const fetchTemplateData = async () => {
  try {
    loading.value = true;
    const response = await fetch(`http://localhost:8000/api/company/invoice-templates/${route.params.id}`, {
      headers: {
        'Authorization': `Bearer ${Cookies.get('authToken')}`,
      },
    });
    const data = await response.json();
    loading.value = false;
    return data;
  } catch (error) {
    console.error('Error fetching template:', error);
    notify({
      severity: 'error', // 'success', 'info', 'warn', 'error'
      summary: t('operation_could_not_be_completed'),
      detail: t('error_fetching_data'),
      life: 3000, // Tiempo de duración en ms
    });
  }
};

onMounted(async () => {
    const data = await fetchTemplateData();

    console.log(data.validation_rules)
    const duplicated = data.validation_rules.filter(rule => rule.duplicate_field);
    console.log(duplicated);
    duplicated_fields.value = duplicated;

    //exclude duplicated fields from validation rules
    data.validation_rules = data.validation_rules.filter(rule => !rule.duplicate_field);

    templateData.value = data;

});

// Add Formula
const addFormula = () => {
  templateData.value.formulas.push({
    new_column: '',
    formula: ''
  });
};

// Remove Formula
const removeFormula = (index) => {
  templateData.value.formulas.splice(index, 1);
};

// Add Validation Rule
const addValidationRule = () => {
  templateData.value.validation_rules.push({
    field: '',
    operator: '',
    value: '',
    highlight: '',
    row_highlight: '',
    conditions: [] // Asegúrate de inicializar esto como un arreglo vacío
  });
};

const duplicated_fields = ref([]);

// Add Duplicated Field
const addDuplicatedField = () => {
  duplicated_fields.value.push({
    duplicate_field: '',
    row_highlight: ''
  });
};


// Remove Duplicated Field
const removeDuplicatedField = (index) => {
  duplicated_fields.value.splice(index, 1);
};

// Remove Validation Rule
const removeValidationRule = (index) => {
  templateData.value.validation_rules.splice(index, 1);
};

// Add Condition to Rule
const addCondition = (rule) => {
  if (!Array.isArray(rule.conditions)) {
    rule.conditions = [];
  }
  rule.conditions.push({
    field: '',
    operator: '',
    value: '',
    highlight: '',
    row_highlight: ''
  });
};

// Remove Condition from Rule
const removeCondition = (rule, idx) => {
  rule.conditions.splice(idx, 1);
};

// Add Aggregation
const addAggregation = () => {
  templateData.value.aggregations.push({
    type: '',
    fields: ''
  });
};

// Remove Aggregation
const removeAggregation = (index) => {
  templateData.value.aggregations.splice(index, 1);
};

const cleanValidationRules = (rules) => {
  return rules.map((rule) => {
    // Filtra los campos vacíos o nulos en cada regla
    const cleanedRule = Object.fromEntries(
      Object.entries(rule).filter(([key, value]) => {
        if (key === 'conditions') {
          return Array.isArray(value) && value.length > 0; // Incluye solo si tiene condiciones
        }
        return value !== null && value !== ''; // Excluye valores nulos o vacíos
      })
    );

    // Procesa las condiciones si existen
    if (cleanedRule.conditions) {
      cleanedRule.conditions = cleanedRule.conditions.map((condition) =>
        Object.fromEntries(
          Object.entries(condition).filter(([key, value]) => value !== null && value !== '')
        )
      );
    }

    return cleanedRule;
  }).filter((rule) => Object.keys(rule).length > 0); // Excluye reglas completamente vacías
};

const processAggregations = (aggregations) => {
  return aggregations.map((aggregation) => ({
    ...aggregation,
    fields: typeof aggregation.fields === 'string'
      ? aggregation.fields.split(',').map((field) => field.trim()) // Convierte la cadena en un arreglo
      : Array.isArray(aggregation.fields)
        ? aggregation.fields // Si ya es un array, lo deja como está
        : [] // Si no es ni string ni array, devuelve un arreglo vacío
  }));
};

const saveTemplate = async () => {
  try {

    loading.value = true;
    // Procesa `validation_rules` y `aggregations` antes de enviar
    const processedTemplateData = {
      ...templateData.value,
      validation_rules: cleanValidationRules(templateData.value.validation_rules),
      aggregations: processAggregations(templateData.value.aggregations),
    };

    processedTemplateData.validation_rules = [
      ...processedTemplateData.validation_rules,
      ...duplicated_fields.value.map(duplicate => ({
        duplicate_field: duplicate.duplicate_field,
        row_highlight: duplicate.row_highlight
      }))
    ]


    const response = await fetch(`http://localhost:8000/api/company/invoice-templates/${route.params.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${Cookies.get('authToken')}`,
      },
      body: JSON.stringify(processedTemplateData),
    });

    if (response.ok) {
      notify({
        severity: 'success', // 'success', 'info', 'warn', 'error'
        summary: t('success'),
        detail: t('corrector.template.edit.template_saved'),
        life: 3000, // Tiempo de duración en ms
      });
    } else {
       notify({
        severity: 'error', // 'success', 'info', 'warn', 'error'
        summary: t('error'),
        detail: t('corrector.template.edit.failed_save_template'),
        life: 3000, // Tiempo de duración en ms
      });
    }

    loading.value = false;
  } catch (error) {
    console.error('Error saving template:', error);
    notify({
      severity: 'error', // 'success', 'info', 'warn', 'error'
      summary: t('error'),
      detail: t('corrector.template.edit.failed_save_template'),
      life: 3000, // Tiempo de duración en ms
    });
  }
};
</script>

<style scoped lang="scss">
  form{
    padding: 20px 10px; 
  }
  .form-group {
    margin-bottom: 20px;
    background-color: #f7f7f7;
    padding: 20px;
    border-radius: 5px;

    label {
      display: block;
      margin-bottom: 5px;
    }

    input {
      margin: 5px;
      padding: 5px;
      width: 100%;
    }

    &.aggregations {
      div {
        margin-top: 10px;
        margin-bottom: 10px;
        padding: 10px;
        background-color: #f0f0f0;
        input {
          margin: 5px;
          padding: 5px;
          width: 40%;
        }
      }
    }

    &.mapping {
      div{
        label small{
          color: #666;
        }
      }
    }

    &.formulas{
     
      div{
        margin-bottom: 10px;
        input{
          margin: 5px;
          padding: 5px;
          width: 40%;
        }
      }
    }

    .conditions_group {
      margin-top: 10px;
      padding: 10px;
      background-color: #f0f0f0;
      border-radius: 5px;

      label {
        display: block;
        margin-bottom: 5px;
      }

      input {
        margin: 5px;
        padding: 5px;
        width: 100%;
      }

      .condition_inputs{
        margin-top: 10px;
        padding: 10px;
        background-color: #f7f7f7;
        border-radius: 5px;

        input {
          margin: 5px;
          padding: 5px;
          width: 100%;
        }
      }
    }
  }

  input {
    margin: 5px;
    padding: 5px;
    width: 100%;
  }

  .button_add {
    margin-top: 10px;
    padding: 10px;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;

    &:hover {
      background-color: #218838;
    }

    &.condition {
      background-color: #29753b;
    }
  }

  .buttonremove {
    margin-top: 10px;
    padding: 10px;
    background-color: #dc3545;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;

    &:hover {
      background-color: #c82333;
    }
  }

  button[type="submit"] {
    margin-top: 10px;
    padding: 10px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;

    &:hover {
      background-color: #0056b3;
    }
  }

  input.optional {
    border: 1px dashed #ccc;
    background-color: #f9f9f9;
  }

  button:disabled {
    background-color: #ccc;
    cursor: not-allowed;

    &:hover {
      background-color: #ccc;
    }
  }

  .color_picker{
    display: flex;
    border: 1px solid #d1d1d1;
    width: fit-content;
    align-items: center;
    justify-content: space-between;
    border-radius: 5px;
    margin: 5px;
    padding: 10px;
    gap: 13px;
  }

  .remove_color{
    cursor: pointer;
  }

  .duplicated_fields{


    div.duplicated_field{
      margin-top: 10px;
      padding: 10px;
      background-color: #f0f0f0;
      border-radius: 5px;
      input{
        margin: 5px;
        padding: 5px;
        width: 40%;
      }


    }
  }

</style>
