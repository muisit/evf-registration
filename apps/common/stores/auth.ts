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
    const registrationUser = ref(false);
    const codeUser = ref(false);
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
        return me().then((data:MeSchema) => {
            hasLoaded('me');
            token.value = data.token || '';
            if (data.status && data.username && data.username.length) {
                isGuest.value = false;
                userName.value = data.username;
                credentials.value = data.credentials || [];
                codeUser.value = credentials.value.includes('code');
                registrationUser.value = credentials.value.includes('user');
                if (data.countryId) countryId.value = data.countryId;
                if (data.eventId) eventId.value = data.eventId;
                console.log(data);
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

    function isRegistrationUser() {
        return credentials.value.includes('user');
    }

    function isCodeUser() {
        return credentials.value.includes('code');
    }

    function isSysop(type:string = 'user') {
        return credentials.value.includes('sysop') && credentials.value.includes(type);
    }

    function isHod(type:string = 'user') {
        return credentials.value.includes('hod') && credentials.value.includes('user');
    }

    function isSuperHod(type:string = 'user') {
        if (credentials.value.includes("superhod") && credentials.value.includes('user')) return true;
    }

    function isHodFor(type:string = 'user') {
        if (isSuperHod(type)) return true;
        return credentials.value.includes('hod:' + countryId.value) && credentials.value.includes('user');
    }

    function isOrganisation(eid?:number|null|undefined, type:string = 'user') {
        if (!eid) eid = eventId.value;
        return (credentials.value.includes('organisation:' + eid) && credentials.value.includes(type)) || isSysop(type );
    }

    function isOrganiser(eid?:number|null|undefined, type:string = 'user') {
        if (!eid) eid = eventId.value;
        return credentials.value.includes('organiser:' + eid)  && credentials.value.includes(type);
    }

    function isRegistrar(eid?:number|null|undefined, type:string = 'user') {
        if (!eid) eid = eventId.value;
        return credentials.value.includes('registrar:' + eid) && credentials.value.includes(type);
    }

    function isCashier(eid?:number|null|undefined, type:string = 'user') {
        if (!eid) eid = eventId.value;
        return credentials.value.includes('cashier:' + eid) && credentials.value.includes('user');
    }

    function isCheckin(eid?:number|null|undefined, type:string = 'user') {
        if (!eid) eid = eventId.value;
        return credentials.value.includes('checkin:' + eid) && credentials.value.includes('code');
    }

    function isCheckout(eid?:number|null|undefined, type:string = 'user') {
        if (!eid) eid = eventId.value;
        return credentials.value.includes('checkout:' + eid) && credentials.value.includes('code');
    }

    function isAccreditor(eid?:number|null|undefined, type:string = 'user') {
        if (!eid) eid = eventId.value;
        return credentials.value.includes('accreditation:' + eid) && credentials.value.includes(type);
    }

    function isDT(eid?:number|null|undefined, type:string = 'user') {
        if (!eid) eid = eventId.value;
        return credentials.value.includes('dt:' + eid) && credentials.value.includes('code');
    }

    function canSwitchCountry(eid?:number|null|undefined, type:string = 'user') {
        if (!eid) eid = eventId.value;
        return isSysop(type) || isOrganiser(eid, type) || isRegistrar(eid, type) || isSuperHod(type);
    }

    function canRegister(eid?:number|null|undefined, type:string = 'user') {
        return isSysop(type) || isOrganiser(eid, type) || isRegistrar(eid, type);
    }

    function canCashier(eid?:number|null|undefined, type:string = 'user') {
        return isSysop(type) || isOrganiser(eid, type) || isCashier(eid, type);
    }

    return {
        userName, isGuest, codeUser, registrationUser, token, credentials, countryId, eventId,
        isLoading, hasLoaded, isCurrentlyLoading, isLoadingData,
        sendMe, logIn, logOut,
        isRegistrationUser, isCodeUser,
        isSysop, isHod, isSuperHod, isHodFor, isOrganisation, isOrganiser, isRegistrar, isCashier, isAccreditor,
        isCheckin, isCheckout, isDT,
        canRegister, canCashier, canSwitchCountry
    }
})
