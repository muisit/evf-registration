import { ref } from 'vue'
import type { Ref } from 'vue';
import { defineStore } from 'pinia'

interface RouteAfterLogin {
    [key:string]: any;
}

export const useAuthStore = defineStore('auth', () => {
    const userName = ref('');
    const userId = ref(-1);
    const isGuest = ref(true);
    const token = ref('');
    const routeAfterLogin:Ref<RouteAfterLogin> = ref({});

    return {
        userName, userId, isGuest, token, routeAfterLogin
    }
})
