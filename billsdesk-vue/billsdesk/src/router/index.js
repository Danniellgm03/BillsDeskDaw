import { createRouter, createWebHistory } from 'vue-router';
import Login from '@/views/Login.vue'; 
import Register from '@/views/Register.vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import FileManager from '@/views/FileManager.vue';
import Corrector from '@/views/Corrector.vue';
import MappingSettings from '@/views/MappingSettings.vue';
import SettingsPanel from '@/views/SettingsPanel.vue';
import RegisterWithInvitation from '@/views/RegisterWithInvitation.vue';
import Cookies from 'js-cookie'; // Importa js-cookie para acceder a las cookies
import SelectingFile from '@/views/mappings_views/SelectingFileView.vue';
import Mapping from '@/views/mappings_views/MappingView.vue';
import InvoiceTemplate from '@/views/mappings_views/InvoiceTemplateView.vue';
import CorrectionRules from '@/views/mappings_views/CorrectionRulesView.vue';
import NewTemplateInvoice from '@/views/mappings_views/invoiceTemplate_views/NewTemplateInvoiceView.vue';
import ExistingTemplateInvoiceView from '@/views/mappings_views/invoiceTemplate_views/ExistingTemplateInvoiceview.vue';
import EditTemplateInvoice from '@/views/mappings_views/invoiceTemplate_views/EditTemplateInvoiceView.vue';
import Finish from '@/views/mappings_views/FinishView.vue';
import { useAuthStore } from '@/stores/authStore';
import ResetPassword from '@/views/ResetPassword.vue';
import ForgotPassword from '@/views/ForgotPassword.vue';


const routes = [
  { path: '/login', component: Login },
  { path: '/register', component: Register },
  { path: '/forgot-password', component: ForgotPassword },
  { path: '/reset-password', component: ResetPassword },
  { path: '/register/invite/', component: RegisterWithInvitation },
   {
    path: '/',
    component: DashboardLayout, 
    meta: { requiresAuth: true },
    children: [
      { path: '', component: FileManager },
      { path: 'file-manager', component: FileManager,  meta: { permission: ['manage_files'] }},
      { path: 'corrector', component: Corrector, meta: { permission: ['manage_invoices'] }},
      {
        path: 'mapping-settings', 
        component: MappingSettings,
        meta: { permission: ['manage_invoices'] },
        children: [
          {
            redirect: '/mapping-settings/selecting-files',
            path: ''
          },
          { path: 'selecting-files', component: SelectingFile },
          { path: 'mapping', component: Mapping },
          {
            path: 'invoice-template',
            component: InvoiceTemplate,  // Vista principal de los templates
            children: [
              { path: 'new', component: NewTemplateInvoice },  // Subruta para crear un nuevo template
              { path: 'existing', component: ExistingTemplateInvoiceView },  // Subruta para usar un template existente
              { path: 'edit/:id', component: EditTemplateInvoice },  // Subruta para editar un template existente
            ],
          },          
          { path: 'correction-rules', component: CorrectionRules },
          { path: 'finish', component: Finish }
        ]
      },
      { path: 'settings', component: SettingsPanel, meta: { permission: ['manage_users', 'manage_roles', 'meProfile', 'updateProfile', 'manage_companies', 'view_companies', 'manage_invitations', 'manage_contacts'] } },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  const isAuthenticated = Cookies.get('authToken');

  if (!isAuthenticated) {
    authStore.logout();

    if(to.path === '/forgot-password' || to.path === '/reset-password' || to.path === '/register' || to.path === '/register/invite/') {
      next();
      return ;
    }

    // Evita redirigir si ya está en /login
    if (to.path !== '/login') {
      next('/login');
    } else {
      next(); // Permite permanecer en /login
    }
  } else {
    authStore.loadUserData();

    // Comprueba si la ruta requiere autenticación
    if (to.meta.requiresAuth && !isAuthenticated) {
      next('/login');
    } else {
      // Comprueba si el usuario es administrador
      const isAdmin = authStore.user?.role === 'admin'; // Ajusta según cómo definas el rol

      // Si es administrador, permite el acceso
      if (isAdmin) {
        next();
      } else if (to.meta.permission) {
        // Para usuarios no administradores, verifica los permisos
        const userPermissions = authStore.permissions || [];
        const requiredPermissions = Array.isArray(to.meta.permission)
          ? to.meta.permission
          : [to.meta.permission];

        // Verificar si el usuario tiene al menos uno de los permisos requeridos
        const hasPermission = requiredPermissions.some((permission) =>
          userPermissions.includes(permission)
        );

        if (!hasPermission) {
          alert('No tienes permiso para acceder a esta página.');
          next('/'); // Redirige si no tiene permisos
        } else {
          next();
        }
      } else {
        next(); // Permite el acceso si no hay restricciones de permisos
      }
    }
  }
});



export default router;
