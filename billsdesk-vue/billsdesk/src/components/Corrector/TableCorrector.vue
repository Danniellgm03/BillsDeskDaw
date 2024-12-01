<template>
    <div>
        <DataTable 
            :value="props.invoiceProccesed" 
            showGridlines 
            stripedRows 
            paginator 
            :rows="5" 
            :rowsPerPageOptions="[5, 10, 20, 50]" 
            tableStyle="min-width: 50rem"
        >
            <Column 
                v-for="(col, index) in columns" 
                :key="index" 
                :field="col.field" 
                :header="col.header" 
            />
        </DataTable>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

const props = defineProps({
    invoiceProccesed: {
        type: Array,
        required: true,
    },
});

const columns = ref([]);

watch(
    () => props.invoiceProccesed,
    (newVal) => {
        if (newVal && newVal.length > 0) {
            columns.value = Object.keys(newVal[0])
                // Filtrar todas las claves que terminan en "_highlight"
                .filter((key) => !key.endsWith("_highlight") && key !== "row_highlight")
                .map((key) => ({
                    field: key,
                    header: key.charAt(0).toUpperCase() + key.slice(1),
                }));
        } else {
            columns.value = [];
        }
    },
    { immediate: true }
);
</script>

<style scoped lang="scss">
</style>
