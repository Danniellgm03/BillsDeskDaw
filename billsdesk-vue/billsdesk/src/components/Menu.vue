<template>
    <div>
        <div class="main_menu">
            <header>
                <h1>BillsDesk</h1>
            </header>
            <nav>
                <div class="menu_container">
                    <p>Menu</p>
                    <ul>
                    <li v-if="hasPermission(['manage_files'])">
                        <router-link
                        to="/file-manager"
                        :class="{ 'active-link': $route.path === '/file-manager' || $route.path === '/' }"
                        >
                        <i class="pi pi-folder"></i> File manager
                        </router-link>
                    </li>
                    <li v-if="hasPermission( ['manage_invoices'] )">
                        <router-link
                        to="/corrector"
                        :class="{ 'active-link': $route.path.includes('/corrector') }"
                        >
                        <i class="pi pi-file-check"></i> Corrector
                        </router-link>
                    </li>
                    <li v-if="hasPermission(['manage_invoices'])">
                        <router-link
                        to="/mapping-settings"
                        :class="{ 'active-link': $route.path.includes('/mapping-settings') }"
                        >
                        <i class="pi pi-sitemap"></i> Mapping
                        </router-link>
                    </li>
                </ul>

                <div class="menu_general_container">
                    <p>General</p>
                    <ul>
                        <li v-if="hasPermission(['manage_users', 'manage_roles', 'meProfile', 'updateProfile', 'manage_companies', 'view_companies', 'manage_invitations', 'manage_contacts', 'view_contacts'])">
                        <router-link
                            to="/settings"
                            :class="{ 'active-link': $route.path.includes('/settings') }"
                        >
                            <i class="pi pi-cog"></i> Settings
                        </router-link>
                        </li>
                    </ul>
                </div>
            </div>

            </nav>
            <footer>
                <Avatar :label="user_data?.name[0] ?? 'A'" class="mr-2" shape="circle" />
                <div>
                    <p class="name_user">{{user_data?.name ?? 'User'}} </p>
                    <p class="email_user">{{ user_data?.email ?? ''}}</p>
                </div>
            </footer>
            <button @click="logout" class="btn btn-danger">
                <i class="pi pi-power-off"></i>
            </button>

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


const router = useRouter();

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
                summary: 'Error',
                detail: 'Error to logout'
            });
        }

        authStore.logout();
        router.push('/login'); 

    } catch (error) {
        notify({
            severity: 'error',
            summary: 'Error',
            detail: 'Error to logout'
        });
    }
  authStore.logout(); 
  router.push('/login'); // Redirige a la página de login
};


</script>

<style scoped lang='scss'>

.btn.btn-danger{
    background-color: #ff292c;
    border-color: #ff292c;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 5px 10px;
    border-radius: 5px;
    transition: background-color .15s;
    cursor: pointer;

    &:hover{
        background-color: #ff7875;
        border-color: #ff7875;
    }
}

.main_menu {
    background-color: #000016;
    color: white;
    height: 100dvh;
    width: 100%;
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;

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
            gap: 10px;

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

                    &:hover{
                        color: white;
                    }


                    &.active-link{
                        color: white;

                        i{
                            color: #5aaaff;
                        }

                        &::before{
                            content: "";
                            width: 3px;
                            height: 150%;
                            background-color: #5aaaff;
                            position: absolute;
                            left: -20px;
                            border-radius: 0px 10px 10px 0 ;
                        }
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
        padding: 10px 5px;
        border-radius: 10px;
        transition: background-color .15s;

        &:hover{
            cursor: pointer;
            background-color: rgb(255 255 255 / 9%);
        }
        

        &::before{
            content: "";
            width: 100%;
            height: 1px;
            background-color: #ffffff2b;
            position: absolute;
            top: -10px;
        }

        .name_user{
            font-weight: 500;
        }

        .email_user{
            font-size: .9em;
            font-weight: 400;
            color: #b9b9b9;
        }

        
    }
}


</style>