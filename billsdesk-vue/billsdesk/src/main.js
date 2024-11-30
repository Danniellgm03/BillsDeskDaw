import './assets/main.css'
import 'primeicons/primeicons.css'; 

import { createApp } from 'vue'
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import App from './App.vue';
import router from './router'; 
import { createPinia } from 'pinia';


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
app.mount('#app')
