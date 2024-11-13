import { createRouter, createWebHistory } from 'vue-router';
import Login from '@/views/Login.vue'; 
import Register from '@/views/Register.vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import DashboardView from '@/views/DashBoard.vue';
import FileManager from '@/views/FileManager.vue';
import Corrector from '@/views/Corrector.vue';
import MappingSettings from '@/views/MappingSettings.vue';
import SettingsPanel from '@/views/SettingsPanel.vue';
import Cookies from 'js-cookie'; // Importa js-cookie para acceder a las cookies

const routes = [
  { path: '/login', component: Login },
  { path: '/register', component: Register },
   {
    path: '/',
    component: DashboardLayout, 
    meta: { requiresAuth: true },
    children: [
      { path: '', component: DashboardView },
      { path: 'file-manager', component: FileManager},
      { path: 'corrector', component: Corrector},
      { path: 'mapping-settings', component: MappingSettings},
      { path: 'settings', component: SettingsPanel},
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const isAuthenticated = Cookies.get('authToken'); // Lee la cookie para verificar la autenticación

  if (to.meta.requiresAuth && !isAuthenticated) {
    next('/login'); // Redirige al login si no está autenticado
  } else if ((to.path === '/login' || to.path === '/register') && isAuthenticated) {
    // Si el usuario está autenticado y trata de acceder al login o register, redirige al dashboard
    next('/');
  } else {
    next(); // Permite el acceso a la ruta
  }
});

export default router;
