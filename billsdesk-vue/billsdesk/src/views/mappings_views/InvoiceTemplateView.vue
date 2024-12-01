<template>
    <div>
        <h2>Template Settings</h2>
        <div v-if="showOptions" >
            <p>
                Do you want to create a new template or use an existing one?
            </p>
            <div class="options"> <!-- Las opciones solo se mostrarán si showOptions es true -->
                <div class="option">
                    <router-link to="/mapping-settings/mapping" @click="hideOptions">Create new template</router-link>
                </div>
                <div class="option">
                    <router-link to="/mapping-settings/invoice-template/existing" @click="hideOptions">Use existing template</router-link>
                </div>
            </div>
        </div>
        <div v-else>
            <router-view></router-view>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';  

const router = useRouter();

const showOptions = ref(true);

const hideOptions = () => {
    showOptions.value = false;
};


if (router.currentRoute.value.path === '/mapping-settings/invoice-template') {
    showOptions.value = true;
}else{
    showOptions.value = false;
}
</script>

<style scoped lang="scss">
.options {
    display: flex;
    gap: 20px;
    margin-top: 20px;
}

.option {
    border: 1px solid #e7e7e7;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s;
    width: 50%;
    text-align: center;

    a {
        text-decoration: none;
        color: #000;
        width: 100%;
        height: 100%;
        display: block;
        padding: 20px;
    }

    &:hover {
        background-color: #f1f1f1;
    }
}
</style>
