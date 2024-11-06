import { createRouter, createWebHistory } from 'vue-router';
import Login from '@/views/Login.vue'; 
import Register from '@/views/Register.vue';
import Dashboard from '@/views/DashBoard.vue';
import Cookies from 'js-cookie'; // Importa js-cookie para acceder a las cookies

const routes = [
  { path: '/login', component: Login },
  { path: '/register', component: Register },
  { path: '/', component: Dashboard, meta: { requiresAuth: true } }
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
