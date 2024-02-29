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

    function subscribeToCheckin(cb:Function)
    {
        manager.private('checkin.' + authStore.eventId)
            .listen('CheckinEvent', (e) => {
                cb('CheckinEvent', e);
            })
            .listen('ProcessStartEvent', (e) => {
                cb('ProcessStartEvent', e);
            })
            .listen('ProcessEndEvent', (e) => {
                cb('ProcessEndEvent', e);
            })
            .listen('CheckoutEvent', (e) => {
                cb('CheckoutEvent', e);
            });
    }

    function subscribeToCheckout(cb:Function)
    {
        manager.private('checkout.' + authStore.eventId)
            .listen('CheckinEvent', (e) => {
                cb('CheckinEvent', e);
            })
            .listen('ProcessStartEvent', (e) => {
                cb('ProcessStartEvent', e);
            })
            .listen('ProcessEndEvent', (e) => {
                cb('ProcessEndEvent', e);
            })
            .listen('CheckoutEvent', (e) => {
                cb('CheckoutEvent', e);
            });
    }

    function subscribeToDt(cb:Function)
    {
        manager.private('dt.' + authStore.eventId)
            .listen('CheckinEvent', (e) => {
                cb('CheckinEvent', e);
            })
            .listen('AccreditationHandoutEvent', (e) => {
                cb('AccreditationHandoutEvent', e);
            })
            .listen('CheckoutEvent', (e) => {
                cb('CheckoutEvent', e);
            });
    }

    function unsubscribe(channel:string)
    {
        manager.leaveChannel(channel + '.' + authStore.eventId);
    }

    return {
        unsubscribe, subscribeToCheckin, subscribeToCheckout, subscribeToDt
    }
});
