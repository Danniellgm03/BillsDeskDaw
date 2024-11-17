<template>
    <div>
        <div class="container_header">
            <h2>FileManager</h2>
            <div class="actions_file_manager">
                <ul>
                    <li :class="{
                        'adding': addingFile,
                    }"> <i class="pi pi-plus" @click="handleAddFile"></i> </li>
                    <li>
                        <i class="pi pi-star" @click="handleFavouriteFilter" v-if="!filterFavorites"></i>
                        <i class="pi pi-star-fill" @click="handleFavouriteFilter" v-if="filterFavorites"></i>
                    </li>
                </ul>
            </div>
        </div>
        <div class="search_file">
            <input v-model="search_input" type="text" placeholder="Search file" @change="searchFile" />
            <i class="pi pi-search" @click="searchFile"></i>
        </div>
        <div class="add_file" v-if="addingFile">
            <FileUpload name="file[]" url="/api/upload" :multiple="true" accept="image/*" :maxFileSize="1000000">
                <template #empty>
                    <span>Drag and drop files to here to upload.</span>
                </template>
            </FileUpload>
        </div>
        <section class="files_manager">
            <div class="header_file_manager">
                <h4>Recent Files</h4>
                <div class="view_tab" @click="changeLayout">
                    <button :class="{ active: layout === 'grid_extend' }">
                        <i class="pi pi-table"></i>
                    </button>
                    <button :class="{ active: layout === 'grid' }">
                        <i class="pi pi-th-large"></i>
                    </button>
                </div>
            </div>
            <div :class="['container_files', layout]">
                <FileComponenteContent v-for="file in files" :key="file.id" :file="file"
                    @openDrawer="handleOpenDrawer" />
            </div>
        </section>

        <Drawer v-model:visible="isDrawerOpen" position="right" class="p-drawer_styled">
            <template #header>
                <header>
                    <i class="pi pi-file"></i>File Details
                </header>
            </template>
            <div class="file_details_drawer">
                <div class="file_prev">
                    <i class="pi pi-file"></i>
                </div>

                <div class="file_info">
                    <h2>{{ selectedFile.name }}</h2>
                    <p class="file_size">{{ selectedFile.size }}</p>
                    <div class="file_description">
                        <strong>Description</strong>
                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, quidem.</p>
                    </div>
                </div>


                <div class="actions_file">
                    <div class="divider"></div>
                    <div class="actions">
                        <button class="pi pi-download"></button>
                        <button class="pi pi-file-edit"></button>
                        <button class="pi pi-trash"></button>
                    </div>
                    <div class="divider"></div>
                </div>
            </div>
        </Drawer>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import FileComponenteContent from '@/components/FileManager/FileComponenteContent.vue';
import Drawer from 'primevue/drawer';
import FileUpload from 'primevue/fileupload';
import Button from 'primevue/button';

const filterFavorites = ref(false);
const search_input = ref('');
const addingFile = ref(false);
const isDrawerOpen = ref(false);
const selectedFile = ref({});
const files = ref(
    Array.from({ length: 50 }, (_, index) => ({
        id: index + 1, // Asignar un ID único para cada objeto
        name: 'TestFile' + (index + 1) +'.pdf',
        size: '1.5 MB',
        fav: index % 2.5 === 0,
    }))
);


const layout = ref('grid_extend'); 

const handleOpenDrawer = (file) => {
    selectedFile.value = file;
    isDrawerOpen.value = true;
};

const handleAddFile = () => {
    addingFile.value = !addingFile.value;
};

const searchFile = () => {
    if (search_input.value) {
        files.value = files.value.filter((file) => file.name.includes(search_input.value));
    } else {
        files.value = Array.from({ length: 50 }, (_, index) => ({
            id: index + 1,
            name: 'TestFile' + (index + 1) +'.pdf',
            size: '1.5 MB',
        }));
    }
};

const handleFavouriteFilter = () => {
    filterFavorites.value = !filterFavorites.value;
    if (filterFavorites.value) {
        files.value = files.value.filter((file) => file.fav);
    } else {
        files.value = Array.from({ length: 50 }, (_, index) => ({
            id: index + 1,
            name: 'TestFile' + (index + 1) +'.pdf',
            size: '1.5 MB',
            fav: index % 2.5 === 0,
        }));
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



</style>