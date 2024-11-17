<template>
    <div class="dashboard-layout">
        <Menu class="menu"/>
        <main>
            <router-view /> 
        </main>
    </div>
</template>

<script setup>
import Menu from '@/components/Menu.vue';
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