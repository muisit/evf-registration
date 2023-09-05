import { ref } from 'vue'
import { defineStore } from 'pinia'
import { me } from '../api/me';
import { login } from '../api/login';
import { logout } from '../api/logout';

export const useAuthStore = defineStore('auth', () => {
    const userName = ref('');
    const isGuest = ref(true);
    const token = ref('');

    function sendMe() {
        const self = this;
        me().then((data) => {
            self.token = data.token || '';
            if (data.status && data.username && data.username.length) {
                self.isGuest = false;
                self.userName = data.username;
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

    return {
        userName, isGuest, token,
        sendMe, logIn, logOut,
    }
})
