<template>
    <div class="file_container">
        <header class="rapid_actions">
            <i class="pi pi-star" v-if="!file.is_fav"></i>
            <i class="pi pi-star-fill" v-if="file.is_fav"></i>
            <i class="pi pi-ellipsis-v" @click="handleEllipsisClick(file)">
            </i>
        </header>
        <i class="pi pi-file"></i>
        <p class="name_file">{{ file.file_name }}</p>
        <div class="divider"></div>
        <div class="footer_container">
            <strong>File Size</strong>
            <p>{{ file.file_size }} {{ file.file_size_type }}</p>
        </div>
    </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

defineProps({
    file: {
        type: Object,
        required: true,
    },
});

// Emisor de eventos
const emit = defineEmits(['openDrawer']);

// Método para manejar el clic
const handleEllipsisClick = (file) => {
    emit('openDrawer', file); // Emitir el objeto file
};
</script>

<style scoped lang='scss'>

    .file_container{
        width: 100%;
        background-color: #f1f1f1;
        border-radius: 5px;
        display: flex;
        align-items: center;
        flex-direction: column;
        padding: 20px 5px;
        gap: 6px;

        .rapid_actions {
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 0 10px;
    
            i {
                cursor: pointer;
                font-size: 20px;
            }
        }
    
    
        .divider {
            width: 90%;
            height: 1px;
            background-color: #d1d1d1;
        }
    
        .pi.pi-file {
            font-size: 3em;
            margin: 10px 0;
        }
    
    
        .name_file {
            font-size: 1em;
            font-weight: 300;
        }
    
        .footer_container {
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 0 10px;
            font-size: .9em;
    
            strong {
                font-weight: 300;
            }
        }
    }

</style>