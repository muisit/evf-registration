<script lang="ts" setup>
import { watch } from 'vue';
import { useAuthStore } from '../../../common/stores/auth';
import { useDataStore } from '../stores/data';
const props = defineProps<{
    visible:boolean;
}>();
const emits = defineEmits(['changeTab']);

const auth = useAuthStore();
const data = useDataStore();

watch (
    () => [props.visible, data.currentEvent.id, data.currentCountry.id],
    (nw, old) => {
        if (props.visible) {
            console.log('loading overview due to change', nw, old);
            auth.isLoading = true;
            data.getOverview().then(() => { auth.isLoading = false; });
        }
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
        <EventOverview @change-tab="(e) => $emit('changeTab', e)"/>
        <OrgOverview v-if="canSeeOrgOverview()" />
    </div>
</template>