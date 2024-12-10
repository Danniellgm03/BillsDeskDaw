<template>
    <div>
        <div class="main_menu">
            <header>
                <h1>BillsDesk</h1>
                <div class="close_menu"  @click="open_menu">
                    <i class="pi pi-times"></i>
                </div>
            </header>
            <nav>
                <div class="menu_container">
                    <!-- <p>
                        {{ $t('menu') }}
                    </p> -->
                    <ul>
                    <li v-if="hasPermission(['manage_files'])"  @click="open_menu">
                        <router-link
                        to="/file-manager"
                        :class="{ 'active-link': $route.path === '/file-manager' || $route.path === '/' }"
                        >
                        <i class="pi pi-folder"></i> {{ $t('file_manager.title') }}
                        </router-link>
                    </li>
                    <li v-if="hasPermission( ['manage_invoices'] )"  @click="open_menu">
                        <router-link
                        to="/corrector"
                        :class="{ 'active-link': $route.path.includes('/corrector') }"
                        >
                        <i class="pi pi-file-check"></i> {{ $t('corrector.title') }}
                        </router-link>
                    </li>
                    <li v-if="hasPermission(['manage_invoices'])" @click="open_menu">
                        <router-link
                        to="/mapping-settings"
                        :class="{ 'active-link': $route.path.includes('/mapping-settings') }"
                        >
                        <i class="pi pi-sitemap"></i> {{ $t('mapping_settings.title_menu') }}
                        </router-link>
                    </li>
                </ul>
                <div class="divider"></div>
                <div>
                    <Select v-model="selectedLanguage"  @change="changeLanguage" :options="languages" optionLabel="name" optionValue="code" placeholder="Select a Lang" class="p-select_lang" />
                </div>
                
                <div class="menu_general_container">
                    <!-- <p>
                        {{ $t('general.title') }}
                    </p> -->
                    <ul>
                        <li v-if="hasPermission(['manage_users', 'manage_roles', 'meProfile', 'updateProfile', 'manage_companies', 'view_companies', 'manage_invitations', 'manage_contacts', 'view_contacts'])" @click="open_menu">
                        <router-link
                            to="/settings"
                            :class="{ 'active-link': $route.path.includes('/settings') }"
                        >
                            <i class="pi pi-cog"></i> {{ $t('settings.title') }}
                        </router-link>
                        </li>
                    </ul>
                </div>
            </div>

            </nav>
            <button @click="logout" class="btn logout">
                <i class="pi pi-sign-out"></i> {{ $t('logout') }}
            </button>
            <footer>
                <Avatar :label="user_data?.name[0] ?? 'A'" class="mr-2" shape="circle" />
                <div>
                    <p class="name_user">{{user_data?.name ?? 'User'}} </p>
                    <p class="email_user">{{ user_data?.email ?? ''}}</p>
                </div>
            </footer>

            <div class="open_mobile" @click="open_menu">
                <i class="pi pi-angle-right"></i>
            </div>
        </div>
    </div>
</template>


<script setup>
import Avatar from 'primevue/avatar';
import { ref } from 'vue';
import { useAuthStore } from '@/stores/authStore'; // Importa el store
import { useRouter } from 'vue-router';
import Cookies from 'js-cookie'; 
import { useNotificationService } from '@/utils/notificationService';
const { notify } = useNotificationService();
import { useI18n } from 'vue-i18n';
import { defineEmits } from 'vue';
import Select from 'primevue/select';



const { t, locale } = useI18n();
const router = useRouter();
const emit = defineEmits(['open_menu']);

const open_menu = () => {
    emit('open_menu');
};

const languages = [
    { name: 'English', code: 'en' },
    { name: 'Español', code: 'es' },
    { name: 'Italy', code: 'it' },
    { name: 'China', code: 'zh-CN'}
];

const selectedLanguage = ref(locale.value)

const changeLanguage = () => {
  locale.value = selectedLanguage.value; 
};

const authStore = useAuthStore(); // Accede al store de autenticación
const user_data = JSON.parse(localStorage.getItem('user_data'));

const hasPermission = (requiredPermissions) => {
  // Comprueba si el usuario es administrador
  const isAdmin = authStore.user?.role === 'admin'; // Ajusta según cómo identifiques al administrador

  if (isAdmin) return true; // Si es administrador, siempre tiene permisos

  // Verifica si el usuario tiene los permisos requeridos
  if (!authStore.permissions) return false;
  return requiredPermissions.some((perm) => authStore.permissions.includes(perm));
};


const logout = async () => {
    try {
        const response = await fetch('http://localhost:8000/api/logout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': Cookies.get('authToken') ?? ''
            }
        });

        if (!response.ok) {
            notify({
                severity: 'error',
                summary: t('error'),
                detail: t('error_logout')
            });
        }

        authStore.logout();
        router.push('/login'); 

    } catch (error) {
        notify({
            severity: 'error',
            summary: t('error'),
            detail: t('error_logout')
        });
    }
  authStore.logout(); 
  router.push('/login'); // Redirige a la página de login
};


</script>

<style scoped lang='scss'>

header{
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;


    .close_menu{
        @media screen and (min-width: 768px){
            display: none;
        }
        color: white;
        background-color: #2c2c2c;
        padding: 10px;
        border-radius: 5px;
        transition: background-color .15s;

        &:hover{
            cursor: pointer;
            background-color: #454545;
        }
    }
}

.logout{
    color: rgb(195, 195, 195);
    background-color: transparent;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    margin-top: 20px;
    transition: background-color .15s;
    text-align: left;
    display: flex;
    align-items: center;
    gap: 10px;
    

    &:hover{
        cursor: pointer;
        background-color: #454545;
        color: red;
    }

    margin-bottom: 10px;

}

.main_menu {
    background-color: #2c2c2c;
    color: white;
    height: 100dvh;
    width: 100%;
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;

    @media screen and (max-width: 768px){
        width: 100%;
        max-width: 100%;
    }

    nav{
        display: flex;
        flex-direction: column;
        height: 80%;
        gap: 40px;

        p{
            font-size: 1.2rem;
            font-weight: 500;
        }

        ul{
            list-style: none;
            padding: 0;
            margin: 20px 0 0 0;
            display: flex;
            flex-direction: column;

            li{
                margin-bottom: 10px;
                font-size: 1em;

                a{
                    color: rgb(195, 195, 195);
                    text-decoration: none;
                    display: flex;
                    align-items: center;
                    gap: 7px;
                    position: relative;
                    padding: 10px;
                    border-radius: 3px;

                    &:hover{
                        color: white;
                        background-color: #393939;
                    }


                    &.active-link{
                        color: white;
                        background-color: #454545;
                        padding: 10px;
                        border-radius: 3px;

                        i{
                            color: #3f9cff;
                        }

                        // &::before{
                        //     content: "";
                        //     width: 3px;
                        //     height: 150%;
                        //     background-color: #3f9cff;
                        //     position: absolute;
                        //     left: -20px;
                        //     border-radius: 0px 10px 10px 0 ;
                        // }
                    }
                }
            }
        }
    }

    footer{
        display: flex;
        align-items: center;
        gap: 10px;
        position: relative;
        padding: 10px 11px;
        border-radius: 10px;
        transition: background-color .15s;

        &:hover{
            cursor: pointer;
            background-color: rgb(255 255 255 / 9%);
        }
        

        // &::before{
        //     content: "";
        //     width: 100%;
        //     height: 1px;
        //     background-color: #ffffff2b;
        //     position: absolute;
        //     top: -10px;
        // }

        .name_user{
            font-weight: 500;
            font-size: .8em;
        }

        .email_user{
            font-size: .7em;
            font-weight: 400;
            color: #b9b9b9;
        }

        
    }
}

.divider{
    width: 100%;
    height: 1px;
    background-color: #ffffff2b;
    margin: 20px 0;
    border-bottom: #454545;
}

.open_mobile{
    position: absolute;
    width: 32px;
    height: 60px;
    background-color: #2c2c2c;
    right: -31px;
    top: 50%;
    transform: translateY(-50%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;

    @media  (min-width: 768px){
        display: none;
    }
}


</style>