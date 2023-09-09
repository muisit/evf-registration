import { ref } from 'vue'
import { defineStore } from 'pinia'
import { me } from '../api/auth/me';
import { login } from '../api/auth/login';
import { logout } from '../api/auth/logout';

export const useAuthStore = defineStore('auth', () => {
    const userName = ref('');
    const isGuest = ref(true);
    const token = ref('');
    const credentials = ref([]);
    const countryId = ref(0);
    const country = ref({});

    function sendMe() {
        me().then((data) => {
            token.value = data.token || '';
            if (data.status && data.username && data.username.length) {
                isGuest.value = false;
                userName.value = data.username;
                credentials.value = data.credentials;
                if (data.countryId) countryId.value = data.countryId;
            }
        });
    }

    function logIn(username:string, password: string) {
        return login(username, password)
            .finally(() => {
                sendMe();
            });
    }

    function logOut() {
        logout()
            .then(() => {
                isGuest.value = true;
                userName.value = '';
                sendMe();
            })
            .catch(() => {
                sendMe();
            });
    }

    function isSysop() {
        return credentials.value.includes('sysop');
    }

    function isHod() {
        return credentials.value.includes('hod');
    }

    function isSuperHod() {
        if (credentials.value.includes("superhod")) return true;
    }

    function isHodFor(countryId:number) {
        if (isSuperHod()) return true;
        return credentials.value.includes('hod:' + countryId);
    }

    function isOrganisation(eventId:number) {
        return credentials.value.includes('organisation:' + eventId);
    }

    function isOrganiser(eventId:number) {
        return credentials.value.includes('organiser:' + eventId);
    }

    function isRegistrar(eventId:number) {
        return credentials.value.includes('registrar:' + eventId);
    }

    function isCashier(eventId:number) {
        return credentials.value.includes('cashier:' + eventId);
    }

    function isAccreditor(eventId:number) {
        return credentials.value.includes('accreditation:' + eventId);
    }

    return {
        userName, isGuest, token, credentials, countryId, country,
        sendMe, logIn, logOut,
        isSysop, isHod, isSuperHod, isHodFor, isOrganisation, isOrganiser, isRegistrar, isCashier, isAccreditor,
    }
})
