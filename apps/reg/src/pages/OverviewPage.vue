<script lang="ts" setup>
import { ref, watch } from 'vue';
import { useAuthStore } from '../../../common/stores/auth';
import { useDataStore } from '../stores/data';

const auth = useAuthStore();
const data = useDataStore();

watch (
    () => data.currentEvent,
    () => {
        console.log('currentEvent changed, getting overview from page');
        data.getOverview(data.currentEvent.id);
    },
    { immediate: true }
);

function canSeeOrgOverview()
{
    return auth.isSysop() || auth.isOrganisation(data.currentEvent.id);
}

import EventOverview from '../components/overview/EventOverview.vue';
import OrgOverview from '../components/overview/OrgOverview.vue';
</script>
<template>
    <div class="overview-page">
        <EventOverview />
        <OrgOverview v-if="canSeeOrgOverview()" />
    </div>
</template>