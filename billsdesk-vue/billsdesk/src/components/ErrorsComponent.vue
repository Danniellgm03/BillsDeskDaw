<template>
    <div class="errors">
        <div v-for="(error, key) in props.errors" :key="key">
            <div v-for="message in error" :key="message">
                <p>Error {{key}}: {{ message }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, defineProps } from 'vue'

const props = defineProps({
    errors: {
        type: Object,
        required: true
    }
})

</script>

<style scoped lang='scss'>


.errors {
    margin: 10px 0;
    padding: 10px;
    border-radius: 5px;
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

</style>