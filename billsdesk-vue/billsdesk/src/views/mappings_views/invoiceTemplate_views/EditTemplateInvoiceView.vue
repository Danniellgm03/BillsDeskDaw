<template>
  <div>
    <form @submit.prevent="saveTemplate">
      <!-- {{ templateData }} -->
      <div class="form-group">
        <label for="template_name">Template Name</label>
        <InputText type="text" v-model="templateData.template_name" v-if="!loading" />
        <Skeleton v-else width="100%" height="30px" />
      </div>

      <!-- Column Mappings (simple key-value pairs) -->
      <div class="form-group mapping">
        <label>Column Mappings</label>
        <div v-if="!loading">
          <div v-for="(value, key, index) in templateData.column_mappings" :key="index" >
            <label><small>Column key: {{ key }}</small></label>
            <InputText type="text" v-model="templateData.column_mappings[key]" :placeholder="'Column ' + key" /> 
          </div>
        </div>
        <div class="loading_container" v-if="loading">
            <LoadingTemplate />
        </div>
      </div>

      <!-- Formulas -->
      <div class="form-group formulas">
        <label>Formulas</label>
        <div v-if="!loading">
          <div v-for="(formula, index) in templateData.formulas" :key="index">
            <InputText type="text" v-model="formula.new_column" placeholder="New Column" />
            <InputText type="text" v-model="formula.formula" placeholder="Formula" />
            <button type="button" @click="removeFormula(index)" class="buttonremove">Remove Formula</button>
          </div>
          <button type="button" @click="addFormula" class="button_add">Add Formula</button>
        </div>
        <div class="loading_container" v-else>
          <LoadingTemplate />
        </div>
      </div>

      <!-- Validation Rules -->
      <div class="form-group">
        <label>Validation Rules</label>
        <div v-if="!loading">
          <div v-for="(rule, index) in templateData.validation_rules" :key="index">
            <div>
              <InputText type="text" v-model="rule.field" placeholder="Field" />
              <Select v-model="rule.operator" :options="opertators" optionLabel="label" optionValue="value" placeholder="Select an operator" style="
              width: 100%;
              margin: 5px;
              padding: 5px;
              " />
              <InputText type="text" v-model="rule.value" placeholder="Value" />
              <div class="color_picker" :style="{
                borderColor: '#'+rule.highlight || '#000',
              }">
                <label for="">Row Highlight</label>
                <ColorPicker v-model="rule.highlight" placeholder="Highlight (Optional)" />
                <!-- remove color -->
                 <a @click="rule.highlight = ''" class="remove_color">
                  <i class="pi pi-times"></i>
                 </a>
              </div>
              <div class="color_picker" :style="{
                borderColor: '#'+rule.row_highlight || '#000',
              }">
                <label for="">Row Highlight</label>
                <ColorPicker v-model="rule.row_highlight" placeholder="Row Highlight (Optional)" />
                <!-- remove color -->
                 <a @click="rule.row_highlight = ''" class="remove_color">
                  <i class="pi pi-times"></i>
                 </a>
              </div>
              <button type="button" @click="removeValidationRule(index)" class="buttonremove">Remove Rule</button>
            </div>
            <!-- Conditions -->
            <div class="conditions_group">
              <label>Conditions</label>
              <div v-for="(condition, idx) in rule.conditions" :key="idx" class="condition_inputs">
                <InputText type="text" v-model="condition.field" placeholder="Condition Field" />
                <Select v-model="condition.operator" :options="opertators" optionLabel="label" optionValue="value" placeholder="Select an operator" style="
                width: 100%;
                margin: 5px;
                padding: 5px;
                " />
                <InputText type="text" v-model="condition.value" placeholder="Condition Value" />
                <div class="color_picker" :style="{
                  borderColor: '#'+condition.highlight || '#000',
                }">
                  <label for="">Celd Highlight Color </label>
                  <ColorPicker v-model="condition.highlight" placeholder="Highlight Color" />
                  <!-- remove color -->
                  <a @click="condition.highlight = ''" class="remove_color">
                    <i class="pi pi-times"></i>
                  </a>
                </div>
                
                <div class="color_picker" :style="{
                  borderColor: '#'+condition.row_highlight || '#000',
                }">
                  <label for="">Row Highlight Color </label>
                  <ColorPicker v-model="condition.row_highlight" placeholder="Row Highlight Color" />
                  <!-- remove color -->
                  <a @click="condition.row_highlight = ''" class="remove_color">
                    <i class="pi pi-times"></i>
                  </a>
                </div>
                <button type="button" @click="removeCondition(rule, idx)" class="buttonremove">Remove Condition</button>
              </div>
              <button type="button" @click="addCondition(rule)" class="button_add condition">Add Condition</button>
            </div>
          </div>
          <button type="button" @click="addValidationRule" class="button_add">Add Validation Rule</button>
        </div>
        <div class="loading_container" v-else>
          <LoadingTemplate />
        </div>
      </div>


      <!-- duplicate field into validation rules -->
      <div class="form-group duplicated_fields">
        <label>Duplicated</label>
        <div v-if="!loading">
          <div v-for="(duplicate, index) in duplicated_fields" :key="index" class="duplicated_field">
            <InputText type="text" v-model="duplicate.duplicate_field" placeholder="Duplicate Field" />
            <div class="color_picker" :style="{
              borderColor: '#'+duplicate.row_highlight || '#000',
            }">
              <label for="">Row Highlight</label>
              <ColorPicker v-model="duplicate.row_highlight" placeholder="Row Highlight (Optional)" />
              <!-- remove color -->
              <a @click="duplicate.row_highlight = ''" class="remove_color">
                <i class="pi pi-times"></i>
              </a>
            </div>
            <button type="button" @click="removeDuplicatedField(index)" class="buttonremove">Remove Duplicated Field</button>
          </div>
          <button type="button" @click="addDuplicatedField" class="button_add">Add Duplicated Field</button>

        </div>
        <div class="loading_container" v-else>
          <LoadingTemplate />
        </div>
      </div>


      <!-- Aggregations -->
      <div class="form-group aggregations">
        <label>Aggregations</label>
        <div v-if="!loading">
          <div v-for="(aggregation, index) in templateData.aggregations" :key="index">
            <InputText type="text" v-model="aggregation.type" placeholder="Aggregation Type" />
            <InputText type="text" v-model="aggregation.fields" placeholder="Fields (comma-separated)" />
            <button type="button" @click="removeAggregation(index)" class="buttonremove">Remove Aggregation</button>
          </div>
          <button type="button" @click="addAggregation"  class="button_add">Add Aggregation</button>
        </div>
        <div class="loading_container" v-else>
          <LoadingTemplate />
        </div>
      </div>

      <button type="submit" :disabled="loading">Save Template</button>
      <button type="submit" @click.prevent="saveAndContinue()" style="margin-left: 10px;" :disabled="loading">Save and Finish</button>
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


const { notify } = useNotificationService();


const opertators = [
  { label: 'Equal', value: '==' },
  { label: 'Not Equal', value: '!=' },
  { label: 'Greater Than', value: '>' },
  { label: 'Less Than', value: '<' },
  { label: 'Greater Than or Equal', value: '>=' },
  { label: 'Less Than or Equal', value: '<=' }
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
      summary: 'The operation could not be completed',
      detail: 'An error occurred while fetching the template',
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
        summary: 'Template saved successfully!',
        detail: 'The template has been saved successfully',
        life: 3000, // Tiempo de duración en ms
      });
    } else {
       notify({
        severity: 'error', // 'success', 'info', 'warn', 'error'
        summary: 'The operation could not be completed',
        detail: 'An error occurred while saving the template',
        life: 3000, // Tiempo de duración en ms
      });
    }

    loading.value = false;
  } catch (error) {
    console.error('Error saving template:', error);
    notify({
      severity: 'error', // 'success', 'info', 'warn', 'error'
      summary: 'The operation could not be completed',
      detail: 'An error occurred while saving the template',
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
