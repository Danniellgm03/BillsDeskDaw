<template>
    <div>
        <span class="circle loading"></span>
    </div>
</template>

<script setup>
</script>

<style scoped lang='scss'>

    .circle {
        width: 150px;
        height: 150px;
        background: rgba(0, 58, 193, 0.905);
        background-size: cover;
        background-position: center;
        border-radius: 100%;
        display: inline-block;


        &.loading {
            animation: identifier 2s infinite;
        }

        @keyframes identifier {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }
    }
    

</style>