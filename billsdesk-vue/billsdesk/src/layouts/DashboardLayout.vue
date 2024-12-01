<template>
    <div class="dashboard-layout">
        <Menu class="menu"/>
        <main>
            <router-view /> 
        </main>
        <Toast ref="toast" />
    </div>
</template>

<script setup>
import Menu from '@/components/Menu.vue';
import Toast from 'primevue/toast';
import { ref, onMounted } from 'vue';
import { useNotificationService } from '@/utils/notificationService';

const toast = ref(null);
const { setToast } = useNotificationService();

onMounted(() => {
  setToast(toast.value); // Asocia el Toast al servicio
});
</script>

<style scoped lang='scss'>
.dashboard-layout {
    display: flex;

    .menu{
        max-width: 250px;
    }

    main {
        flex: 1;
        padding: 20px;
        overflow-y: scroll;
        height: 100dvh;
    }
}
</style>