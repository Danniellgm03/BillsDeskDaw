import { defineStore } from 'pinia';

export const useMappingColumnsStore = defineStore('mappingColumns', {
  state: () => ({
    columns: {} // Estado reactivo para almacenar las columnas del mapeo
  }),
  actions: {
    // Establecer las columnas del mapeo, inicializando con valores vacíos
    setColumns(columns) {
      // Inicializar el mapeo de columnas con valores vacíos
      this.columns = Object.keys(columns).reduce((acc, column) => {
        return acc;
      }, {});
    },

    // Limpiar las columnas del mapeo
    clearColumns() {
      this.columns = {}; // Limpiar las columnas del mapeo
    },

    // Actualizar un mapeo específico de una columna
    updateColumnMapping(column, mappedValue) {
      this.columns[column] = mappedValue;
    }
  }
});
