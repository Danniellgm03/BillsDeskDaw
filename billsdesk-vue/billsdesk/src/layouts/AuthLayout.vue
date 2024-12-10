<template>
    <div>
        <section class="auth-component">
            <div class="left_container" :class="{'loading': loading}">
                <slot name="left-content"></slot>
            </div>
            <div class="right_container">
                <span class="circle" :class="{'loading': loading}"></span>
                <div class="blur_overlay"></div>
            </div>
        </section>
        <Toast ref="toast" />
    </div>
</template>

<script setup>
import Toast from 'primevue/toast';
import { useNotificationService } from '@/utils/notificationService';
const { setToast } = useNotificationService();
import { ref, onMounted } from 'vue';

const toast = ref(null);

onMounted(() => {
  setToast(toast.value); // Asocia el Toast al servicio
});


defineProps({
    loading: {
        type: Boolean,
        default: false,
    }
});
</script>


<style lang="scss">

.auth-component {
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 100vh;
    background-color: #ffffff;

    @media (max-width: 991px) {

        .right_container {
            display: none !important;
        }

        .left_container {
            width: 100% !important;
            padding: 50px !important;
        }
    }

    .left_container {
        width: 50%;
        height: 100%;
        padding: 100px;
        display: flex;
        flex-direction: column;
        align-items: start;
        justify-content: center;

        &.loading {
            opacity: 0.5;
            pointer-events: none;
        }

        h2 {
            font-size: 2.5em;
            margin-bottom: 15px;
        }

        p{
            margin: 0;
            color: #585858;
        }

        .form-group {
            margin-top: 20px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: start;
            label {
                font-size: .9em;
                margin-bottom: 5px;
            }

            input {
                width: 100%;
                padding: 10px;
                border: 1px solid #ccc;
                border-radius: 5px;
                margin-top: 5px;
                font-size: .9em;
            }
             
        }

        .login_link {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            width: 100%;

            p {
                font-size: .9em;
                color: #585858;

                a {
                    color: #0066d4;
                    text-decoration: none;
                }
            }
        }

        .forgot-password {
            margin-top: 20px;
            a {
                color: #0052aa;
                text-decoration: none;
                font-size: .9em;
            }
        }

        .p-button {
            margin-top: 20px;
            width: 100%;
            padding: 10px;
            border: 0px;
            border-radius: 5px;
            background-color: #003ac1;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s;
            &:hover {
                background-color: #0056c4;
                border: 0px;
            }
        }

        .register_link {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            width: 100%;
            p {
                font-size: .9em;
                color: #585858;
                a {
                    color: #0066d4;
                    text-decoration: none;
                }
            }
        }
    }

    .right_container {
        width: 50%;
        height: 100%;
        overflow: hidden;
        display: grid;
        place-items: center;
        position: relative;
        background-color: rgb(244, 244, 244);

        .circle {
            width: 300px;
            height: 300px;
            background: rgb(0, 58, 193);
            background-size: cover;
            background-position: center;
            border-radius: 100%;
            display: inline-block;


            &.loading {
                animation: identifier 2s infinite;
            }

            @keyframes identifier {
                0% {
                    transform: scale(1);
                }
                50% {
                    transform: scale(1.1);
                }
                100% {
                    transform: scale(1);
                }
            }
        }

        .blur_overlay {
            width: 100%;
            height: 50%;
            position: absolute;
            background-color: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
            bottom: 0;
        }
    }

}

</style>
