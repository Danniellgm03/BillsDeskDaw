// src/utils/notificationService.js
import { ref } from 'vue';

const toastInstance = ref(null);

export const useNotificationService = () => {
  const setToast = (toastRef) => {
    toastInstance.value = toastRef;
  };

  const notify = ({ severity, summary, detail, life = 3000 }) => {
    if (toastInstance.value) {
      toastInstance.value.add({ severity, summary, detail, life });
    } else {
      console.error('Toast instance is not set');
    }
  };

  return { setToast, notify };
};
