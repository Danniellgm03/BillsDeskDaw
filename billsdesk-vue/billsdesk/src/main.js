import './assets/main.css'
import 'primeicons/primeicons.css'; 

import { createApp } from 'vue'
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import App from './App.vue';
import router from './router'; 
import { createPinia } from 'pinia';
import i18n from './i18n'; 


const app = createApp(App)

app.use(PrimeVue, {
    theme: {
        preset: Aura,
        options: {
            prefix: 'p',
            darkModeSelector: false,
            cssLayer: false
        }
    }
});

app.use(createPinia())
app.use(router)
app.use(i18n)
app.mount('#app')
