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
                        <li>
                            <router-link
                                to="/"
                                :class="{ 'active-link': $route.path === '/' }"
                            >
                                <i class="pi pi-th-large"></i> Overview
                            </router-link>
                        </li>
                        <li>
                            <router-link
                                to="/file-manager"
                                :class="{ 'active-link': $route.path === '/file-manager' }"
                            >
                                <i class="pi pi-folder"></i> File manager
                            </router-link>
                        </li>
                        <li>
                            <router-link
                                to="/corrector"
                                :class="{ 'active-link': $route.path === '/corrector' }"
                            >
                                <i class="pi pi-file-check"></i> Corrector
                            </router-link>
                        </li>
                        <li>
                            <router-link
                                to="/mapping-settings"
                                :class="{ 'active-link': $route.path === '/mapping-settings' }"
                            >
                                <i class="pi pi-sitemap"></i> Mapping
                            </router-link>
                        </li>
                    </ul>
                </div>
                <div class="menu_general_container">
                    <p>General</p>
                    <ul>
                        <li>
                            <router-link
                                to="/settings"
                                :class="{ 'active-link': $route.path === '/settings' }"
                            >
                                <i class="pi pi-cog"></i> Settings
                            </router-link>
                        </li>
                    </ul>
                </div>
            </nav>
            <footer @click="toggle">
                <Avatar :label="user_data?.name[0] ?? 'A'" class="mr-2" shape="circle" />
                <div>
                    <p class="name_user">{{user_data?.name ?? 'User'}} </p>
                    <p class="email_user">{{ user_data?.email ?? ''}}</p>
                </div>
            </footer>

        </div>
    </div>
</template>


<script setup>
    import Avatar from 'primevue/avatar';
    import Popover from 'primevue/popover';

    import { ref } from 'vue';

    const op = ref(true);

    const user_data = JSON.parse(localStorage.getItem('user_data'));

    const toggle = (event) => {
        op.value.toggle(event);
    }

</script>

<style scoped lang='scss'>

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