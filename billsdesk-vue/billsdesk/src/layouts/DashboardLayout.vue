<template>
    <div class="dashboard-layout">
        <Menu class="menu" :class="{
            'active': menu_open
        }" @open_menu="openMenu"/>
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

const menu_open = ref(false);

const openMenu = () => {
    menu_open.value = !menu_open.value;
};

onMounted(() => {
  setToast(toast.value); // Asocia el Toast al servicio
});
</script>

<style scoped lang='scss'>
.dashboard-layout {
    display: flex;

    .menu{
        max-width: 230px;
        width: 30%;

        @media (max-width: 768px) {
            width:  100% !important;
            max-width: 100% !important;
            position: fixed;
            z-index: 99;
            left: -100vw;
            transition: left 0.3s;


            &.active{    
                left: 0;
                transition: left 0.3s;
            }
        }
    }

    main {
        flex: 1;
        padding: 20px;
        overflow-y: scroll;
        height: 100dvh;
    }
}
</style>