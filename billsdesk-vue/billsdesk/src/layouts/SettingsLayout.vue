<template>
    <section class="tabpanel_container">
        <div class="info_container_setting">
           <slot name="info"></slot>
        </div>
        <div class="main_container">
            <slot name="main"></slot>
        </div>
    </section>
</template>

<script setup>


</script>

<style scoped lang='scss'>


    .tabpanel_container{
        display: flex;
        gap: 20px;

        .info_container_setting{
            width: 45%;

            h4{
                margin-bottom: 10px;
            }

        }

        .main_container{
            width: 55%;
        }
    }
</style>