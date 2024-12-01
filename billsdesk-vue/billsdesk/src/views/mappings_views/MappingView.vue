<template>
  <div>
    <h3>Mapping View</h3>
    <div class="mapping_container">
        <div class="columns_file">
            <h4>File columns</h4>
            <div class="file_columns">
                <div class="column" v-for="header in headersFile" :key="header">
                    <InputText disabled type="text" :value="header"></InputText>
                </div>
            </div>
        </div>
        <span class="pi pi-arrow-right-arrow-left"></span>
        <div class="columns_to_map">
            <h4>Columns to map</h4>
            <div class="file_columns">
                <div class="column" v-for="(header) in headersFile" :key="header">
                    <InputText type="text" v-model="columnsToMap[header]" />
                </div>
            </div>
        </div>
    </div>
    <button class="button_continue"><router-link to="/mapping-settings/invoice-template/new">Continue</router-link></button>
  </div>
</template>

<script setup>
import { useMappingColumnsStore } from '@/stores/mappingColumnsStore';
import { computed, ref, onBeforeMount } from 'vue';
import Cookies from 'js-cookie';
import InputText from 'primevue/inputtext';

const mappingColumnsStore = useMappingColumnsStore();
const columnsToMap = computed(() => mappingColumnsStore.columns); // Obtener las columnas del store

const headersFile = ref([]);

// Obtener los encabezados del archivo
onBeforeMount(async () => {
    const headers = await getFileHeaders();
    headersFile.value = headers.data;
    
    // Inicializar las columnas en el store con valores vacíos
    mappingColumnsStore.setColumns(headers.data);
});

// Obtener las cabeceras del archivo
const getFileHeaders = async () => {
  try {
    const response = await fetch(`http://localhost:8000/api/files/2/getHeaders`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': Cookies.get('authToken') ?? ''
      }
    });

    if (!response.ok) {
      throw new Error('Error fetching file headers');
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error(error);
  }
};
</script>

<style scoped lang="scss">
.mapping_container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    gap: 20px;

    .columns_file, .columns_to_map {
        width: 45%;
        border-radius: 5px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;

        h4 {
            margin-bottom: 10px;
        }

        .file_columns {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: center;
            flex-direction: column;

            .column {
                width: 100%;
            }
        }
    }
}

.button_continue {
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
</style>
