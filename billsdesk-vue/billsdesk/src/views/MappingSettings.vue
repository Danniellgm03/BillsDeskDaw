<template>
    <div>
        <h2>Mapping Settings</h2>
        <section>
            <div class="left_side">
                <MenuMapping />
            </div>
            <div class="right_side">
                <router-view></router-view>
            </div>
        </section>
    </div>
</template>

<script setup>
    import { ref } from 'vue'
    import MenuMapping from '@/components/Mapping/MenuMapping.vue';
</script>

<style scoped lang='scss'>

    section {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
        gap: 20px;
    }

    .left_side {
        width: 20%;
    }

    .right_side {
        width: 80%;
        border: 1px solid #e7e7e7;
        box-shadow: 0 0 30px -10px rgba(0, 0, 0, 0.1);
        border-radius: 5px;
        padding: 20px;
    }


</style>