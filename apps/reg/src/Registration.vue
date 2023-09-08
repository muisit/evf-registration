<script lang="ts" setup>
import { watch } from 'vue';
import { useAuthStore } from '../../common/stores/auth';
import { useDataStore } from './stores/data';

const authStore = useAuthStore();
const dataStore = useDataStore();

// this function calls for a new token and status update if
// we determined the current user is a guest at some point,
// for example due to network issues
watch(
    () => authStore.isGuest,
    (nw) => {
        if(nw) {
            authStore.sendMe();
        }
        else {
            dataStore.getBasicData().then(() => dataStore.getEvents());
        }
    },
    { immediate: true }
);

import DashboardView from './pages/DashboardView.vue';
</script>
<template>
    <DashboardView/>
</template>
