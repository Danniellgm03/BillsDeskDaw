// src/stores/InvoiceTemplateStore.js
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useInvoiceTemplateStore = defineStore('invoiceTemplateStore', () => {
  // Estado inicial del template con valores por defecto
  const template = ref({
    template_name: '',
    column_mappings: {}, // Se actualizará desde fuera del store
    formulas: [],
    validation_rules: [],
    aggregations: [],
    template_id: null
  });

  // Acción para establecer el nombre del template
  const setTemplateName = (name) => {
    template.value.template_name = name;
  };

  // Acción para actualizar el mapeo de columnas
  const setColumnMappings = (columns) => {
    template.value.column_mappings = columns; // Establece los mapeos de columnas
  };

  const setFormulas = (formulas) => {
    template.value.formulas = formulas;
  }

  const setValidationRules = (rules) => {
    template.value.validation_rules = rules;
  }

  const setAggregations = (aggregations) => {
    template.value.aggregations = aggregations;
  }

  const setTemplateId = (id) => {
    template.value.template_id = id;
  };

  // Acción para limpiar los datos del template
  const clearTemplate = () => {
    template.value = {
      template_name: '',
      column_mappings: {},
      formulas: [],
      validation_rules: [],
      aggregations: [],
    template_id: null
    };
  };

  // Retornar las variables y las acciones que se usarán en los componentes
  return {
    template,
    setTemplateName,
    setColumnMappings,
    clearTemplate,
    setTemplateId,
    setFormulas,
    setValidationRules,
    setAggregations
    
  };
});
