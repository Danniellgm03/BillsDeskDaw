<template>
  <div>
    <h3>{{ $t('corrector.mapping.title') }}</h3>
    <div v-if="!loading">
         <div class="mapping_container">
            <div class="columns_file">
                <h4>{{ $t('corrector.mapping.file_columns') }}</h4>
                <div class="file_columns">
                    <div class="column" v-for="header in headersFile" :key="header">
                        <InputText disabled type="text" :value="header"></InputText>
                    </div>
                </div>
            </div>
            <span class="pi pi-arrow-right-arrow-left"></span>
            <div class="columns_to_map">
                <h4>{{ $t('corrector.mapping.columns_to_map') }}</h4>
                <div class="file_columns">
                    <div class="column" v-for="(header) in headersFile" :key="header">
                        <InputText type="text" v-model="columnsToMap[header]" />
                    </div>
                </div>
            </div>
        </div>
        <button class="button_continue"><router-link to="/mapping-settings/invoice-template/new">{{ $t('continue') }}</router-link></button>
    </div>
    <div class="loading_container" v-else>
      <LoadingTemplate/>
    </div>
  </div>
</template>

<script setup>
import { useMappingColumnsStore } from '@/stores/mappingColumnsStore';
import { useSelectedFileStore } from '@/stores/selectedFileStore';
import { computed, ref, onBeforeMount } from 'vue';
import Cookies from 'js-cookie';
import InputText from 'primevue/inputtext';
import LoadingTemplate from '@/components/LoadingTemplate.vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const router = useRouter();

const mappingColumnsStore = useMappingColumnsStore();
const columnsToMap = computed(() => mappingColumnsStore.columns); // Obtener las columnas del store

const selectedFileStore = useSelectedFileStore();
const selectedFile = computed(() => selectedFileStore.selectedFile);

const headersFile = ref([]);
const loading = ref(false);

// Obtener los encabezados del archivo
onBeforeMount(async () => {

  console.log(selectedFile.value);
    if (!selectedFile.value || Object.keys(selectedFile.value).length <= 0 || selectedFile.value.id === null) {
      router.push('/mapping-settings/selecting-files');
      return;
    }

    loading.value = true;
    const headers = await getFileHeaders();
    headersFile.value = headers.data;
    
    // Inicializar las columnas en el store con valores vacíos
    mappingColumnsStore.setColumns(headers.data);
    loading.value = false;
});

// Obtener las cabeceras del archivo
const getFileHeaders = async () => {
  let id = selectedFile.value.id;
  try {
    const response = await fetch(`http://localhost:8000/api/files/${id}/getHeaders`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': Cookies.get('authToken') ?? ''
      }
    });

    if (!response.ok) {
      throw new Error(t('corrector.mapping.error_fetch_file_headers'));
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

    @media (max-width: 768px) {
       gap: 0;
    }

    .columns_file, .columns_to_map {
        width: 45%;
        border-radius: 5px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;

        @media (max-width: 768px) {
          .p-inputtext {
            width: 100%;
          }

          padding: 5px;
        }

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


.loading_container {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 50px 0;
    }

</style>
