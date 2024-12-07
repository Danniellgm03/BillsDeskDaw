<template>
    <div>
        <div  v-if="!files_loading">
            <h3>{{ $t('corrector.selecting_file.title') }}</h3>
            <div  v-if="files.length > 0">
                <div :class="['container_files']">
                
                   <div class="file"
                      v-for="file in files"
                        :key="file.id"
                        @click="selectFile(file)"
                        :class="{'selected': selectedFile.id === file.id}"
                   >
                        <div class="file_name">{{ file.file_name }}</div>
                        <div class="file_size">{{ file.size }}</div>
                        <div class="file_date">{{ dateFormated(file.created_at) }}</div>
                    </div>
                </div>
                <button class="button_continue"><router-link to="/mapping-settings/invoice-template">{{ $t('continue') }}</router-link></button>
            </div>
            <div v-else class="container_not_found">
                <img src="/not_found.webp" alt="not found">
                <button class="button_back">
                    <router-link to="/file-manager">{{ $t('upload_file') }}</router-link>
                </button>
            </div>
        </div>
        <div class="loading_container" v-else>
            <LoadingTemplate/>
        </div>
       
    </div>
</template>

<script setup>

import { ref, onBeforeMount, computed  } from 'vue'
import Cookies from 'js-cookie';
import LoadingTemplate from '@/components/LoadingTemplate.vue';
import { useSelectedFileStore } from '@/stores/selectedFileStore';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const files = ref([]);
const selectedFile = computed(() => selectedFileStore.selectedFile);
const files_loading = ref(true);
const selectedFileStore = useSelectedFileStore();

const selectFile = (file) => {
  selectedFileStore.setSelectedFile(file); 
};


onBeforeMount(async () => {
    files_loading.value = true;
    files.value = await fetchFiles();
});


const dateFormated = (date) => {
    return new Date(date).toLocaleDateString();
}

const fetchFiles = async (
    page = 1,
    limit = 5,
    search = '',
    is_fav = null
) => {
    files_loading.value = true;
    try{


        let url = `http://localhost:8000/api/files?page=${page}&limit=${limit}`;

        if (search) {
            url += `&search=${search}`;
        }

        if (is_fav !== null) {
            url += `&is_fav=${is_fav}`;
        }

        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': Cookies.get('authToken') ?? ''
            },
        });
        const data = await response.json();
        if(!response.ok){
            throw new Error(data.message || t('file_manager.failed_fetch_files'));
        }
        files_loading.value = false;
        return data.data.data;
    } catch (error) {
        console.log(error);
    }
    files_loading.value = false;
}

</script>

<style scoped lang='scss'>

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

    .container_not_found{
        flex-direction: column;
    }

    .container_files {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-top: 20px;
    }

    .file {
        width: fit-content;
        padding: 20px;
        border: 1px solid #e7e7e7;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .file:hover {
        background-color: #f7f7f7;
    }

    .file_name {
        font-size: 16px;
        font-weight: 600;
    }

    .file_size {
        font-size: 14px;
        color: #666;
    }

    .file_date {
        font-size: 14px;
        color: #666;
    }

    .selected {
        background-color: #e1ecff;
    }

    .loading_container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
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