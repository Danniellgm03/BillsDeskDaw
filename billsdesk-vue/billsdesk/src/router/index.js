import { createRouter, createWebHistory } from 'vue-router';
import Login from '@/views/Login.vue'; 
import Register from '@/views/Register.vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import DashboardView from '@/views/DashBoard.vue';
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


const routes = [
  { path: '/login', component: Login },
  { path: '/register', component: Register },
  { path: '/register/invite/', component: RegisterWithInvitation },
   {
    path: '/',
    component: DashboardLayout, 
    meta: { requiresAuth: true },
    children: [
      { path: '', component: DashboardView },
      { path: 'file-manager', component: FileManager},
      { path: 'corrector', component: Corrector},
      {
        path: 'mapping-settings', 
        component: MappingSettings,
        children: [
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
