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

    function sendMe() {
        const self = this;
        me().then((data) => {
            self.token = data.token || '';
            if (data.status && data.username && data.username.length) {
                self.isGuest = false;
                self.userName = data.username;
                self.credentials = data.credentials;
            }
        });
    }

    function logIn(username:string, password: string) {
        const self = this;
        return login(username, password)
            .finally(() => {
                self.sendMe();
            });
    }

    function logOut() {
        const self = this;
        logout()
            .then(() => {
                self.isGuest = true;
                self.userName = '';
                self.sendMe();
            })
            .catch(() => {
                self.sendMe();
            });
    }

    function isSysop() {
        return this.credentials.includes('sysop');
    }

    function isHod() {
        return this.credentials.includes('hod');
    }

    function isHodFor(countryId:number) {
        if (this.credentials.includes("superhod")) return true;
        return this.credentials.includes('hod:' + countryId);
    }

    function isOrganisation(eventId:number) {
        return this.credentials.includes('organisation:' + eventId);
    }

    function isOrganiser(eventId:number) {
        return this.credentials.includes('organiser:' + eventId);
    }

    function isRegistrar(eventId:number) {
        return this.credentials.includes('registrar:' + eventId);
    }

    function isCashier(eventId:number) {
        return this.credentials.includes('cashier:' + eventId);
    }

    function isAccreditor(eventId:number) {
        return this.credentials.includes('accreditation:' + eventId);
    }

    return {
        userName, isGuest, token, credentials,
        sendMe, logIn, logOut,
        isSysop, isHod, isHodFor, isOrganisation, isOrganiser, isRegistrar, isCashier, isAccreditor,
    }
})
