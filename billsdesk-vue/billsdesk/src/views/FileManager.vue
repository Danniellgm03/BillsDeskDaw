<template>
    <div>
        <div class="container_header">
            <h2>{{ $t('file_manager.title') }}</h2>
            <div class="actions_file_manager">
                <ul>
                    <li :class="{
                        'adding': addingFile,
                    }"> <i class="pi pi-plus" @click="handleAddFile"></i> </li>
                    <li>
                        <i class="pi pi-star" @click="handleFavouriteFilter(filterFavorites)" v-if="!filterFavorites"></i>
                        <i class="pi pi-star-fill" @click="handleFavouriteFilter(filterFavorites)" v-if="filterFavorites"></i>
                    </li>
                </ul>
            </div>
        </div>
        <div class="search_file">
            <input v-model="search_input" type="text" :placeholder="$t('file_manager.search_file')" @change="searchFile" />
            <i class="pi pi-search" @click="searchFile"></i>
        </div>
        <section class="files_manager">
            <div class="header_file_manager">
                <h4>{{ $t('file_manager.recent_files') }}</h4>
                <div class="view_tab" @click="changeLayout">
                    <button :class="{ active: layout === 'grid_extend' }">
                        <i class="pi pi-table"></i>
                    </button>
                    <button :class="{ active: layout === 'grid' }">
                        <i class="pi pi-th-large"></i>
                    </button>
                </div>
            </div>
            <div :class="['container_files', layout]" v-if="!files_loading">
                <template v-if="files.length > 0">
                    <FileComponenteContent v-for="file in files" :key="file.id" :file="file"
                        @openDrawer="handleOpenDrawer" @updateFav="handleUpdateFav" />
                </template>
            </div>
            <div class="loading_container" v-else>
                <LoadingTemplate/>
            </div>
            <div v-if="files.length <= 0 && !files_loading" class="container_not_found">
                <img src="/not_found.webp" alt="not found">
            </div>

            <div class="pagination" :class="{
                'd-none': files.length <= 0 || files_loading
            }">
                <Paginator v-model:page="pagination.page" :totalRecords="pagination.total" :rows="pagination.limit"
                    :rowsPerPageOptions="[5, 10, 20]" @page="pageChange" />
            </div>
        </section>

        <Drawer v-model:visible="isDrawerOpen" position="right" class="p-drawer_styled">
            <template #header>
                <header>
                    <i class="pi pi-file"></i>{{ $t('file_manager.file_details') }}
                </header>
            </template>
            <div class="file_details_drawer">
                <div class="file_prev">
                    <i class="pi pi-file"></i>
                </div>

                <div class="file_info">
                    <h2>{{ selectedFile.file_name }}</h2>
                    <p class="file_size">{{ selectedFile.file_size }} {{ selectedFile.file_size_type }}</p>
                    <div class="file_description">
                        <strong>{{ $t('file_manager.description') }}</strong>
                        <p v-if="!editDescription">{{ selectedFile.file_description }}</p>
                        <Textarea v-if="editDescription" autoResize :rows="3" style="width: 100%;" v-model="selectedFile.file_description" />
                        <button v-if="editDescription" @click="handleEditDescription(selectedFile)" class="button_description">Save</button>

                    </div>
                </div>


                <div class="actions_file">
                    <div class="divider"></div>
                    <div class="actions">
                        <button class="pi pi-download" @click="handleDownloadFile(selectedFile)"></button>
                        <button class="pi pi-file-edit" @click="editDescription = true"></button>
                        <button class="pi pi-trash" @click="fetchDeleteFile(selectedFile)"></button>
                    </div>
                    <div class="divider"></div>
                </div>
            </div>
        </Drawer>

        <Dialog v-model:visible="addingFile" modal class="modal_file" header="Upload file" :style="{ width: '50rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <div v-if="!loadingFileUpload">

                <div class="field">
                    <ErrorsComponent :errors="file_form.errors" v-if="file_form.errors != null" />
                     <label><strong>{{ $t('file_manager.file') }}:</strong></label>
                    <FileUpload
                        name="file"
                        :multiple="false"
                        :fileLimit="1"
                        :maxFileSize="1000000"
                        @select="onFileSelect"
                        v-model="file_form.file"
                        accept=".xlsx,.xls,.csv"
                    >
                        <template #header="{ chooseCallback, clearCallback }">
                            <Button 
                                icon="pi pi-upload" 
                                :label="$t('file_manager.choose_file')" 
                                @click="chooseCallback" 
                            />
                            <Button 
                                icon="pi pi-times" 
                                :label="$t('file_manager.clear')"
                                @click="clearCallback"
                            />
                            
                        </template>
                        <template #empty>
                            <span>{{ $t('file_manager.drag_drop_file') }}</span>
                        </template>
                        <template #content="{ files, uploadedFiles, removeUploadedFileCallback, removeFileCallback }">
                            <div class="cards-container">
                                <div 
                                    v-for="file in files" 
                                    :key="file.name" 
                                    class="card"
                                >
                                    <img 
                                    :src="file.objectURL" 
                                    alt="Uploaded preview" 
                                    v-if="file.type.startsWith('image/')"
                                    class="preview"
                                    />
                                    <div class="card-details">
                                    <h3>{{ file.name }}</h3>
                                    <p>{{ $t('file_manager.size') }}: {{ (file.size / 1024).toFixed(2) }} KB</p>
                                    </div>
                                    <Button 
                                    icon="pi pi-trash" 
                                    class="p-button-danger p-button-rounded"
                                    @click="removeFileCallback(file)"
                                    :label="$t('file_manager.remove')"
                                    />
                                </div>
                            </div>
                        </template>
    
                    </FileUpload>
                </div>
                <div class="field">
                    <label><strong>{{ $t('file_manager.description') }}:</strong></label>
                    <Textarea autoResize :rows="3" style="width: 100%;" v-model="file_form.description" />
                </div>
                <div class="field field_upload_buttons">
                    <Button :label="$t('file_manager.cancel')" @click="handleAddFile" />
                    <Button :label="$t('file_manager.upload')" @click="handleUploadFileServer" />
                </div>
            </div>
            <div class="loading_container" v-else>
                <LoadingTemplate/>
            </div>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, onBeforeMount } from 'vue';
import FileComponenteContent from '@/components/FileManager/FileComponenteContent.vue';
import Drawer from 'primevue/drawer';
import Cookies from 'js-cookie';
import FileUpload from 'primevue/fileupload';
import Button from 'primevue/button';
import LoadingTemplate from '@/components/LoadingTemplate.vue';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import Paginator from 'primevue/paginator';
import { useNotificationService } from '@/utils/notificationService';
const { notify } = useNotificationService();
import ErrorsComponent from '@/components/ErrorsComponent.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const filterFavorites = ref(false);
const search_input = ref('');
const addingFile = ref(false);
const isDrawerOpen = ref(false);
const selectedFile = ref({});
const files = ref([]);
const files_loading = ref(true);
const editDescription = ref(false);

const loadingFileUpload = ref(false);

const dowloadingFile = ref(false);

const file_form = ref({
    file: null,
    description: '',
    errors: null,
});


onBeforeMount(async () => {
    files_loading.value = true;
    files.value = await fetchFiles();
});

const pagination = ref({
    page: 1,
    last_page: 5,
    limit: 5,
    total: 0,
    search: '',
    is_fav: null,
});


const pageChange = async (event) => {
    files.value = await fetchFiles(event.page + 1, event.rows, pagination.value.search, pagination.value.is_fav);
};

const handleEditDescription = async (file) => {
    try {
        isDrawerOpen.value = false;
        files_loading.value = true;
        const response = await fetch(`http://localhost:8000/api/files/${file.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': Cookies.get('authToken') ?? ''
            },
            body: JSON.stringify({
                file_description: file.file_description,
                is_fav: file.is_fav
            }),
        });
        const data = await response.json();
        if (!response.ok) {
            notify({
                severity: 'error',
                summary: t('file_manager.error'),
                detail: t('file_manager.failed_update_description'),
            });
            throw new Error(data.message || t('file_manager.failed_update_description'));
        }
        notify({
            severity: 'success',
            summary: t('file_manager.success'),
            detail: t('file_manager.success_update_description'),
        });
        files.value = await fetchFiles();
        editDescription.value = false;
    } catch (error) {
        console.log(error);
    }
};

const handleUpdateFav = async (file) => {
    try {
        files_loading.value = true;
        const response = await fetch(`http://localhost:8000/api/files/${file.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': Cookies.get('authToken') ?? ''
            },
            body: JSON.stringify({
                is_fav: !file.is_fav
            }),
        });
        const data = await response.json();
        if (!response.ok) {
            notify({
                severity: 'error',
                summary: t('file_manager.error'),
                detail: t('file_manager.failed_favorite'),
            });
            throw new Error(data.message || t('file_manager.failed_favorite'));
        }
        files.value = await fetchFiles();
        notify({
            severity: 'success',
            summary: t('file_manager.success'),
            detail: t('file_manager.favorite_updated'),
        });
    } catch (error) {
        console.log(error);
    }
};

const handleDownloadFile = (selectedFile) => {

    try {
        const url = `http://localhost:8000/api/files/${selectedFile.id}/download`;
        const response = fetch(url, {
            method: 'GET',
            headers: {
                'Authorization': Cookies.get('authToken') ?? ''
            },
        });
        response.then((response) => {
            response.blob().then((blob) => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = selectedFile.file_name;
                a.target = '_blank';
                a.click();
            });
        });

    } catch (error) {
        console.error('Error al descargar el archivo:', error);
        notify({
            severity: 'error',
            summary: t('file_manager.error'),
            detail: t('file_manager.failed_download'),
        });
    }

};

const handleUploadFileServer = async () => {
    loadingFileUpload.value = true;
    const formData = new FormData();

    formData.append('file', file_form.value.file);
    formData.append("file_type", "invoice");
    formData.append('file_description', file_form.value.description);

    try {
        const response = await fetch('http://localhost:8000/api/files/', {
            method: 'POST',
            headers: {
                'Authorization': Cookies.get('authToken') ?? ''
            },
            body: formData,
        });
        const data = await response.json();
        if (!response.ok) {
            file_form.value.errors = data.errors;
            loadingFileUpload.value = false;

            throw new Error(data.message || t('file_manager.failed_upload'));
        }
        addingFile.value = false;
        loadingFileUpload.value = false;
        file_form.value = {
            file: null,
            description: '',
        };
        files.value = await fetchFiles();
        notify({
            severity: 'success',
            summary: t('file_manager.success'),
            detail:  t('file_manager.file_uploaded'),
        });
    } catch (error) {
        console.log(error);
        notify({
            severity: 'error',
            summary: t('file_manager.error'),
            detail: t('file_manager.failed_upload'),
        });
    }
};

const onFileSelect = (e) => {
    file_form.value.file = e.files[0];
};

const fetchFiles = async (
    page = 1,
    limit = 5,
    search = '',
    is_fav = null
) => {


    files_loading.value = true;
    try{


        let url = `http://localhost:8000/api/files?page=${page}&per_page=${limit}`;

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

        pagination.value = {
            page: data.data.current_page,
            last_page: data.data.last_page,
            limit: data.data.per_page,
            total: data.data.total,
            search: search,
            is_fav: is_fav,
        };
        
        return data.data.data;
    } catch (error) {
        console.log(error);
    }
    files_loading.value = false;
}

const fetchDeleteFile = async (file) => {
    try{
        isDrawerOpen.value = false;
        files_loading.value = true;
        const response = await fetch(`http://localhost:8000/api/files/${file.id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': Cookies.get('authToken') ?? ''
            },
        });
        const data = await response.json();
        if(!response.ok){
            throw new Error(data.message || t('file_manager.failed_remove_file'));
        }

        isDrawerOpen.value = false;
        files.value = await fetchFiles();
        notify({
            severity: 'success',
            summary: t('file_manager.success'),
            detail: t('file_manager.file_removed'),
        });
        return data.data;
    } catch (error) {
        console.log(error);
        notify({
            severity: 'error',
            summary: t('file_manager.error'),
            detail: t('file_manager.failed_remove_file'),
        });
    }
}


const layout = ref('grid_extend'); 

const handleOpenDrawer = (file) => {
    selectedFile.value = file;
    isDrawerOpen.value = true;
};

const handleAddFile = () => {
    addingFile.value = !addingFile.value;
};

const searchFile = async () => {
    try{

        files_loading.value = true;
        const response = await fetch(`http://localhost:8000/api/files/search?search=${search_input.value}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': Cookies.get('authToken') ?? ''
            },
        }); 

        const data = await response.json();
        if(!response.ok){
            throw new Error(data.message || t('file_manager.failed_search_files'));
        }
        files.value = data.data;
        files_loading.value = false;

    }catch(error){
        console.log(error);
        notify({
            severity: 'error',
            summary: t('file_manager.error'),
            detail: t('file_manager.failed_search_files'),
        });
    }
};

const handleFavouriteFilter = async (is_fav) => {
    try{
        filterFavorites.value = !is_fav;
        if(filterFavorites.value){
            files.value = await fetchFiles(1, 5, '',  filterFavorites.value);
        }else{
            files.value = await fetchFiles();
        }
    }catch(error){
        console.log(error);
    }
};

const changeLayout = () => {
    if (layout.value === 'grid') {
        layout.value = 'grid_extend';
    } else {
        layout.value = 'grid';
    }
};
</script>


<style scoped lang='scss'>

    .cards-container {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    }

    .card {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 16px;
    max-width: 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .card img.preview {
    width: 100%;
    height: auto;
    border-radius: 4px;
    }

    .card-details {
    margin: 8px 0;
    text-align: center;
    }

    .card-details h3 {
    font-size: 14px;
    margin: 0;
    word-wrap: break-word;
    }

    .card-details p {
    font-size: 12px;
    color: #666;
    margin: 4px 0 0;
    }

    .modal_file{
        .field{
            margin-top: 20px;

            label{
                font-weight: 300;
                display: block;
                margin-bottom: 10px;
            }
        }

        .field_upload_buttons{
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 20px;
        }
    }


    .loading_container {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 50px 0;
    }

    .container_header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        position: sticky;
        top: -20px;
        background-color: #fff;
        padding: 20px 0;
        border-bottom: 1px solid #ccc;
    }

    .actions_file_manager {
        ul {
            display: flex;
            gap: 20px;
            list-style: none;

            li {
                cursor: pointer;
                width: 32px;
                height: 32px;
                display: flex;
                justify-content: center;
                align-items: center;
                border-radius: 5px;
                transition: .3s;

                &.adding {
                    background-color: #f1f1f1;
                }

                &:hover {
                    background-color: #f1f1f1;
                }

                i{
                    font-size: 1.15em;
                }

            }
        }
    }

    .files_manager {
        margin-top: 20px;
        h4 {
            margin-bottom: 20px;
            font-weight: 300;
        }
    }

    .header_file_manager {
        display: flex;
        justify-content: space-between;
        align-items: center;

        .view_tab {
            display: flex;
            gap: 4px;
            background-color: #f1f1f1;
            border-radius: 5px;
            overflow: hidden;

            button{
                background-color: transparent;
                border: none;


                i{
                    font-size: 1.15em;
                    padding: 5px;
                    cursor: pointer;
                }

                &.active {
                    background-color: #007bff;
                    color: white;
                }
            }

        }
    }


    .container_files {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 20px;

        &.grid {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        }

        &.grid_extend {
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        }
    }

    .file_details_drawer {
        display: flex;
        height: 100%;
        margin-top: 20px;
        flex-direction: column;
        gap: 20px;

        i{
            font-size: 10em;
        }


        .file_size{
            font-size: .9em;
            font-weight: 300;
        }


        .file_info{
            display: flex;
            flex-direction: column;
            gap: 5px;
            flex: 1;
        }

        .file_description{
            margin-top: 20px;
        }
    }

    .actions_file{
        display: flex;
        flex-direction: column;

        .actions{
            display: flex;
            gap: 20px;
            justify-content: space-around;

            button{
                background-color: transparent;
                border: none;
                font-size: 1.5em;
                cursor: pointer;
            }
        }
    }

    .search_file{
        position: relative;

        input{
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;

            &:focus{
                outline: none;
            }
        }

        i{
            font-size: 1.15em;
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }
    }

    .add_file{
        margin-top: 20px;
    }


    .pagination{
        margin-top: 20px;
        display: flex;
        justify-content: center;

        &.d-none{
            display: none;
        }
    }

    .button_description{
        background-color: #007bff;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 5px;
        cursor: pointer;
    }


</style>