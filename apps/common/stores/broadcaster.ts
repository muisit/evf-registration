import type { Ref } from 'vue';
import { ref, watch } from 'vue';
import { defineStore } from 'pinia'
import { broadcaster } from '../api/broadcaster';
import { useAuthStore } from './auth';
import Echo from 'laravel-echo';

export const useBroadcasterStore = defineStore('broadcaster', () => {

    const authStore = useAuthStore();
    let manager:any = {};

    watch(() => authStore.token,
        (nw) => {
            // need this for the csrf token in the Echo broadcast connector
            if (typeof window !== 'undefined') {
                let wndw:any = window;
                if (!wndw.Laravel) {
                    wndw.Laravel = {};
                }
                wndw.Laravel.csrfToken = nw;
            }
        },
        { immediate: true }
    );

    // initialise the broadcaster
    manager = broadcaster();
    console.log(manager);

    function subscribeToCheckin(cb:Function)
    {
        console.log(manager);
        manager.private('checkin.' + authStore.eventId)
            .listen('CheckinEvent', (e) => {
                cb('CheckinEvent', e);
            });
    }

    return {
        subscribeToCheckin
    }
});
