import { ref } from 'vue'
import type { Ref } from 'vue';
import { defineStore } from 'pinia'
import { me } from '../api/auth/me';
import { login } from '../api/auth/login';
import { logout } from '../api/auth/logout';
import type { MeSchema } from '../api/schemas/me';

export const useAuthStore = defineStore('auth', () => {
    const userName = ref('');
    const isGuest = ref(true);
    const token = ref('');
    const credentials:Ref<Array<string>> = ref([]);
    const countryId = ref(0);
    const eventId = ref(0);
    const isLoadingData:Ref<string[]> = ref([]);

    function isLoading(section:string)
    {
        if (!isLoadingData.value.includes(section)) {
            isLoadingData.value.push(section);
        }
    }

    function hasLoaded(section:string)
    {
        isLoadingData.value = isLoadingData.value.filter((s) => s != section);
    }

    function isCurrentlyLoading()
    {
        return isLoadingData.value.length > 0;
    }

    function sendMe() {
        isLoading('me');
        me().then((data:MeSchema) => {
            hasLoaded('me');
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
        isLoading('login');
        return login(username, password)
            .finally(() => {
                hasLoaded('login');
                sendMe();
            });
    }

    function logOut() {
        isLoading('logout');
        return logout()
            .then(() => {
                hasLoaded('logout');
                isGuest.value = true;
                userName.value = '';
                sendMe();
            })
            .catch(() => {
                hasLoaded('logout');
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

    function isOrganisation(eid?:number|null|undefined) {
        if (!eid) eid = eventId.value;
        return credentials.value.includes('organisation:' + eid) || isSysop();
    }

    function isOrganiser(eid?:number|null|undefined) {
        if (!eid) eid = eventId.value;
        return credentials.value.includes('organiser:' + eid);
    }

    function isRegistrar(eid?:number|null|undefined) {
        if (!eid) eid = eventId.value;
        return credentials.value.includes('registrar:' + eid);
    }

    function isCashier(eid?:number|null|undefined) {
        if (!eid) eid = eventId.value;
        return credentials.value.includes('cashier:' + eid);
    }

    function isAccreditor(eid?:number|null|undefined) {
        if (!eid) eid = eventId.value;
        return credentials.value.includes('accreditation:' + eid);
    }

    function canSwitchCountry(eid?:number|null|undefined) {
        if (!eid) eid = eventId.value;
        return isSysop() || isOrganiser(eid) || isRegistrar(eid) || isSuperHod();
    }

    function canRegister(eid?:number|null|undefined) {
        return isSysop() || isOrganiser(eid) || isRegistrar(eid);
    }

    function canCashier(eid?:number|null|undefined) {
        return isSysop() || isOrganiser(eid) || isCashier(eid);
    }

    return {
        userName, isGuest, token, credentials, countryId, eventId,
        isLoading, hasLoaded, isCurrentlyLoading, isLoadingData,
        sendMe, logIn, logOut,
        isSysop, isHod, isSuperHod, isHodFor, isOrganisation, isOrganiser, isRegistrar, isCashier, isAccreditor,
        canRegister, canCashier, canSwitchCountry
    }
})
