<template>
  <div class="template-card" @click="selectTemplate">
    <strong>{{ template.template_name }}</strong>
    <p><strong>{{ $t('created_at') }}:</strong> {{ formattedDate }}</p>
    <button>{{ $t('use_template') }}</button>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { defineProps, defineEmits } from 'vue';

// Definir las propiedades que recibe el componente
const props = defineProps({
  template: {
    type: Object,
    required: true
  }
});

// Definir el evento que va a emitir el componente
const emit = defineEmits();

// Formatear la fecha para mostrarla de una manera legible
const formattedDate = computed(() => {
  const date = new Date(props.template.created_at);
  return date.toLocaleString();
});

// Emitir el evento cuando el usuario seleccione el template
const selectTemplate = () => {
  emit('select-template', props.template);  // Emitir el evento con los datos del template
};
</script>

<style scoped lang="scss">
.template-card {
  border: 1px solid #e7e7e7;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 15px;
  cursor: pointer;
  background-color: #f9f9f9;
  transition: background-color 0.3s;
  max-height: 200px;
  max-width: 300px;
  flex: 1;

  &:hover {
    background-color: #f1f1f1;
  }

  h3 {
    margin-bottom: 10px;
    font-size: 1.2rem;
  }

  p {
    margin: 5px 0;
    font-size: 1rem;
  }

  button {
    margin-top: 10px;
    padding: 8px 16px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;

    &:hover {
      background-color: #0056b3;
    }
  }
}
</style>
