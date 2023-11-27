<script lang="ts" setup>
import { watch } from 'vue';
import { useAuthStore } from '../../common/stores/auth';
import { useDataStore } from './stores/data';

const authStore = useAuthStore();
const dataStore = useDataStore();

// This is the place to deal with changes in the login state
// of the user
// We make sure we have retrieved all basic data
// Then we set the default country
// And we retrieve all events this user has access to
watch(
    () => authStore.isGuest,
    (nw) => {
        if(nw) {
            authStore.sendMe();
        }
        else {
            dataStore.getBasicData().then(
                () => {
                    if (authStore.countryId && dataStore.countriesById['c' + authStore.countryId] && !authStore.canSwitchCountry()) {
                        dataStore.setCountry(authStore.countryId);
                    }
                    dataStore.getEvents();
                });
        }
    },
    { immediate: true }
);

import DashboardView from './pages/DashboardView.vue';
</script>
<template>
    <DashboardView/>
</template>
