// src/utils/permissions.js
import { useAuthStore } from '@/stores/authStore';

export const hasPermission = (requiredPermissions) => {
  const authStore = useAuthStore(); // Accede al store
  const isAdmin = authStore.user?.role === 'admin'; // Verifica si es admin
  const userPermissions = authStore.permissions || []; // Obtén los permisos

  if (isAdmin) return true; // Si es admin, tiene acceso total

  // Verifica si tiene al menos uno de los permisos requeridos
  return requiredPermissions.some((perm) => userPermissions.includes(perm));
};
