<template>
    <div>
        <SettingsLayout>
            <template #info>
                <h4>Users</h4>
                <p>
                    Welcome to the User Management section, where you can efficiently manage all user accounts within the platform. Here, 
                    you have the flexibility to create new users, invite team members to join, and manage existing user roles. Easily grant access, 
                    assign permissions, or remove users to maintain secure and streamlined operations. This section provides all the tools necessary to ensure each user has the appropriate level of access and to keep your team’s 
                    accounts organized.
                </p>
                <div class="actions_users">
                    <button class="action_button" @click="visible = true">Create</button>
                    <button class="action_button invite">Invite</button>
                </div>
            </template>
            <template #main>
               
                    <div class="users_table">
                         <template v-if="loading">
                                <div class="row_user" v-for="i in 2" :key="i">
                                    <div class="important_info">
                                        <Skeleton width="40px" height="40px" class="circle_image"/>
                                        <div class="info_user">
                                            <Skeleton width="100px" height="20px"/>
                                            <Skeleton width="150px" height="15px" style="margin-top: 5px;"/>
                                        </div>
                                    </div>
                                    <div class="actions">
                                        <Skeleton width="100px" height="30px"/>
                                        <Skeleton width="100px" height="30px"/>
                                    </div>
                                </div>
                        </template>
                        <template v-else>
                            <div class="row_user" v-for="user in users" :key="user.id">
                                <div class="important_info">
                                    <span class="circle_image">
                                        {{ user.name.charAt(0) }}
                                    </span>
                                    <div class="info_user">
                                            <span class="user_name">
                                                {{ user.name }}
                                                <i v-if="user.id === authenticatedUser.id" class="pending_label">You</i>
                                            </span>
                                        <span class="user_email">{{ user.email }}</span>
                                    </div>
                                </div>
                                <div class="actions">
                                    
                                    <Select 
                                        :options="roles" 
                                        optionLabel="name" 
                                        optionValue="id" 
                                        v-model="user.role_id"
                                        placeholder="Selecciona un rol"
                                        @change="updateRole(user)"
                                        :disabled="user.id === authenticatedUser.id"
                                    />                           
                                    <span class="pi pi-trash" @click="deleteUser(user)" v-if="user.id !== authenticatedUser.id"></span>
                                </div>
                            </div>
                        </template>
                   </div>
            </template>
        </SettingsLayout>
        <div class="divider"></div>
        <SettingsLayout>
            <template #info>
                <h4>Pending Invites</h4>
                <p>
                    In the Pending Invitations section, you can review and manage outstanding user invitations. Here, you’ll find details on each pending invite, including the recipient’s 
                    information and the date of issuance. You can resend invitations, 
                    cancel pending requests, or send reminders as needed. This feature helps you keep track of invite status and ensures a smooth onboarding process for new users.
                </p>
            </template>
            <template #main>
                <div class="users_table">

                    <template v-if="loading">
                        <div class="row_user" v-for="i in 2" :key="i">
                            <div class="important_info">
                                <Skeleton width="40px" height="40px" class="circle_image"/>
                                <div class="info_user">
                                    <Skeleton width="100px" height="20px"/>
                                    <Skeleton width="150px" height="15px" style="margin-top: 5px;"/>
                                </div>
                            </div>
                            <div class="actions">
                                <Skeleton width="100px" height="30px"/>
                                <Skeleton width="100px" height="30px"/>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="row_user" v-for="i in 5" :key="i">
                            <div class="important_info">
                                <span class="circle_image">
                                    J
                                </span>
                                <div class="info_user">
                                    <span class="user_name">John Doe <i class="pending_label">Pending</i></span>
                                    <span class="user_email">jhon@gmail.com</span>
                                </div>
                            </div>
                            <div class="actions">
                                <button class="action_button">Resend</button>
                                <button class="action_button cancel">Cancel</button>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </SettingsLayout>

        <Dialog v-model:visible="visible" modal header="Create User" :style="{ width: '25rem' }">
            <form>
                <div class="field_form">
                    <label for="name">Name</label>
                    <InputText id="name" v-model="form.name" />
                </div>
                <div class="field_form">
                    <label for="email">Email</label>
                    <InputText id="email" v-model="form.email" />
                </div>
                <div class="field_form">
                    <label for="role">Role</label>
                    <Select :options="roles" optionLabel="name" placeholder="Select a Role" v-model="form.role" />
                </div>
                <div class="field_form">
                    <label for="password">Password</label>
                    <InputText id="password" v-model="form.password" />
                    <label for="password" style="margin-top: 10px;">Confirm Password</label>
                    <InputText id="password-confirm" v-model="form.password_confirm" />
                </div>
                <div class="field_form">
                    <button class="create_user_btn" @click.prevent="createUser">Create</button>
                </div>
            </form>
        </Dialog>

    </div>
</template>

<script setup>
import { ref, onBeforeMount } from 'vue'
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import Select from 'primevue/select';
import Cookies from 'js-cookie'; 
import Skeleton from 'primevue/skeleton';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';

const authenticatedUser = ref(null);

const visible = ref(false);
const loading = ref(true);

const roles = ref([]);
const users = ref([]);

const form = ref({
    name: '',
    email: '',
    role: null,
    password: '',
    password_confirm: ''
});

const getAuthenticatedUser = () => {
    return JSON.parse(localStorage.getItem('user_data'));
}

const getUsers = async () => {
    loading.value = true;
    try {
        const response = await fetch('http://localhost:8000/api/company/users', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': Cookies.get('authToken') ?? ''
            }
        });
        const data = await response.json();
        
        if (response.ok) {
            users.value = data;
        } else {
            console.error(data);
        }

    } catch (error) {
        console.error(error);
    }
    loading.value = false;
}

onBeforeMount(() => {
    authenticatedUser.value = getAuthenticatedUser();
    getUsers();
    getRoles();
})

const getRoles = async () => {
    loading.value = true;
    try {
        const response = await fetch('http://localhost:8000/api/roles', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': Cookies.get('authToken') ?? ''
            }
        });
        const data = await response.json();
        
        if (response.ok) {
            roles.value = data;
        } else {
            console.error(data);
        }

    } catch (error) {
        console.error(error);
    }
    loading.value = false;
}

//Api  http://localhost:8000/api/company/users

const createUser = async () => {
    try {

        let body = {
            name: form.value.name,
            email: form.value.email,
            role_id: form.value.role.id,
            password: form.value.password,
        }

        const response = await fetch('http://localhost:8000/api/company/users', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': Cookies.get('authToken') ?? ''
            },
            body: JSON.stringify(body)
        });
        const data = await response.json();

        
        if (response.ok) {
            getUsers();
            visible.value = false;
        } else {
            console.error(data);
        }

    } catch (error) {
        console.error(error);
    }
}


const updateRole = async (user) => {
    try {

        let body = {
            role_id: user.role_id
        }

        const response = await fetch(`http://localhost:8000/api/company/users/${user.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': Cookies.get('authToken') ?? ''
            },
            body: JSON.stringify(body)
        });
        const data = await response.json();

        
        if (response.ok) {
            getUsers();
        } else {
            console.error(data);
        }

    } catch (error) {
        console.error(error);
    }
}

const deleteUser = async (user) => {
    try {

        const response = await fetch(`http://localhost:8000/api/company/users/${user.id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': Cookies.get('authToken') ?? ''
            }
        });
        
        if (response.ok) {
            getUsers();
        } else {
            console.error(data);
        }

    } catch (error) {
        console.error(error);
    }
}

</script>

<style scoped lang='scss'>

   .divider{
        margin: 40px 0;
        border-bottom: 1px solid #d5d5d5;
    }


    .actions_users{
        display: flex;
        gap: 10px;
        align-items: center;
        margin-top: 10px;

        .action_button{
            background-color: #000000;
            color: #ffffff;
            border: none;
            padding: 7px 22px;
            border-radius: 5px;
            cursor: pointer;

            &.invite{
                background-color: #ffffff;
                color: #000000;
                border: 1px solid #000000;
            }
        }
    }


    .users_table{
        border: 1px solid #bebebe;
        border-radius: 5px;
        background-color: #f6f6f6;

        .row_user{
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 10px;
            border-bottom: 1px solid #bebebe;

            &:last-child{
                border-bottom: none;
            }


            .important_info{
                display: flex;
                gap: 10px;
                align-items: center;

                .circle_image{
                    width: 40px;
                    height: 40px;
                    border-radius: 100%;
                    overflow: hidden;
                    border: 1px solid #8ea3fe;
                    display: grid;
                    place-items: center;
                    font-size: 1.3em;
                    color: #000;
                    background-color: #c5d8ff;
                }

                .info_user{
                    display: flex;
                    flex-direction: column;

                    .user_name{
                        font-weight: bold;
                        display: flex;
                        align-items: center;
                        gap: 6px;

                        .pending_label{
                            background-color: #d1d1d1;
                            color: #363636;
                            font-size: .7em;
                            padding: 2px 7px;
                            border-radius: 10px;
                        }
                    }

                    .user_email{
                        font-size: .8em;
                        color: #898989;
                    }
                }
            }

            .actions{
                display: flex;
                gap: 10px;
                align-items: center;

                .action_button{
                    background-color: #000000;
                    color: #ffffff;
                    border: none;
                    padding: 7px 20px;
                    border-radius: 5px;
                    cursor: pointer;

                    &.cancel{
                        background-color: #ffffff;
                        color: #9d0000;
                        border: 1px solid #9d0000;
                    }
                }
            }
        }
    }


    .field_form{
        margin-bottom: 10px;

        label{
            display: block;
            font-size: .9em;
            margin-bottom: 5px;
        }

        input{
            width: 100%;
            padding: 10px;
            border: 1px solid #bebebe;
            border-radius: 5px;

            &:focus{
                outline: none;
            }
        }

        .create_user_btn{
            background-color: #000000;
            color: #ffffff;
            border: none;
            padding: 7px 20px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
    }



</style>