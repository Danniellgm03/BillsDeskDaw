import { defineStore } from 'pinia';

export const useSelectedFileStore = defineStore('selectedFile', {
  state: () => ({
    selectedFile: {} // Estado reactivo para almacenar el archivo seleccionado
  }),
  actions: {
    setSelectedFile(file) {
      this.selectedFile = file; // Establecer el archivo seleccionado
    },
    clearSelectedFile() {
      this.selectedFile = {}; // Limpiar el archivo seleccionado
    }
  }
});
