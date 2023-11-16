import { Ref, ref } from 'vue'
import { defineStore } from 'pinia'
import { me } from '../api/auth/me';
import { login } from '../api/auth/login';
import { logout } from '../api/auth/logout';

export const useAuthStore = defineStore('auth', () => {
    const userName = ref('');
    const isGuest = ref(true);
    const token = ref('');
    const credentials:Ref<Array<string>> = ref([]);
    const countryId = ref(0);
    const eventId = ref(0);

    function sendMe() {
        me().then((data) => {
            token.value = data.token || '';
            if (data.status && data.username && data.username.length) {
                isGuest.value = false;
                userName.value = data.username;
                credentials.value = data.credentials || [];
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

    function isHodFor() {
        if (isSuperHod()) return true;
        return credentials.value.includes('hod:' + countryId.value);
    }

    function isOrganisation() {
        return credentials.value.includes('organisation:' + eventId.value);
    }

    function isOrganiser() {
        return credentials.value.includes('organiser:' + eventId.value);
    }

    function isRegistrar() {
        return credentials.value.includes('registrar:' + eventId.value);
    }

    function isCashier() {
        return credentials.value.includes('cashier:' + eventId.value);
    }

    function isAccreditor() {
        return credentials.value.includes('accreditation:' + eventId.value);
    }

    return {
        userName, isGuest, token, credentials, countryId, eventId,
        sendMe, logIn, logOut,
        isSysop, isHod, isSuperHod, isHodFor, isOrganisation, isOrganiser, isRegistrar, isCashier, isAccreditor,
    }
})
