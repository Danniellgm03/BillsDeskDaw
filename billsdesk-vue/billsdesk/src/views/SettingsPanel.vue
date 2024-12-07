<template>
  <div>
    <h2>{{ $t('settings.title') }}</h2>
    <Tabs value="0">
      <TabList>
        <!-- Perfil siempre accesible -->
        <Tab value="0">
          {{ $t('settings.profile') }}
        </Tab>

        <!-- Usuarios (requiere permisos específicos o admin) -->
        <Tab value="1" v-if="hasPermission(['manage_users', 'manage_roles'])">
          {{ $t('settings.users') }}
        </Tab>

        <!-- Contactos (requiere permisos específicos o admin) -->
        <Tab value="2" v-if="hasPermission(['manage_contacts', 'view_contacts'])">
          {{ $t('settings.contacts') }}
        </Tab>
      </TabList>
      <TabPanels>
        <TabPanel value="0">
          <ProfileComponent />
        </TabPanel>
        <TabPanel value="1" v-if="hasPermission(['manage_users', 'manage_roles'])">
          <UsersComponent />
        </TabPanel>
        <TabPanel value="2" v-if="hasPermission(['manage_contacts', 'view_contacts'])">
          <ContactsComponent />
        </TabPanel>
      </TabPanels>
    </Tabs>
  </div>
</template>


<script setup>
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import UsersComponent from '@/components/Settings/UsersComponent.vue';
import ProfileComponent from '@/components/Settings/ProfileComponent.vue';
import ContactsComponent from '@/components/Settings/ContactsComponent.vue';

import { useAuthStore } from '@/stores/authStore';

const authStore = useAuthStore();
const userPermissions = authStore.permissions || [];
const isAdmin = authStore.user?.role === 'admin'; // Define cómo identificar el rol de admin

// Función para verificar permisos
const hasPermission = (requiredPermissions) => {
  if (isAdmin) return true; // Si es admin, permite todo
  return requiredPermissions.some((perm) => userPermissions.includes(perm));
};
</script>

<style scoped lang='scss'>


    .p-tab-active{
        color: #000000;
        font-weight: bold;
        border-color: #000000;
    }

    .p-tablist-active-bar{
        display: none;
    }


 
</style>