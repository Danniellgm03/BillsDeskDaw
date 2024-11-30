<template>
  <div>
    <form @submit.prevent="saveTemplate">
      Template Name
      <div class="form-group">
        <label for="template_name">Template Name</label>
        <input type="text" v-model="templateData.template_name" />
      </div>

      <!-- Column Mappings (simple key-value pairs) -->
      <div class="form-group">
        <label>Column Mappings</label>
        <div v-for="(value, key, index) in templateData.column_mappings" :key="index">
          <input v-model="templateData.column_mappings[key]" :placeholder="'Column ' + key" />
        </div>
      </div>

      <!-- Formulas -->
      <div class="form-group">
        <label>Formulas</label>
        <div v-for="(formula, index) in templateData.formulas" :key="index">
          <input v-model="formula.new_column" placeholder="New Column" />
          <input v-model="formula.formula" placeholder="Formula" />
          <button type="button" @click="removeFormula(index)">Remove Formula</button>
        </div>
        <button type="button" @click="addFormula">Add Formula</button>
      </div>

      <!-- Validation Rules -->
      <div class="form-group">
        <label>Validation Rules</label>
        <div v-for="(rule, index) in templateData.validation_rules" :key="index">
          <div>
            <input v-model="rule.field" placeholder="Field" />
            <input v-model="rule.operator" placeholder="Operator" />
            <input v-model="rule.value" placeholder="Value" />
            <button type="button" @click="removeValidationRule(index)">Remove Rule</button>
          </div>
          <div>
            <label>Conditions</label>
            <div v-for="(condition, idx) in rule.conditions" :key="idx">
              <input v-model="condition.field" placeholder="Condition Field" />
              <input v-model="condition.operator" placeholder="Condition Operator" />
              <input v-model="condition.value" placeholder="Condition Value" />
              <input v-model="condition.highlight" placeholder="Highlight" />
              <button type="button" @click="removeCondition(rule, idx)">Remove Condition</button>
            </div>
            <button type="button" @click="addCondition(rule)">Add Condition</button>
          </div>
        </div>
        <button type="button" @click="addValidationRule">Add Validation Rule</button>
      </div>

      <!-- Aggregations -->
      <div class="form-group">
        <label>Aggregations</label>
        <div v-for="(aggregation, index) in templateData.aggregations" :key="index">
          <input v-model="aggregation.type" placeholder="Aggregation Type" />
          <input v-model="aggregation.fields" placeholder="Fields (comma-separated)" />
          <button type="button" @click="removeAggregation(index)">Remove Aggregation</button>
        </div>
        <button type="button" @click="addAggregation">Add Aggregation</button>
      </div>

      <button type="submit">Save Template</button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useInvoiceTemplateStore } from '@/stores/invoiceTemplaceStore'; // Asegúrate de que el store esté configurado para manejar el template
import Cookies from 'js-cookie';

const route = useRoute();
const templateData = ref({
  template_name: "",
  company_id: 1,
  column_mappings: {},
  formulas: [],
  validation_rules: [],
  aggregations: []
});

const fetchTemplateData = async () => {
  try {
    const response = await fetch(`http://localhost:8000/api/company/invoice-templates/${route.params.id}`, {
      headers: {
        'Authorization': `Bearer ${Cookies.get('authToken')}`,
      },
    });
    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Error fetching template:', error);
  }
};

onMounted(async () => {
    const data = await fetchTemplateData();
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
    conditions: []
  });
};

// Remove Validation Rule
const removeValidationRule = (index) => {
  templateData.value.validation_rules.splice(index, 1);
};

// Add Condition to Rule
const addCondition = (rule) => {
  rule.conditions.push({
    field: '',
    operator: '',
    value: '',
    highlight: ''
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

// Save Template (simulate save to API)
const saveTemplate = async () => {
  try {
    const response = await fetch(`http://localhost:8000/api/company/invoice-templates/${route.params.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${Cookies.get('authToken')}`,
      },
      body: JSON.stringify(templateData.value),
    });

    if (response.ok) {
      alert('Template saved successfully!');
    } else {
      alert('Error saving template!');
    }
  } catch (error) {
    console.error('Error saving template:', error);
  }
};
</script>

<style scoped lang="scss">
.form-group {
  margin-bottom: 20px;
}

input {
  margin: 5px;
  padding: 5px;
  width: 100%;
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
</style>
